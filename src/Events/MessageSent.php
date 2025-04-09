<?php

namespace Settled\MCP\Events;

use Settled\MCP\Chat\Messages\Message;

class MessageSent
{
    public function __construct(
        public Message $message,
        public Message $response
    ) {}
}
