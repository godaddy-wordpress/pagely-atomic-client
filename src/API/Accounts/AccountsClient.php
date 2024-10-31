<?php

namespace Pagely\AtomicClient\API\Accounts;

use Pagely\AtomicClient\API\BaseApiClient;

class AccountsClient extends BaseApiClient
{
    protected $apiName = 'accounts';

    public function getCollaborators(string $accessToken, int $accountId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get("accounts/{$accountId}/access");
    }

    // this whole function feels super hacky but I don't think there's an API way to get this info
    private function getCollabRole(string $accessToken, int $accountId, int $collabId, int $appId = 0): int
    {
        $r = $this->getCollaborators($accessToken, $accountId);
        $collabInfo = json_decode($r->getBody()->getContents(), true);
        foreach(@$collabInfo['whoCanAccess'] as $tidbit) {
            if (($tidbit['appId'] == $appId) && ($tidbit['sourceId'] == $collabId)) {
                return $tidbit['role'];
            }
        }
        return 0;
    }

    public function addCollaboratorToAcct(string $accessToken, string $newAcctEmail, string $newAcctName, int $newAcctId, int $newAcctRole, int $newAppId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("accounts/{$newAcctId}/collaborators", ['json' => [
                'email' => $newAcctEmail,
                'name' => $newAcctName,
                'role' => $newAcctRole,
                'appId' => $newAppId
            ],
        ]);
    }

    public function removeCollaboratorFromAcct(string $accessToken, int $acctId, int $collabId, int $appId = 0)
    {
        $role = $this->getCollabRole($accessToken, $acctId, $collabId, $appId);
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
        ->delete("accounts/{$acctId}/collaborators/{$collabId}/{$role}/{$appId}");
    }

    public function createSshPublicKey(
        string $accessToken,
        int    $accountId,
        string $key,
        ?string $orchestration = null,
        ?string $sshUsername = null,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("accounts/{$accountId}/ssh/keys", [
                'json' => array_filter(compact(
                    'key',
                    'orchestration',
                    'sshUsername',
                )),
            ]);
    }

}
