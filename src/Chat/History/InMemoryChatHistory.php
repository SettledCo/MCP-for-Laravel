<?php

namespace Settled\MCP\Chat\History;

use Settled\MCP\Chat\Messages\Message;

class InMemoryChatHistory extends AbstractChatHistory
{
    protected function storeMessage(Message $message): void
    {
        $this->history[] = $message;
    }

    public function getMessages(): array
    {
        return $this->history;
    }

    public function removeOldestMessage(): ChatHistoryInterface
    {
        \array_shift($this->history);
        return $this;
    }

    public function clear(): ChatHistoryInterface
    {
        $this->history = [];
        return $this;
    }
}
