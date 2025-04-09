<?php

namespace Settled\MCP\Events;

use Settled\MCP\Chat\Messages\Message;

class VectorStoreSearching
{
    public function __construct(
        public Message $question
    ) {}
}
