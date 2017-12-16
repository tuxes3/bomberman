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
    private $createdAt;

    /**
     * @var array|string[]
     */
    private $connectedPlayers;

    /**
     * @var Field
     */
    private $field;

    /**
     * Room constructor.
     * @param int $maxPlayers
     * @param string $uniqueId
     */
    public function __construct($maxPlayers, $uniqueId)
    {
        $this->maxPlayers = $maxPlayers;
        $this->uniqueId = $uniqueId;
        $this->connectedPlayers = [];
        $this->createdAt = new \DateTime();
        // TODO: calculate field size depending on player
        $this->field = new Field($maxPlayers);
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
        return true;
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
    public function getCreatedAt()
    {
        return $this->createdAt;
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

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
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

}
