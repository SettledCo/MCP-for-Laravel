<?php

namespace Settled\MCP;

use Settled\MCP\Chat\History\AbstractChatHistory;
use Settled\MCP\Chat\Messages\Message;
use Settled\MCP\Providers\AIProviderInterface;
use Settled\MCP\Tools\ToolInterface;

interface AgentInterface extends \SplSubject
{
    public function provider(): AIProviderInterface;

    public function setProvider(AIProviderInterface $provider): AgentInterface;

    public function instructions(): string;

    public function setInstructions(string $instructions): AgentInterface;

    public function tools(): array;

    public function addTool(ToolInterface $tool): AgentInterface;

    public function resolveChatHistory(): AbstractChatHistory;

    public function withChatHistory(AbstractChatHistory $chatHistory): AgentInterface;

    public function observe(\SplObserver $observer, string $event = "*"): AgentInterface;

    public function chat(Message|array $messages): Message;

    public function stream(Message|array $messages): \Generator;
}
