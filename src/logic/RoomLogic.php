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
    const EVENT_CLOSE = 'close';

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
     * @param ClientConnection $sender
     */
    protected function create($data, ClientConnection $sender)
    {
        $uniqueId = $this->context->getData()->getFreeUniqueId();
        $room = new Room($data->maxPlayers, $uniqueId);
        $this->context->getData()->add($room);
        $this->sendRoomsToAll();
    }

    /**
     * @param Room $data
     * @param ClientConnection $sender
     */
    protected function close($data, $sender)
    {
        $this->context->getData()->removeUniqueId($data->getUniqueId());
        $this->sendRoomsToAll();
    }

    /**
     * sends room changes
     */
    protected function sendRoomsToAll()
    {
        $this->context->sendToClients(Context::SEND_ALL, Message::fromCode(RoomJSLogic::NAME, RoomJSLogic::EVENT_LIST, $this->context->getData()->getValues()));
    }

    /**
     * @param \stdClass $data
     * @param ClientConnection $sender
     */
    protected function join($data, ClientConnection $sender)
    {
        $room = $this->context->getData()->findRoomByUniqueId($data->uniqueId);
        $return = null;
        if (is_null($room)) {
            $return = Message::fromCode(MessageJSLogic::NAME, MessageJSLogic::EVENT_WARNING, sprintf('Room (%s) not existing.', $data->uniqueId));
        } else {
            $result = $room->addPlayer($sender->getUuid());
            if (is_string($result)) {
                $return = Message::fromCode(MessageJSLogic::NAME, MessageJSLogic::EVENT_WARNING, $result);
            } elseif ($room->isStartable()) {
                $this->context->send(Message::fromCode(FieldLogic::$name, FieldLogic::EVENT_START, $data), $sender);
                $this->sendRoomsToAll();
            } else {
                $return = Message::fromCode(MessageJSLogic::NAME, MessageJSLogic::EVENT_INFO, 'Waiting for players.');
                $this->sendRoomsToAll();
            }
        }
        if (!is_null($return)) {
            $sender->send(json_encode($return));
        }
    }

    /**
     * @param ClientConnection $sender
     */
    protected function getAll($data, ClientConnection $sender)
    {
        $sender->send(json_encode(Message::fromCode(RoomJSLogic::NAME, RoomJSLogic::EVENT_LIST, $this->context->getData()->getValues())));
    }

}
