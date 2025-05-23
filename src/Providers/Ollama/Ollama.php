<?php

namespace Settled\MCP\Providers\Ollama;

use GuzzleHttp\Client;
use Settled\MCP\Chat\Messages\Message;
use Settled\MCP\Chat\Messages\ToolCallMessage;
use Settled\MCP\Providers\AIProviderInterface;
use Settled\MCP\Providers\HandleClient;
use Settled\MCP\Providers\HandleWithTools;
use Settled\MCP\Tools\ToolInterface;
use Settled\MCP\Tools\ToolProperty;

class Ollama implements AIProviderInterface
{
    use HandleChat;
    use HandleClient;
    use HandleStream;
    use HandleWithTools;

    /**
     * The http client.
     */
    protected Client $client;

    protected ?string $system;

    public function __construct(
        protected string $url, // http://localhost:11434/api
        protected string $model,
        protected array $parameters = [],
    ) {
        $this->client = new Client([
            'base_uri' => trim($this->url, '/').'/',
        ]);
    }

    public function systemPrompt(?string $prompt): AIProviderInterface
    {
        $this->system = $prompt;

        return $this;
    }

    public function generateToolsPayload(): array
    {
        return \array_map(function (ToolInterface $tool) {
            $payload = [
                'type' => 'function',
                'function' => [
                    'name' => $tool->getName(),
                    'description' => $tool->getDescription(),
                ],
            ];

            $properties = \array_reduce($tool->getProperties(), function (array $carry, ToolProperty $property) {
                $carry[$property->getName()] = [
                    'type' => $property->getType(),
                    'description' => $property->getDescription(),
                ];

                return $carry;
            }, []);

            if (! empty($properties)) {
                $payload['function']['parameters'] = [
                    'type' => 'object',
                    'properties' => $properties,
                    'required' => $tool->getRequiredProperties(),
                ];
            }

            return $payload;
        }, $this->tools);
    }

    protected function createToolMessage(array $message): Message
    {
        $tools = \array_map(function (array $item) {
            return $this->findTool($item['function']['name'])
                ->setInputs($item['function']['arguments']);
        }, $message['tool_calls']);

        $result = new ToolCallMessage(
            $message['content'],
            $tools
        );

        return $result->addMetadata('tool_calls', $message['tool_calls']);
    }
}
