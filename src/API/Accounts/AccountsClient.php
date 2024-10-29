<?php

namespace Pagely\AtomicClient\API\Accounts;

use Pagely\AtomicClient\API\BaseApiClient;

class AccountsClient extends BaseApiClient
{
    protected $apiName = 'accounts';

    public function getCollaborators(string $accessToken, string $accountId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get("accounts/{$accountId}/access");
    }

    public function getCollabRole(string $accessToken, int $accountId, int $collabId, int $appId = 0): int
    {
        $r = $this->getCollaborators($accessToken, $accountId);
        $collabInfo = json_decode($r->getBody()->getContents(), true);
        foreach(@$collabInfo['whoCanAccess'] as $tidbit) {
            // print_r($tidbit);
            // should this output be validated or similar?
            if (($tidbit['appId'] == $appId) && ($tidbit['sourceId'] == $collabId)) {
                return $tidbit['role'];
            }
        }
        return 0;
    }

    public function addCollaboratorToAcct(string $accessToken, string $newAcctEmail, string $newAcctName, int $newAcctRole, int $newAcctId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("accounts/{$newAcctId}/collaborators", ['json' => [
                'email' => $newAcctEmail,
                'name' => $newAcctName,
                'role' => $newAcctRole,
                'appId' => 0
            ],
        ]);
    }

    public function removeCollaboratorFromAcct(string $accessToken, int $acctId, int $collabId, int $appId = 0) {
        // $role = 6; // this is a temp hack, need to actually get user's role somehow
        $role = $this->getCollabRole($accessToken, $acctId, $collabId, $appId);
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
        ->delete("accounts/{$acctId}/collaborators/{$collabId}/{$role}/{$appId}");
    }

    public function removeCollaboratorsForApp(string $accessToken, string $accountId, int $appId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->delete("accounts/{$accountId}/collaborators/apps/{$appId}");
    }

    // This API is both for removing access from individual apps, as well
    // as removing access for whole accounts. fun!
    /**
     * @param string $accessToken
     * @param int|string $targetAccountId
     * @param ?int $sourceAccountId
     * @param null $role
     * @param int $appId
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function removeAccess(string $accessToken, $targetAccountId, $sourceAccountId = null, $role = null, $appId = 0)
    {
        if ($sourceAccountId) {
            return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
                ->delete("accounts/{$targetAccountId}/collaborators/{$sourceAccountId}/{$role}/{$appId}");
        }

        // returned, so at thsi point we know no sourceAccountId was given.

        if ($appId) {
            return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
                ->delete("accounts/{$targetAccountId}/collaborators/apps/{$appId}");
        }

        throw new \Exception('Invalid arguments - must include source & role [& app], or app by itself');
    }

    public function listSshPublicKeys(
        string $accessToken,
        string $accountId,
        ?string $orchestration = null,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->get("accounts/{$accountId}/ssh/keys", [
                'json' => array_filter(
                    compact(
                        'orchestration',
                    )
                ),
            ]);
    }

    public function createSshPublicKey(
        string $accessToken,
        string $accountId,
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

    public function deleteSshPublicKey(
        string $accessToken,
        string $accountId,
        string $sshKeyId,
        ?string $orchestration = null,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->delete("accounts/{$accountId}/ssh/keys/{$sshKeyId}", [
                'json' => array_filter(compact(
                    'orchestration',
                )),
            ]);
    }

    public function createSshUser(
        string $accessToken,
        string $sshUsername,
        string $ownerId,
        string $ownerType,
        string $sshUserType,
        bool $sftpOnly = false,
        array $appIds = [],
        bool $replace = false,
        bool $disabled = false,
        bool $generatePassword = false,
        ?string $password = null,
        ?\DateTimeInterface $expiry = null
    ) {
        $expiry = $expiry ? $expiry->format(\DateTimeInterface::ATOM) : null;

        $nonNullParams = array_filter(compact(
            'sshUsername',
            'ownerType',
            'sshUserType',
            'sftpOnly',
            'appIds',
            'replace',
            'disabled',
            'generatePassword',
            'password',
            'expiry',
        ), function ($value) {
            return $value !== null;
        });

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post("accounts/{$ownerId}/ssh/user", [
                'json' => $nonNullParams,
            ]);
    }

    public function getSshUser(
        string $accessToken,
        string $sshUsername,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->get("accounts/ssh/users/{$sshUsername}");
    }

    public function updateSshUser(
        string $accessToken,
        string $ownerId,
        #[\SensitiveParameter]
        ?string $password = null,
        ?bool $sftpOnly = null,
        ?bool $disabled = null,
        ?\DateTimeInterface $expiry = null
    ) {
        $expiry = $expiry ? $expiry->format(\DateTimeInterface::ATOM) : null;

        $nonNullParams = array_filter(compact(
            'password',
            'sftpOnly',
            'disabled',
            'expiry',
        ), function ($value) {
            return $value !== null;
        });

        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->patch("accounts/{$ownerId}/ssh/user", [
                'json' => $nonNullParams,
            ]);
    }

    public function createSshApps(
        string $accessToken,
        string $sshUsername,
        array $appIds,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post('accounts/ssh/apps/create', [
                'json' => [
                    'sshUsername' => $sshUsername,
                    'appIds' => $appIds,
                ],
            ]);
    }

    public function listApps(
        string $accessToken,
        string $sshUsername,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->get("accounts/ssh/users/{$sshUsername}/apps");
    }

    public function deleteSshUser(
        string $accessToken,
        string $accountId,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->delete("accounts/{$accountId}/ssh/user");
    }

    public function deleteSshApps(
        string $accessToken,
        array $appIds,
    ) {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))
            ->post('accounts/ssh/apps', [
                'json' => [
                    'appIds' => $appIds,
                ],
            ]);
    }


}
