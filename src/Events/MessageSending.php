<?php

namespace NeuronAI\Events;

use NeuronAI\Chat\Messages\Message;

class MessageSending
{
    public function __construct(public Message $message) {}
}
