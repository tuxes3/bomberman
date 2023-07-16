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
 * Class Block
 * @package bomberman\components\field
 */
class Block extends BaseInCell
{
    /**
     * @param array $data
     * @return self
     */
    public static function restore($data)
    {
        return new self($data['x'], $data['y']);
    }

    public function canPlayerEnter(): bool
    {
        return false;
    }

    public function blocksExplosion(): bool
    {
        return true;
    }

    /**
     * @return int
     */
    public function getDisplayPriority()
    {
        return self::BASE_PRIORITY;
    }

    public function canBombEnter(): bool
    {
        return false;
    }
}
