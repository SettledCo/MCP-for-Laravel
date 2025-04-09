<?php

namespace Settled\MCP\Tests\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Settled\MCP\Chat\Messages\UserMessage;
use Settled\MCP\Providers\Ollama\Ollama;
use PHPUnit\Framework\TestCase;

class OllamaTest extends TestCase
{
    public function test_chat_request(): void
    {
        $sentRequests = [];
        $history = Middleware::history($sentRequests);
        $mockHandler = new MockHandler([
            new Response(
                status: 200,
                body: '{"model":"llama3.2","created_at":"2025-03-28T11:00:23.692962Z","message":{"role":"assistant","content":"How can I assist you today?"},"done_reason":"stop","done":true,"total_duration":497173583,"load_duration":33707083,"prompt_eval_count":32,"prompt_eval_duration":321682834,"eval_count":8,"eval_duration":140963041}',
            ),
        ]);
        $stack = HandlerStack::create($mockHandler);
        $stack->push($history);

        $client = new Client(['handler' => $stack]);

        $ollama = (new Ollama(
            url: '',
            model: 'llama3.2',
        ))->setClient($client);

        $response = $ollama->chat([new UserMessage('Hi')]);

        // Ensure we sent one request
        $this->assertCount(1, $sentRequests);
        $request = $sentRequests[0];

        // Ensure we have sent the expected request payload.
        $expectedResponse = [
            'stream' => false,
            'model' => 'llama3.2',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Hi',
                ],
            ],
        ];

        $this->assertSame($expectedResponse, json_decode($request['request']->getBody()->getContents(), true));
        $this->assertSame('How can I assist you today?', $response->getContent());
    }
}
