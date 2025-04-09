<?php

namespace NeuronAI\Providers;

use NeuronAI\Providers\OpenAI\OpenAI;

class Deepseek extends OpenAI
{
    protected string $baseUri = "https://api.deepseek.com/v1";
}
