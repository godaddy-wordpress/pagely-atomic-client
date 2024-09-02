<?php

namespace Pagely\AtomicClient\Command\Apps;

use Pagely\AtomicClient\API\AuthApi;
use Pagely\AtomicClient\Command\Command;
use Pagely\AtomicClient\Command\OauthCommandTrait;
use Pagely\AtomicClient\API\Apps\AppsClient;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class GetAppBackupCommand extends Command
{
    use OauthCommandTrait;

    /**
     * @var AppsClient
     */
    protected $api;

    public function __construct(AuthApi $authApi, AppsClient $apps, $name = 'apps:backups:get')
    {
        $this->authClient = $authApi;
        $this->api = $apps;
        parent::__construct($name);
    }

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription('Get Backup details and download links')
            ->addArgument('appId', InputArgument::REQUIRED, 'App ID')
            ->addOption('backupId', null, InputOption::VALUE_REQUIRED, 'Backup ID. If omitted, the latest backup is returned.')
            ->addOption('curl', null, InputOption::VALUE_REQUIRED, 'Return curl download command eg. file|sql')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Return data is JSON format');
        $this->addOauthOptions();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $appId = $input->getArgument('appId');
        $backupId = $input->getOption('backupId');
        $json = $input->getOption('json');
        $curlOption = $input->getOption('curl');

        $token = $this->token->token;

        if ($backupId) {
            $r = $this->api->getAppBackup($token, $appId, $backupId);
        } else {
            $r = $this->api->getLatestAppBackup($token, $appId);
        }

        $body = json_decode($r->getBody()->getContents(), true);

        if ($json) {
            echo json_encode($body, JSON_PRETTY_PRINT);
            return 1;
        }

        if (empty($curlOption)) {
            $rows = [];
            foreach ($body as $k => $v) {
                if ($k === 'sqlLink' || $k === 'fileLink') {
                    continue;
                }
                $rows[] = [$k, $v];
            }

            $table = new Table($output);
            $table
                ->setHeaders(['Field', 'Value'])
                ->setRows($rows);
            $table->render();

            $output->writeln("Files Link");
            $output->writeln($body['fileLink']);
            $output->writeln("Sql Link");
            $output->writeln($body['sqlLink']);
            return 0;
        }
        switch ($curlOption) {
            case 'file':
                $output->writeln("curl {$body['fileLink']} --output pagely-file-backup.tar.gz");
                break;
            case 'sql':
                $output->writeln("curl {$body['sqlLink']} --output pagely-sql-backup.tar.gz");
                break;
            default:
                $output->writeln("Unknown backup type for --curl");
                return 1;
        }
        return 0;
    }
}
