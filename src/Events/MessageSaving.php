<?php

namespace NeuronAI\Events;

use NeuronAI\Chat\Messages\Message;

class MessageSaving
{
    public function __construct(public Message $message) {}
}
