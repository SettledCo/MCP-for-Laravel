<?php

namespace Settled\MCP\Chat\History;

use Settled\MCP\Chat\Messages\Message;
use Settled\MCP\Chat\Messages\Usage;
use Settled\MCP\Exceptions\ChatHistoryException;

class FileChatHistory extends AbstractChatHistory
{
    public function __construct(
        protected string $directory,
        protected string $key,
        int $contextWindow = 50000,
        protected string $prefix = 'mcp_',
        protected string $ext = '.chat'
    ) {
        parent::__construct($contextWindow);

        if (!\is_dir($this->directory)) {
            throw new ChatHistoryException("Directory '{$this->directory}' does not exist");
        }

        $this->initHistory();
    }

    protected function initHistory(): void
    {
        if (\is_file($this->getFilePath())) {
            $messages = \json_decode(\file_get_contents($this->getFilePath()), true);
            $this->history = $this->unserializeMessages($messages);
        } else {
            $this->history = [];
        }
    }

    protected function getFilePath(): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $this->prefix.$this->key.$this->ext;
    }

    protected function storeMessage(Message $message): void
    {
        $this->history[] = $message;
        $this->updateFile();
    }

    public function getMessages(): array
    {
        return $this->history;
    }

    public function removeOldestMessage(): ChatHistoryInterface
    {
        \array_unshift($this->history);
        $this->updateFile();
        return $this;
    }

    public function clear(): ChatHistoryInterface
    {
        if (!\unlink($this->getFilePath())) {
            throw new ChatHistoryException("Unable to delete file '{$this->getFilePath()}'");
        }
        $this->history = [];
        return $this;
    }

    protected function updateFile(): void
    {
        \file_put_contents($this->getFilePath(), json_encode($this->jsonSerialize()), LOCK_EX);
    }
}
