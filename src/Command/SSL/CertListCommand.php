<?php

namespace Pagely\AtomicClient\Command\SSL;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CertListCommand extends AbstractSSLCmd
{
    protected $commandName = 'ssl:cert:ls';

    public function configure()
    {
        parent::configure();
        $this->setDescription('List certificates for account and/or app');
        $this->addOption('accountID', 'a', InputOption::VALUE_REQUIRED, 'Account ID')
             ->addOption('appId', 'd', InputOption::VALUE_REQUIRED, 'App ID')
             ->addOption('includeChain', 'c', InputOption::VALUE_NONE, 'Include the cert chain?');

        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $accountId = $input->getOption('accountID');
        $appId = $input->getOption('appId');
        $includeChain = $input->getOption('appId');
        $r = $this->api->listCerts($this->token->token, $accountId, $appId, $includeChain);
        $output->writeln(json_encode(json_decode($r->getBody()->getContents()), JSON_PRETTY_PRINT));
        return 0;
    }
}
