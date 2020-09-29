<?php

namespace Oro\Component\MessageQueue\Client\Meta;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command shows all available destinations and some information about them.
 */
class DestinationsCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'oro:message-queue:destinations';

    /** @var DestinationMetaRegistry */
    private $destinationMetaRegistry;

    /**
     * @param DestinationMetaRegistry $destinationMetaRegistry
     */
    public function __construct(DestinationMetaRegistry $destinationMetaRegistry)
    {
        $this->destinationMetaRegistry = $destinationMetaRegistry;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('A command shows all available destinations and some information about them.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(['Client Name', 'Transport Name', 'Subscribers']);

        $count = 0;
        $firstRow = true;
        foreach ($this->destinationMetaRegistry->getDestinationsMeta() as $destination) {
            if (!$firstRow) {
                $table->addRow(new TableSeparator());
            }

            $table->addRow([
                $destination->getClientName(),
                $destination->getTransportName(),
                implode(PHP_EOL, $destination->getSubscribers())
            ]);

            $count++;
            $firstRow = false;
        }

        $output->writeln(sprintf('Found %s destinations', $count));
        $output->writeln('');
        $table->render();
    }
}