<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <https://github.com/baleen/migrations>.
 */

namespace Baleen;

use Baleen\Exception\BaleenException;
use Baleen\Exception\MigrationException;
use Baleen\Exception\MigrationMissingException;
use Baleen\Migration\Command\MigrationBusFactory;
use Baleen\Migration\Command\MigrateCommand;
use Baleen\Migration\MigrationInterface;
use Baleen\Migration\MigrateOptions;
use Baleen\Timeline\TimelineInterface;
use Baleen\Version\Collection;
use Baleen\Version\Comparator\DefaultComparator;
use League\Tactician\CommandBus;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class Timeline implements TimelineInterface
{

    protected $allowedDirections;

    /** @var Collection */
    protected $versions;

    /** @var callable */
    protected $comparator;

    /** @var CommandBus */
    protected $migrationBus;

    /**
     * @param array|Collection $versions
     * @param callable $comparator
     */
    public function __construct($versions, callable $comparator = null)
    {
        $this->migrationBus = MigrationBusFactory::create();

        if (is_array($versions)) {
            $versions = new Collection($versions);
        }
        if (null === $comparator) {
            $comparator = new DefaultComparator();
        }
        $versions->sortWith($comparator);
        $this->comparator = $comparator;
        $this->versions = $versions;
    }

    /**
     * @param Version|string $goalVersion
     * @param MigrateOptions $options
     * @throws MigrationMissingException
     */
    public function upTowards($goalVersion, MigrateOptions $options = null)
    {
        if (null === $options) {
            $options = new MigrateOptions(MigrateOptions::DIRECTION_UP);
        }
        $goalVersion = $this->versions->getOrException($goalVersion);
        $options->setDirection(MigrateOptions::DIRECTION_UP); // make sure its right
        foreach ($this->versions as $version) {
            if ($options->isForced() || !$version->isMigrated()) {
                $migration = $version->getMigration();
                if (null === $migration) {
                    throw new MigrationMissingException('Migration object missing for registered version "%s".', $version->getId());
                }
                $this->doRun($migration, $options);
                $version->setMigrated(true); // won't get executed if an exception is thrown
            }
            $goalReached = call_user_func($this->comparator, $goalVersion, $version) === 0;
            if ($goalReached) {
                break;
            }
        }
    }

    /**
     * @param Version|string $goalVersion
     * @param MigrateOptions $options
     * @throws \Exception
     */
    public function downTowards($goalVersion, MigrateOptions $options = null)
    {
        if (null === $options) {
            $options = new MigrateOptions(MigrateOptions::DIRECTION_DOWN);
        }
        $goalVersion = $this->versions->getOrException($goalVersion);
        $options->setDirection(MigrateOptions::DIRECTION_DOWN); // make sure its right
        $reversed = $this->versions->getReverse();
        foreach($reversed as $version) {
            /** @var Version $version */
            if ($options->isForced() || $version->isMigrated()) {
                if (null === $version->getMigration()) {
                    throw new MigrationException('Migration object missing for registered version "%s".', $version->getId());
                }
                $this->doRun($version->getMigration(), $options);
                $version->setMigrated(false); // won't get executed if an exception is thrown
            }
            $goalReached = call_user_func($this->comparator, $goalVersion, $version) === 0;
            if ($goalReached) {
                break;
            }
        }
    }

    /**
     * Runs migrations up/down so that all versions *before and including* the specified version are "up" and
     * all versions *after* the specified version are "down".
     *
     * @param $goalVersion
     * @param \Baleen\Migration\MigrateOptions $options
     * @return mixed
     */
    public function goTowards($goalVersion, MigrateOptions $options = null)
    {
        if (null === $options) {
            $options = new MigrateOptions(MigrateOptions::DIRECTION_UP);
        }
        $this->versions->rewind();
        $this->upTowards($goalVersion, $options);
        $this->versions->next(); // advance to the next element...
        $newGoal = $this->versions->current(); // ... and make it the goal for downTowards
        if ($newGoal !== false) { // are we at the end of the array?
            $this->downTowards($newGoal, $options);
        }
    }

    /**
     * @param \Baleen\Version $version
     * @param MigrateOptions $options
     * @throws MigrationException
     */
    public function runSingle($version, MigrateOptions $options)
    {
        switch ($options->getDirection()) {
            case MigrateOptions::DIRECTION_UP:
                if (!$options->isForced() && $version->isMigrated()) {
                    throw new MigrationException(
                        sprintf("Cowardly refusing to run up() on a version that has already been migrated (%s).", $version->getId())
                    );
                }
                break;

            case MigrateOptions::DIRECTION_DOWN:
                if (!$options->isForced() && !$version->isMigrated()) {
                    throw new MigrationException(
                        sprintf("Cowardly refusing to run down() on a version that hasn't been migrated yet (%s).", $version->getId())
                    );
                }
                break;
            default:
        }
        $this->doRun($version->getMigration(), $options);
    }

    /**
     * @param MigrationInterface $migration
     * @param MigrateOptions $options
     * @return bool
     */
    protected function doRun(MigrationInterface $migration, MigrateOptions $options)
    {
        $command = new MigrateCommand($migration, $options);
        $this->migrationBus->handle($command);
    }
}
