<?php
/*
 * Copyright (c) 2017, whatwedo GmbH
 * All rights reserved
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace bomberman\components;

use bomberman\components\field\FieldCell;
use bomberman\components\field\InCell;
use bomberman\io\FieldCollection;

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

    public function jsonSerialize()
    {
        return [
            'cells' => $this->cells
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
