<?php

namespace Pagely\AtomicClient\Command\SSL;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CSRListCommand extends AbstractSSLCmd
{
    protected $commandName = 'ssl:csr:ls';

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('List CSRs for an account')
            ->addArgument('account', InputArgument::REQUIRED, 'Account ID')
            ->addOption('linked', null, InputOption::VALUE_REQUIRED, 'Show only CSRs linked to a Certificate or not yet linked (1 or 0)')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->isDebug()) {
            $this->api->setDebug();
        }

        $accountId = $input->getArgument('account');
        $token = $this->token->token;

        $r = $this->api->listCSRs($token, $accountId, $input->getOption('linked'));
        $output->writeln(json_encode(json_decode($r->getBody()->getContents()), JSON_PRETTY_PRINT));
    }
}
