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
    public function jsonSerialize(): array
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
        if ($room instanceof \bomberman\components\Room) {
            return $room->getField()->getFieldCollection()->findPlayerBySender($uuid);
        }
        return null;
    }

    /**
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function findExpiredRoom()
    {
        $fiveMinutesEarlier = new \DateTime();
        $fiveMinutesEarlier->modify(sprintf('-%s seconds', Config::get(Config::ROOM_EXPIRATION_SECONDS)));
        $criteria = Criteria::create()->where(
            Criteria::expr()->lte('lastTouch', $fiveMinutesEarlier)
        );
        return $this->matching($criteria);
    }

    /**
     * @param string $uuid
     * @return ArrayCollection|\Doctrine\Common\Collections\Collection
     */
    public function findByCreatedBy($uuid)
    {
        $criteria = Criteria::create()
            ->where(
                Criteria::expr()->eq('createdBy', $uuid)
            );
        return $this->matching($criteria);
    }

    /**
     * @param string $uuid
     * @return Room|null
     */
    public function findRoomBySender($uuid)
    {
        $rooms = $this->filter(fn (Room $room): bool => in_array($uuid, $room->getConnectedPlayers()));
        /** @var Room|bool $room */
        $room = $rooms->first();
        if ($room) {
            return $room;
        }
        return null;
    }
}
