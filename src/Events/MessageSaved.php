<?php

namespace NeuronAI\Events;

use NeuronAI\Chat\Messages\Message;

class MessageSaved
{
    public function __construct(public Message $message) {}
}
