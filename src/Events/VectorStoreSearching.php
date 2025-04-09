<?php

namespace NeuronAI\Events;

use NeuronAI\Chat\Messages\Message;

class VectorStoreSearching
{
    public function __construct(
        public Message $question
    ) {}
}
