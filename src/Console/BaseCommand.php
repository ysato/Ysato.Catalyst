<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseCommand extends Command
{
    protected function handleUserError(Exception $e): int
    {
        $this->error('User error occurred: ' . $e->getMessage());

        if ($this->output->isVerbose()) {
            $this->error("Stack trace:\n" . $e->getTraceAsString());
        }

        return 1;
    }

    protected function handleSystemError(Exception $e): int
    {
        $this->error('System error occurred: ' . $e->getMessage());

        if ($this->output->isVerbose()) {
            $this->error("Stack trace:\n" . $e->getTraceAsString());
        }

        Log::error('Console Command System Error', [
            'command' => $this->getName(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'arguments' => $this->arguments(),
            'options' => $this->options(),
        ]);

        return 1;
    }

    protected function handleFatalError(Throwable $e): int
    {
        $this->error("Fatal error occurred. Please contact system administrator.");

        if ($this->getOutput()->isVerbose()) {
            $this->error("Error details: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . ":" . $e->getLine());
        }

        Log::critical("Console Command Fatal Error", [
            'command' => $this->getName(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'arguments' => $this->arguments(),
            'options' => $this->options(),
        ]);

        return 1;
    }
}