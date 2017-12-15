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
     * @var string
     */
    protected $uuid;

    /**
     * @var int
     */
    protected $lastMoved;

    /**
     * @var float
     */
    protected $movementSpeed;

    /**
     * @var int
     */
    protected $bombCount;

    /**
     * @var int
     */
    protected $explosionSpread;

    /**
     * @var boolean
     */
    protected $alive = true;

    /**
     * Player constructor.
     * @param $x
     * @param $y
     * @param $uuid
     */
    public function __construct($x, $y, $uuid)
    {
        parent::__construct($x, $y);
        $this->uuid = $uuid;
        $this->lastMoved = milliseconds();
        $this->movementSpeed = Config::get(Config::MOVEMENT_SPEED);
        $this->bombCount = Config::get(Config::BOMB_COUNT);
        $this->explosionSpread = Config::get(Config::EXPLOSION_SPREAD);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'alive' => $this->alive,
        ]);
    }

    /**
     * @return array
     */
    public function backup()
    {
        return array_merge(parent::backup(), [
            'lastMoved' => $this->lastMoved,
            'movementSpeed' => $this->movementSpeed,
            'bombCount' => $this->bombCount,
            'explosionSpread' => $this->explosionSpread,
            'uuid' => $this->uuid,
        ]);
    }

    /**
     * @param array $data
     * @return Player
     */
    public static function restore($data)
    {
        $player = new Player($data['x'], $data['y'], $data['uuid']);
        $player->lastMoved = $data['lastMoved'];
        $player->movementSpeed = $data['movementSpeed'];
        $player->bombCount = $data['bombCount'];
        $player->explosionSpread = $data['explosionSpread'];
        return $player;
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
     * @return boolean
     */
    public function canPlayerMove()
    {
        return (milliseconds() - $this->lastMoved) > $this->movementSpeed && $this->alive;
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
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return $this
     */
    public function setLastMoved()
    {
        $this->lastMoved = milliseconds();
        return $this;
    }

    /**
     * @return int
     */
    public function getNextMovement()
    {
        return $this->lastMoved + $this->movementSpeed;
    }

    /**
     *
     */
    public function setDead()
    {
        $this->alive = false;
    }

    /**
     * @return int
     */
    public function getExplosionSpread()
    {
        return $this->explosionSpread;
    }

    /**
     * @return bool
     */
    public function isAlive()
    {
        return $this->alive;
    }

}
