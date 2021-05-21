<?php

namespace Pagely\AtomicClient\Command\SSL;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CertLinkCommand extends AbstractSSLCmd
{
    use CertLinkCommandTrait;

    protected $commandName = 'ssl:cert:link';

    public function configure()
    {
        $this->setDescription('Link certificate to domain')
            ->addArgument('certID', InputArgument::REQUIRED, 'Certificate ID')
            ->addArgument('aliasID', InputArgument::REQUIRED, 'Alias ID')
        ;
        $this->addCertLinkOptions();
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $certId = $input->getArgument('certID');
        $aliasId = $input->getArgument('aliasID');

        $r = $this->api->linkCertificateToApp(
            $this->token->token,
            $certId,
            $aliasId,
            $input->getOption('redirect'),
            $input->getOption('hsts'),
            $input->getOption('http2'),
            $input->getOption('tlsConfig'),
            null,
            $input->getOption('minTlsVersion'),
            $input->getOption('redirectConfig'),
            $input->getOption('cipher')
        );

        if ($r->getStatusCode() == 200) {
            $output->writeln('Success');
        } else {
            $output->writeln('<error>Failed Linking</error>');
            $output->writeln($r->getBody()->getContents());
        }
        return 0;
    }
}
