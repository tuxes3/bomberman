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
 * Interface FieldJSLogic
 * @package bomberman\logic\javascript
 */
interface FieldJSLogic
{
    public const NAME = 'field_js';

    public const EVENT_UPDATE = 'update';

    public const EVENT_PATCH = 'patch';
}
