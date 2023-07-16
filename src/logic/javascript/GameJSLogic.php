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

namespace bomberman\logic\javascript;

/**
 * Interface GameJSLogic
 * @package bomberman\logic\javascript
 */
interface GameJSLogic
{
    public const NAME = 'game_js';

    public const EVENT_STARTED = 'started';

    public const EVENT_FINISHED = 'finished';

    public const EVENT_BOMB_MOVEMENT_SPEED = 'bombMovementSpeed';
}
