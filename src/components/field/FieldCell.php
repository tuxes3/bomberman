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
