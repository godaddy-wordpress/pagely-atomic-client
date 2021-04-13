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
            ->addArgument('jobId', InputArgument::REQUIRED, 'Job id')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $r = $this->api->getLetsencryptStatus(
            $this->token->token,
            $input->getArgument('jobId')
        );
        $output->writeln($r->getBody()->getContents());
    }
}

