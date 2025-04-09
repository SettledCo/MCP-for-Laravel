<?php

namespace NeuronAI\Providers\OpenAI;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\ToolCallResultMessage;

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
        foreach ($tools as $tool) {
            $this->mapping[] = [
                'role' => Message::ROLE_TOOL,
                'tool_call_id' => $tool->getCallId(),
                'content' => $tool->getResult()
            ];
        }
    }
}
