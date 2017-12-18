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
 * Class MoveBombItem
 * @package bomberman\components\field
 */
class MoveBombItem extends BaseItem
{

    /**
     * @param Player $player
     */
    public function consume(Player $player)
    {
        $player->setCanMoveBombs();

        // if the player can already kick bombs, then the powerup would be useless
        // so we make him faster if he gets it again :)
        if($player->isCanMoveBombs()){
            $player->decreaseMovementSpeed();
        }
    }

}
