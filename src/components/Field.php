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

use bomberman\components\field\InCell;

class Field implements \JsonSerializable
{

    /**
     * @var array|InCell[]
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
     * @return InCell|null
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
     * @return InCell[]
     */
    public function getCells()
    {
        return $this->cells;
    }

    /**
     * @param $cells
     */
    public function setCells($cells)
    {
        $this->cells = $cells;
    }

}
