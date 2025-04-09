<?php

namespace NeuronAI\RAG\DataLoader;

class TextFileReader implements ReaderInterface
{
    public static function getText(string $filePath, array $options = []): string
    {
        return file_get_contents($filePath);
    }
}
