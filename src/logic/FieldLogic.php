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
use bomberman\components\field\FixBlock;
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

        $playercount = count($room->getConnectedPlayers());

        // add two more rows per additional player
        $fieldsize= 11 + (($playercount-2)*2);

        // initialize fieldCells
        for ($i = 0; $i < $fieldsize; $i++) {
            $cells[] = [];
            for ($j = 0; $j < $fieldsize; $j++) {
                $cells[$i][$j] = new FieldCell();
            }
        }

        // STEP 1: Generate fix (not bombable) blocks 
        // (every second row and every second column)
        for ($i = 1; $i < $fieldsize-1; $i = $i+2) {
            for ($j = 1; $j < $fieldsize-1; $j = $j+2) {
                $cells[$i][$j]->add(new FixBlock($i, $j));
            }
        }

        // step 2: generate random blocks
        for ($i = 0; $i < $fieldsize; $i++) {
            for ($j = 0; $j < $fieldsize; $j++) {
                if($cells[$i][$j]->isEmpty()){
                    if (1 == rand(1, 2)) {
                        $cells[$i][$j]->add(new Block($i, $j));
                    }
                }
            }
        }
        
        
        // Spawn players
        // TODO! @LUKAS
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
