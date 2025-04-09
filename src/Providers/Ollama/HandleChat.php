<?php

namespace Settled\MCP\Providers\Ollama;

use Settled\MCP\Chat\Messages\AssistantMessage;
use Settled\MCP\Chat\Messages\Message;
use Settled\MCP\Chat\Messages\Usage;
use Settled\MCP\Exceptions\ProviderException;

trait HandleChat
{
    public function chat(array $messages): Message
    {
        // Include the system prompt
        if (isset($this->system)) {
            \array_unshift($messages, new Message(Message::ROLE_SYSTEM, $this->system));
        }

        $mapper = new MessageMapper($messages);

        $json = [
            'stream' => false,
            'model' => $this->model,
            'messages' => $mapper->map(),
            ...$this->parameters,
        ];

        if (! empty($this->tools)) {
            $json['tools'] = $this->generateToolsPayload();
        }

        $response = $this->client->post('chat', compact('json'));

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new ProviderException("Ollama chat error: {$response->getBody()->getContents()}");
        }

        $response = json_decode($response->getBody()->getContents(), true);
        $message = $response['message'];

        if (\array_key_exists('tool_calls', $message)) {
            $message = $this->createToolMessage($message);
        } else {
            $message = new AssistantMessage($message['content']);
        }

        if (\array_key_exists('prompt_eval_count', $response) && \array_key_exists('eval_count', $response)) {
            $message->setUsage(
                new Usage($response['prompt_eval_count'], $response['eval_count'])
            );
        }

        return $message;
    }
}
