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
use bomberman\components\field\FieldCell;
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

    const EVENT_NAME = 'check';

    /**
     * @param \stdClass $data
     * @param ClientConnection $sender
     */
    public function check($data, $sender)
    {
        /** @var Room $room */
        foreach ($this->context->getData() as $room) {
            $updateRoom = false;
            /** @var FieldCell $fieldCell */
            foreach ($room->getField()->getFieldCollection()->filterContainsItem() as $fieldCell) {
                $updateRoom = $updateRoom || $fieldCell->consumeItem();
            }
            if ($updateRoom) {
                $this->context->sendToClients($room->getConnectedPlayers(), Message::fromCode(FieldJSLogic::NAME, FieldJSLogic::EVENT_UPDATE, $room->getField()));
            }
        }
    }

}
