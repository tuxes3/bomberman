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

namespace bomberman;

use bomberman\io\DataCollection;
use bomberman\io\Message;
use bomberman\logic\BaseLogic;
use bomberman\logic\FieldLogic;
use bomberman\logic\RoomLogic;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * Class BombermanWebsocket
 */
class BombermanWebsocket implements MessageComponentInterface, Context
{

    /**
     * @var \SplObjectStorage|ConnectionInterface[]
     */
    protected $clients;

    /**
     * @var BaseLogic[]|array
     */
    protected $logics;

    /**
     * @var DataCollection $data
     */
    protected $data;

    /**
     * Channel constructor.
     */
    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
        $this->data = new DataCollection();
        $this->logics = [
            RoomLogic::$name => new RoomLogic($this),
            FieldLogic::$name => new FieldLogic($this),
        ];
    }

    /**
     * Nachrichten werden allen verbundenen Clients geschickt.
     *
     * @param ConnectionInterface $from
     * @param string $msg
     */
    function onMessage(ConnectionInterface $from, $msg)
    {
        echo ($msg . PHP_EOL);
        $message = Message::fromJson($msg);
        $this->send($message, $from);
        echo ($this->data->count().' : count');
    }

    /**
     * @param Message $message
     * @param ConnectionInterface $from
     */
    public function send($message, ConnectionInterface $from)
    {
        $this->logics[$message->getLogicName()]->execute($message, $from);
    }

    /**
     * @return DataCollection
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array|int[] $playerIds
     * @param Message $message
     */
    public function sendToClients($playerIds, $message)
    {
        foreach ($this->clients as $client) {
            if (in_array($client->resourceId, $playerIds)) {
                $client->send(json_encode($message));
            }
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        // TODO:
        print_r($e);
    }

    /**
     * @param ConnectionInterface $conn
     */
    function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
    }

    /**
     * @param ConnectionInterface $conn
     */
    function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
    }

}
