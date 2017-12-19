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

use bomberman\components\Field;
use bomberman\components\field\Bomb;
use bomberman\components\field\Explosion;
use bomberman\components\field\FieldCell;
use bomberman\components\Room;
use bomberman\io\Config;
use bomberman\io\Message;
use bomberman\logic\javascript\FieldJSLogic;
use bomberman\logic\javascript\GameJSLogic;

/**
 * Class ExplosionLogic
 * @package bomberman\logic
 */
class ExplosionLogic extends BaseLogic
{

    const EVENT_REMOVE = 'remove';
    const EVENT_CREATE = 'create';

    public static $name = 'explosion';

    /**
     * @return array
     */
    public function getEventsAllowedFromClient()
    {
        return [];
    }

    /**
     * @param Explosion $explosion
     * @param ClientConnection $sender
     */
    public function remove($explosion, $sender)
    {
        $explosion->setTimer(null);
        /** @var Room $room */
        $room = $this->context->getData()->findRoomBySender($sender->getUuid());
        if (!$room) {
            return;
        }
        $current = milliseconds();
        if (($current - $explosion->getExploded()) >= Config::get(Config::EXPLOSION_DURATION)) {
            $room->getField()->getXY($explosion->getX(), $explosion->getY())->removeById($explosion->getId());
            $this->context->sendToClients(
                $room->getConnectedPlayers(),
                Message::fromCode(FieldJSLogic::NAME, FieldJSLogic::EVENT_UPDATE, $room->getField())
            );
        }
    }

    /**
     * @param \stdClass $data
     * @param ClientConnection $sender
     */
    public function create($data, $sender)
    {
        /** @var Room $room */
        $room = $data->room;
        /** @var Field $field */
        $field = $room->getField();
        /** @var Bomb $bomb */
        $bomb = $data->bomb;
        $spread = $bomb->getExplosionSpread() - 1;
        $this->setExplosionAt($room, $bomb->getX(), $bomb->getY(), $sender);
        foreach ([[1, 0], [0, 1], [-1, 0], [0, -1]] as $movements) {
            $x = $bomb->getX();
            $y = $bomb->getY();
            for ($i = 0; $i < $spread; $i++) {
                $x += $movements[0];
                $y += $movements[1];
                if ($this->setExplosionAt($room, $x, $y, $sender)) {
                    break;
                }
            }
        }
    }

    /**
     * @param Room $room
     * @param $x
     * @param $y
     * @param ClientConnection $sender
     * @return boolean
     */
    private function setExplosionAt($room, $x, $y, $sender)
    {
        /** @var FieldCell $fieldCell */
        $fieldCell = $room->getField()->getXY($x, $y);
        if (is_null($fieldCell)) {
            return true;
        }
        $blockExplosion = $fieldCell->blocksExplosion();
        $explosion = new Explosion($x, $y);
        $fieldCell->explode($explosion);
        $this->context->send(Message::fromCode(FieldLogic::$name, FieldLogic::EVENT_CHECK_FINISH, $room), $sender);
        $fieldCell->add($explosion);
        $this->context->executeAfter(function () use ($explosion, $sender) {
            $this->context->send(Message::fromCode(ExplosionLogic::$name, ExplosionLogic::EVENT_REMOVE, $explosion), $sender);
        }, Config::get(Config::EXPLOSION_DURATION));
        return $blockExplosion;
    }

}
