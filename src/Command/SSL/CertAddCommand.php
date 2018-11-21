<?php

namespace Pagely\AtomicClient\Command\SSL;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CertAddCommand extends AbstractSSLCmd
{
    protected $commandName = 'ssl:cert:add';

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Import a cert with an existing CSR')
            ->addArgument('account', InputArgument::REQUIRED, 'Account ID')
            ->addArgument('certificateFile', InputArgument::OPTIONAL, 'Certificate file (you may also pipe the certificate to this command)')
            ->addOption('certChainFile', null, InputOption::VALUE_REQUIRED, 'Certificate Chain file (usually unnecessary)')
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
        if ($file = $input->getArgument('certificateFile')) {
            if (is_readable($file)) {
                $cert = file_get_contents($file);
            }
        } elseif (0 === ftell(STDIN)) {
            $cert = '';
            while (!feof(STDIN)) {
                $cert .= fread(STDIN, 1024);
            }
        }
        if (!isset($cert) || !$cert) {
            $output->writeln('<error>You must provide a file name or pipe the certificate to this command.</error>');
            return;
        }

        $certChain = null;
        if ($certChainFile = $input->getOption('certChainFile')) {
            $certChain = file_get_contents($certChainFile);
        }
        $r = $this->api->addCertificate($token, $accountId, $cert, $certChain);
        $output->writeln(json_encode(json_decode($r->getBody()->getContents()), JSON_PRETTY_PRINT));
    }
}
