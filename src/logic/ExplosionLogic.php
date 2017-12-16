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

    const EVENT_CHECK = 'check';
    const EVENT_CREATE = 'create';

    public static $name = 'explosion';

    /**
     * @param \stdClass $data
     * @param ClientConnection $sender
     */
    public function check($data, $sender)
    {
        $current = milliseconds();
        /** @var Room $room */
        foreach ($this->context->getData() as $room) {
            $updateRoom = false;
            /** @var Explosion $explosion */
            foreach ($room->getField()->getFieldCollection()->findExplosions() as $explosion) {
                $fieldCell = $room->getField()->getXY($explosion->getX(), $explosion->getY());
                if (($current - $explosion->getExploded()) >= Config::get(Config::EXPLOSION_DURATION)) {
                    $fieldCell->removeById($explosion->getId());
                    $updateRoom = true;
                } else {
                    $updateRoom = $fieldCell->explode($explosion);
                }
            }
            if ($updateRoom || $room->getField()->isFinished()) {
                $this->context->sendToClients(
                    $room->getConnectedPlayers(),
                    Message::fromCode(FieldJSLogic::NAME, FieldJSLogic::EVENT_UPDATE, $room->getField())
                );
                if ($room->getField()->isFinished()) {
                    $this->context->send(Message::fromCode(RoomLogic::$name, RoomLogic::EVENT_CLOSE, $room), $sender);
                    // send finish
                    foreach ($room->getConnectedPlayers() as $uuid) {
                        $player = $room->getField()->getFieldCollection()->findPlayerBySender($uuid);
                        $data = new \stdClass();
                        $data->won = $player->isAlive();
                        $this->context->sendToClients(
                            [$uuid],
                            Message::fromCode(GameJSLogic::NAME, GameJSLogic::EVENT_FINISHED, $data)
                        );
                    }
                }
            }
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
        $this->setExplosionAt($field, $bomb->getX(), $bomb->getY());
        foreach ([[1, 0], [0, 1], [-1, 0], [0, -1]] as $movements) {
            $x = $bomb->getX();
            $y = $bomb->getY();
            for ($i = 0; $i < $spread; $i++) {
                $x += $movements[0];
                $y += $movements[1];
                if ($this->setExplosionAt($field, $x, $y)) {
                    break;
                }
            }
        }
    }

    /**
     * @param Field $field
     * @param $x
     * @param $y
     * @return boolean
     */
    private function setExplosionAt($field, $x, $y)
    {
        /** @var FieldCell $fieldCell */
        $fieldCell = $field->getXY($x, $y);
        if (is_null($fieldCell)) {
            return true;
        }
        $blockExplosion = $fieldCell->blocksExplosion();
        $fieldCell->add(new Explosion($x, $y));
        return $blockExplosion;
    }

}
