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
 * Class ExplosionRadiusItem
 * @package bomberman\components\field
 */
class ExplosionRadiusItem extends BaseItem
{

    /**
     * @param Player $player
     */
    public function consume(Player $player)
    {
        $player->incrementExplosionSpread();
    }

}
