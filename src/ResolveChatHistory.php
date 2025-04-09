<?php

namespace Settled\MCP;

use Settled\MCP\Chat\History\AbstractChatHistory;
use Settled\MCP\Chat\History\InMemoryChatHistory;

trait ResolveChatHistory
{
    /**
     * @var AbstractChatHistory
     */
    protected AbstractChatHistory $chatHistory;

    /**
     * Called on the agent instance.
     *
     * @param AbstractChatHistory $chatHistory
     * @return ResolveChatHistory|Agent
     */
    public function withChatHistory(AbstractChatHistory $chatHistory): self
    {
        $this->chatHistory = $chatHistory;
        return $this;
    }

    /**
     * Used extending the Agent.
     *
     * @return AbstractChatHistory
     */
    public function chatHistory(): AbstractChatHistory
    {
        return new InMemoryChatHistory();
    }

    /**
     * Get the current instance of the chat history.
     *
     * @return AbstractChatHistory
     */
    public function resolveChatHistory(): AbstractChatHistory
    {
        if (!isset($this->chatHistory)) {
            $this->chatHistory = $this->chatHistory();
        }

        return $this->chatHistory;
    }
}
