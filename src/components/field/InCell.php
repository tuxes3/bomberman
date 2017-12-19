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

/**
 * Interface InCell
 * @package bomberman\components\field
 */
interface InCell
{

    /**
     * @return int
     */
    public function getX();

    /**
     * @return int
     */
    public function getY();

    /**
     * @param int $x
     */
    public function setX($x);

    /**
     * @param int $y
     */
    public function setY($y);

    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getClass();

    /**
     * @return boolean
     */
    public function canPlayerEnter();

    /**
     * @return boolean
     */
    public function canBombEnter();

    /**
     * @return boolean
     */
    public function blocksExplosion();

    /**
     * @return int
     */
    public function getDisplayPriority();

    /**
     * @return array
     */
    public function backup();

    /**
     * @param array $data
     * @return self
     */
    public static function restore($data);

}
