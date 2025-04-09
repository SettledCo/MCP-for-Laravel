<?php

namespace NeuronAI\Chat\Messages;

class AssistantMessage extends Message
{
    public function __construct(array|string|int|float|null $content)
    {
        parent::__construct(Message::ROLE_ASSISTANT, $content);
    }
}
