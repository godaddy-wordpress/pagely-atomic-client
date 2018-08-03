<?php

namespace Pagely\AtomicClient\Command\SSL;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppCertUpdateCommand extends AbstractSSLCmd
{
    use CertLinkCommandTrait;

    protected $commandName = 'ssl:cert:update';

    public function configure()
    {
        $this->setDescription('Update cert config for a app/alias')
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

        $redirect = $input->getOption('redirect');
        if (null !== $redirect) {
            $redirect = (bool) $redirect;
        }
        $http2 = $input->getOption('http2');
        if (null !== $http2) {
            $http2 = (bool) $http2;
        }
        $r = $this->api->updateAppCertificateConfig(
            $this->token->token,
            $certId,
            $aliasId,
            $redirect,
            $input->getOption('hsts'),
            $http2,
            $input->getOption('tlsConfig'),
            null,
            $input->getOption('minTlsVersion'),
            $input->getOption('redirectConfig'),
            $input->getOption('cipher')
        );

        switch ($r->getStatusCode()) {
            case 200:
                $output->writeln('Success');
                break;
            case 404:
                $output->writeln('<warning>App-Cert link does not exist</warning>');
                break;
            default:
                $output->writeln('<error>Update Failed</error>');
                $output->writeln('Status: '.$r->getStatusCode());
                $output->writeln($r->getBody()->getContents());
                break;
        }
    }
}
