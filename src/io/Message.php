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

/**
 * Class Message
 * @package bomberman\io
 */
class Message implements \JsonSerializable
{

    /**
     * @var string
     */
    private $logicName;

    /**
     * @var string
     */
    private $event;

    /**
     * @var \stdClass
     */
    private $data;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @var boolean
     */
    private $fromClient;

    private function __construct()
    {
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->logicName,
            'event' => $this->event,
            'data' => $this->data,
        ];
    }

    /**
     * @param string $message
     * $message :=
     * {
     *      name: "xxx",
     *      event: "yyy",
     *      uuid: "zzz",
     *      data: {
     *          ...
     *      }
     * }
     * @return Message
     */
    public static function fromJson($message)
    {
        $instance = new self();
        $data = json_decode($message);
        $instance->logicName = $data->name;
        $instance->event = $data->event;
        $instance->data = $data->data;
        $instance->uuid = $data->uuid;
        $instance->fromClient = true;
        return $instance;
    }

    /**
     * @param $name
     * @param $event
     * @param $data
     * @return Message
     */
    public static function fromCode($name, $event, $data)
    {
        $instance = new self();
        $instance->logicName = $name;
        $instance->event = $event;
        $instance->data = $data;
        $instance->fromClient = false;
        return $instance;
    }


    /**
     * @return string
     */
    public function getLogicName()
    {
        return $this->logicName;
    }

    /**
     * @return \stdClass
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return bool
     */
    public function isFromClient()
    {
        return $this->fromClient;
    }

}
