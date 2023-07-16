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

namespace bomberman\logic;

use Ratchet\ConnectionInterface;

/**
 * Class ClientConnection
 * @package bomberman\logic
 */
class ClientConnection implements ConnectionInterface
{
    /**
     * ClientConnection constructor.
     * @param ConnectionInterface $connection
     * @param string $uuid
     */
    public function __construct(
        protected $connection,
        protected $uuid
    ) {
    }

    /**
     * @return null|string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    public function send($data): void
    {
        if (!is_null($this->connection)) {
            $this->connection->send($data);
        }
    }

    public function close()
    {
        if (!is_null($this->connection)) {
            $this->connection->close();
        }
    }
}
