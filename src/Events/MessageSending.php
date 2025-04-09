<?php

namespace Settled\MCP\Events;

use Settled\MCP\Chat\Messages\Message;

class MessageSending
{
    public function __construct(public Message $message) {}
}
