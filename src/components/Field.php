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

namespace bomberman\components;

use bomberman\components\field\FieldCell;
use bomberman\components\field\InCell;
use bomberman\io\FieldCollection;

/**
 * Class Field
 * @package bomberman\components
 */
class Field implements \JsonSerializable
{

    /**
     * @var array|InCell[][]
     */
    protected $cells = [];

    /**
     * @var int $maxPlayers
     */
    protected $maxPlayers;

    public function __construct($maxPlayers)
    {
        $this->maxPlayers = $maxPlayers;
    }

    /**
     * @param $x
     * @param $y
     * @return FieldCell|null
     */
    public function getXY($x, $y)
    {
        if (isset($this->cells[$x])) {
            if (isset($this->cells[$x][$y])) {
                return $this->cells[$x][$y];
            }
        }
        return null;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'players' => $this->maxPlayers,
            'cells' => $this->cells,
        ];
    }

    /**
     * @return InCell[][]
     */
    public function getCells()
    {
        return $this->cells;
    }

    /**
     * @param InCell $a
     * @param int $x
     * @param int $y
     */
    public function moveTo(InCell $a, $x, $y)
    {
        $this->cells[$a->getX()][$a->getY()]->removeById($a->getId());
        $a->setX($x);
        $a->setY($y);
        $this->cells[$a->getX()][$a->getY()]->add($a);
    }

    /**
     * @return FieldCollection
     */
    public function getFieldCollection()
    {
        $inCells = [];
        foreach ($this->cells as $row) {
            /** @var InCell $inCell */
            foreach ($row as $inCell) {
                $inCells[] = $inCell;
            }
        }
        return new FieldCollection($inCells);
    }

    /**
     * @return boolean
     */
    public function isFinished()
    {
        $players = $this->getFieldCollection()->findPlayers();
        if ($this->maxPlayers != count($players)) {
            return false;
        }
        $aliveCount = 0;
        foreach ($players as $player) {
            $aliveCount += $player->isAlive() ? 1 : 0;
        }
        return count($players) == 1 ? $aliveCount == 0 : $aliveCount <= 1;
    }

    /**
     * @param InCell $inCell
     */
    public function addTo(InCell $inCell)
    {
        $this->getXY($inCell->getX(), $inCell->getY())->add($inCell);
    }

    /**
     * @param $cells
     */
    public function setCells($cells)
    {
        $this->cells = $cells;
    }

    /**
     * @return int
     */
    public function getMaxPlayers()
    {
        return $this->maxPlayers;
    }

}
