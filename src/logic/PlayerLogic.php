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

use bomberman\components\field\Bomb;
use bomberman\components\field\Player;
use bomberman\components\Room;
use bomberman\io\Message;
use bomberman\logic\javascript\FieldJSLogic;
use bomberman\logic\javascript\GameJSLogic;
use bomberman\logic\javascript\PlayerJSLogic;
use bomberman\logic\javascript\RoomJSLogic;
use Ratchet\ConnectionInterface;

class PlayerLogic extends BaseLogic
{

    public static $name = 'player';

    const EVENT_MOVE = 'move';
    const EVENT_PLAN = 'plant';
    const EVENT_INIT = 'init';

    /**
     * @param \stdClass $data
     * @param ClientConnection $sender
     */
    protected function init($data, ClientConnection $sender)
    {
        $rooms = $this->context->getData();
        /** @var Room $room */
        $room = $rooms->findRoomBySender($sender->getUuid());
        if (!is_null($room) && $room->isStartable()) {
            $sender->send(json_encode(Message::fromCode(GameJSLogic::NAME, GameJSLogic::EVENT_STARTED, null)));
            $sender->send(json_encode(Message::fromCode(FieldJSLogic::NAME, FieldJSLogic::EVENT_UPDATE, $room->getField())));
        } else {
            $sender->send(json_encode(Message::fromCode(RoomJSLogic::NAME, RoomJSLogic::EVENT_LIST, $rooms->getValues())));
        }
    }

    /**
     * @param \stdClass $data
     * @param ClientConnection $sender
     */
    protected function move($data, ClientConnection $sender)
    {
        $player = $this->context->getData()->findPlayerBySender($sender->getUuid());
        if ($player instanceof Player && $player->canPlayerMove()) {
            $room = $this->context->getData()->findRoomBySender($sender->getUuid());
            $x = -1;
            $y = -1;
            switch ($data->direction) {
                case '↑':
                    $x = $player->getX() - 1;
                    $y = $player->getY();
                    break;
                case '←';
                    $x = $player->getx();
                    $y = $player->getY() - 1;
                    break;
                case '↓':
                    $x = $player->getX() + 1;
                    $y = $player->getY();
                    break;
                case '→':
                    $x = $player->getX();
                    $y = $player->getY() + 1;
                    break;
            }
            $nextField = $room->getField()->getXY($x, $y);
            if (!is_null($nextField) && $nextField->canPlayerEnter()) {
                $room->getField()->moveTo($player, $x, $y);
                $player->setLastMoved();
                $this->context->sendToClients($room->getConnectedPlayers(),
                    Message::fromCode(FieldJSLogic::NAME, FieldJSLogic::EVENT_UPDATE, $room->getField())
                );
                $sender->send(json_encode(Message::fromCode(PlayerJSLogic::NAME, PlayerJSLogic::EVENT_MOVEMENT_SPEED, $player->getMovementSpeed())));
            }
        }
    }

    /**
     * @param \stdClass $data
     * @param ClientConnection $sender
     */
    protected function plant($data, ClientConnection $sender)
    {
        /** @var Room $room */
        $room = $this->context->getData()->findRoomBySender($sender->getUuid());
        if (!is_null($room)) {
            $player = $room->getField()->getFieldCollection()->findPlayerBySender($sender->getUuid());
            $playerBombs = $room->getField()->getFieldCollection()->findBombsByPlanter($player->getUuid());
            if (count($playerBombs) < $player->getBombCount()) {
                $room->getField()->addTo(new Bomb($player->getX(), $player->getY(), $player->getExplosionSpread(), $player->getUuid()));
                $this->context->sendToClients($room->getConnectedPlayers(),
                    Message::fromCode(FieldJSLogic::NAME, FieldJSLogic::EVENT_UPDATE, $room->getField())
                );
            }
        }
    }

}
