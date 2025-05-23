<?php

namespace Settled\MCP;

trait StaticConstructor
{
    /**
     * Static constructor.
     *
     * @param ...$args
     * @return static
     */
    public static function make(...$args): static
    {
        return new static(...$args);
    }
}
