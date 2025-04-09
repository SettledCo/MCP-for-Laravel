<?php

namespace Settled\MCP\Events;

use Settled\MCP\Tools\ToolInterface;

class ToolCalling
{
    public function __construct(public ToolInterface $tool) {}
}
