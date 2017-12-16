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

namespace bomberman\components\field;

abstract class BaseInCell implements InCell, \JsonSerializable
{

    const BASE_PRIORITY = 100;

    /**
     * @var int $x
     */
    protected $x;

    /**
     * @var int $y
     */
    protected $y;

    /**
     * @var string $id
     */
    protected $id;

    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
        $this->id = md5(openssl_random_pseudo_bytes(128));
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'x' => $this->getX(),
            'y' => $this->getY(),
            'class' => strtolower($this->getClass()),
            'displayPriority' => $this->getDisplayPriority(),
            'id' => $this->id,
        ];
    }

    /**
     * @return array
     */
    public function backup()
    {
        return array_merge($this->jsonSerialize(), [
            'id' => $this->id,
            'class' => get_class($this),
        ]);
    }

    /**
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @param int $x
     * @return $this
     */
    public function setX($x)
    {
        $this->x = $x;
        return $this;
    }

    /**
     * @return int
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @param int $y
     * @return $this
     */
    public function setY($y)
    {
        $this->y = $y;
        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        $reflectionClass = new \ReflectionClass(get_class($this));
        return $reflectionClass->getShortName();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

}
