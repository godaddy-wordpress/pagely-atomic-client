<?php

namespace Pagely\AtomicClient\Command\SSL;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use GuzzleHttp\Exception\BadResponseException;

class CertImportCommand extends AbstractSSLCmd
{
    protected $commandName = 'ssl:cert:import';

    protected $output;

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Import a new SSL cert')
            ->addArgument('accountId', InputArgument::REQUIRED, 'Account ID')
            ->addArgument('cert', InputArgument::REQUIRED, 'Path to certificate file')
            ->addArgument('privateKey', InputArgument::REQUIRED, 'Path to private key file')
            ->addOption('csr', null, InputOption::VALUE_REQUIRED, 'Path to CSR file')
            ->addOption('chain', null, InputOption::VALUE_REQUIRED, 'Path to CA chain')
        ;
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($output->isDebug()) {
            $this->api->setDebug();
        }

        $this->output = $output;

        $cert = $input->getArgument('cert');
        $privateKey = $input->getArgument('privateKey');

        try {
            $response = $this->api->import(
                $this->token->token,
                $input->getArgument('accountId'),
                $this->getFile($input->getOption('csr')),
                $this->getFile($input->getArgument('privateKey')),
                $this->getFile($input->getArgument('cert')),
                $this->getFile($input->getOption('chain'))
            );
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
            $this->output->writeln("<error>Could not import cert</error>");
            $this->output->writeln($response->getStatusCode()." - ".$response->getBody()->getContents());
            exit(1);
        }

        $imported = json_decode($response->getBody()->getContents());
        $this->output->writeln(json_encode($imported, JSON_PRETTY_PRINT));
    }

    protected function getFile($path)
    {
        if (is_null($path)) {
            return null;
        }

        if (!is_readable($path)) {
            $this->output->writeln([
                "<error>Oops, this file does not exist or is not readable:</error>",
                $path
            ]);
            exit(1);
        }

        return file_get_contents($path);
    }
}
