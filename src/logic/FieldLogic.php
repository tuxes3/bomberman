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

use bomberman\components\field\Block;
use bomberman\components\field\FieldCell;
use bomberman\components\field\Player;
use bomberman\components\Room;
use bomberman\io\Message;
use bomberman\logic\javascript\FieldJSLogic;
use Ratchet\ConnectionInterface;

/**
 * Class FieldLogic
 * @package bomberman\logic
 */
class FieldLogic extends BaseLogic
{

    const EVENT_START = 'start';
    const EVENT_UPDATE_CLIENTS = 'updateClients';

    /**
     * @var string
     */
    public static $name = 'field';

    /**
     * @param \stdClass $data
     * @param ConnectionInterface $sender
     */
    protected function start($data, ConnectionInterface $sender)
    {
        /** @var Room $room */
        $room = $this->context->getData()->findRoomByUniqueId($data->uniqueId);
        $cells = [];

        // TODO replace with real map creation algo ...
        for ($i = 0; $i < 10; $i++) {
            $cells[] = [];
            for ($j = 0; $j < 10; $j++) {
                $cells[$i][$j] = new FieldCell();
                if (1 == rand(1, 4)) {
                    $cells[$i][$j]->add(new Block($i, $j));
                }
            }
        }
        foreach ($room->getConnectedPlayers() as $playerId) {
            foreach ($cells as $i => $row) {
                /** @var FieldCell $cell */
                foreach ($row as $j => $cell) {
                    if ($cell->canPlayerEnter()) {
                        $player = new Player($i, $j, $playerId);
                        $cell->add($player);
                    }
                    break(2);
                }
            }
        }
        $room->getField()->setCells($cells);
        $this->updateClients($data, $sender);
    }

    /**
     * @param \stdClass $data
     * @param ConnectionInterface $sender
     */
    public function updateClients($data, ConnectionInterface $sender)
    {
        /** @var Room $room */
        $room = $this->context->getData()->findRoomByUniqueId($data->uniqueId);
        $this->context->sendToClients(
            $room->getConnectedPlayers(),
            Message::fromCode(FieldJSLogic::NAME, FieldJSLogic::EVENT_UPDATE, $room->getField())
        );
    }

}
