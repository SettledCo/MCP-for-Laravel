<?php

namespace Settled\MCP\Events;

use Settled\MCP\Chat\Messages\Message;

class MessageSaving
{
    public function __construct(public Message $message) {}
}
