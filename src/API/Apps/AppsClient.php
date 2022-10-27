<?php
namespace Pagely\AtomicClient\API\Apps;

use Pagely\AtomicClient\API\BaseApiClient;
use Psr\Http\Message\ResponseInterface;

class AppsClient extends BaseApiClient
{
    protected $apiName = 'apps';

    public function find($accessToken, $accountId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get("apps", [
            'query' => [
                'accountId' => $accountId,
            ],
        ]);
    }

    public function getById($accessToken, $appId, $includeAlias = false)
    {
        $query = [];
        if ($includeAlias) {
            $query['includeAliases'] = 1;
        }
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get("apps/{$appId}", ['query' => $query]);
    }

    public function getByName($accessToken, $app)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get('apps/findByName', [
            'query' => ['name' => $app],
        ]);
    }

    public function findWithAlias($accessToken, $accountId, $includeAlias = false, $searches = [])
    {
        $query = [
            'accountId' => $accountId,
        ];
        if ($includeAlias) {
            $query['includeAliases'] = 1;
        }
        if ($searches) {
            $query['aliasSearch'] = $searches;
        }
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get("apps", [
            'query' => $query,
        ]);
    }

    public function getAliasById($accessToken, $aliasId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get("apps/aliases/{$aliasId}");
    }

    public function findAliases($accessToken, $accountId = null, $search = null, $names = null, $appId = null, $noPressDns = null)
    {
        return $this->findAliasesAsync($accessToken, $accountId, $search, $names, $appId, $noPressDns)->wait();
    }

    public function findAliasesAsync($accessToken, $accountId = null, $search = null, $names = null, $appId = null, $noPressDns = null)
    {
        $query = [];

        if ($accountId) {
            $query['accountId'] = $accountId;
        }
        if ($appId) {
            $query['appId'] = $appId;
        }
        if ($search) {
            $query['search'] = $search;
        }
        if ($names) {
            $query['names'] = $names;
        }
        if ($noPressDns) {
            $query['noPressDns'] = 1;
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->getAsync('apps/aliases', [
            'query' => $query
        ]);
    }

    public function getAppCustomIps($accessToken, $appId)
    {
        if (is_array($appId)) {
            $appId = implode(',', $appId);
        }
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get('apps/'.$appId.'/ips');
    }

    public function addCustomIp($accessToken, $appId, $ip, $cname = false)
    {
        $json = [
            'ip' => $ip,
        ];
        if ($cname) {
            $json['cname'] = $cname;
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("apps/{$appId}/ips", [
                'json' => $json
            ]);
    }

    public function removeCustomIp($accessToken, $appId, $ip)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->delete("apps/{$appId}/ips/{$ip}");
    }

    public function createApp($accessToken, $accountId, $label, $primaryDomain, $multisite = false, $multisiteType = null, $useLabelInSitesDir = false)
    {
        $data = [
            'label' => $label,
            'accountId' => (int) $accountId,
            'primaryDomain' => $primaryDomain,
            'multisite' => (bool) $multisite,
            'name' => $useLabelInSitesDir ? $label : $primaryDomain
        ];
        if ($multisiteType) {
            $data['multisiteType'] = $multisiteType;
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("apps", [
                'json' => $data,
            ]);
    }

    public function addDomain($accessToken, $appId, $fqdn, $is301 = null, $redirect = null)
    {
        $json = [
            'fqdn' => $fqdn,
        ];
        if ($is301) {
            $json['is301'] = true;
            if ($redirect) {
                $json['destination301'] = $redirect;
            }
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("apps/{$appId}/aliases", [
                'json' => $json,
            ]);
    }

    public function removeDomain($accessToken, $appId, $domainId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->delete("apps/{$appId}/aliases/{$domainId}");
    }

    public function updateDomain($accessToken, $appId, $fqdn, $is301 = null, $redirect = null)
    {
        $json = [
            'fqdn' => $fqdn,
        ];
        if (null !== $is301) {
            $json['is301'] = true;
            if ($redirect) {
                $json['destination301'] = $redirect;
            }
        }

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("apps/{$appId}/aliases", [
                'json' => $json,
            ]);
    }

    public function makePrimary($accessToken, $appId, $domainId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("apps/{$appId}/aliases/{$domainId}/makeprimary");
    }

    public function disable($accessToken, $appId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("apps/{$appId}/disable");
    }

    public function remove($accessToken, $appId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->delete("apps/{$appId}");
    }

    protected function setWebserverType(string $accessToken, $appId, string $type): ResponseInterface
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("apps/{$appId}/server-type", [
                'json' => ['serverType' => $type],
            ]);
    }

    public function setWebserverTypeNginx(string $accessToken, int $appId)
    {
        return $this->setWebserverType($accessToken, $appId, 'nginx');
    }

    public function setWebserverTypeApache(string $accessToken, int $appId)
    {
        return $this->setWebserverType($accessToken, $appId, 'nginx-apache');
    }

    //
    // Git integration methods
    //
    public function createGitIntegration(string $accessToken, int $appId, string $remoteProvider, string $branch): ResponseInterface
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post(
                "apps/{$appId}/git-integration",
                [
                    'json' =>[
                        'remote' => $remoteProvider,
                        'branch' => $branch,
                    ]
                ]
            );
    }

    public function deleteGitIntegration(string $accessToken, int $appId): ResponseInterface
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->delete("apps/{$appId}/git-integration");
    }

    public function getGitIntegration(string $accessToken, int $appId): ResponseInterface
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->get("apps/{$appId}/git-integration");
    }

    public function getAppBackups($accessToken, $appId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->get("apps/{$appId}/backups");
    }

    public function getAppBackup($accessToken, $appId, $backupId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->get("apps/{$appId}/backups/{$backupId}");
    }

    public function getLatestAppBackup($accessToken, $appId)
    {
        $body = json_decode($this->getAppBackups($accessToken, $appId)->getBody()->getContents(), true);
        $latest = end($body['data']);
        return $this->getAppBackup($accessToken, $appId, $latest['id']);
    }
}
