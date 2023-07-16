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
 * Class ShoeItem
 * @package bomberman\components\field
 */
class ShoeItem extends BaseItem
{
    public function consume(Player $player)
    {
        $player->decreaseMovementSpeed();
    }
}
