<?php

namespace Pagely\AtomicClient\Command\SSL;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CertActivateCommand extends AbstractSSLCmd
{
    protected $commandName = 'ssl:cert:activate';

    public function configure()
    {
        parent::configure();
        $this->setDescription('Marks a certificate as active for a specific app')
            ->addArgument('certID', InputArgument::REQUIRED, 'Certificate ID')
            ->addArgument('appId', InputArgument::REQUIRED, 'App ID')
            ->addArgument('aliasId', InputArgument::OPTIONAL, 'Alias ID');
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $r = $this->api->activateCertForApp(
            $this->token->token,
            $input->getArgument('certID'),
            $input->getArgument('appId'),
            $input->getArgument('aliasId')
        );
        if ($r->getStatusCode() === 200) {
            $output->writeln('Success');
        } else {
            $output->writeln('<error>Activation failed</error>');
            $output->writeln($r->getBody()->getContents());
        }
    }
}
