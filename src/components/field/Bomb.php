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
     * Bomb constructor.
     * @param int $x
     * @param int $y
     * @param int $explosionSpread
     */
    public function __construct($x, $y, $explosionSpread)
    {
        parent::__construct($x, $y);
        $this->explosionSpread = $explosionSpread;
        $this->planted = milliseconds();
    }

    public function backup()
    {
        return array_merge(parent::backup(), [
            'explosionSpread' => $this->explosionSpread,
            'planted' => $this->planted,
        ]);
    }

    /**
     * @param array $data
     * @return Bomb
     */
    public static function restore($data)
    {
        $bomb = new self($data['x'], $data['y'], $data['explosionSpread']);
        $bomb->planted = $data['planted'];
        return $bomb;
    }

    /**
     * @return boolean
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
        return BaseInCell::BASE_PRIORITY - 1;
    }

    /**
     * @return float
     */
    public function getPlanted()
    {
        return $this->planted;
    }

    public function explodeNow()
    {
        $this->planted = 0;
    }

    /**
     * @return int
     */
    public function getExplosionSpread()
    {
        return $this->explosionSpread;
    }

}
