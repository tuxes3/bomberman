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
use bomberman\io\Config;

/**
 * Class Player
 * @package bomberman\components\field
 */
class Player extends BaseInCell
{

    /**
     * @var int
     */
    protected $connId;

    /**
     * @var float
     */
    protected $lastMoved;

    /**
     * @var int
     */
    protected $movementSpeed;

    /**
     * @var int
     */
    protected $bombCount;

    /**
     * Player constructor.
     * @param $x
     * @param $y
     * @param $connId
     */
    public function __construct($x, $y, $connId)
    {
        parent::__construct($x, $y);
        $this->connId = $connId;
        $this->lastMoved = microtime(true);
        $this->movementSpeed = Config::get(Config::MOVEMENT_SPEED);
        $this->bombCount = Config::get(Config::BOMB_COUNT);
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
    public function canPlayerMove()
    {
        return (microtime(true) - $this->lastMoved) > $this->movementSpeed;
    }

    /**
     * @return int
     */
    public function getDisplayPriority()
    {
        return BaseInCell::BASE_PRIORITY;
    }

    /**
     * @return int
     */
    public function getConnId()
    {
        return $this->connId;
    }

    /**
     * @return $this
     */
    public function setLastMoved()
    {
        $this->lastMoved = microtime(true);
        return $this;
    }

}
