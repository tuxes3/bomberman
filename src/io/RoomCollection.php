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

namespace bomberman\io;

use bomberman\components\field\FieldCell;
use bomberman\components\field\Player;
use bomberman\components\Room;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Class DataCollection
 * @package bomberman\io
 */
class RoomCollection extends ArrayCollection implements \JsonSerializable
{

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @param string $uniqueId
     */
    public function removeUniqueId($uniqueId)
    {
        /** @var Room $room */
        foreach ($this as $key => $room) {
            if ($room->getUniqueId() == $uniqueId) {
                $this->remove($key);
                break;
            }
        }
    }

    /**
     * @return string
     */
    public function getFreeUniqueId()
    {
        $mayNextId = substr(md5(openssl_random_pseudo_bytes(128)), 0, 8);
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('uniqueId', $mayNextId));
        $tmp = $this->matching($criteria);
        while ($tmp->count() > 0) {
            $mayNextId = substr(md5(openssl_random_pseudo_bytes(128)), 0, 8);
            $criteria = Criteria::create()
                ->where(Criteria::expr()->eq('uniqueId', $mayNextId));
            $tmp = $this->matching($criteria);
        }
        return $mayNextId;
    }

    /**
     * @param $uniqueId
     * @return Room|null
     */
    public function findRoomByUniqueId($uniqueId)
    {
        /** @var Room $room */
        foreach ($this as $room) {
            if ($room->getUniqueId() == $uniqueId) {
                return $room;
            }
        }
        return null;
    }

    /**
     * @param string $uuid
     * @return Player
     */
    public function findPlayerBySender($uuid)
    {
        $room = $this->findRoomBySender($uuid);
        if ($room) {
            return $room->getField()->getFieldCollection()->findPlayerBySender($uuid);
        }
        return null;
    }

    /**
     * @param string $uuid
     * @return Room|null
     */
    public function findRoomBySender($uuid)
    {
        $room = $this->filter(function (Room $room) use ($uuid) {
            return in_array($uuid, $room->getConnectedPlayers());
        });
        /** @var Room|bool $room */
        $room = $room->first();
        if ($room) {
            return $room;
        }
        return null;
    }

}
