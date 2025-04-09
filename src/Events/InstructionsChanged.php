<?php

namespace Settled\MCP\Events;

class InstructionsChanged
{
    public function __construct(
        public string $previous,
        public string $current
    ) {}
}
