<?php

namespace Settled\MCP\Providers\Ollama;

use Settled\MCP\Chat\Messages\Message;
use Settled\MCP\Chat\Messages\ToolCallMessage;
use Settled\MCP\Chat\Messages\ToolCallResultMessage;
use Settled\MCP\Tools\ToolInterface;

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
                'content' => $tool->getResult()
            ];
        }
    }
}
