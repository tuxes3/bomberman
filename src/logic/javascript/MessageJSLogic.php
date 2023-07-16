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
 * Interface MessageJSLogic
 * @package bomberman\logic\javascript
 */
interface MessageJSLogic
{
    public const NAME = 'message_js';

    public const EVENT_WARNING = 'warning';

    public const EVENT_INFO = 'info';
}
