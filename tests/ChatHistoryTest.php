<?php

namespace Settled\MCP\Tests;

use Settled\MCP\Chat\History\AbstractChatHistory;
use Settled\MCP\Chat\History\FileChatHistory;
use Settled\MCP\Chat\History\InMemoryChatHistory;
use Settled\MCP\Chat\Messages\Usage;
use Settled\MCP\Chat\Messages\UserMessage;
use Settled\MCP\Exceptions\ChatHistoryException;
use PHPUnit\Framework\TestCase;

class ChatHistoryTest extends TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @throws \Exception
     */
    public function setUp(): void
    {
    }

    public function test_chat_history_instance()
    {
        $history = new InMemoryChatHistory();
        $this->assertInstanceOf(AbstractChatHistory::class, $history);
    }

    public function test_chat_history_add_message()
    {
        $history = new InMemoryChatHistory();
        $history->addMessage(new UserMessage('Hello!'));
        $this->assertCount(1, $history->getMessages());
    }

    public function test_chat_history_truncate()
    {
        $message = new UserMessage('Hello!');
        $message->setUsage(new Usage(100, 100));
        $history = new InMemoryChatHistory(300);
        $history->addMessage($message);
        $history->addMessage($message);
        $this->assertCount(1, $history->getMessages());
    }

    public function test_chat_history_clear()
    {
        $history = new InMemoryChatHistory();
        $history->addMessage(new UserMessage('Hello!'));
        $history->addMessage(new UserMessage('Hello2!'));
        $history->clear();
        $this->assertCount(0, $history->getMessages());
    }

    /**
     * @throws ChatHistoryException
     */
    public function test_file_chat_history()
    {
        $history = new FileChatHistory(__DIR__, 'test');
        $this->assertFileDoesNotExist(__DIR__.DIRECTORY_SEPARATOR.'mcp_test.chat');

        $history->addMessage(new UserMessage('Hello!'));
        $this->assertFileExists(__DIR__.DIRECTORY_SEPARATOR.'mcp_test.chat');
        $this->assertCount(1, $history->getMessages());

        $history->clear();
        $this->assertFileDoesNotExist(__DIR__.DIRECTORY_SEPARATOR.'mcp_test.chat');
        $this->assertCount(0, $history->getMessages());
    }

    public function test_file_chat_history_init()
    {
        $history = new FileChatHistory(__DIR__, 'test');
        $history->addMessage(new UserMessage('Hello!'));
        $this->assertFileExists(__DIR__.DIRECTORY_SEPARATOR.'mcp_test.chat');

        $history = new FileChatHistory(__DIR__, 'test');
        $this->assertCount(1, $history->getMessages());
        $history->clear();
    }
}
