<?php

namespace NeuronAI\Events;

class InstructionsChanging
{
    public function __construct(
        public string $instructions
    ) {}
}
