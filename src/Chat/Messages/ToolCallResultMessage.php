<?php

namespace NeuronAI\Chat\Messages;

class ToolCallResultMessage extends UserMessage
{
    public function __construct(protected array $tools)
    {
        parent::__construct(null);
    }

    public function getTools(): array
    {
        return $this->tools;
    }

    public function jsonSerialize(): array
    {
        return \array_merge(
            parent::jsonSerialize(),
            [
                'tools' => \array_map(fn ($tool) => $tool->jsonSerialize(), $this->tools)
            ]
        );
    }
}
