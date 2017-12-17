<?php
/*
 * This file is part of the bomberman project.
 *
 * @author Nicolo Singer tuxes3@outlook.com
 * @author Lukas Müller computer_bastler@hotmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bomberman\components\field;
use React\EventLoop\Timer\TimerInterface;

/**
 * Class Bomb
 * @package bomberman\components\field
 */
class Bomb extends BaseInCell
{

    /**
     * @var int $explosionSpread
     */
    protected $explosionSpread;

    /**
     * @var int
     */
    protected $planted;

    /**
     * @var string
     */
    protected $plantedByUuid;

    /**
     * @var TimerInterface
     */
    protected $timer;

    /**
     * @var boolean
     */
    protected $moving = false;

    /**
     * Bomb constructor.
     * @param int $x
     * @param int $y
     * @param int $explosionSpread
     * @param string $plantedByUuid
     */
    public function __construct($x, $y, $explosionSpread, $plantedByUuid)
    {
        parent::__construct($x, $y);
        $this->explosionSpread = $explosionSpread;
        $this->planted = milliseconds();
        $this->plantedByUuid = $plantedByUuid;
    }

    /**
     * @return array
     */
    public function backup()
    {
        return array_merge(parent::backup(), [
            'explosionSpread' => $this->explosionSpread,
            'planted' => $this->planted,
            'plantedByUuid' => $this->plantedByUuid,
        ]);
    }

    /**
     * @param array $data
     * @return Bomb
     */
    public static function restore($data)
    {
        $bomb = new self($data['x'], $data['y'], $data['explosionSpread'], $data['plantedByUuid']);
        $bomb->planted = $data['planted'];
        return $bomb;
    }

    /**
     * @return boolean
     */
    public function canPlayerEnter()
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function blocksExplosion()
    {
        return false;
    }

    /**
     * @return int
     */
    public function getDisplayPriority()
    {
        return BaseInCell::BASE_PRIORITY - 1;
    }

    /**
     * @return float
     */
    public function getPlanted()
    {
        return $this->planted;
    }

    /**
     *
     */
    public function explodeNow()
    {
        if (is_null($this->timer)) {
            return;
        }
        $this->planted = 0;
        call_user_func($this->timer->getCallback());
    }

    /**
     * @return int
     */
    public function getExplosionSpread()
    {
        return $this->explosionSpread;
    }

    /**
     * @return string
     */
    public function getPlantedByUuid()
    {
        return $this->plantedByUuid;
    }

    /**
     * @param TimerInterface $timer
     */
    public function setTimer($timer)
    {
        $this->timer = $timer;
    }

    /**
     * @return TimerInterface
     */
    public function getTimer()
    {
        return $this->timer;
    }

    /**
     * @return bool
     */
    public function canBombEnter()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isMoving()
    {
        return $this->moving;
    }

    /**
     * @param bool $moving
     */
    public function setMoving($moving)
    {
        $this->moving = $moving;
    }

}
