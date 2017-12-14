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

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use bomberman\BombermanWebsocket;
use bomberman\io\Config;
use bomberman\io\Message;
use bomberman\logic\BombLogic;
use bomberman\logic\ExplosionLogic;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// needs to be run 64 bit machine
function milliseconds() {
    $mt = explode(' ', microtime());
    return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
}

$bombermanWebsocket = new BombermanWebsocket();
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $bombermanWebsocket
        )
    ),
    8009
);

$server->loop->addPeriodicTimer(Config::get(Config::BOMB_INTERVAL), function ($timer) use ($bombermanWebsocket) {
    $bombermanWebsocket->send(Message::fromCode(BombLogic::$name, BombLogic::EVENT_CHECK, null), null);
});

$server->loop->addPeriodicTimer(Config::get(Config::EXPLOSION_INTERVAL), function ($timer) use ($bombermanWebsocket) {
    $bombermanWebsocket->send(Message::fromCode(ExplosionLogic::$name, ExplosionLogic::EVENT_CHECK, null), null);
});

$server->run();
