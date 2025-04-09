<?php

namespace NeuronAI\RAG\DataLoader;

abstract class AbstractDataLoader implements DataLoaderInterface
{
    public static function for(...$args): static
    {
        return new static(...$args);
    }
}
