<?php

namespace Settled\MCP\Providers\OpenAI;

use GuzzleHttp\Exception\GuzzleException;
use Settled\MCP\Chat\Messages\AssistantMessage;
use Settled\MCP\Chat\Messages\Message;
use Settled\MCP\Chat\Messages\Usage;

trait HandleChat
{
    /**
     * Send a message to the LLM.
     *
     * @param Message|array<Message> $messages
     * @throws GuzzleException
     */
    public function chat(array $messages): Message
    {
        // Include the system prompt
        if (isset($this->system)) {
            \array_unshift($messages, new AssistantMessage($this->system));
        }

        $mapper = new MessageMapper($messages);

        $json = [
            'model' => $this->model,
            'messages' => $mapper->map(),
            ...$this->parameters
        ];

        // Attach tools
        if (!empty($this->tools)) {
            $json['tools'] = $this->generateToolsPayload();
        }

        $result = $this->client->post('chat/completions', compact('json'))
            ->getBody()->getContents();

        $result = \json_decode($result, true);

        if ($result['choices'][0]['finish_reason'] === 'tool_calls') {
            $response = $this->createToolMessage($result['choices'][0]['message']);
        } else {
            $response = new AssistantMessage($result['choices'][0]['message']['content']);
        }

        if (\array_key_exists('usage', $result)) {
            $response->setUsage(
                new Usage($result['usage']['prompt_tokens'], $result['usage']['completion_tokens'])
            );
        }

        return $response;
    }
}
