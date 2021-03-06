<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Cycle\Command\Common;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Yii\Console\ExitCode;
use Yiisoft\Yii\Cycle\Command\CycleDependencyPromise;
use Yiisoft\Yii\Cycle\Helper\SchemaToPHP;

final class SchemaPhpCommand extends Command
{
    protected static $defaultName = 'cycle/schema/php';

    private CycleDependencyPromise $promise;
    private Aliases $aliases;

    public function __construct(Aliases $aliases, CycleDependencyPromise $promise)
    {
        $this->aliases = $aliases;
        $this->promise = $promise;
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Save current schema in a PHP file')
             ->addArgument('file', InputArgument::OPTIONAL, 'file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');

        $content = (new SchemaToPHP($this->promise->getSchema()))->render();
        if ($file !== null) {
            $file = $this->aliases->get($file);
            $output->writeln("Destination: <fg=cyan>{$file}</>");
            // Dir exists
            $dir = dirname($file);
            if (!is_dir($dir)) {
                throw new \RuntimeException("Directory {$dir} not found");
            }
            if (file_put_contents($file, $content) === false) {
                return ExitCode::UNSPECIFIED_ERROR;
            }
        } else {
            echo $content;
        }
        return ExitCode::OK;
    }
}
