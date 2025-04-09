<?php

namespace Settled\MCP\MCP;

interface McpTransportInterface
{
    public function connect(): void;
    public function send($data): void;
    public function receive(): array;
    public function disconnect(): void;
}
