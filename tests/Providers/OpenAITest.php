<?php

namespace NeuronAI\Tests\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\OpenAI\OpenAI;
use PHPUnit\Framework\TestCase;

class OpenAITest extends TestCase
{
    public function test_chat_request(): void
    {
        $sentRequests = [];
        $history = Middleware::history($sentRequests);
        $mockHandler = new MockHandler([
            new Response(
                status: 200,
                body: '{"model": "gpt-4o","choices":[{"index": 0,"finish_reason": "stop","message": {"role": "assistant","content": "How can I assist you today?"}}],"usage": {"prompt_tokens": 19,"completion_tokens": 10,"total_tokens": 29}}',
            ),
        ]);
        $stack = HandlerStack::create($mockHandler);
        $stack->push($history);

        $client = new Client(['handler' => $stack]);

        $openai = (new OpenAI('', 'gpt-4o'))->setClient($client);

        $response = $openai->chat([new UserMessage('Hi')]);

        // Ensure we sent one request
        $this->assertCount(1, $sentRequests);
        $request = $sentRequests[0];

        // Ensure we have sent the expected request payload.
        $expectedResponse = [
            'model' => 'gpt-4o',
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
