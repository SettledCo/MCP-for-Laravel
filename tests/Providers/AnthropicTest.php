<?php

namespace NeuronAI\Tests\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\Anthropic\Anthropic;
use PHPUnit\Framework\TestCase;

class AnthropicTest extends TestCase
{
    public function test_chat_request(): void
    {
        $sentRequests = [];
        $history = Middleware::history($sentRequests);
        $mockHandler = new MockHandler([
            new Response(
                status: 200,
                body: '{"model": "claude-3-7-sonnet-latest","role": "assistant","stop_reason": "end_turn","content":[{"type": "text","text": "How can I assist you today?"}],"usage": {"input_tokens": 19,"output_tokens": 29}}',
            ),
        ]);
        $stack = HandlerStack::create($mockHandler);
        $stack->push($history);

        $client = new Client(['handler' => $stack]);

        $openai = (new Anthropic('', 'claude-3-7-sonnet-latest'))->setClient($client);

        $response = $openai->chat([new UserMessage('Hi')]);

        // Ensure we sent one request
        $this->assertCount(1, $sentRequests);
        $request = $sentRequests[0];

        // Ensure we have sent the expected request payload.
        $expectedResponse = [
            'model' => 'claude-3-7-sonnet-latest',
            'max_tokens' => 8192,
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
