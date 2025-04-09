<?php

namespace NeuronAI\Tests;


use NeuronAI\Agent;
use NeuronAI\AgentInterface;
use NeuronAI\Chat\Messages\AssistantMessage;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\RAG\RAG;
use NeuronAI\SystemPrompt;
use NeuronAI\Tools\Tool;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Tools\ToolInterface;
use PHPUnit\Framework\TestCase;

class NeuronAITest extends TestCase
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

    public function test_agent_instance()
    {
        $neuron = new Agent();
        $this->assertInstanceOf(AgentInterface::class, $neuron);

        $neuron = new RAG();
        $this->assertInstanceOf(Agent::class, $neuron);
    }

    public function test_system_instructions()
    {
        $neuron = Agent::make()->setInstructions("Agent");
        $this->assertEquals("Agent", $neuron->instructions());

        $neuron->setInstructions(new SystemPrompt(["Agent"]));
        $this->assertEquals("# IDENTITY and PURPOSE".PHP_EOL."Agent", $neuron->instructions());
    }

    public function test_message_instance()
    {
        $tools = [
            new Tool('example', 'example')
        ];

        $this->assertInstanceOf(Message::class, new UserMessage(''));
        $this->assertInstanceOf(Message::class, new AssistantMessage(''));
        $this->assertInstanceOf(Message::class, new ToolCallMessage('', $tools));
    }

    public function test_tool_instance()
    {
        $tool = new Tool('example', 'example');
        $this->assertInstanceOf(ToolInterface::class, $tool);

        $tool->setInputs(null);
        $this->assertEquals([], $tool->getInputs());
    }
}
