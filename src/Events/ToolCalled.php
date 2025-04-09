<?php

namespace Settled\MCP\Events;

use Settled\MCP\Tools\ToolInterface;

class ToolCalled
{
    public function __construct(public ToolInterface $tool) {}
}
