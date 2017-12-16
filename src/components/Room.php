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

/**
 * Class Room
 * @package components
 */
class Room implements \JsonSerializable
{
    const PLAYER_LIMIT = 12;
    /**
     * @var int
     */
    private $maxPlayers;

    /**
     * @var string
     */
    private $uniqueId;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var array|string[]
     */
    private $connectedPlayers;

    /**
     * @var Field
     */
    private $field;

    /**
     * Room constructor.
     * @param int $maxPlayers
     * @param string $uniqueId
     */
    public function __construct($maxPlayers, $uniqueId)
    {
        //max 12 players
        if($maxPlayers >= self::PLAYER_LIMIT){
            $maxPlayers = self::PLAYER_LIMIT;
        }

        $this->maxPlayers = $maxPlayers;
        $this->uniqueId = $uniqueId;
        $this->connectedPlayers = [];
        $this->createdAt = new \DateTime();
        // TODO: calculate field size depending on player
        $this->field = new Field($maxPlayers);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'maxPlayers' => $this->maxPlayers,
            'connectedPlayers' => count($this->connectedPlayers),
            'uniqueId' => $this->uniqueId,
        ];
    }

    /**
     * @param int $playerId
     * @return bool|string
     */
    public function addPlayer($playerId)
    {
        if (in_array($playerId, $this->connectedPlayers)) {
            return sprintf('Player is already in room (%s).', $this->uniqueId);
        }
        if (count($this->connectedPlayers) >= $this->maxPlayers) {
            return sprintf('The room (%s) is already full.', $this->uniqueId);
        }
        $this->connectedPlayers[] = $playerId;
        return true;
    }

    /**
     * @return boolean
     */
    public function isStartable()
    {
        return count($this->connectedPlayers) == $this->maxPlayers;
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @return array|string[]
     */
    public function getConnectedPlayers()
    {
        return $this->connectedPlayers;
    }

    /**
     * @return Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return int
     */
    public function getMaxPlayers()
    {
        return $this->maxPlayers;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param int $maxPlayers
     * @return $this
     */
    public function setMaxPlayers($maxPlayers)
    {
        $this->maxPlayers = $maxPlayers;
        return $this;
    }

    /**
     * @param string $uniqueId
     * @return $this
     */
    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;
        return $this;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @param array|string[] $connectedPlayers
     * @return $this
     */
    public function setConnectedPlayers($connectedPlayers)
    {
        $this->connectedPlayers = $connectedPlayers;
        return $this;
    }

    /**
     * @param Field $field
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }

}
