<?php

declare(strict_types=1);

namespace Ysato\Catalyst\Console\Concerns;

use Throwable;

trait TaskRenderable
{
    protected function task(callable $task)
    {
        $this->output->write("⭐ Run {$this->description}");

        $result = false;

        try {
            $result = $task();
        } catch (Throwable $e) {
            throw $e;
        } finally {
            $result !== false ?
                $this->output->writeln("   ✅  Success - {$this->description}") :
                $this->output->writeln("   ❌  Failure - {$this->description}");
        }
    }
}
