<?php

namespace Settled\MCP\RAG\DataLoader;

interface ReaderInterface
{
    public static function getText(string $filePath, array $options = []): string;
}
