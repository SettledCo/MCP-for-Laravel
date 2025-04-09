<?php

namespace NeuronAI\Chat\History;

use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\Usage;

abstract class AbstractChatHistory implements ChatHistoryInterface
{
    protected array $history = [];

    public function __construct(protected int $contextWindow = 50000) {}

    protected function updateUsedTokens(Message $message): void
    {
        if ($message->getUsage() && $message->getRole() === Message::ROLE_ASSISTANT) {
            // For every new message, we store only the marginal contribution of input tokens
            // of the latest interactions.
            $currentInputConsumption = \array_reduce($this->getMessages(), function ($carry, Message $message) {
                if ($message->getUsage() && $message->getRole() === Message::ROLE_ASSISTANT) {
                    $carry += $message->getUsage()->inputTokens;
                }
                return $carry;
            }, 0);

            $message->getUsage()->inputTokens = $message->getUsage()->inputTokens - $currentInputConsumption;
        }
    }

    public function addMessage(Message $message): ChatHistoryInterface
    {
        $this->updateUsedTokens($message);

        $this->storeMessage($message);

        $this->cutHistoryToContextWindow();

        return $this;
    }

    abstract protected function storeMessage(Message $message): void;

    abstract public function getMessages(): array;

    abstract public function removeOldestMessage(): ChatHistoryInterface;

    abstract public function clear(): ChatHistoryInterface;

    public function calculateTotalUsage(): int
    {
        return \array_reduce($this->getMessages(), function (int $carry, Message $message) {
            if ($message->getUsage() instanceof Usage) {
                $carry = $carry + $message->getUsage()->getTotal();
            }

            return $carry;
        }, 0);
    }

    protected function cutHistoryToContextWindow(): void
    {
        $freeMemory = $this->contextWindow - $this->calculateTotalUsage();

        if ($freeMemory > 0) {
            return;
        }

        // Cut old messages
        do {
            $this->removeOldestMessage();
        } while ($this->contextWindow - $this->calculateTotalUsage() < 0);
    }

    public function jsonSerialize(): array
    {
        return \array_map(function (Message $message) {
            return $message->jsonSerialize();
        }, $this->getMessages());
    }

    protected function unserializeMessages(array $messages): array
    {
        return \array_map(function (array $message) {
            return $this->unserializeMessage($message);
        }, $messages);
    }

    protected function unserializeMessage(array $message): Message
    {
        $item = new Message($message['role'], $message['content']??'');

        foreach ($message as $key => $value) {
            if ($key === 'role' || $key === 'content') {
                continue;
            }
            if ($key === 'usage') {
                $item->setUsage(
                    new Usage($message['usage']['input_tokens'], $message['usage']['output_tokens'])
                );
                continue;
            }
            $item->addMetadata($key, $value);
        }

        return $item;
    }
}
