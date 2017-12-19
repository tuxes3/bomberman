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

namespace bomberman\components;

/**
 * Class Room
 * @package components
 */
class Room implements \JsonSerializable
{
    const PLAYER_LIMIT = 10;
    /**
     * @var int
     */
    private $maxPlayers;

    /**
     * @var string
     */
    private $uniqueId;

    /**
     * @var \DateTime
     */
    private $lastTouch;

    /**
     * @var array|string[]
     */
    private $connectedPlayers;

    /**
     * @var Field
     */
    private $field;

    /**
     * @var string
     */
    private $name;

    /**
     * @var $createdBy (player uuid)
     */
    private $createdBy;

    /**
     * Room constructor.
     * @param int $maxPlayers
     * @param string $uniqueId
     * @param string $name
     * @param string $createdBy (uuid)
     */
    public function __construct($maxPlayers, $uniqueId, $name, $createdBy)
    {
        //max 10 players
        if($maxPlayers >= self::PLAYER_LIMIT){
            $maxPlayers = self::PLAYER_LIMIT;
        }

        $this->maxPlayers = $maxPlayers;
        $this->uniqueId = $uniqueId;
        $this->connectedPlayers = [];
        $this->lastTouch = new \DateTime();
        $this->field = new Field($maxPlayers);
        $this->name = $name;
        $this->createdBy = $createdBy;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'maxPlayers' => $this->maxPlayers,
            'connectedPlayers' => count($this->connectedPlayers),
            'uniqueId' => $this->uniqueId,
            'name' => $this->name,
            'players' => $this->connectedPlayers,
        ];
    }

    /**
     * @param int $playerId
     * @return bool|string
     */
    public function addPlayer($playerId)
    {
        if (in_array($playerId, $this->connectedPlayers)) {
            return sprintf('Player is already in room (%s).', $this->uniqueId);
        }
        if (count($this->connectedPlayers) >= $this->maxPlayers) {
            return sprintf('The room (%s) is already full.', $this->uniqueId);
        }
        $this->connectedPlayers[] = $playerId;
        // uncomment for 9 dummy players
        //for ($i = 0; $i < 9; $i++) {
        //    $this->connectedPlayers[] = $i.'dummy-test-player';
        //}
        return true;
    }

    /**
     * @param $playerId
     * @return bool|string
     */
    public function removePlayer($playerId)
    {
        if (($key = array_search($playerId, $this->connectedPlayers)) !== false) {
            unset($this->connectedPlayers[$key]);
        }
    }

    /**
     * @return boolean
     */
    public function isStartable()
    {
        return count($this->connectedPlayers) == $this->maxPlayers;
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @return array|string[]
     */
    public function getConnectedPlayers()
    {
        return $this->connectedPlayers;
    }

    /**
     * @return Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return int
     */
    public function getMaxPlayers()
    {
        return $this->maxPlayers;
    }

    /**
     * @return \DateTime
     */
    public function getLastTouch()
    {
        return $this->lastTouch;
    }

    /**
     * @param int $maxPlayers
     * @return $this
     */
    public function setMaxPlayers($maxPlayers)
    {
        $this->maxPlayers = $maxPlayers;
        return $this;
    }

    /**
     * @param string $uniqueId
     * @return $this
     */
    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;
        return $this;
    }

    public function touch()
    {
        $this->lastTouch = new \DateTime();
    }

    /**
     * @param \DateTime $touchedAt
     * @return $this
     */
    public function setLastTouch($touchedAt)
    {
        $this->lastTouch = $touchedAt;
        return $this;
    }

    /**
     * @param array|string[] $connectedPlayers
     * @return $this
     */
    public function setConnectedPlayers($connectedPlayers)
    {
        $this->connectedPlayers = $connectedPlayers;
        return $this;
    }

    /**
     * @param Field $field
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

}
