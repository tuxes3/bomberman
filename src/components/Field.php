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

use bomberman\components\field\Block;
use bomberman\components\field\EmptySpace;
use bomberman\components\field\OnField;
use bomberman\components\field\Player;

class Field implements \JsonSerializable
{
    protected $field = [];

    public function __construct($width, $height)
    {
        for ($i = 0; $i < $width; $i++) {
            $this->field[] = [];
            for ($j = 0; $j < $height; $j++) {
                $this->field[$i][$j] = rand(0, 4) == 0
                    ? new Block($i, $j, $this)
                    : new EmptySpace($i, $j, $this)
                ;
            }
        }
    }

    /**
     * @param $x
     * @param $y
     * @return OnField|null
     */
    public function getXY($x, $y)
    {
        if (isset($this->field[$x])) {
            if (isset($this->field[$x][$y])) {
                return $this->field[$x][$y];
            }
        }
        return null;
    }

    /**
     * @param OnField $a
     * @param OnField $b
     */
    public function switchOnField(OnField $a, OnField $b)
    {
        $this->field[$a->getX()][$a->getY()] = $b;
        $this->field[$b->getX()][$b->getY()] = $a;
        $bxTemp = $b->getX();
        $byTemp = $b->getY();
        $b->setX($a->getX());
        $b->setY($a->getY());
        $a->setX($bxTemp);
        $a->setY($byTemp);
    }

    public function jsonSerialize()
    {
        return [
            'field' => $this->field
        ];
    }

    /**
     * @return OnField[]
     */
    public function getField()
    {
        return $this->field;
    }

    public function addToEmptySpace(OnField $toAdd)
    {
        foreach ($this->field as $i => $fieldArray) {
            foreach ($fieldArray as $j => $onField) {
                if ($onField->getClass() == EmptySpace::class) {
                    $toAdd->setX($onField->getX());
                    $toAdd->setY($onField->getY());
                    $this->field[$i][$j] = $toAdd;
                    break 2;
                }
            }
        }
    }

    public function remove(OnField $onField)
    {
        $this->field[$onField->getX()][$onField->getY()] = new EmptySpace($onField->getX(), $onField->getY(), $this);
    }

    /**
     * @param $connId
     * @return null|Player
     */
    public function getPlayer($connId)
    {
        foreach ($this->field as $i => $fieldArray) {
            foreach ($fieldArray as $j => $onField) {
                if ($onField instanceof Player && $onField->getConnId() == $connId) {
                    return $onField;
                }
            }
        }
        return null;
    }

    public function countPlayer()
    {
        $count = 0;
        foreach ($this->field as $i => $fieldArray) {
            foreach ($fieldArray as $j => $onField) {
                if ($onField instanceof Player) {
                    $count++;
                }
            }
        }
        return $count;
    }

}
