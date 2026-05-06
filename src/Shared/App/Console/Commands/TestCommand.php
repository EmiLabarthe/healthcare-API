<?php

declare(strict_types=1);

namespace Lightit\Shared\App\Console\Commands;

use Illuminate\Console\Command;
use Psr\Log\LoggerInterface;

class TestCommand extends Command
{
    /**
     * @var string
     */
    #[\Override]
    protected $signature = 'app:test-command';

    /**
     * @var string
     */
    #[\Override]
    protected $description = 'Command Tester';

    public function handle(LoggerInterface $logger): int
    {
        $logger->info('Hi, Im am Logger! How are u?');
        $this->info('Done');

        return Command::SUCCESS;
    }
}
