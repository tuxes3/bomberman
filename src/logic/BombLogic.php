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
use bomberman\components\Room;
use bomberman\io\Config;
use bomberman\io\Message;
use bomberman\io\Milliseconds;

/**
 * Class BombLogic
 * @package bomberman\logic
 */
class BombLogic extends BaseLogic
{
    final public const EVENT_EXPLODE = 'explode';

    final public const EVENT_MOVE = 'move';

    public static $name = 'bomb';

    /**
     * @return array
     */
    public function getEventsAllowedFromClient()
    {
        return [];
    }

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
        $current = (new Milliseconds())->get();
        $room = $this->context->getData()->findRoomBySender($sender->getUuid());
        if (!$room instanceof \bomberman\components\Room) {
            return;
        }
        if (($current - $bomb->getPlanted()) >= Config::get(Config::BOMB_TIMEOUT)) {
            $fieldCell = $room->getField()->getXY($bomb->getX(), $bomb->getY());
            $fieldCell->removeById($bomb->getId());
            $std = new \stdClass();
            $std->bomb = $bomb;
            $std->room = $room;
            $this->context->send(Message::fromCode(ExplosionLogic::$name, ExplosionLogic::EVENT_CREATE, $std), $sender);
            $this->context->send(Message::fromCode(FieldLogic::$name, FieldLogic::EVENT_UPDATE_CLIENTS, $room), $sender);
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
            foreach ($fieldCell->getAllExplosions() as $explosion) {
                $fieldCell->explode($explosion);
            }
            $this->context->send(Message::fromCode(FieldLogic::$name, FieldLogic::EVENT_UPDATE_CLIENTS, $room), $sender);
            $this->context->executeAfter(function () use ($data, $sender) {
                $this->context->send(Message::fromCode(self::$name, self::EVENT_MOVE, $data), $sender);
            }, Config::get(Config::BOMB_MOVEMENT_SPEED));
        } else {
            $bomb->setMoving(false);
        }
    }
}
