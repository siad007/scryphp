<?php
/**
 * Scryphp.
 * Batch Tool for scrayping websites using xml based execution plans.
 *
 * @author Siad Ardroumli <siad.ardroumli@idealo.de>
 * @package Scryphp
 * @subpackage Command
 * @version 0.1
 * @since 0.1
 */

namespace Scryphp\Command;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Scryphp\Scrype;
use Scryphp\Plan;
use Monolog;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\MemoryUsageProcessor;

/**
 * Plan command.
 */
class PlanCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $help = <<<'HELP'
 **********************************************************************
 * Scryphp.                                                           *
 * Batch Tool for scrayping websites using xml based execution plans. *
 **********************************************************************
HELP;
        $this->setName('scryphp:plan')
            ->setDescription('Scrypes a page by setting an execution plan.')
            ->addArgument('name', InputArgument::REQUIRED, 'Which plan do you want to scrype?')
            ->setHelp($help);
    }

    /**
     * {@inheritdoc}
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        $scryper = new Scrype($name);
        $scryper->setLogger(self::createLogger());
        $text = $scryper->process();

        $output->writeln($text);
    }

    protected static function createLogger()
    {
        $handler = new StreamHandler(__DIR__ . '/../../../logs/scryphp.log', Monolog\Logger::INFO);
        $processors = array(
            new MemoryUsageProcessor(),
            new MemoryPeakUsageProcessor()
        );
        return new Logger('scryphp', array($handler), $processors);
    }
}
