<?php

namespace NeuronAI;

class SystemPrompt implements \Stringable
{
    public function __construct(
        public array $background,
        public array $steps = [],
        public array $output = [],
        public array $context = [],
    ) {}

    public function __toString()
    {
        $prompt = "# IDENTITY and PURPOSE".PHP_EOL.implode(PHP_EOL, $this->background);

        if (!empty($this->steps)) {
            $prompt .= PHP_EOL.PHP_EOL."# INTERNAL ASSISTANT STEPS".PHP_EOL.implode(PHP_EOL, $this->steps);
        }

        if (!empty($this->output)) {
            $prompt .= PHP_EOL.PHP_EOL."# OUTPUT INSTRUCTIONS".PHP_EOL
                . implode(PHP_EOL.' - ', $this->output) . PHP_EOL
                . " - Always respond using the proper JSON schema.".PHP_EOL
                . " - Always use the available additional information and context to enhance the response.";
        }

        if (!empty($this->context)) {
            $prompt .= PHP_EOL.PHP_EOL."# EXTRA INFORMATION AND CONTEXT".PHP_EOL.implode(PHP_EOL, $this->context) . PHP_EOL.PHP_EOL;
        }

        return $prompt;
    }
}
