<?php

declare(strict_types=1);
/*
 * This file is part of the bomberman project.
 *
 * @author Nicolo Singer tuxes3@outlook.com
 * @author Lukas Müller computer_bastler@hotmail.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace bomberman\io;

use bomberman\components\Field;
use bomberman\components\field\FieldCell;
use bomberman\components\Room;

/**
 * Class BackupManager
 * @package bomberman\io
 */
class BackupManager
{
    final public const BACK_UP_FILE = __DIR__.'/../../var/rooms.json';

    public function backup(RoomCollection $roomCollection)
    {
        $backup = [];
        foreach ($roomCollection->toArray() as $room) {
            $backup[] = $this->getRoomAsArray($room);
        }
        file_put_contents(self::BACK_UP_FILE, json_encode($backup, JSON_PRETTY_PRINT));
    }

    /**
     * @return RoomCollection
     */
    public function restore()
    {
        $restore = new RoomCollection();
        $rooms = json_decode(file_get_contents(self::BACK_UP_FILE), true, 512, JSON_THROW_ON_ERROR);
        foreach ($rooms as $room) {
            $restore->add($this->getRoomAsObject($room));
        }
        return $restore;
    }

    /**
     * @return Room
     */
    private function getRoomAsObject(array $room)
    {
        $newRoom = new Room($room['maxPlayers'], $room['uniqueId'], $room['name'], $room['createdBy']);
        return $newRoom
            ->setConnectedPlayers($room['connectedPlayers'])
            ->setLastTouch((new \DateTime())->setTimestamp($room['lastTouch']))
            ->setField($this->getFieldAsObject($room['field']))
        ;
    }

    /**
     * @return Field
     */
    private function getFieldAsObject(array $field)
    {
        $newField = new Field($field['maxPlayers']);
        $cells = [];
        foreach ($field['cells'] as $i => $row) {
            $cells[$i] = [];
            foreach ($row as $j => $fieldCell) {
                $newFieldCell = new FieldCell();
                foreach ($fieldCell as $inCell) {
                    $newInCell = call_user_func($inCell['class'].'::restore', $inCell);
                    $newFieldCell->add($newInCell);
                }
                $cells[$i][$j] = $newFieldCell;
            }
        }
        $newField->setCells($cells);
        return $newField;
    }

    private function getRoomAsArray(Room $room)
    {
        return [
            'uniqueId' => $room->getUniqueId(),
            'lastTouch' => $room->getLastTouch()->getTimestamp(),
            'maxPlayers' => $room->getMaxPlayers(),
            'connectedPlayers' => $room->getConnectedPlayers(),
            'field' => $this->getFieldAsArray($room->getField()),
            'name' => $room->getName(),
            'createdBy' => $room->getCreatedBy(),
        ];
    }

    /**
     * @return array
     */
    private function getFieldAsArray(Field $field)
    {
        $cells = $field->getCells();
        foreach ($cells as $key => $row) {
            $cells[$key] = array_map(fn (FieldCell $fieldCell) => $fieldCell->backup(), $row);
        }
        return [
            'cells' => $cells,
            'maxPlayers' => $field->getMaxPlayers(),
        ];
    }
}
