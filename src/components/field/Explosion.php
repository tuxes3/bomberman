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
 * Class Explosion
 * @package bomberman\components\field
 */
class Explosion extends BaseInCell
{

    /**
     * @var int
     */
    private $exploded;

    /**
     * @var TimerInterface
     */
    protected $timer;

    public function __construct($x, $y)
    {
        parent::__construct($x, $y);
        $this->exploded = milliseconds();
    }

    /**
     * @return array
     */
    public function backup()
    {
        return array_merge(parent::backup(), [
            'exploded' => $this->exploded,
        ]);
    }

    /**
     * @param array $data
     * @return Explosion
     */
    public static function restore($data)
    {
        $explosion = new self($data['x'], $data['y']);
        $explosion->exploded = $data['exploded'];
        return $explosion;
    }

    /**
     * @return bool
     */
    public function canPlayerEnter()
    {
        return true;
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
        return BaseInCell::BASE_PRIORITY + 1;
    }

    /**
     * @return float
     */
    public function getExploded()
    {
        return $this->exploded;
    }

    /**
     * @return TimerInterface
     */
    public function getTimer()
    {
        return $this->timer;
    }

    /**
     * @param TimerInterface $timer
     */
    public function setTimer($timer)
    {
        $this->timer = $timer;
    }

    /**
     * @return bool
     */
    public function canBombEnter()
    {
        return true;
    }

}
