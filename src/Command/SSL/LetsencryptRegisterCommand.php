<?php

namespace Pagely\AtomicClient\Command\SSL;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LetsencryptRegisterCommand extends AbstractSSLCmd
{
    protected $commandName = 'ssl:le:register';

    public function configure()
    {
        parent::configure();
        $this->setDescription('Begins certificate registration process for domain via letsencrypt')
            ->addArgument('accountId', InputArgument::REQUIRED, 'Account ID')
            ->addArgument('domain', InputArgument::REQUIRED, 'App name')
            ->addArgument('alternateNames', InputArgument::IS_ARRAY | InputArgument::OPTIONAL)
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $r = $this->api->letsencryptRegister(
            $this->token->token,
            $input->getArgument('domain'),
            $input->getArgument('accountId'),
            $input->getArgument('alternateNames') ?: []
        );
        if ($r->getStatusCode() === 200) {
            $data = json_decode($r->getBody()->getContents());
            $output->writeln('Successfully queued job: '.$data->id);
            $output->writeln('Check status with:    atomic ssl:le:jobstatus '.$data->id);
        } else {
            $output->writeln('<error>Registration failed</error>');
            $output->writeln($r->getBody()->getContents());
        }
        return 0;
    }
}
