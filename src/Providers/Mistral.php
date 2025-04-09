<?php

namespace Settled\MCP\Providers;

use Settled\MCP\Providers\OpenAI\OpenAI;

class Mistral extends OpenAI
{
    protected string $baseUri = 'https://api.mistral.ai/v1';
}
