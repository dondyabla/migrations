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

namespace Baleen\Migrations\Migration;

use Baleen\Migrations\Exception\InvalidArgumentException;

/**
 * @author Gabriel Somoza <gabriel@strategery.io>
 */
class MigrateOptions
{
    const DIRECTION_UP = 'up';
    const DIRECTION_DOWN = 'down';

    /**
     * @var array
     */
    protected $allowedDirections;

    /**
     * @var string
     */
    protected $direction;

    /**
     * @var bool
     */
    protected $forced;

    /**
     * @var bool
     */
    protected $dryRun;

    /**
     * @var array
     */
    protected $custom;

    /**
     * @param $direction
     * @param bool $forced
     * @param bool $dryRun
     * @param array $custom
     * @throws InvalidArgumentException
     */
    function __construct($direction, $forced = false, $dryRun = false, $custom = [])
    {
        $this->allowedDirections = [
            self::DIRECTION_UP,
            self::DIRECTION_DOWN,
        ];
        $this->setDirection($direction);
        $this->setForced($forced);
        $this->setDryRun($dryRun);
        $this->setCustom($custom);
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     * @throws InvalidArgumentException
     */
    public function setDirection($direction)
    {
        if (!in_array($direction, $this->allowedDirections)) {
            throw new InvalidArgumentException(
                sprintf('Unknown direction "%s". Valid options are "up" or "down".', $direction)
            );
        }
        $this->direction = $direction;
    }

    /**
     * @return boolean
     */
    public function isForced()
    {
        return $this->forced;
    }

    /**
     * @param boolean $forced
     */
    public function setForced($forced)
    {
        $this->forced = (bool)$forced;
    }

    /**
     * @return boolean
     */
    public function isDryRun()
    {
        return $this->dryRun;
    }

    /**
     * @param boolean $dryRun
     */
    public function setDryRun($dryRun)
    {
        $this->dryRun = (bool)$dryRun;
    }

    /**
     * @return array
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * @param array $custom
     */
    public function setCustom($custom)
    {
        $this->custom = $custom;
    }


}