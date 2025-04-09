<?php

namespace Settled\MCP\MCP;

class StdioTransport implements McpTransportInterface
{
    private $process;
    private $pipes;

    /**
     * Create a new StdioTransport with the given configuration
     */
    public function __construct(protected array $config) {}

    /**
     * Connect to the MCP server by spawning the process
     */
    public function connect() : void
    {
        $descriptorSpec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"]   // stderr
        ];

        $command = $this->config['command'];
        $args = $this->config['args'] ?? [];
        $env = $this->config['env'] ?? [];

        // Merge current environment with provided environment variables
        $fullEnv = array_merge(getenv(), $env);

        // Build command with arguments
        $commandLine = $command;
        foreach ($args as $arg) {
            $commandLine .= ' ' . escapeshellarg($arg);
        }

        // Start the process
        $this->process = proc_open(
            $commandLine,
            $descriptorSpec,
            $this->pipes,
            null,
            $fullEnv
        );

        if (!is_resource($this->process)) {
            throw new \Exception("Failed to start the MCP server process");
        }

        // Configure pipes for binary data
        stream_set_write_buffer($this->pipes[0], 0);
        stream_set_read_buffer($this->pipes[1], 0);

        // Check that process started successfully
        $status = proc_get_status($this->process);
        if (!$status['running']) {
            $error = stream_get_contents($this->pipes[2]);
            throw new \Exception("Process failed to start: " . $error);
        }
    }

    /**
     * Send a request to the MCP server
     */
    public function send($data) : void
    {
        if (!is_resource($this->process)) {
            throw new \Exception("Process is not running");
        }

        $status = proc_get_status($this->process);
        if (!$status['running']) {
            throw new \Exception("MCP server process is not running");
        }

        $jsonData = json_encode($data);
        if ($jsonData === false) {
            throw new \Exception("Failed to encode request data to JSON");
        }

        $bytesWritten = fwrite($this->pipes[0], $jsonData . "\n");
        if ($bytesWritten === false || $bytesWritten < strlen($jsonData) + 1) {
            throw new \Exception("Failed to write complete request to MCP server");
        }

        fflush($this->pipes[0]);
    }

    /**
     * Receive a response from the MCP server
     */
    public function receive() : array
    {
        if (!is_resource($this->process)) {
            throw new \Exception("Process is not running");
        }

        // Set stream to non-blocking mode
        stream_set_blocking($this->pipes[1], false);

        $response = "";
        $startTime = time();
        $timeout = 30; // 30 seconds timeout

        // Keep reading until we get a complete JSON response or timeout
        while (time() - $startTime < $timeout) {
            $status = proc_get_status($this->process);
            if (!$status['running']) {
                throw new \Exception("MCP server process has terminated unexpectedly");
            }

            $chunk = fread($this->pipes[1], 4096);
            if ($chunk !== false && strlen($chunk) > 0) {
                $response .= $chunk;

                // Try to parse what we have so far
                $decoded = json_decode($response, true);
                if ($decoded !== null) {
                    // We've got a valid JSON response
                    return $decoded;
                }
            }

            // Small delay to prevent CPU spinning
            usleep(10000); // 10ms
        }

        throw new \Exception("Timeout waiting for response from MCP server");
    }

    /**
     * Disconnect from the MCP server
     */
    public function disconnect() : void
    {
        if (is_resource($this->process)) {
            // Close all pipe handles
            foreach ($this->pipes as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }

            // Try graceful termination first
            $status = proc_get_status($this->process);
            if ($status['running']) {
                // On Unix systems, try sending SIGTERM
                if (function_exists('proc_terminate')) {
                    proc_terminate($this->process);

                    // Give the process a moment to shut down gracefully
                    usleep(500000); // 500ms
                }
            }

            // Close the process handle
            proc_close($this->process);
            $this->process = null;
        }
    }
}