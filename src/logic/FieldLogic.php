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

use bomberman\components\field\Block;
use bomberman\components\field\FieldCell;
use bomberman\components\field\Player;
use bomberman\components\Room;
use bomberman\io\Message;
use bomberman\logic\javascript\FieldJSLogic;
use bomberman\logic\javascript\GameJSLogic;
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
     * @param ClientConnection $sender
     */
    protected function start($data, ClientConnection $sender)
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
                        break(2);
                    }
                }
            }
        }
        $room->getField()->setCells($cells);
        $this->context->sendToClients(
            $room->getConnectedPlayers(),
            Message::fromCode(GameJSLogic::NAME, GameJSLogic::EVENT_STARTED, null)
        );
        $this->updateClients($data, $sender);
    }

    /**
     * @param \stdClass $data
     * @param ClientConnection $sender
     */
    public function updateClients($data, ClientConnection $sender)
    {
        /** @var Room $room */
        $room = $this->context->getData()->findRoomByUniqueId($data->uniqueId);
        $this->context->sendToClients(
            $room->getConnectedPlayers(),
            Message::fromCode(FieldJSLogic::NAME, FieldJSLogic::EVENT_UPDATE, $room->getField())
        );
    }

}
