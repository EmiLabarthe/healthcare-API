<?php

declare(strict_types=1);

namespace Lightit\Shared\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class RunEcsFixerQuietCommand extends Command
{
    #[\Override]
    protected $signature = 'ecs:run';

    #[\Override]
    protected $description = 'Run ECS code style fixer';

    public function handle(): int
    {
        $ecs = $this->laravel->basePath('vendor/bin/ecs');

        if (! file_exists($ecs)) {
            $this->error("ECS binary not found at: $ecs. Please run 'composer install' to install dependencies.");

            return self::FAILURE;
        }

        $result = Process::run([$ecs, '--fix', '--quiet']);

        if ($result->failed()) {
            $this->error('ECS failed to run.');

            $this->line($result->errorOutput());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
