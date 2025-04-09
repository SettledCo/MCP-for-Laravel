<?php

namespace Settled\MCP;

use Settled\MCP\Tools\ToolInterface;

trait ResolveTools
{
    /**
     * Registered tools.
     *
     * @var array<ToolInterface>
     */
    protected array $tools = [];

    /**
     * Get the list of tools.
     *
     * @return array<ToolInterface>
     */
    public function tools(): array
    {
        return $this->tools;
    }

    /**
     * Add a tool.
     *
     * @param ToolInterface $tool
     * @return AgentInterface
     */
    public function addTool(ToolInterface $tool): AgentInterface
    {
        $this->tools[] = $tool;
        return $this;
    }
}
