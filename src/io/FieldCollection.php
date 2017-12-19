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

namespace bomberman\io;

use bomberman\components\field\Bomb;
use bomberman\components\field\Explosion;
use bomberman\components\field\FieldCell;
use bomberman\components\field\Player;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class FieldCollection
 * @package bomberman\io
 */
class FieldCollection extends ArrayCollection
{

    /**
     * @param string $uuid
     * @return null|Player
     */
    public function findPlayerBySender($uuid)
    {
        $fieldCells = $this->filter(function (FieldCell $fieldCell) use ($uuid) {
            return null !== $fieldCell->getPlayer($uuid);
        });
        if ($fieldCells->count() > 0) {
            return $fieldCells->first()->getPlayer($uuid);
        }
        return null;
    }

    /**
     * @return array|Bomb[]
     */
    public function findBombs()
    {
        $bombs = [];
        /** @var FieldCell $fieldCell */
        foreach ($this as $fieldCell) {
            $bombs = array_merge($bombs, $fieldCell->getAllBombs());
        }
        return $bombs;
    }

    /**
     * @return array|Explosion[]
     */
    public function findExplosions()
    {
        $explosions = [];
        /** @var FieldCell $fieldCell */
        foreach ($this as $fieldCell) {
            $explosions = array_merge($explosions, $fieldCell->getAllExplosions());
        }
        return $explosions;
    }

    /**
     * @return array|Player[]
     */
    public function findPlayers()
    {
        $players = [];
        /** @var FieldCell $fieldCell */
        foreach ($this as $fieldCell) {
            $players = array_merge($players, $fieldCell->getAllPlayers());
        }
        return $players;
    }

    /**
     * @param string $uuid
     * @return array|Bomb[]
     */
    public function findBombsByPlanter($uuid)
    {
        $bombs = [];
        /** @var FieldCell $fieldCell */
        foreach ($this as $fieldCell) {
            $bombs = array_merge($bombs, $fieldCell->getAllBombsByPlanter($uuid));
        }
        return $bombs;
    }

    /**
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function filterContainsItem()
    {
        return $this->filter(function(FieldCell $fieldCell){
            return count($fieldCell->getAllItems()) > 0;
        });
    }

}
