<?php
/*
 * Copyright (c) 2017, whatwedo GmbH
 * All rights reserved
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
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
use Ratchet\ConnectionInterface;

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
                    $updateRoom = $fieldCell->explode();
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
