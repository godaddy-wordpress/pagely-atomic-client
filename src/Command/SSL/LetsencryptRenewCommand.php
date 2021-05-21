<?php

namespace Pagely\AtomicClient\Command\SSL;

use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LetsencryptRenewCommand extends AbstractSSLCmd
{
    protected $commandName = 'ssl:le:renew';

    public function configure()
    {
        parent::configure();
        $this->setDescription('Renew single LE Cert for a domain');
        $this->addOauthOptions();
        $this->addArgument('appId', InputArgument::REQUIRED, 'App ID')
            ->addArgument('aliasId', InputArgument::REQUIRED, 'Alias (domain) ID')
            ->addArgument('certId', InputArgument::REQUIRED, 'Cert ID')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $r = $this->api->letsencryptRenew(
                $this->token->token,
                (int) $input->getArgument('appId'),
                (int) $input->getArgument('aliasId'),
                (int) $input->getArgument('certId')
            );
            $output->writeln(json_encode(json_decode($r->getBody()->getContents(), true), JSON_PRETTY_PRINT));
        } catch (ClientException $e) {
            $output->writeln('<error>Failed to renew</error>');
            $output->writeln($e->getResponse()->getBody()->getContents());
        }
        return 0;
    }
}
