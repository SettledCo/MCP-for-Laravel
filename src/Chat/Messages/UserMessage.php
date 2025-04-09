<?php

namespace Settled\MCP\Chat\Messages;

class UserMessage extends Message
{
    public function __construct(array|string|int|float|null $content)
    {
        parent::__construct(Message::ROLE_USER, $content);
    }
}
