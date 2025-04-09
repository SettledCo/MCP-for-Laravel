<?php

namespace NeuronAI\Providers\Anthropic;

use GuzzleHttp\Exception\GuzzleException;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\Usage;

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
        $mapper = new MessageMapper($messages);

        $json = [
            'model' => $this->model,
            'max_tokens' => $this->max_tokens,
            'messages' => $mapper->map(),
            ...$this->parameters,
        ];

        if (isset($this->system)) {
            $json['system'] = $this->system;
        }

        if (!empty($this->tools)) {
            $json['tools'] = $this->generateToolsPayload();
        }

        // https://docs.anthropic.com/claude/reference/messages_post
        $result = $this->client->post('messages', compact('json'))
            ->getBody()->getContents();

        $result = \json_decode($result, true);

        $content = \end($result['content']);

        if ($content['type'] === 'tool_use') {
            $response = $this->createToolMessage($content);
        } else {
            $response = new AssistantMessage($content['text']);
        }

        // Attach the usage for the current interaction
        if (\array_key_exists('usage', $result)) {
            $response->setUsage(
                new Usage(
                    $result['usage']['input_tokens'],
                    $result['usage']['output_tokens']
                )
            );
        }

        return $response;
    }
}
