<?php

namespace Pagely\AtomicClient\Command\SSL;

use Pagely\AtomicClient\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class CertLinkCommandTrait
 * @package Pagely\AtomicClient\Command\SSL
 */
trait CertLinkCommandTrait
{
    public function addCertLinkOptions(Command $cmd) {
        $cmd
            ->addOption('hsts', null, InputOption::VALUE_REQUIRED, 'HSTS Cache Length in Seconds')
            ->addOption('http2', null, InputOption::VALUE_REQUIRED, 'En/Disable HTTP2 protocol (0 or 1)')
            ->addOption('tlsConfig', null, InputOption::VALUE_REQUIRED, 'Custom TLS configuration')
            ->addOption('minTlsVersion', null, InputOption::VALUE_REQUIRED, 'Minimum TLS Version')
            ->addOption('cipher', null, InputOption::VALUE_REQUIRED, 'Cipher method (Compatible or Modern)')
            ->addOption('redirect', null, InputOption::VALUE_REQUIRED, 'En/Disable Redirect to HTTPS (0 or 1)')
            ->addOption('redirectConfig', null, InputOption::VALUE_REQUIRED, 'JSON for custom redirect')
            ;
    }
}
