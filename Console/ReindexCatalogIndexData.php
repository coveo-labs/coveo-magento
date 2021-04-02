<?php

declare(strict_types=1);

namespace Coveo\Search\Console;

use Coveo\Search\Api\Service\Indexer\CatalogInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReindexCatalogIndexData extends Command
{
    /**
     * @var CatalogInterface
     */
    protected $catalog;

    /**
     * @param DataSenderInterface $dataSender
     */
    public function __construct(
        CatalogInterface $catalog
    ) {
        $this->catalog = $catalog;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('coveo:index:catalog-reindex')
            ->setDescription('Reind local Coveo catalog table');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Reindexing the local Coveo catalog table...</info>');
        $this->catalog->execute();
        $output->writeln('<info>Done!</info>');
    }
}
