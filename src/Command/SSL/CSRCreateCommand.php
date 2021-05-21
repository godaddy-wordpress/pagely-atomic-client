<?php

namespace Pagely\AtomicClient\Command\SSL;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CSRCreateCommand extends AbstractSSLCmd
{
    protected $commandName = 'ssl:csr:create';

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Generate a CSR')
            ->addArgument('account', InputArgument::REQUIRED, 'Account ID')
            ->addArgument('commonName', InputArgument::REQUIRED, 'Common name for CSR (usually domain name)')
            ->addArgument('countryName', InputArgument::OPTIONAL, 'Country abbreviation', 'US')
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

        $r = $this->api->createCSR($token, $accountId, $input->getArgument('commonName'), $input->getArgument('countryName'));
        $output->writeln(json_encode(json_decode($r->getBody()->getContents()), JSON_PRETTY_PRINT));
        return 0;
    }
}
