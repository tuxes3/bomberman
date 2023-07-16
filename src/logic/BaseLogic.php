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

use bomberman\io\Message;
use bomberman\logic\javascript\MessageJSLogic;

/**
 * Class BaseLogic
 * @package bomberman\logic
 */
abstract class BaseLogic
{
    /**
     * @var string
     */
    public static $name = '';

    public function __construct(
        protected \bomberman\Context $context
    ) {
    }

    /**
     * @return array
     */
    abstract public function getEventsAllowedFromClient();

    /**
     * @param Message $message
     * @param ClientConnection $sender
     */
    public function execute($message, $sender)
    {
        $event = $message->getEvent();
        if (($message->isFromClient() && !in_array($event, $this->getEventsAllowedFromClient()))
            || !method_exists($this, $event)) {
            $sender->send(json_encode(Message::fromCode(MessageJSLogic::NAME, MessageJSLogic::EVENT_WARNING, 'Event not found.'), JSON_THROW_ON_ERROR));
        } else {
            $this->{$event}($message->getData(), $sender);
        }
    }
}
