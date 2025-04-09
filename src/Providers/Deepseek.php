<?php

namespace Settled\MCP\Providers;

use Settled\MCP\Providers\OpenAI\OpenAI;

class Deepseek extends OpenAI
{
    protected string $baseUri = "https://api.deepseek.com/v1";
}
