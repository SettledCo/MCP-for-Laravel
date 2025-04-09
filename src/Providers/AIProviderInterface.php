<?php

namespace Settled\MCP\Providers;

use Settled\MCP\Chat\Messages\Message;
use Settled\MCP\Tools\ToolInterface;

interface AIProviderInterface
{
    /**
     * Send predefined instruction to the LLM.
     *
     * @param ?string $prompt
     * @return AIProviderInterface
     */
    public function systemPrompt(?string $prompt): AIProviderInterface;

    /**
     * Set the tools to be exposed to the LLM.
     *
     * @param array<ToolInterface> $tools
     * @return AIProviderInterface
     */
    public function setTools(array $tools): AIProviderInterface;

    /**
     * Send a prompt to the AI agent.
     *
     * @param array<Message> $messages
     * @return Message
     */
    public function chat(array $messages): Message;

    public function stream(array|string $messages, callable $executeToolsCallback): \Generator;
}
