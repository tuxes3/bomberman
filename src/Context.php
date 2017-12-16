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

namespace bomberman;

use bomberman\io\RoomCollection;
use bomberman\io\Message;
use bomberman\logic\ClientConnection;
use Ratchet\ConnectionInterface;

interface Context
{

    const SEND_ALL = -1;

    /**
     * @param Message $message
     * @param ClientConnection $from
     */
    public function send($message, $from);

    /**
     * @return RoomCollection
     */
    public function getData();

    /**
     * @param array|int[]|int $playerIds
     * @param Message $message
     */
    public function sendToClients($playerIds, $message);

}
