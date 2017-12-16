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

namespace bomberman\logic;

use bomberman\Context;
use bomberman\io\Message;
use Ratchet\ConnectionInterface;

abstract class BaseLogic
{

    /**
     * @var string
     */
    public static $name = '';

    /**
     * @var Context
     */
    protected $context;

    /**
     * BaseLogic constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param Message $message
     * @param ClientConnection $sender
     */
    public function execute($message, $sender)
    {
        // TODO: use reflection and protect unwanted method calls !
        //      !! message.save
        $event = $message->getEvent();
        $this->$event($message->getData(), $sender);
    }

}
