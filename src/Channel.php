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

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * Class Channel
 */
class Channel implements MessageComponentInterface
{

    const SET_NAME = 'name=';

    /**
     * @var Field $field
     */
    protected $field;

    /**
     * @var \SplObjectStorage|ConnectionInterface[]
     */
    protected $clients;

    /**
     * Channel constructor.
     */
    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    /**
     * Nachrichten werden allen verbundenen Clients geschickt.
     *
     * @param ConnectionInterface $from
     * @param string $msg
     */
    function onMessage(ConnectionInterface $from, $msg)
    {
        /** @var Field $field */
        foreach ($this->field->getField() as $onFieldArray) {
            foreach ($onFieldArray as $onField) {
                $onField->event($from, $msg);
            }
        }
        foreach ($this->clients as $client) {
            $client->send(json_encode($this->field));
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    function onError(ConnectionInterface $conn, \Exception $e)
    {
        print_r($e);
    }

    /**
     * @param ConnectionInterface $conn
     */
    function onOpen(ConnectionInterface $conn)
    {
        echo "New connection! ({$conn->resourceId})\n";
        $this->clients->attach($conn);
        if (is_null($this->field)) {
            $this->field = new Field(15, 15);
        }

        $this->field->addToEmptySpace(new Player(0, 0, $this->field, $conn->resourceId));
        foreach ($this->clients as $client) {
            $client->send(json_encode($this->field));
        }
    }

    /**
     * @param ConnectionInterface $conn
     */
    function onClose(ConnectionInterface $conn)
    {
        $player = $this->field->getPlayer($conn->resourceId);
        if (!is_null($player)) {
            $this->field->remove($player);
            if ($this->field->countPlayer() == 0) {
                $this->field = null;
            } else {
                foreach ($this->clients as $client) {
                    $client->send(json_encode($this->field));
                }
            }
        }
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

}
