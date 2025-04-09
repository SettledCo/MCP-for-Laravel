<?php

namespace Settled\MCP\Providers;

use Settled\MCP\Tools\Tool;

trait HandleWithTools
{
    /**
     * https://docs.anthropic.com/en/docs/build-with-claude/tool-use/overview
     *
     * @var array<Tool>
     */
    protected array $tools = [];

    public function setTools(array $tools): self
    {
        $this->tools = $tools;
        return $this;
    }

    public function findTool($name): ?Tool
    {
        foreach ($this->tools as $tool) {
            if ($tool->getName() === $name) {
                return $tool;
            }
        }

        return null;
    }
}
