<?php

namespace App\Services\Routerboard\Ppp;

use Exception;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\BadCredentialsException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;
use App\Http\Resources\Pppoe\SecretActiveResource;
use App\Helpers\LogHelper;
use RouterOS\Exceptions\StreamException;

class SecretService
{
    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws BadCredentialsException
     * @throws QueryException
     * @throws ConfigException
     * @throws Exception
     */
    public function getMikrotik($mikrotik): ?Client
    {
        try {
            if (!$mikrotik) {
                throw new Exception('Mikrotik not found.');
            }

            // LogHelper::logInfo(
            //     null,
            //     [
            //         'host' => $mikrotik->host ?? 'N/A',
            //         'port' => $mikrotik->port ?? 'N/A',
            //     ],
            //     200, // Gateway Timeout
            //     "Try to connect"
            // );

            $config = (new Config())
                ->set('timeout', 2)
                ->set('host', $mikrotik->host)
                ->set('port', (int) $mikrotik->port)
                ->set('pass', $mikrotik->password)
                ->set('user', $mikrotik->username);

            // LogHelper::logInfo(
            //     null,
            //     [
            //         'host' => $mikrotik->host ?? 'N/A',
            //         'port' => $mikrotik->port ?? 'N/A',
            //         'config_ready' => true
            //     ],
            //     200,
            //     "Mikrotik config created successfully"
            // );

            return new Client($config);
        } catch (StreamException $e) { // Jika library mendukung exception spesifik
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'port' => $mikrotik->port ?? 'N/A',
                    'error' => 'Stream Timeout: ' . $e->getMessage()
                ],
                504, // Gateway Timeout
                "Failed to connect to Mikrotik due to stream timeout"
            );
            throw $e;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'port' => $mikrotik->port ?? 'N/A',
                    'error' => $e->getMessage()
                ],
                503, // Service Unavailable
                "Failed to connect to Mikrotik"
            );
            throw $e;
        }

        return null;
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */

    public function createSecret($mikrotik, $username, $password, $service, $profile, $comment)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            // Log sebelum membuat query
            LogHelper::logInfo(null, [
                'router' => $mikrotik->name ?? 'unknown',
                'username' => $username,
                'profile' => $profile,
                'service' => $service,
                'comment' => $comment
            ], 200, 'Preparing to create secret');

            $query = (new Query('/ppp/secret/add'))
                ->equal('name', $username)
                ->equal('password', $password)
                ->equal('service', $service)
                ->equal('comment', $comment)
                ->equal('profile', $profile)
                ->equal('disabled', 'false');

            $response = $client->query($query)->read();

            LogHelper::logInfo(null, [
                'router' => $mikrotik->name,
                'username' => $username,
                'profile' => $profile,
                'service' => $service,
                'comment' => $comment,
                'response' => $response
            ], 200, 'Secret created successfully');

            return $response;
        } catch (\Exception $e) {
            LogHelper::logError(null, [
                'router' => $mikrotik->name ?? 'unknown',
                'username' => $username,
                'profile' => $profile,
                'service' => $service,
                'comment' => $comment,
                'error' => $e->getMessage()
            ], 500, 'Failed to create secret');

            throw $e;
        }
    }

    public function updateSecret($mikrotik, $username, $password, $service, $profile, $id)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
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
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to update secret');
            throw $e;
        }
    }

    public function updateUserPasswordServiceSecret($mikrotik, $username, $password, $service, $id, $callerID)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
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
                return $client->query($edit)->read();
            }
        } catch (\Exception $e) {
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to update user password/service secret');
            throw $e;
        }
    }

    public function updateProfileSecret($mikrotik, $profile, $id)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
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
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to update profile secret');
            throw $e;
        }
    }

    public function addCommentSecret($mikrotik, $comment, $secretId)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
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
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to add comment to secret');
            throw $e;
        }
    }

    public function enableSecret($mikrotik, $username)
    {
        try {
            $toEnable = 'false';
            $client = $this->getMikrotik($mikrotik);
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
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to enable secret');
            throw $e;
        }
    }

    public function enableOneSecret($mikrotik, $secretId)
    {
        try {
            $toEnable = 'false';
            $client = $this->getMikrotik($mikrotik);
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
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to enable one secret');
            throw $e;
        }
    }

    public function disableSecret($mikrotik, $username)
    {
        try {
            $toNoDisable = 'true';
            $client = $this->getMikrotik($mikrotik);
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
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to disable secret');
            throw $e;
        }
    }

    public function disableOneSecret($mikrotik, $secretId)
    {
        try {
            $toNoDisable = 'true';
            $client = $this->getMikrotik($mikrotik);
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
            LogHelper::logError(null, ['error' => $e->getMessage()], 500, 'Failed to disable one secret');
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

    public function deleteSecretByName($mikrotik, $username): void
    {
        try {
            $client = $this->getMikrotik($mikrotik);
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
                    404, // Not Found
                    "Failed to delete Mikrotik secret"
                );
                return;
            }

            foreach ($deleteSecrets as $secret) {
                $deleteQuery = (new Query('/ppp/secret/remove'))
                    ->where('name', $username)
                    ->equal('.id', $secret['.id']);
                $client->query($deleteQuery)->read();

                // LogHelper::logInfo(
                //     null,
                //     [
                //         'host' => $mikrotik->host ?? 'N/A',
                //         'username' => $username,
                //         'secret_id' => $secret['.id']
                //     ],
                //     200,
                //     "Successfully deleted Mikrotik secret"
                // );
            }
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'username' => $username,
                    'error' => $e->getMessage()
                ],
                500, // Internal Server Error
                "Failed to delete Mikrotik secret"
            );
            throw $e;
        }
    }

    public function deleteOneSecret($mikrotik, $secretId): void
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $deleteQuery = (new Query('/ppp/secret/remove'))
                ->where('.id', $secretId);
            $client->query($deleteQuery)->read();

            // LogHelper::logInfo(
            //     null,
            //     [
            //         'host' => $mikrotik->host ?? 'N/A',
            //         'secret_id' => $secretId
            //     ],
            //     200,
            //     "Successfully deleted Mikrotik secret"
            // );
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                [
                    'host' => $mikrotik->host ?? 'N/A',
                    'secret_id' => $secretId,
                    'error' => $e->getMessage()
                ],
                500, // Internal Server Error
                "Failed to delete Mikrotik secret"
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

    public function getAllPppSecrets($mikrotik)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/secret/print');
            $result = $client->query($query)->read();

            // LogHelper::logInfo(
            //     null,
            //     ['host' => $mikrotik->host ?? 'N/A', 'count' => count($result)],
            //     200,
            //     "Successfully fetched all Mikrotik secrets"
            // );

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'error' => $e->getMessage()],
                500,
                "Failed to fetch all Mikrotik secrets"
            );
            throw $e;
        }
    }

    public function getSecretById($mikrotik, $id)
    {
        return $this->getSecret($mikrotik, ['.id' => $id], 'ID', $id);
    }

    public function getSecretByName($mikrotik, $username)
    {
        return $this->getSecret($mikrotik, ['name' => $username], 'Username', $username);
    }

    public function getSecretByProfile($mikrotik, $profile)
    {
        return $this->getSecret($mikrotik, ['profile' => $profile], 'Profile', $profile);
    }

    public function getSecretByNameProfile($mikrotik, $name, $profile)
    {
        return $this->getSecret($mikrotik, ['name' => $name, 'profile' => $profile], 'Name and Profile', "$name | $profile");
    }

    private function getSecret($mikrotik, $filters, $filterType, $filterValue)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
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
                    ['host' => $mikrotik->host ?? 'N/A', 'filter' => $filterValue],
                    404,
                    "No Mikrotik secrets found for $filterType"
                );
                return [];
            }

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'filter' => $filterValue, 'error' => $e->getMessage()],
                500,
                "Failed to fetch Mikrotik secrets by $filterType"
            );
            throw $e;
        }
    }

    public function deleteActiveSecret($mikrotik, $username): void
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/active/print');
            $query->where('name', $username);
            $activeSecrets = $client->query($query)->read();

            if (empty($activeSecrets)) {
                LogHelper::logWarning(
                    null,
                    ['host' => $mikrotik->host ?? 'N/A', 'username' => $username],
                    404,
                    "No active secrets found to delete"
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
                ['host' => $mikrotik->host ?? 'N/A', 'username' => $username, 'error' => $e->getMessage()],
                500,
                "Failed to delete active Mikrotik secret"
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

    public function getPppActive($mikrotik)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/active/print');
            $result = $client->query($query)->read();

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'error' => $e->getMessage()],
                500,
                "Failed to fetch active Mikrotik PPP sessions"
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

    public function getOnePppSecretActive($mikrotik, $username)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = (new Query('/ppp/active/print'))->where('name', $username);
            $result = $client->query($query)->read();

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'username' => $username, 'error' => $e->getMessage()],
                500,
                "Failed to fetch active PPP secret by username"
            );
            throw $e;
        }
    }

    public function getPppSecretActiveByName($mikrotik)
    {
        try {
            $name = request()->username;
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = (new Query('/ppp/active/print'))->where('name', $name);
            $result = $client->query($query)->read();

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'username' => $name, 'error' => $e->getMessage()],
                500,
                "Failed to fetch active PPP secret by name"
            );
            throw $e;
        }
    }

    public function getPppSecretActiveByIp($mikrotik, $ipAddress)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = (new Query('/ppp/active/print'))->where('address', $ipAddress);
            $result = $client->query($query)->read();

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'ip' => $ipAddress, 'error' => $e->getMessage()],
                500,
                "Failed to fetch active PPP secret by IP"
            );
            throw $e;
        }
    }

    // PPP Profile
    public function getPppProfile($mikrotik)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/profile/print');
            $result = $client->query($query)->read();

            return $result;
        } catch (\Exception $e) {
            LogHelper::logError(
                null,
                ['host' => $mikrotik->host ?? 'N/A', 'error' => $e->getMessage()],
                500,
                "Failed to fetch PPP profiles"
            );
            throw $e;
        }
    }

    public function getPppProfileByName($mikrotik, $profileName)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = (new Query('/ppp/profile/print'))->where('name', $profileName);
            $result = $client->query($query)->read();

            return $result ?: 'error';
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
                "Failed to fetch PPP profile by name"
            );
            return 'error';
        }
    }

    public function updateRateLimitProfileSecret($mikrotik, $rateLimit, $profileName)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            $host = $mikrotik->host ?? 'Unknown Host';
            $name = $mikrotik->name ?? 'Unknown Name';
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $targetProfile = $this->getPppProfileByName($mikrotik, $profileName);

            if (!is_array($targetProfile) || empty($targetProfile)) {
                LogHelper::logWarning(
                    null,
                    ['host' => $mikrotik->host ?? 'Unknown Host', 'profile_name' => $profileName],
                    404,
                    "PPP Profile not found during rate limit update"
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
                ['host' => $mikrotik->host ?? 'N/A', 'profile_name' => $profileName, 'error' => $e->getMessage()],
                500,
                "Failed to update rate limit for PPP profile"
            );
            throw $e;
        }
    }

    public function createPppProfile($mikrotik, array $data)
    {
        try {
            $client = $this->getMikrotik($mikrotik);
            if (!$client) {
                throw new Exception('Failed to connect to Mikrotik.');
            }

            $query = new Query('/ppp/profile/add');

            foreach ($data as $key => $value) {
                // Lewati field kosong atau null, kecuali boolean false
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
                "Created new PPP profile"
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
                "Failed to create new PPP profile"
            );
            throw $e;
        }
    }
}
