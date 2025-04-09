<?php

namespace Settled\MCP\Chat\History;

use Settled\MCP\Chat\Messages\Message;

interface ChatHistoryInterface extends \JsonSerializable
{
    public function addMessage(Message $message): ChatHistoryInterface;

    /**
     * @return array<Message>
     */
    public function getMessages(): array;

    public function removeOldestMessage(): ChatHistoryInterface;

    public function clear(): ChatHistoryInterface;

    public function calculateTotalUsage(): int;
}
