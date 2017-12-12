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

namespace bomberman\logic;
use bomberman\components\MessageForwarder;
use bomberman\io\Message;
use components\Room;
use system\ConnectionInterface;

/**
 * Class RoomLogic
 * @package bomberman\logic
 */
class RoomLogic extends BaseLogic
{

    const CREATE = 'create';
    const JOIN = 'join';

    /**
     * @var string
     */
    public static $name = 'room';

    /**
     * @var array|Room[]
     */
    private $rooms;

    /**
     * RoomLogic constructor.
     * @param MessageForwarder $messageForwarder
     */
    public function __construct(MessageForwarder $messageForwarder)
    {
        parent::__construct($messageForwarder);
        $this->rooms = [];
    }

    /**
     * @param Message $message
     * @param ConnectionInterface $sender
     */
    public function execute($message, ConnectionInterface $sender)
    {
        switch ($message->getData()->event) {
            case self::CREATE:
                $this->createRoom($message);
                break;
            case self::JOIN:
                $this->joinRoom($message, $sender);
                break;
        }
    }

    /**
     * @return string
     */
    private function getFreeUniqueId()
    {
        $mayNextId = substr(md5(openssl_random_pseudo_bytes(128)), 0, 8);
        while (array_key_exists($mayNextId, $this->rooms)) {
            $mayNextId = substr(md5(openssl_random_pseudo_bytes(128)), 0, 8);
        }
        return $mayNextId;
    }

    /**
     * @param Message $message
     */
    protected function createRoom($message)
    {
        $room = new Room($message->getData()->maxPlayers, $this->getFreeUniqueId());
        $this->rooms[$room->getUniqueId()] = $room;
    }

    /**
     * @param Message $message
     * @param ConnectionInterface $sender
     */
    protected function joinRoom($message, ConnectionInterface $sender)
    {
        $room = $this->rooms[$message->getData()->room];
        $this->messageForwarder->send()
    }

}
