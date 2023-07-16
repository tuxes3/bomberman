<?php

declare(strict_types=1);
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
use bomberman\io\Config;
use bomberman\io\Message;
use bomberman\logic\javascript\FieldJSLogic;
use bomberman\logic\javascript\GameJSLogic;
use bomberman\logic\javascript\PlayerJSLogic;
use bomberman\logic\javascript\RoomJSLogic;

class PlayerLogic extends BaseLogic
{
    final public const EVENT_MOVE = 'move';

    final public const EVENT_PLAN = 'plant';

    final public const EVENT_INIT = 'init';

    public static $name = 'player';

    /**
     * @return array
     */
    public function getEventsAllowedFromClient()
    {
        return [
            self::EVENT_MOVE,
            self::EVENT_PLAN,
            self::EVENT_INIT,
        ];
    }

    /**
     * @param \stdClass $data
     */
    protected function init($data, ClientConnection $sender)
    {
        $rooms = $this->context->getData();
        /** @var Room $room */
        $room = $rooms->findRoomBySender($sender->getUuid());
        $sender->send(json_encode(Message::fromCode(RoomJSLogic::NAME, RoomJSLogic::EVENT_LIST, $rooms->getValues()), JSON_THROW_ON_ERROR));
        $sender->send(json_encode(Message::fromCode(GameJSLogic::NAME, GameJSLogic::EVENT_BOMB_MOVEMENT_SPEED, Config::get(Config::BOMB_MOVEMENT_SPEED)), JSON_THROW_ON_ERROR));
        if (!is_null($room) && $room->isStartable()) {
            $sender->send(json_encode(Message::fromCode(GameJSLogic::NAME, GameJSLogic::EVENT_STARTED, $room->getField()->getDimension()), JSON_THROW_ON_ERROR));
            $sender->send(json_encode(Message::fromCode(FieldJSLogic::NAME, FieldJSLogic::EVENT_UPDATE, $room->getField()), JSON_THROW_ON_ERROR));
        }
    }

    /**
     * @param \stdClass $data
     */
    protected function move($data, ClientConnection $sender)
    {
        $player = $this->context->getData()->findPlayerBySender($sender->getUuid());
        if ($player instanceof Player && $player->canPlayerMove()) {
            $room = $this->context->getData()->findRoomBySender($sender->getUuid());
            $room->touch();
            $x = -1;
            $y = -1;
            $x2 = -1;
            $y2 = -1;
            switch ($data->direction) {
                case '↑':
                    $x = $player->getX();
                    $x2 = $player->getX();
                    $y = $player->getY() - 1;
                    $y2 = $player->getY() - 2;
                    break;
                case '←':
                    $x = $player->getx() - 1;
                    $x2 = $player->getx() - 2;
                    $y = $player->getY();
                    $y2 = $player->getY();
                    break;
                case '↓':
                    $x = $player->getX();
                    $x2 = $player->getX();
                    $y = $player->getY() + 1;
                    $y2 = $player->getY() + 2;
                    break;
                case '→':
                    $x = $player->getX() + 1;
                    $x2 = $player->getX() + 2;
                    $y = $player->getY();
                    $y2 = $player->getY();
                    break;
            }
            $nextField = $room->getField()->getXY($x, $y);
            $nextNextField = $room->getField()->getXY($x2, $y2);
            if (!is_null($nextField) && $nextField->canPlayerEnter($player, $nextNextField)) {
                $movingBombAhead = false;
                foreach ($nextField->getAllBombs() as $bomb) {
                    if ($bomb->isMoving()) {
                        $movingBombAhead = true;
                        continue;
                    }
                    $std = new \stdClass();
                    $std->bomb = $bomb;
                    $std->x = $x - $player->getX();
                    $std->y = $y - $player->getY();
                    $bomb->setMoving(true);
                    $this->context->send(Message::fromCode(BombLogic::$name, BombLogic::EVENT_MOVE, $std), $sender);
                }
                if (!$movingBombAhead) {
                    $room->getField()->moveTo($player, $x, $y);
                    $explosions = $nextField->getAllExplosions();
                    foreach ($explosions as $explosion) {
                        $nextField->explode($explosion);
                    }
                    if ($explosions !== []) {
                        $this->context->send(Message::fromCode(FieldLogic::$name, FieldLogic::EVENT_CHECK_FINISH, $room), $sender);
                    }
                    foreach ($nextField->getAllItems() as $item) {
                        $item->consume($player);
                        $this->context->send(Message::fromCode(ItemLogic::$name, ItemLogic::EVENT_NAME, $item), $sender);
                    }
                    $player->setLastMoved();
                    $this->context->send(Message::fromCode(FieldLogic::$name, FieldLogic::EVENT_UPDATE_CLIENTS, $room), $sender);
                    $sender->send(json_encode(Message::fromCode(PlayerJSLogic::NAME, PlayerJSLogic::EVENT_MOVEMENT_SPEED, $player->getMovementSpeed()), JSON_THROW_ON_ERROR));
                }
            }
        }
    }

    /**
     * @param \stdClass $data
     */
    protected function plant($data, ClientConnection $sender)
    {
        /** @var Room $room */
        $room = $this->context->getData()->findRoomBySender($sender->getUuid());
        if (!is_null($room)) {
            $room->touch();
            $player = $room->getField()->getFieldCollection()->findPlayerBySender($sender->getUuid());
            if ($player->isAlive()) {
                $playerBombs = $room->getField()->getFieldCollection()->findBombsByPlanter($player->getUuid());
                if (count($playerBombs) < $player->getBombCount()) {
                    $bomb = new Bomb($player->getX(), $player->getY(), $player->getExplosionSpread(), $player->getUuid());
                    $room->getField()->addTo($bomb);
                    $this->context->send(Message::fromCode(FieldLogic::$name, FieldLogic::EVENT_UPDATE_CLIENTS, $room), $sender);
                    $timer = $this->context->executeAfter(function () use ($bomb, $sender) {
                        $this->context->send(Message::fromCode(BombLogic::$name, BombLogic::EVENT_EXPLODE, $bomb), $sender);
                    }, Config::get(Config::BOMB_TIMEOUT));
                    $bomb->setTimer($timer);
                }
            }
        }
    }
}
