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

class Block extends BaseInCell
{

    /**
     * @param array $data
     * @return Block
     */
    public static function restore($data)
    {
        return new self($data['x'], $data['y']);
    }

    /**
     * @return bool
     */
    public function canPlayerEnter()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function blocksExplosion()
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

    /**
     * @return bool
     */
    public function canBombEnter()
    {
        return false;
    }

}
