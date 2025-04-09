<?php

namespace NeuronAI\Providers;

use GuzzleHttp\Client;

trait HandleClient
{
    public function setClient(Client $client): AIProviderInterface
    {
        $this->client = $client;
        return $this;
    }
}
