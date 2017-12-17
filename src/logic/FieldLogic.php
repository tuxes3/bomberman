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
use bomberman\components\field\Block;
use bomberman\components\field\FixBlock;
use bomberman\components\field\FieldCell;
use bomberman\components\field\Player;
use bomberman\components\Room;
use bomberman\io\Message;
use bomberman\logic\javascript\FieldJSLogic;
use bomberman\logic\javascript\GameJSLogic;

/**
 * Class FieldLogic
 * @package bomberman\logic
 */
class FieldLogic extends BaseLogic
{
    const EVENT_START = 'start';
    const EVENT_UPDATE_CLIENTS = 'updateClients';
    const EVENT_CHECK_FINISH = 'checkFinish';

    /**
     * @var string
     */
    public static $name = 'field';

    /**
     * @param Room $room
     * @param ClientConnection $sender
     */
    protected function checkFinish($room, ClientConnection $sender)
    {
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
        $height= 11 + (($playercount-2)*2);
        $width= 11 + (($playercount-2)*2);


        // if more than 8 players no more square field. instead a wider one :)
        if($playercount > 8){
            $height = 11 + ((8-2)*2);
        }

        // initialize fieldCells
        for ($i = 0; $i < $height; $i++) {
            $cells[] = [];
            for ($j = 0; $j < $width; $j++) {
                $cells[$i][$j] = new FieldCell();
            }
        }

        // STEP 1: Generate fix (not bombable) blocks 
        // (every second row and every second column)
        for ($i = 1; $i < $height-1; $i = $i+2) {
            for ($j = 1; $j < $width-1; $j = $j+2) {
                $cells[$i][$j]->add(new FixBlock($i, $j));
            }
        }

        // step 2: generate random blocks
        for ($i = 0; $i < $height; $i++) {
            for ($j = 0; $j < $width; $j++) {
                if($cells[$i][$j]->isEmpty()){
                    if (1 == rand(1, 2)) {
                        $cells[$i][$j]->add(new Block($i, $j));
                    }
                }
            }
        }


        //step 3: spawn players
        // this stuff is pretty hardcoded...
        // if you have a better solution, feel free to implement it and create a merge request on github :)
        $players = $room->getConnectedPlayers();

        // NOTICE WE DECREASE TO USE THEM AS INDEXES IN ARRAYS!!!
        // otherwise we always would have to subtract 1 more, which makes stuff so confusing and not that inuitive
        $height--;
        $width--;


        // first 4 are always the same (in the corners)
        $cells[0][0] = new FieldCell();
        $cells[0][1] = new FieldCell();
        $cells[1][0] = new FieldCell();
        $cells[0][0]->add(new Player(0, 0, $players[0]));

        if($playercount >= 2){
            $cells[$height][$width] = new FieldCell();
            $cells[$height-1][$width] = new FieldCell();
            $cells[$height][$width-1] = new FieldCell();
            $cells[$height][$width]->add(new Player($height, $width, $players[1]));
        }

        if($playercount >= 3){
            $cells[0][$width] = new FieldCell();
            $cells[0][$width-1] = new FieldCell();
            $cells[1][$width] = new FieldCell();
            $cells[0][$width]->add(new Player(0, $width, $players[2]));
        }

        if($playercount >= 4){
            $cells[$height][0] = new FieldCell();
            $cells[$height-1][0] = new FieldCell();
            $cells[$height][1] = new FieldCell();
            $cells[$height][0]->add(new Player($height, 0, $players[3]));
        }

        // 5-8  => got to the middle of the sides
        if($playercount >= 5 ) {
            // left side middle
            $cells[$height/2][0] = new FieldCell(); // midddle
            $cells[$height/2][1] = new FieldCell();
            $cells[($height/2)+1][0] = new FieldCell();
            $cells[$height/2][0]->add(new Player($height/2, 0, $players[4]));
        }

        if($playercount>= 6 ){
            // 6 right side middle
            $cells[$height/2][$width] = new FieldCell();
            $cells[$height/2][$width-1] = new FieldCell();
            $cells[($height/2)-1][$width] = new FieldCell();
            $cells[$height/2][$width]->add(new Player($height/2, $width, $players[5]));
        }



        if($playercount>=7 && $playercount < 10 ){
            // 7 top middle
            $cells[0][$width/2] = new FieldCell();
            $cells[0][($width/2)+1] = new FieldCell();
            $cells[1][$width/2] = new FieldCell();
            $cells[0][$width/2]->add(new Player(0, $width/2, $players[6]));
        }


        if($playercount==8){
            // 8 bottom middle
            $cells[$height][$width/2] = new FieldCell();
            $cells[$height][($width/2)+1] = new FieldCell();
            $cells[$height][$width/2] = new FieldCell();
            $cells[$height][$width/2]->add(new Player($height, $width/2, $players[7]));
        }


        // above 8 => field goes wide :)
        //  9 / 10 => two in between on top and on bottom
        if($playercount >= 9){
            // player 8 on bottom after 1/3
            $cells[$height][round($width/3)] = new FieldCell();
            $cells[$height][round(($width/3))+1] = new FieldCell();
            $cells[$height-1][round($width/3)] = new FieldCell();
            $cells[$height][round($width/3)]->add(new Player($height, round($width/3), $players[7]));

            // player 9 on bottom after 2/3
            $cells[$height][$width-round($width/3)] = new FieldCell();
            $cells[$height][$width-(round($width/3)+1)] = new FieldCell();
            $cells[$height-1][$width-round($width/3)] = new FieldCell();
            $cells[$height][$width-round($width/3)]->add(new Player($height, $width - round($width/3), $players[8]));
        }

        if($playercount == 10){
            // player 7 on top after 1/3
            $cells[0][round($width/3)] = new FieldCell();
            $cells[0][round(($width/3))+1] = new FieldCell();
            $cells[1][round($width/3)] = new FieldCell();
            $cells[0][round($width/3)]->add(new Player(0, round($width/3), $players[6]));

            // player 10 on top after 2/3
            $cells[0][$width-round($width/3)] = new FieldCell();
            $cells[0][$width-(round($width/3)+1)] = new FieldCell();
            $cells[1][$width-round($width/3)] = new FieldCell();
            $cells[0][$width-round($width/3)]->add(new Player(0, $width - round($width/3), $players[9]));
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
