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
 * Class FieldCell
 * @package bomberman\components\field
 */
class FieldCell implements \JsonSerializable
{
    /**
     * @var array|InCell[]
     */
    protected $inCells = [];

    public function jsonSerialize(): array
    {
        return [
            'inCells' => $this->inCells,
        ];
    }

    /**
     * @param self $nextNextField
     * @return boolean
     */
    public function canPlayerEnter(Player $player, $nextNextField)
    {
        $canEnter = true;
        foreach ($this->inCells as $inCell) {
            if ($inCell instanceof Bomb && $player->isCanMoveBombs() && !is_null($nextNextField) && $nextNextField->canBombEnter()) {
                $canEnter = $canEnter && true;
            } else {
                $canEnter = $canEnter && $inCell->canPlayerEnter();
            }
        }
        return $canEnter;
    }

    /**
     * @return boolean
     */
    public function contains($id): bool
    {
        foreach ($this->inCells as $inCell) {
            if ($inCell->getId() === $id) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return boolean
     */
    public function canBombEnter()
    {
        $canEnter = true;
        foreach ($this->inCells as $inCell) {
            $canEnter = $canEnter && $inCell->canBombEnter();
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
            if (!$inCell instanceof Bomb && !$inCell instanceof Explosion) {
                $backup[] = $inCell->backup();
            }
        }
        return $backup;
    }

    public function removeById($id)
    {
        foreach ($this->inCells as $key => $inCell) {
            if ($inCell->getId() == $id) {
                unset($this->inCells[$key]);
            }
        }
        $this->inCells = array_values($this->inCells);
    }

    /**
     * @return boolean
     */
    public function consumeItem()
    {
        $consumed = false;
        foreach ($this->getAllItems() as $key => $item) {
            foreach ($this->getAllPlayers() as $player) {
                $item->consume($player);
                $consumed = true;
                unset($this->inCells[$key]);
                break;
            }
        }
        if ($consumed) {
            $this->inCells = array_values($this->inCells);
        }
        return $consumed;
    }

    /**
     * @param Explosion $explosion
     * @return boolean if something changed
     */
    public function explode($explosion)
    {
        $changes = false;
        $createItem = null;
        foreach ($this->inCells as $key => $inCell) {
            if ($inCell instanceof Player) {
                $inCell->setDead();
                $changes = true;
            } elseif ($inCell instanceof Explosion) {
            } elseif ($inCell instanceof FixBlock) {
            } elseif ($inCell instanceof Bomb) {
                $inCell->explodeNow();
            } elseif ($inCell instanceof Block) {
                $createItem = $inCell;
                unset($this->inCells[$key]);
                $changes = true;
            } elseif ($inCell instanceof BaseItem) {
                if ($inCell->getExplosionId() !== $explosion->getId()) {
                    unset($this->inCells[$key]);
                    $changes = true;
                }
            } else {
                unset($this->inCells[$key]);
                $changes = true;
            }
        }
        if ($createItem && random_int(1, 3) == 1) {
            $itemClass = BaseItem::ALL_IMPL[random_int(0, count(BaseItem::ALL_IMPL) - 1)];
            $this->inCells[] = new $itemClass($createItem->getX(), $createItem->getY(), $explosion->getId());
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
     * @param string $uuid
     * @return array
     */
    public function getAllBombsByPlanter($uuid)
    {
        $bombs = [];
        foreach ($this->getAllBombs() as $bomb) {
            if ($bomb->getPlantedByUuid() == $uuid) {
                $bombs[] = $bomb;
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
     * @return array|Player[]
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
     * @return array|BaseItem[]
     */
    public function getAllItems()
    {
        $items = [];
        foreach ($this->inCells as $key => $inCell) {
            if ($inCell instanceof BaseItem) {
                $items[$key] = $inCell;
            }
        }
        return $items;
    }

    public function add(InCell $inCell)
    {
        $this->inCells[] = $inCell;
    }

    /**
     * return boolean
     */
    public function isEmpty()
    {
        return $this->inCells === [];
    }
}
