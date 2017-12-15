<?php
/*
 * Copyright (c) 2017, whatwedo GmbH
 * All rights reserved
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
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
    private $save = false;

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
        $instance->save = true;
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

}
