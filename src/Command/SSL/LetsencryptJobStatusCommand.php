<?php

namespace Pagely\AtomicClient\Command\SSL;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LetsencryptJobStatusCommand extends AbstractSSLCmd
{
    protected $commandName = 'ssl:le:jobstatus';

    public function configure()
    {
        parent::configure();
        $this->setDescription('Check letsencrypt job status')
            ->addArgument('id', InputArgument::REQUIRED, 'ID of the letsencrypt job (not jobId)')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $r = $this->api->getLetsencryptStatus(
            $this->token->token,
            $input->getArgument('id')
        );
        $output->writeln($r->getBody()->getContents());
        return 0;
    }
}

