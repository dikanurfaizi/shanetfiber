<?php

namespace App\Services\Routerboard\Ppp;

use Exception;
use RouterOS\Exceptions\BadCredentialsException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;
use App\Helpers\LogHelper;
use App\Services\Routerboard\MikrotikConnectionService;

class SecretService
{
    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */
    public function createSecret($mikrotik, $username, $password, $service, $profile, $comment, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            LogHelper::logInfo(
                null,
                [
                    'router' => $mikrotik->name ?? 'unknown',
                    'username' => $username,
                    'profile' => $profile,
                    'service' => $service,
                    'comment' => $comment
                ],
                200,
                'Preparing to create secret',
                $traceId
            );

            $query = (new Query('/ppp/secret/add'))
                ->equal('name', $username)
                ->equal('password', $password)
                ->equal('service', $service)
                ->equal('comment', $comment)
                ->equal('profile', $profile)
                ->equal('disabled', 'false');

            $response = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'router' => $mikrotik->name,
                    'username' => $username,
                    'profile' => $profile,
                    'service' => $service,
                    'comment' => $comment,
                    'response' => $response
                ],
                200,
                'Secret created successfully',
                $traceId
            );

            return $response;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'router' => $mikrotik->name ?? 'unknown',
                    'username' => $username,
                    'profile' => $profile,
                    'service' => $service,
                    'comment' => $comment,
                    'error' => $e->getMessage()
                ],
                500,
                'Failed to create secret',
                $traceId
            );
            throw $e;
        }
    }

    public function updateSecret($mikrotik, $username, $password, $service, $profile, $id, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('.id', $id);
            $editPppSecrets = $client->query($pppsecrets)->read();

            if (empty($editPppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($editPppSecrets as $editPppSecret) {
                $edit = (new Query('/ppp/secret/set'))
                    ->where('.id', $id)
                    ->equal('name', $username)
                    ->equal('password', $password)
                    ->equal('service', $service)
                    ->equal('profile', $profile);

                return $client->query($edit)->read();
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'router' => $mikrotik->name ?? 'unknown',
                    'username' => $username,
                    'profile' => $profile,
                    'service' => $service,
                    'error' => $e->getMessage()
                ],
                500,
                'Failed to update secret',
                $traceId
            );
            throw $e;
        }
    }

    public function updateUserPasswordServiceSecret($mikrotik, $username, $password, $service, $id, $callerID, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('.id', $id);
            $editPppSecrets = $client->query($pppsecrets)->read();

            if (empty($editPppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($editPppSecrets as $editPppSecret) {
                $edit = (new Query('/ppp/secret/set'))
                    ->where('.id', $id)
                    ->equal('.id', $editPppSecret['.id'])
                    ->equal('name', $username)
                    ->equal('password', $password)
                    ->equal('service', $service)
                    ->equal('caller-id', $callerID);

                $result = $client->query($edit)->read();

                // ✅ Log success
                LogHelper::logInfo(
                    null,
                    [
                        'router'   => $mikrotik->name ?? 'unknown',
                        'username' => $username,
                        'service'  => $service,
                        'id'       => $id,
                        'callerId' => $callerID,
                    ],
                    200,
                    'Successfully updated user password/service secret',
                    $traceId
                );

                return $result;
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'router' => $mikrotik->name ?? 'unknown',
                    'username' => $username,
                    'service' => $service,
                    'error' => $e->getMessage()
                ],
                500,
                'Failed to update user password/service secret',
                $traceId
            );
            throw $e;
        }
    }

    public function updateProfileSecret($mikrotik, $profile, $id, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('.id', $id);
            $editPppSecrets = $client->query($pppsecrets)->read();

            if (empty($editPppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($editPppSecrets as $editPppSecret) {
                $edit = (new Query('/ppp/secret/set'))
                    ->where('.id', $id)
                    ->equal('.id', $editPppSecret['.id'])
                    ->equal('profile', $profile);

                return $client->query($edit)->read();
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'router' => $mikrotik->name ?? 'unknown',
                    'profile' => $profile,
                    'error' => $e->getMessage()
                ],
                500,
                'Failed to update profile secret',
                $traceId
            );
            throw $e;
        }
    }

    public function addCommentSecret($mikrotik, $comment, $secretId, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('.id', $secretId);
            $editPppSecrets = $client->query($pppsecrets)->read();

            if (empty($editPppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($editPppSecrets as $editPppSecret) {
                $edit = (new Query('/ppp/secret/set'))
                    ->where('.id', $secretId)
                    ->equal('.id', $editPppSecret['.id'])
                    ->equal('comment', $comment);

                return $client->query($edit)->read();
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'router' => $mikrotik->name ?? 'unknown',
                    'secret_id' => $secretId,
                    'error' => $e->getMessage()
                ],
                500,
                'Failed to add comment to secret',
                $traceId
            );
            throw $e;
        }
    }

    public function enableSecret($mikrotik, $username, string $traceId)
    {
        try {
            $toEnable = 'false';
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('name', $username);
            $enablePppSecrets = $client->query($pppsecrets)->read();

            if (empty($enablePppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($enablePppSecrets as $enablePppSecret) {
                $enable = (new Query('/ppp/secret/set'))
                    ->where('name', $username)
                    ->equal('.id', $enablePppSecret['.id'])
                    ->equal('disabled', $toEnable);
                $client->query($enable)->read();
            }
            return ($response = 'success');
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'router' => $mikrotik->name ?? 'unknown',
                    'username' => $username,
                    'error' => $e->getMessage()
                ],
                500,
                'Failed to enable secret',
                $traceId
            );
            throw $e;
        }
    }

    public function enableOneSecret($mikrotik, $secretId, string $traceId)
    {
        try {
            $toEnable = 'false';
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('.id', $secretId);
            $enablePppSecrets = $client->query($pppsecrets)->read();

            if (empty($enablePppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($enablePppSecrets as $enablePppSecret) {
                $enable = (new Query('/ppp/secret/set'))
                    ->where('.id', $secretId)
                    ->equal('.id', $enablePppSecret['.id'])
                    ->equal('disabled', $toEnable);
                $client->query($enable)->read();
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'router' => $mikrotik->name ?? 'unknown',
                    'secret_id' => $secretId,
                    'error' => $e->getMessage()
                ],
                500,
                'Failed to enable one secret',
                $traceId
            );
            throw $e;
        }
    }

    public function disableSecret($mikrotik, $username, string $traceId)
    {
        try {
            $toNoDisable = 'true';
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('name', $username);
            $disablePppSecrets = $client->query($pppsecrets)->read();

            if (empty($disablePppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($disablePppSecrets as $disablePppSecret) {
                $disable = (new Query('/ppp/secret/set'))
                    ->where('name', $username)
                    ->equal('.id', $disablePppSecret['.id'])
                    ->equal('disabled', $toNoDisable);
                $client->query($disable)->read();
            }
            return ($response = 'success');
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'router' => $mikrotik->name ?? 'unknown',
                    'username' => $username,
                    'error' => $e->getMessage()
                ],
                500,
                'Failed to disable secret',
                $traceId
            );
            throw $e;
        }
    }

    public function disableOneSecret($mikrotik, $secretId, string $traceId)
    {
        try {
            $toNoDisable = 'true';
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecrets = new Query('/ppp/secret/print');
            $pppsecrets->where('.id', $secretId);
            $disablePppSecrets = $client->query($pppsecrets)->read();

            if (empty($disablePppSecrets)) {
                throw new Exception('PPP Secret not found.');
            }

            foreach ($disablePppSecrets as $disablePppSecret) {
                $disable = (new Query('/ppp/secret/set'))
                    ->where('.id', $secretId)
                    ->equal('.id', $disablePppSecret['.id'])
                    ->equal('disabled', $toNoDisable);
                $client->query($disable)->read();
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'router' => $mikrotik->name ?? 'unknown',
                    'secret_id' => $secretId,
                    'error' => $e->getMessage()
                ],
                500,
                'Failed to disable one secret',
                $traceId
            );
            throw $e;
        }
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */
    public function deleteSecretByName($mikrotik, $username, string $traceId): void
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $pppsecret = new Query('/ppp/secret/print');
            $pppsecret->where('name', $username);
            $deleteSecrets = $client->query($pppsecret)->read();

            if (empty($deleteSecrets)) {
                LogHelper::logError(
                    null,
                    [
                        'host' => $mikrotik->host ?? 'N/A',
                        'username' => $username,
                        'error' => 'Secret not found'
                    ],
                    404,
                    "Failed to delete Mikrotik secret",
                    $traceId
                );
                return;
            }

            foreach ($deleteSecrets as $secret) {
                $deleteQuery = (new Query('/ppp/secret/remove'))
                    ->where('name', $username)
                    ->equal('.id', $secret['.id']);
                $client->query($deleteQuery)->read();
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'username' => $username,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to delete Mikrotik secret",
                $traceId
            );
            throw $e;
        }
    }

    public function deleteOneSecret($mikrotik, $secretId, string $traceId): void
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $deleteQuery = (new Query('/ppp/secret/remove'))
                ->where('.id', $secretId);
            $client->query($deleteQuery)->read();
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'secret_id' => $secretId,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to delete Mikrotik secret",
                $traceId
            );
            throw $e;
        }
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */
    public function getAllPppSecrets($mikrotik, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/secret/print');
            $result = $client->query($query)->read();

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to fetch all Mikrotik secrets",
                $traceId
            );
            throw $e;
        }
    }

    public function getSecretById($mikrotik, $id, string $traceId)
    {
        return $this->getSecret($mikrotik, ['.id' => $id], 'ID', $id, $traceId);
    }

    public function getSecretByName($mikrotik, $username, string $traceId)
    {
        return $this->getSecret($mikrotik, ['name' => $username], 'Username', $username, $traceId);
    }

    public function getSecretByProfile($mikrotik, $profile, string $traceId)
    {
        return $this->getSecret($mikrotik, ['profile' => $profile], 'Profile', $profile, $traceId);
    }

    public function getSecretByNameProfile($mikrotik, $name, $profile, string $traceId)
    {
        return $this->getSecret($mikrotik, ['name' => $name, 'profile' => $profile], 'Name and Profile', "$name | $profile", $traceId);
    }

    private function getSecret($mikrotik, $filters, $filterType, $filterValue, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/secret/print');
            foreach ($filters as $key => $value) {
                $query->where($key, $value);
            }

            $result = $client->query($query)->read();

            if (empty($result)) {
                LogHelper::logWarning(
                    null,
                    [
                        'mikrotik_id' => $mikrotik->id ?? 'N/A',
                        'mikrotik_name' => $mikrotik->name ?? 'N/A',
                        'host' => $mikrotik->host ?? 'N/A',
                        'filter' => $filters,
                        'filterValue' => $filterValue
                    ],
                    404,
                    "No Mikrotik secrets found for $filterType",
                    $traceId
                );
                return [];
            }

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'filter' => $filterValue,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to fetch Mikrotik secrets by $filterType",
                $traceId
            );
            throw $e;
        }
    }

    public function deleteActiveSecret($mikrotik, $username, string $traceId): void
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/active/print');
            $query->where('name', $username);
            $activeSecrets = $client->query($query)->read();

            if (empty($activeSecrets)) {
                LogHelper::logWarning(
                    null,
                    [
                        'host' => $mikrotik->host ?? 'N/A',
                        'username' => $username
                    ],
                    404,
                    "No active secrets found to delete",
                    $traceId
                );
                return;
            }

            foreach ($activeSecrets as $active) {
                $removeQuery = (new Query('/ppp/active/remove'))
                    ->where('name', $username)
                    ->equal('.id', $active['.id']);
                $client->query($removeQuery)->read();
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'username' => $username,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to delete active Mikrotik secret",
                $traceId
            );
            throw $e;
        }
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */
    public function getPppActive($mikrotik, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);

            if (!$client) {
                LogHelper::logError(
                    null,
                    ['mikrotik_id' => $mikrotik->id ?? null],
                    500,
                    "Failed to connect to Mikrotik",
                    $traceId
                );
                return []; // return kosong agar job tetap berjalan
            }

            $query = new Query('/ppp/active/print');
            $result = $client->query($query)->read();

            if (empty($result)) {
                LogHelper::logWarning(
                    null,
                    [],
                    404,
                    "No active PPP sessions found",
                    $traceId
                );
                return [];
            }

            LogHelper::logInfo(
                null,
                ['active_count' => count($result)],
                200,
                "Active PPP sessions retrieved successfully",
                $traceId
            );

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to fetch active Mikrotik PPP sessions",
                $traceId
            );
            return []; // jangan throw, cukup return kosong
        }
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */
    public function getOnePppSecretActive($mikrotik, $username, $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = (new Query('/ppp/active/print'))->where('name', $username);
            $result = $client->query($query)->read();

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'username' => $username,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to fetch active PPP secret by username",
                $traceId
            );
            throw $e;
        }
    }

    public function getPppSecretActiveByName($mikrotik, string $traceId)
    {
        try {
            $name = request()->username;
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = (new Query('/ppp/active/print'))->where('name', $name);
            $result = $client->query($query)->read();

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'username' => $name,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to fetch active PPP secret by name",
                $traceId
            );
            throw $e;
        }
    }

    public function getPppSecretActiveByIp($mikrotik, $ipAddress, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = (new Query('/ppp/active/print'))->where('address', $ipAddress);
            $result = $client->query($query)->read();

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'ip' => $ipAddress,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to fetch active PPP secret by IP",
                $traceId
            );
            throw $e;
        }
    }

    // PPP Profile
    public function getPppProfile($mikrotik, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/profile/print');
            $result = $client->query($query)->read();

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to fetch PPP profiles",
                $traceId
            );
            throw $e;
        }
    }

    public function getPppProfileByName($mikrotik, $profileName, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new \RuntimeException("Failed to connect to Mikrotik: {$mikrotik->host}");
            }

            $query = (new Query('/ppp/profile/print'))->where('name', $profileName);
            $result = $client->query($query)->read();

            if (empty($result)) {
                throw new \RuntimeException("PPP profile '{$profileName}' not found on Mikrotik '{$mikrotik->host}'.");
            }

            LogHelper::logInfo(
                null,
                [
                    'name' => $mikrotik->name ?? 'N/A',
                    'host' => $mikrotik->host ?? 'N/A',
                    'profile' => $profileName,
                    'result' => $result
                ],
                200,
                "Return Result getPppProfileByName",
                $traceId
            );


            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'name' => $mikrotik->name ?? 'N/A',
                    'host' => $mikrotik->host ?? 'N/A',
                    'profile' => $profileName,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to fetch PPP profile by name",
                $traceId
            );

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function updateRateLimitProfileSecret($mikrotik, $rateLimit, $profileName, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            $host = $mikrotik->host ?? 'Unknown Host';
            $name = $mikrotik->name ?? 'Unknown Name';
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $targetProfile = $this->getPppProfileByName($mikrotik, $profileName, $traceId);

            if (!is_array($targetProfile) || empty($targetProfile)) {
                LogHelper::logWarning(
                    null,
                    [
                        'host' => $mikrotik->host ?? 'Unknown Host',
                        'profile_name' => $profileName
                    ],
                    404,
                    "PPP Profile not found during rate limit update",
                    $traceId
                );
                throw new Exception("Profile '{$profileName}' not found in Mikrotik '{$name}' with host '{$host}'");
            }

            $profile = $targetProfile[0] ?? null;

            if (!$profile || !isset($profile['.id'])) {
                throw new Exception("No valid profile found or missing '.id'");
            }

            $updateQuery = (new Query('/ppp/profile/set'))
                ->equal('.id', $profile['.id'])
                ->equal('rate-limit', $rateLimit);

            $result = $client->query($updateQuery)->read();

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'profile_name' => $profileName,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to update rate limit for PPP profile",
                $traceId
            );
            throw $e;
        }
    }

    public function createPppProfile($mikrotik, array $data, string $traceId)
    {
        try {
            $client = MikrotikConnectionService::getClient($mikrotik, $traceId);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/profile/add');

            foreach ($data as $key => $value) {
                if ($value === null || $value === '') continue;
                $query->equal($key, $value);
            }

            $result = $client->query($query)->read();

            LogHelper::logInfo(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'profile_data' => $data,
                    'result' => $result,
                ],
                200,
                "Created new PPP profile",
                $traceId
            );

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'profile_data' => $data,
                    'error' => $e->getMessage()
                ],
                500,
                "Failed to create new PPP profile",
                $traceId
            );
            throw $e;
        }
    }
}
