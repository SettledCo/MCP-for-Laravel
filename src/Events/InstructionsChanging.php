<?php

namespace Settled\MCP\Events;

class InstructionsChanging
{
    public function __construct(
        public string $instructions
    ) {}
}
