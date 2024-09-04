<?php

namespace Pagely\AtomicClient\Command\SSL;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CertGetCommand extends AbstractSSLCmd
{
    protected $commandName = 'ssl:cert';

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Get certificate & linked apps (aliases)')
            ->addArgument('id', InputArgument::REQUIRED, 'Cert ID')
            ->addOption('include-chain', 'c', InputOption::VALUE_NONE, 'Include the certificate chain')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($output->isDebug()) {
            $this->api->setDebug();
        }

        $id = $input->getArgument('id');
        $includeChain = (bool)$input->getOption('include-chain');
        $token = $this->token->token;

        $r = $this->api->getCert($token, $id, $includeChain);

        $cert = json_decode($r->getBody()->getContents());
        $output->writeln(json_encode($cert, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return 0;
    }
}
