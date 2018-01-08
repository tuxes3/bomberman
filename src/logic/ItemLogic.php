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
use bomberman\components\field\BaseItem;
use bomberman\components\Room;
use bomberman\io\Message;
use bomberman\logic\javascript\FieldJSLogic;

/**
 * Class ItemLogic
 * @package bomberman\logic
 */
class ItemLogic extends BaseLogic
{

    public static $name = 'item';

    const EVENT_NAME = 'remove';

    /**
     * @return array
     */
    public  function getEventsAllowedFromClient()
    {
        return [];
    }

    /**
     * @param BaseItem $data
     * @param ClientConnection $sender
     */
    public function remove($data, $sender)
    {
        /** @var Room $room */
        $room = $this->context->getData()->findRoomBySender($sender->getUuid());
        if (is_null($room)) {
            return;
        }
        $room->getField()->getXY($data->getX(), $data->getY())->removeById($data->getId());
        $this->context->send(Message::fromCode(FieldLogic::$name, FieldLogic::EVENT_UPDATE_CLIENTS, $room), $sender);
    }

}
