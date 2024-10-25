<?php

namespace Pagely\AtomicClient\API\Accounts;

use Pagely\AtomicClient\API\BaseApiClient;

class AccountsClient extends BaseApiClient
{
    protected $apiName = 'accounts';

    public function getCollaboratorAccess($accessToken, $accountId)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($accessToken))->get("accounts/{$accountId}/access");
    }

    public function removeCollaboratorsForApp($accessToken, $accountId, $appId)
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


    public function getApiKeys(string $token, array $accountIds)
    {
        return $this->guzzle($this->getBearerTokenMiddleware($token))
            ->get(
                'accounts/apikeys',
                [
                'query' => [
                    'accountIds' => $accountIds,
                ],
            ]
            );
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
