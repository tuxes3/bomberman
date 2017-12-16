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

use bomberman\components\field\Bomb;
use bomberman\components\Room;
use bomberman\io\Config;
use bomberman\io\Message;
use bomberman\logic\javascript\FieldJSLogic;

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
