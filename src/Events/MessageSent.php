<?php

namespace NeuronAI\Events;

use NeuronAI\Chat\Messages\Message;

class MessageSent
{
    public function __construct(
        public Message $message,
        public Message $response
    ) {}
}
