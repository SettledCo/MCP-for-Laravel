<?php

namespace NeuronAI\Providers;

use NeuronAI\Providers\OpenAI\OpenAI;

class Mistral extends OpenAI
{
    protected string $baseUri = 'https://api.mistral.ai/v1';
}
