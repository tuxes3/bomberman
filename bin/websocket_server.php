<?php
/*
 * This file is part of the bomberman project.
 *
 * @author Nicolo Singer tuxes3@outlook.com
 * @author Lukas Müller computer_bastler@hotmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use bomberman\BombermanWebsocket;
use bomberman\io\Config;
use bomberman\io\Message;
use bomberman\logic\BombLogic;
use bomberman\logic\ExplosionLogic;
use bomberman\io\BackupManager;
use \bomberman\io\RoomCollection;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// needs to be run 64 bit machine
function milliseconds() {
    $mt = explode(' ', microtime());
    return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
}

$backupManager = new BackupManager();
$roomCollection = $roomCollection = $backupManager->restore();
if (!$roomCollection instanceof RoomCollection) {
    $roomCollection = new RoomCollection();
}
$bombermanWebsocket = new BombermanWebsocket($roomCollection);
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

$server->loop->addPeriodicTimer(Config::get(Config::BACK_UP_INTERVAL), function ($timer) use ($bombermanWebsocket, $backupManager) {
    $backupManager->backup($bombermanWebsocket->getData());
});

$server->run();
