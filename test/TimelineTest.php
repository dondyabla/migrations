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
 * <http://www.doctrine-project.org>.
 */

namespace BaleenTest;

use Baleen\Version as V;
use Baleen\Version\Comparator\DefaultComparator;
use Mockery as m;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class TimelineTest extends BaseTestCase
{

    /**
     * @var \Mockery\Mock
     */
    protected $migrationMock;

    /**
     * @param array $versions
     * @param null $callable
     * @return m\Mock|\Baleen\Timeline
     */
    public function getInstance($versions = [], $callable = null)
    {
        if (null === $callable) {
            $callable = new DefaultComparator();
        }
        return m::mock('Baleen\Timeline', [$versions, $callable])->makePartial()->shouldAllowMockingProtectedMethods();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('Baleen\Timeline\TimelineInterface', $this->getInstance());
    }

    public function arrayToVersions(array $array)
    {
        $result = [];
        foreach ($array as $id) {
            $result[$id] = new V($id);
        }
        return $result;
    }

    /**
     * @param $versions
     * @param $goal
     *
     * @dataProvider versionsAndGoalsProvider
     */
    public function testUpTowards($versions, $goal)
    {
        $instance = $this->getInstance($versions);
        $instance->upTowards($goal);

        $versions = $this->getInstanceVersions($instance);
        foreach ($versions as $version) {
            /** @var V $version */
            $this->assertTrue($version->isMigrated(), sprintf('Expected version %s to be migrated', $version->getId()));
            if ($version->getId() == $goal) {
                break;
            }
        }
    }

    /**
     * @param $versions
     * @param $goal
     *
     * @dataProvider versionsAndGoalsProvider
     */
    public function testDownTowards($versions, $goal)
    {
        $instance = $this->getInstance($versions);
        $instance->downTowards($goal);

        $versions = $this->getInstanceVersions($instance);
        $versions = array_reverse($versions);
        foreach ($versions as $version) {
            /** @var V $version */
            $this->assertFalse($version->isMigrated(), sprintf('Expected version %s not to be migrated', $version->getId()));
            if ($version->getId() == $goal) {
                break;
            }
        }
    }

    /**
     * @param $versions
     * @param $goal
     *
     * @dataProvider versionsAndGoalsProvider
     */
    public function testGoTowards($versions, $goal)
    {
        $instance = $this->getInstance($versions);
        $instance->goTowards($goal);

        $afterGoal = false;
        $versions = $this->getInstanceVersions($instance);
        foreach ($versions as $version) {
            /** @var V $version */
            if (!$afterGoal) {
                $this->assertTrue($version->isMigrated(), sprintf('Expected version %s to be migrated', $version->getId()));
            } else {
                $this->assertFalse($version->isMigrated(), sprintf('Expected version %s not to be migrated', $version->getId()));
            }
            if ($version->getId() == $goal) {
                $afterGoal = true;
            }
        }
    }

    public function getAllMigratedVersionsFixture()
    {
        return $this->getFixtureFor([
            ['id' => 1, 'migrated' => true],
            ['id' => 2, 'migrated' => true],
            ['id' => 3, 'migrated' => true],
            ['id' => 4, 'migrated' => true],
            ['id' => 5, 'migrated' => true],
            ['id' => 6, 'migrated' => true],
            ['id' => 7, 'migrated' => true],
            ['id' => 8, 'migrated' => true],
            ['id' => 9, 'migrated' => true],
            ['id' => 10, 'migrated' => true],
            ['id' => 11, 'migrated' => true],
            ['id' => 12, 'migrated' => true],
        ]);
    }

    public function getNoMigratedVersions()
    {
        return $this->getFixtureFor([
            ['id' => 1, 'migrated' => false],
            ['id' => 2, 'migrated' => false],
            ['id' => 3, 'migrated' => false],
            ['id' => 4, 'migrated' => false],
            ['id' => 5, 'migrated' => false],
            ['id' => 6, 'migrated' => false],
            ['id' => 7, 'migrated' => false],
            ['id' => 8, 'migrated' => false],
            ['id' => 9, 'migrated' => false],
            ['id' => 10, 'migrated' => false],
            ['id' => 11, 'migrated' => false],
            ['id' => 12, 'migrated' => false],
        ]);
    }

    public function getMixedVersions()
    {
        return $this->getFixtureFor([
            ['id' => 1, 'migrated' => true],
            ['id' => 2, 'migrated' => false],
            ['id' => 3, 'migrated' => true],
            ['id' => 4, 'migrated' => true],
            ['id' => 5, 'migrated' => false],
            ['id' => 6, 'migrated' => false],
            ['id' => 7, 'migrated' => false],
            ['id' => 8, 'migrated' => true],
            ['id' => 9, 'migrated' => false],
            ['id' => 10, 'migrated' => true],
            ['id' => 11, 'migrated' => false],
            ['id' => 12, 'migrated' => false],
        ]);
    }

    /**
     * This fixture is meant to cover all use-cases.
     *
     * @return array
     */
    public function getFixtureFor(array $versions)
    {
        $migrationMock = m::mock('Baleen\Migration\MigrationInterface');
        $migrationMock->shouldReceive('up')->zeroOrMoreTimes();
        $migrationMock->shouldReceive('down')->zeroOrMoreTimes();
        $migrationMock->shouldReceive('abort')->zeroOrMoreTimes();
        $migrationMock->shouldReceive('setOptions')->zeroOrMoreTimes();
        $this->migrationMock = $migrationMock;
        return array_map(function ($arr) use (&$migrationMock) {
            $v = new V($arr['id']);
            $v->setMigrated($arr['migrated']);
            $v->setMigration($migrationMock);
            return $v;
        }, $versions);
    }

    public function versionsAndGoalsProvider()
    {
        return [
            [$this->getAllMigratedVersionsFixture(), 12],
            [$this->getAllMigratedVersionsFixture(), 1],
            [$this->getAllMigratedVersionsFixture(), 8],
            [$this->getAllMigratedVersionsFixture(), 9],
            [$this->getNoMigratedVersions(), 12],
            [$this->getNoMigratedVersions(), 1],
            [$this->getNoMigratedVersions(), 8],
            [$this->getNoMigratedVersions(), 9],
            [$this->getMixedVersions(), 12],
            [$this->getMixedVersions(), 1],
            [$this->getMixedVersions(), 8],
            [$this->getMixedVersions(), 9],
        ];
    }

    /**
     * @param $instance
     * @return mixed
     */
    protected function getInstanceVersions($instance)
    {
        $prop = new \ReflectionProperty($instance, 'versions');
        $prop->setAccessible(true);
        $versions = $prop->getValue($instance);
        return $versions->toArray();
    }
}
