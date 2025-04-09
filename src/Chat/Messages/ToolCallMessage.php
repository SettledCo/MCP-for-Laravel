<?php

namespace NeuronAI\Chat\Messages;

use NeuronAI\Tools\Tool;

class ToolCallMessage extends AssistantMessage
{
    /**
     * @param array<Tool> $tools
     */
    public function __construct(
        protected array|string|int|float|null $content,
        protected array $tools
    ) {
        parent::__construct($this->content);
    }

    /**
     * @return array<Tool>
     */
    public function getTools(): array
    {
        return $this->tools;
    }
}
