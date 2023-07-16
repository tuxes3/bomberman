<?php

declare(strict_types=1);
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
 * Class BaseItem
 * @package bomberman\components\field
 */
abstract class BaseItem extends BaseInCell
{
    final public const ALL_IMPL = [
        BombItem::class,
        ShoeItem::class,
        ExplosionRadiusItem::class,
        MoveBombItem::class,
    ];

    /**
     * BaseItem constructor.
     * @param int $x
     * @param int $y
     * @param string $explosionId
     */
    final public function __construct(
        $x,
        $y,
        private $explosionId
    ) {
        parent::__construct($x, $y);
    }

    abstract public function consume(Player $player);

    /**
     * @return bool
     */
    public function canPlayerEnter()
    {
        return true;
    }

    /**
     * @return bool
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
        return BaseInCell::BASE_PRIORITY - 5;
    }

    /**
     * @param array $data
     * @return self|InCell
     */
    public static function restore($data)
    {
        return new static($data['x'], $data['y'], '');
    }

    /**
     * @return string
     */
    public function getExplosionId()
    {
        return $this->explosionId;
    }

    /**
     * @return boolean
     */
    public function canBombEnter()
    {
        return true;
    }
}
