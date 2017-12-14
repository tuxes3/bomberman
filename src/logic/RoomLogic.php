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

use bomberman\components\Room;
use bomberman\Context;
use bomberman\io\Message;
use bomberman\logic\javascript\MessageJSLogic;
use bomberman\logic\javascript\RoomJSLogic;
use Ratchet\ConnectionInterface;

/**
 * Class RoomLogic
 * @package bomberman\logic
 */
class RoomLogic extends BaseLogic
{

    const EVENT_CREATE = 'create';
    const EVENT_JOIN = 'join';
    const EVENT_LIST = 'getAll';

    /**
     * @var string
     */
    public static $name = 'room';

    /**
     * RoomLogic constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @param \stdClass $data
     * @param ConnectionInterface $sender
     */
    protected function create($data, ConnectionInterface $sender)
    {
        $uniqueId = $this->context->getData()->getFreeUniqueId();
        $room = new Room($data->maxPlayers, $uniqueId);
        $this->context->getData()->add($room);
    }

    /**
     * @param \stdClass $data
     * @param ConnectionInterface $sender
     */
    protected function join($data, ConnectionInterface $sender)
    {
        $room = $this->context->getData()->findRoomByUniqueId($data->uniqueId);
        $return = null;
        if (is_null($room)) {
            $return = Message::fromCode(MessageJSLogic::NAME, MessageJSLogic::EVENT_WARNING, sprintf('Room (%s) not existing.', $data->uniqueId));
        } else {
            $result = $room->addPlayer($sender->resourceId);
            if (is_string($result)) {
                $return = Message::fromCode(MessageJSLogic::NAME, MessageJSLogic::EVENT_WARNING, $result);
            } elseif ($room->isStartable()) {
                $this->context->send(Message::fromCode(FieldLogic::$name, FieldLogic::EVENT_START, $data), $sender);
            } else {
                $return = Message::fromCode(MessageJSLogic::NAME, MessageJSLogic::EVENT_INFO, 'Waiting for players.');
            }
        }
        if (!is_null($return)) {
            $sender->send(json_encode($return));
        }
    }

    /**
     * @param ConnectionInterface $sender
     */
    protected function getAll($data, ConnectionInterface $sender)
    {
        $sender->send(json_encode(Message::fromCode(RoomJSLogic::NAME, RoomJSLogic::EVENT_LIST, $this->context->getData())));
    }

}
