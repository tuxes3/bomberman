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
use bomberman\io\RoomCollection;
use bomberman\logic\ItemLogic;
use bomberman\components\Room;
use bomberman\logic\RoomLogic;
use bomberman\logic\javascript\GameJSLogic;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// restore rooms.json incase of websocket failure
$backupManager = new BackupManager();
$roomCollection = $backupManager->restore();
if (!$roomCollection instanceof RoomCollection) {
    $roomCollection = new RoomCollection();
}
$bombermanWebsocket = new BombermanWebsocket($roomCollection);
$wsServer = new WsServer($bombermanWebsocket);
$server = IoServer::factory(new HttpServer($wsServer),8009);
$wsServer->enableKeepAlive($server->loop, 30);
$bombermanWebsocket->setLoop($server->loop);

// backup data every x
$server->loop->addPeriodicTimer(Config::get(Config::BACK_UP_INTERVAL), function ($timer) use ($bombermanWebsocket, $backupManager) {
    $backupManager->backup($bombermanWebsocket->getData());
});

// delete unused room every x
$server->loop->addPeriodicTimer(Config::get(Config::ROOM_EXPIRATION_SECONDS), function ($timer) use ($bombermanWebsocket) {
    $expiredRooms = $bombermanWebsocket->getData()->findExpiredRoom();
    /** @var Room $room */
    foreach ($expiredRooms as $room) {
        $std = new \stdClass();
        $std->room = $room;
        $std->inactivity = true;
        $bombermanWebsocket->sendToClients(
            $room->getConnectedPlayers(),
            Message::fromCode(GameJSLogic::NAME, GameJSLogic::EVENT_FINISHED, null)
        );
        $bombermanWebsocket->send(Message::fromCode(RoomLogic::$name, RoomLogic::EVENT_CLOSE, $std), null);
    }
});

$server->run();
