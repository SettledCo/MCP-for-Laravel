<?php

namespace NeuronAI\Providers\Anthropic;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolCallResultMessage;
use NeuronAI\Tools\ToolInterface;

class MessageMapper
{
    /**
     * Mapped messages.
     *
     * @var array
     */
    protected array $mapping = [];

    /**
     * @param array<Message> $messages
     */
    public function __construct(protected array $messages) {}

    public function map(): array
    {
        foreach ($this->messages as $message) {
            if ($message instanceof ToolCallResultMessage) {
                $this->mapToolsResult($message->getTools());
            } else {
                $this->mapping[] = $this->mapMessage($message);
            }
        }

        return $this->mapping;
    }

    public function mapMessage(Message $message): array
    {
        $message = $message->jsonSerialize();
        unset($message['usage']);
        return $message;
    }

    public function mapToolsResult(array $tools): void
    {
        $this->mapping[] = [
            'role' => Message::ROLE_USER,
            'content' => \array_map(function (ToolInterface $tool) {
                return [
                    'type' => 'tool_result',
                    'tool_use_id' => $tool->getCallId(),
                    'content' => $tool->getResult(),
                ];
            }, $tools)
        ];
    }
}
