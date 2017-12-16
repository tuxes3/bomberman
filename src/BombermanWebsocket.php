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

namespace bomberman;

use bomberman\io\RoomCollection;
use bomberman\io\Message;
use bomberman\logic\BaseLogic;
use bomberman\logic\BombLogic;
use bomberman\logic\ClientConnection;
use bomberman\logic\ExplosionLogic;
use bomberman\logic\FieldLogic;
use bomberman\logic\ItemLogic;
use bomberman\logic\PlayerLogic;
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
     * @var RoomCollection $data
     */
    protected $data;

    protected $clientConnectionUuidMap;

    /**
     * Channel constructor.
     * @param RoomCollection $roomCollection
     */
    public function __construct($roomCollection)
    {
        $this->clients = new \SplObjectStorage();
        $this->data = $roomCollection;
        $this->clientConnectionUuidMap = [];
        $this->logics = [
            RoomLogic::$name => new RoomLogic($this),
            FieldLogic::$name => new FieldLogic($this),
            PlayerLogic::$name => new PlayerLogic($this),
            BombLogic::$name => new BombLogic($this),
            ExplosionLogic::$name => new ExplosionLogic($this),
            ItemLogic::$name => new ItemLogic($this),
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
        $clientConnection = new ClientConnection($from, $message->getUuid());
        $this->clientConnectionUuidMap[$from->resourceId] = $message->getUuid();
        $this->send($message, $clientConnection);
    }

    /**
     * @param Message $message
     * @param ClientConnection $from
     */
    public function send($message, $from)
    {
        $this->logics[$message->getLogicName()]->execute($message, $from);
    }

    /**
     * @return RoomCollection
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array|string[] $playerIds
     * @param Message $message
     */
    public function sendToClients($playerIds, $message)
    {
        if (is_int($playerIds) && $playerIds == Context::SEND_ALL) {
            foreach ($this->clients as $client) {
                $client->send(json_encode($message));
            }
        } else {
            foreach ($this->clients as $client) {
                if (array_key_exists($client->resourceId, $this->clientConnectionUuidMap) && in_array($this->clientConnectionUuidMap[$client->resourceId], $playerIds)) {
                    $client->send(json_encode($message));
                }
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
