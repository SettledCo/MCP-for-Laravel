<?php

namespace NeuronAI\Events;

use NeuronAI\Tools\ToolInterface;

class ToolCalled
{
    public function __construct(public ToolInterface $tool) {}
}
