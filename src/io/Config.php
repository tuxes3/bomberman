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

use Symfony\Component\Yaml\Yaml;

class Config
{

    const MOVEMENT_SPEED = 'movement_speed';
    const BOMB_COUNT = 'bomb_count';
    const BOMB_INTERVAL = 'bomb_interval';
    const BOMB_TIMEOUT = 'bomb_timeout';
    const EXPLOSION_DURATION = 'explosion_duration';
    const EXPLOSION_SPREAD = 'explosion_spread';
    const EXPLOSION_INTERVAL = 'explosion_interval';
    const BACK_UP_INTERVAL = 'back_up_interval';

    private static $configFile = null;

    /**
     * @param string $key
     * @return string
     */
    public static function get($key)
    {
        if (is_null(Config::$configFile)) {
            Config::$configFile = Yaml::parseFile(__DIR__.'/../../app/config.yml');
        }
        return Config::$configFile[$key];
    }

}
