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
 * Class FieldCell
 * @package bomberman\components\field
 */
class FieldCell implements \JsonSerializable
{

    /**
     * @var array|InCell[] $inCells
     */
    protected $inCells = [];

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'inCells' => $this->inCells,
        ];
    }

    /**
     * @return boolean
     */
    public function canPlayerEnter()
    {
        $canEnter = true;
        foreach ($this->inCells as $inCell) {
            $canEnter = $canEnter && $inCell->canPlayerEnter();
        }
        return $canEnter;
    }

    /**
     * @return boolean
     */
    public function blocksExplosion()
    {
        $blocks = false;
        foreach ($this->inCells as $inCell) {
            $blocks = $blocks || $inCell->blocksExplosion();
        }
        return $blocks;
    }

    /**
     * @param int $connId
     * @return Player|null
     */
    public function getPlayer($connId)
    {
        foreach ($this->inCells as $inCell) {
            if ($inCell instanceof Player && $inCell->getUuid() == $connId) {
                return $inCell;
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function backup()
    {
        $backup = [];
        foreach ($this->inCells as $inCell) {
            $backup[] = $inCell->backup();
        }
        return $backup;
    }

    /**
     * @param $id
     */
    public function removeById($id)
    {
        foreach ($this->inCells as $key => $inCell) {
            if ($inCell->getId() == $id) {
                unset ($this->inCells[$key]);
            }
        }
        $this->inCells = array_values($this->inCells);
    }

    /**
     * @return boolean if something changed
     */
    public function explode()
    {
        $changes = false;
        foreach ($this->inCells as $key => $inCell) {
            if ($inCell instanceof Player) {
                $inCell->setDead();
                $changes = true;
            } elseif ($inCell instanceof Explosion) {
            } elseif ($inCell instanceof Bomb) {
                $inCell->explodeNow();
            } else {
                unset($this->inCells[$key]);
                $changes = true;
            }
        }
        $this->inCells = array_values($this->inCells);
        return $changes;
    }

    /**
     * @return array|Bomb[]
     */
    public function getAllBombs()
    {
        $bombs = [];
        foreach ($this->inCells as $inCell) {
            if ($inCell instanceof Bomb) {
                $bombs[] = $inCell;
            }
        }
        return $bombs;
    }

    /**
     * @return array|Explosion[]
     */
    public function getAllExplosions()
    {
        $explosions = [];
        foreach ($this->inCells as $inCell) {
            if ($inCell instanceof Explosion) {
                $explosions[] = $inCell;
            }
        }
        return $explosions;
    }

    /**
     * return array|Player[]
     */
    public function getAllPlayers()
    {
        $players = [];
        foreach ($this->inCells as $inCell) {
            if ($inCell instanceof Player) {
                $players[] = $inCell;
            }
        }
        return $players;
    }

    /**
     * @param InCell $inCell
     */
    public function add(InCell $inCell)
    {
        $this->inCells[] = $inCell;
    }

}
