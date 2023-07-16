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

use Symfony\Component\Yaml\Yaml;

/**
 * Class Config
 * @package bomberman\io
 */
class Config
{
    final public const MOVEMENT_SPEED = 'movement_speed';

    final public const BOMB_MOVEMENT_SPEED = 'bomb_movement_speed';

    final public const BOMB_COUNT = 'bomb_count';

    final public const BOMB_INTERVAL = 'bomb_interval';

    final public const BOMB_TIMEOUT = 'bomb_timeout';

    final public const EXPLOSION_DURATION = 'explosion_duration';

    final public const EXPLOSION_SPREAD = 'explosion_spread';

    final public const EXPLOSION_INTERVAL = 'explosion_interval';

    final public const BACK_UP_INTERVAL = 'back_up_interval';

    final public const ITEM_INTERVAL = 'item_interval';

    final public const MAX_MOVEMENT_SPEED = 'max_movement_speed';

    final public const ITEM_MOVEMENT_SPEED_DECREASE = 'item_movement_speed_decrease';

    final public const ROOM_EXPIRATION_SECONDS = 'room_expiration_seconds';

    final public const MAX_ROOMS_PER_PLAYER = 'max_rooms_per_player';

    private static $configFile = null;

    /**
     * @param string $key
     * @return string
     */
    public static function get($key)
    {
        if (is_null(self::$configFile)) {
            self::$configFile = Yaml::parseFile(__DIR__.'/../../app/config.yml');
        }
        return self::$configFile[$key];
    }
}
