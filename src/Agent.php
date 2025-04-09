<?php

namespace NeuronAI;

use NeuronAI\Chat\History\InMemoryChatHistory;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\ToolCallResultMessage;
use NeuronAI\Chat\Messages\Usage;
use NeuronAI\Events\MessageSaved;
use NeuronAI\Events\MessageSaving;
use NeuronAI\Events\MessageSending;
use NeuronAI\Events\MessageSent;
use NeuronAI\Events\ToolCalled;
use NeuronAI\Events\ToolCalling;
use NeuronAI\Exceptions\InvalidMessageInstance;
use NeuronAI\Exceptions\MissingCallbackParameter;
use NeuronAI\Exceptions\ToolCallableNotSet;
use NeuronAI\Observability\AgentMonitoring;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Tools\Tool;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Tools\ToolInterface;

class Agent implements AgentInterface
{
    use StaticConstructor;
    use ResolveTools;
    use ResolveChatHistory;

    /**
     * The AI provider instance.
     *
     * @var AIProviderInterface
     */
    protected AIProviderInterface $provider;

    /**
     * The system message.
     *
     * @var string
     */
    protected string $instructions = 'Your are a helpful and friendly AI assistant built with Neuron AI PHP framework.';

    /**
     * @var array<\SplObserver>
     */
    private array $observers = [];

    public function setProvider(AIProviderInterface $provider): AgentInterface
    {
        $this->provider = $provider;
        return $this;
    }

    public function provider(): AIProviderInterface
    {
        return $this->provider;
    }

    /**
     * Execute the chat.
     *
     * @param Message|array $messages
     * @return Message
     * @throws MissingCallbackParameter
     * @throws ToolCallableNotSet
     */
    public function chat(Message|array $messages): Message
    {
        $this->notify('chat-start');

        $messages = is_array($messages) ? $messages : [$messages];

        foreach ($messages as $message) {
            $this->notify('message-saving', new MessageSaving($message));
            $this->resolveChatHistory()->addMessage($message);
            $this->notify('message-saved', new MessageSaved($message));
        }

        $message = \end($messages);

        $this->notify(
            'message-sending',
            new MessageSending($message)
        );

        $response = $this->provider()
            ->systemPrompt($this->instructions())
            ->setTools($this->tools())
            ->chat(
                $this->resolveChatHistory()->getMessages()
            );

        $this->notify(
            'message-sent',
            new MessageSent($message, $response)
        );

        if ($response instanceof ToolCallMessage) {
            $toolCallResult = $this->executeTools($response);
            $response = $this->chat([$response, $toolCallResult]);
        }

        $this->notify('message-saving', new MessageSaving($response));
        $this->resolveChatHistory()->addMessage($response);
        $this->notify('message-saved', new MessageSaved($response));

        $this->notify('chat-stop');
        return $response;
    }

    public function stream(Message|array $messages): \Generator
    {
        $this->notify('stream-start');

        $messages = is_array($messages) ? $messages : [$messages];

        foreach ($messages as $message) {
            $this->notify('message-saving', new MessageSaving($message));
            $this->resolveChatHistory()->addMessage($message);
            $this->notify('message-saved', new MessageSaved($message));
        }

        $stream = $this->provider()
            ->systemPrompt($this->instructions())
            ->setTools($this->tools())
            ->stream(
                $this->resolveChatHistory()->getMessages(),
                function (ToolCallMessage $toolCallMessage) {
                    $toolCallResult = $this->executeTools($toolCallMessage);
                    yield from $this->stream([$toolCallMessage, $toolCallResult]);
                }
            );

        $content = '';
        $usage = new Usage(0, 0);
        foreach ($stream as $text) {
            // Catch usage when streaming
            $decoded = \json_decode($text, true);
            if (\is_array($decoded) && \array_key_exists('usage', $decoded)) {
                $usage->inputTokens += $decoded['usage']['input_tokens']??0;
                $usage->outputTokens += $decoded['usage']['output_tokens']??0;
                continue;
            }

            $content .= $text;
            yield $text;
        }

        $response = new AssistantMessage($content);
        $response->setUsage($usage);

        // Avoid double saving due to the recursive call.
        $history = $this->resolveChatHistory()->getMessages();
        $last = \end($history);
        if ($response->getRole() !== $last->getRole()) {
            $this->notify('message-saving', new MessageSaving($response));
            $this->resolveChatHistory()->addMessage($response);
            $this->notify('message-saved', new MessageSaved($response));
        }

        $this->notify('stream-stop');
    }

    protected function executeTools(ToolCallMessage $toolCallMessage): ToolCallResultMessage
    {
        $toolCallResult = new ToolCallResultMessage($toolCallMessage->getTools());

        foreach ($toolCallResult->getTools() as $tool) {
            $this->notify('tool-calling', new ToolCalling($tool));
            $tool->execute();
            $this->notify('tool-called', new ToolCalled($tool));
        }

        return $toolCallResult;
    }

    public function instructions(): string
    {
        return $this->instructions;
    }

    public function setInstructions(string $instructions): AgentInterface
    {
        $this->instructions = $instructions;
        return $this;
    }

    private function initEventGroup(string $event = "*"): void
    {
        if (!isset($this->observers[$event])) {
            $this->observers[$event] = [];
        }
    }

    private function getEventObservers(string $event = "*"): array
    {
        $this->initEventGroup($event);
        $group = $this->observers[$event];
        $all = $this->observers["*"] ?? [];

        return \array_merge($group, $all);
    }

    public function observe(\SplObserver $observer, string $event = "*"): self
    {
        $this->attach($observer, $event);
        return $this;
    }

    public function attach(\SplObserver $observer, string $event = "*"): void
    {
        $this->initEventGroup($event);
        $this->observers[$event][] = $observer;
    }

    public function detach(\SplObserver $observer, string $event = "*"): void
    {
        foreach ($this->getEventObservers($event) as $key => $s) {
            if ($s === $observer) {
                unset($this->observers[$event][$key]);
            }
        }
    }

    public function notify(string $event = "*", $data = null): void
    {
        // Broadcasting the '$event' event";
        foreach ($this->getEventObservers($event) as $observer) {
            $observer->update($this, $event, $data);
        }
    }
}
