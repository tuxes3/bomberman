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
use bomberman\components\field\Bomb;
use bomberman\components\Room;
use bomberman\io\Config;
use bomberman\io\Message;
use bomberman\logic\javascript\FieldJSLogic;
use Ratchet\ConnectionInterface;

/**
 * Class BombLogic
 * @package bomberman\logic
 */
class BombLogic extends BaseLogic
{

    public static $name = 'bomb';

    const EVENT_CHECK = 'check';

    /**
     * @param $data
     * @param ClientConnection $sender
     */
    public function check($data, $sender)
    {
        $current = milliseconds();
        /** @var Room $room */
        foreach ($this->context->getData() as $room) {
            $updateRoom = false;
            /** @var Bomb $bomb */
            foreach ($room->getField()->getFieldCollection()->findBombs() as $bomb) {
                if (($current - $bomb->getPlanted()) >= Config::get(Config::BOMB_TIMEOUT)) {
                    $fieldCell = $room->getField()->getXY($bomb->getX(), $bomb->getY());
                    $fieldCell->removeById($bomb->getId());
                    $std = new \stdClass();
                    $std->bomb = $bomb;
                    $std->room = $room;
                    $this->context->send(Message::fromCode(ExplosionLogic::$name, ExplosionLogic::EVENT_CREATE, $std), $sender);
                    $updateRoom = true;
                }
            }
            if ($updateRoom) {
                $this->context->sendToClients($room->getConnectedPlayers(), Message::fromCode(FieldJSLogic::NAME, FieldJSLogic::EVENT_UPDATE, $room->getField()));
            }
        }
    }

}
