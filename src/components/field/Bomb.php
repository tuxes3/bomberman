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
 * Class Bomb
 * @package bomberman\components\field
 */
class Bomb extends BaseInCell
{

    /**
     * @var int $explosionSpread
     */
    protected $explosionSpread;

    /**
     * @var int
     */
    protected $planted;

    /**
     * Bomb constructor.
     * @param int $x
     * @param int $y
     * @param int $explosionSpread
     */
    public function __construct($x, $y, $explosionSpread)
    {
        parent::__construct($x, $y);
        $this->explosionSpread = $explosionSpread;
        $this->planted = milliseconds();
    }

    public function backup()
    {
        return array_merge(parent::backup(), [
            'explosionSpread' => $this->explosionSpread,
            'planted' => $this->planted,
        ]);
    }

    /**
     * @param array $data
     * @return Bomb
     */
    public static function restore($data)
    {
        $bomb = new self($data['x'], $data['y'], $data['explosionSpread']);
        $bomb->planted = $data['planted'];
        return $bomb;
    }

    /**
     * @return boolean
     */
    public function canPlayerEnter()
    {
        return true;
    }

    /**
     * @return boolean
     */
    public function blocksExplosion()
    {
        return false;
    }

    /**
     * @return int
     */
    public function getDisplayPriority()
    {
        return BaseInCell::BASE_PRIORITY - 1;
    }

    /**
     * @return float
     */
    public function getPlanted()
    {
        return $this->planted;
    }

    public function explodeNow()
    {
        $this->planted = 0;
    }

    /**
     * @return int
     */
    public function getExplosionSpread()
    {
        return $this->explosionSpread;
    }

}
