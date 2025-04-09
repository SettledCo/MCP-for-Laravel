<?php

namespace NeuronAI\Events;

use NeuronAI\Tools\ToolInterface;

class ToolCalling
{
    public function __construct(public ToolInterface $tool) {}
}
