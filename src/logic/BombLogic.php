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
use bomberman\components\Room;
use bomberman\io\Config;
use bomberman\io\Message;
use bomberman\logic\javascript\FieldJSLogic;

/**
 * Class BombLogic
 * @package bomberman\logic
 */
class BombLogic extends BaseLogic
{

    public static $name = 'bomb';

    const EVENT_EXPLODE = 'explode';
    const EVENT_MOVE = 'move';

    /**
     * @param Bomb $bomb
     * @param ClientConnection $sender
     */
    public function explode($bomb, $sender)
    {
        // already exploded
        if (is_null($bomb->getTimer())) {
            return;
        }
        $bomb->setTimer(null);
        $current = milliseconds();
        $room = $this->context->getData()->findRoomBySender($sender->getUuid());
        if (!$room) {
            return;
        }
        if (($current - $bomb->getPlanted()) >= Config::get(Config::BOMB_TIMEOUT)) {
            $fieldCell = $room->getField()->getXY($bomb->getX(), $bomb->getY());
            $fieldCell->removeById($bomb->getId());
            $std = new \stdClass();
            $std->bomb = $bomb;
            $std->room = $room;
            $this->context->send(Message::fromCode(ExplosionLogic::$name, ExplosionLogic::EVENT_CREATE, $std), $sender);
            $this->context->sendToClients($room->getConnectedPlayers(), Message::fromCode(FieldJSLogic::NAME, FieldJSLogic::EVENT_UPDATE, $room->getField()));
        }
    }

    /**
     * @param \stdClass $data
     * @param ClientConnection $sender
     */
    public function move($data, $sender)
    {
        /** @var Room $room */
        $room = $this->context->getData()->findRoomBySender($sender->getUuid());
        if (is_null($room)) {
            return;
        }
        /** @var Bomb $bomb */
        $bomb = $data->bomb;
        $x = $data->x;
        $y = $data->y;
        $fieldCell = $room->getField()->getXY($bomb->getX() + $x, $bomb->getY() + $y);
        if (!$room->getField()->getXY($bomb->getX(), $bomb->getY())->contains($bomb->getId())) {
            return;
        }
        if (!is_null($fieldCell) && $fieldCell->canBombEnter()) {
            $room->getField()->moveTo($bomb, $bomb->getX() + $x, $bomb->getY() + $y);
            $this->context->sendToClients($room->getConnectedPlayers(), Message::fromCode(FieldJSLogic::NAME, FieldJSLogic::EVENT_UPDATE, $room->getField()));
            $this->context->executeAfter(function () use ($data, $sender) {
                $this->context->send(Message::fromCode(BombLogic::$name, BombLogic::EVENT_MOVE, $data), $sender);
            }, 600);
        } else {
            $bomb->setMoving(false);
        }
    }

}
