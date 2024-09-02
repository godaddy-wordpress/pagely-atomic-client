<?php
namespace Pagely\AtomicClient;

use Auryn\Injector;
use Dotenv\Exception\InvalidPathException;
use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Pagely\AtomicClient\API\AuthApi;


class Helper
{
    public function apiClient($className)
    {
        $injector = new Injector();
        $handlers = [
            $injector->make(\Monolog\Handler\NullHandler::class)
        ];
        $injector
            ->alias(\Psr\Log\LoggerInterface::class, \Monolog\Logger::class)
            ->define(\Monolog\Logger::class, [':name' => 'CLI', ':handlers' => $handlers])
            ->share(\Psr\Log\LoggerInterface::class)
            ->define($className, [
                ':baseUrl' => getenv('MGMT_API_URL') ?: 'https://mgmt.pagely.com/api/'
            ]);

        return $injector->make($className);
    }

    public function cachedClientAuth(
        $reauth = false,
        $config = 'atomic-client.conf',
        $cache = null
    )
    {
        if ($cache === null) {
            $cachefile = getenv('STAGE') && !getenv('PROD_OVERRIDE') ? '.atomic-staging.auth' : '.atomic.auth';
            $cache = getenv('HOME')."/{$cachefile}";
        }

        $config = getenv('HOME').'/'.$config;

        if (file_exists($config))
        {
            $dotenv = \Dotenv\Dotenv::createImmutable(dirname($config), basename($config));
            $dotenv->load();
        }

        if (!$reauth && file_exists($cache) && is_readable($cache) && filemtime($cache) > time()-(12*60*60))
        {
            return json_decode(file_get_contents($cache));
        }

        $clientId = getenv('ATOMIC_CLIENT_ID');
        $clientSecret = getenv('ATOMIC_CLIENT_SECRET');

        $auth = $this->apiClient(AuthApi::class);
        $token = json_decode($auth->clientLogin($clientId, $clientSecret)->getBody()->getContents());
        file_put_contents($cache, json_encode($token));

        return $token;
    }

    public function configureInjector(Injector $injector)
    {

        $injector
            ->share(\Psr\Log\LoggerInterface::class)
            ->delegate(\Psr\Log\LoggerInterface::class, function()
            {
                $logger = new Logger('CLI');
                if (getenv('DEBUG') != 1)
                {
                    $handler = new NullHandler();
                    $logger->pushHandler($handler);
                }

                return $logger;
            });
    }

    public function loadConfig($baseDir)
    {
        $staging = getenv('STAGE') && !getenv('PROD_OVERRIDE');
        try
        {
            $home = getenv('HOME');
            $dotenv = \Dotenv\Dotenv::createImmutable($home, $staging ? '/.atomic-staging' : '/.atomic');
            $dotenv->load();
        }
        catch (InvalidPathException $e)
        {
            // just ignore this we don't care if you don't have a .env file
        }

        try
        {
            $dotenv = \Dotenv\Dotenv::createImmutable($baseDir, $staging ? '/.env-staging' : '/.env');
            $dotenv->load();
        }
        catch (InvalidPathException $e)
        {
            // just ignore this we don't care if you don't have a .env file
        }

        // doesn't overwrite so defaults get loaded last
        $dotenv = \Dotenv\Dotenv::createImmutable($baseDir, $staging ? '/.env-staging' : '/.env.defaults');
        $dotenv->load();
    }
}
