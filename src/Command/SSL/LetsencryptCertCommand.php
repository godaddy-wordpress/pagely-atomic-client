<?php

namespace Pagely\AtomicClient\Command\SSL;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LetsencryptCertCommand extends AbstractSSLCmd
{
    protected $commandName = 'ssl:le:cert';

    public function configure()
    {
        parent::configure();
        $this->setDescription('Generates letsencrypt certificate')
            ->addArgument('domain', InputArgument::REQUIRED, 'Domain name (fqdn)')
            ->addArgument('key', InputArgument::REQUIRED, 'Domain key')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $r = $this->api->letsencryptGetCertificate(
            $this->token->token,
            $input->getArgument('domain'),
            $input->getArgument('key')
        );
        if ($r->getStatusCode() === 200) {
            $output->writeln('Job Queued');
        } else {
            $output->writeln('<error>Queueing Job Failed</error>');
            $output->writeln($r->getBody()->getContents());
        }
        return 0;
    }
}
