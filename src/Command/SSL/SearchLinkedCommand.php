<?php

namespace Pagely\AtomicClient\Command\SSL;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class SearchLinkedCommand extends AbstractSSLCmd
{
    protected $commandName = 'ssl:cert:linked';

    public function configure()
    {
        parent::configure();
        $this->setDescription('List certificates linked to apps');
        $this->addArgument('appId', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'App ID');

        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $appId = $input->getArgument('appId');
        $r = $this->api->searchLinkedCerts($this->token->token, $appId);
        $output->writeln(json_encode(json_decode($r->getBody()->getContents()), JSON_PRETTY_PRINT));
        return 0;
    }
}
