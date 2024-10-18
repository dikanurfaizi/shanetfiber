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
    public function getMikrotik($mikrotik): Client
    {
        // $mikrotik = $router;
        if (!$mikrotik) {
            throw new Exception('Mikrotik not found.');
        }

        $config = (new Config())
            ->set('timeout', 2)
            ->set('host', $mikrotik->host)
            ->set('port', (int) $mikrotik->port)
            ->set('pass', $mikrotik->password)
            ->set('user', $mikrotik->username);

        return new Client($config);
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
        $client = $this->getMikrotik($mikrotik);
        $query = (new Query('/ppp/secret/add'))
            ->equal('name', $username)
            ->equal('password', $password)
            ->equal('service', $service)
            ->equal('comment', $comment)
            ->equal('profile', $profile)
            ->equal('disabled', 'true');

        return $client->query($query)->read();
    }

    public function updateSecret($mikrotik, $username, $password, $service, $profile, $id)
    {

        $client = $this->getMikrotik($mikrotik);
        $pppsecrets = new Query('/ppp/secret/print');
        $pppsecrets->where('.id', $id);
        $editPppSecrets = $client->query($pppsecrets)->read();
        //return dd($client->query($pppsecrets)->read());
        if ($editPppSecrets == []) {
            return ('error');
        }
        foreach ($editPppSecrets as $editPppSecret) {
            $edit = (new Query('/ppp/secret/set'))
                ->where('.id', $id)
                ->equal('.id', $editPppSecret['.id'])
                ->equal('name', $username)
                ->equal('password', $password)
                ->equal('service', $service)
                ->equal('profile', $profile);
            return $client->query($edit)->read();
            // return ('success');
        }
    }

    public function updateUserPasswordServiceSecret($mikrotik, $username, $password, $service, $id, $callerID)
    {

        $client = $this->getMikrotik($mikrotik);
        $pppsecrets = new Query('/ppp/secret/print');
        $pppsecrets->where('.id', $id);
        $editPppSecrets = $client->query($pppsecrets)->read();
        //return dd($client->query($pppsecrets)->read());
        if ($editPppSecrets == []) {
            return ('error');
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
            // return ('success');
        }
    }

    public function updateProfileSecret($mikrotik, $profile, $id)
    {
        // return dd($profile);
        $client = $this->getMikrotik($mikrotik);
        $pppsecrets = new Query('/ppp/secret/print');
        $pppsecrets->where('.id', $id);
        $editPppSecrets = $client->query($pppsecrets)->read();
        foreach ($editPppSecrets as $editPppSecret) {
            $edit = (new Query('/ppp/secret/set'))
                ->where('.id', $id)
                ->equal('.id', $editPppSecret['.id'])
                ->equal('profile', $profile);
            return $client->query($edit)->read();
        }
    }

    public function addCommentSecret($mikrotik, $comment, $secretId)
    {
        //return dd($comment);
        $client = $this->getMikrotik($mikrotik);
        $pppsecrets = new Query('/ppp/secret/print');
        $pppsecrets->where('.id', $secretId);
        $editPppSecrets = $client->query($pppsecrets)->read();
        foreach ($editPppSecrets as $editPppSecret) {
            $edit = (new Query('/ppp/secret/set'))
                ->where('.id', $secretId)
                ->equal('.id', $editPppSecret['.id'])
                ->equal('comment', $comment);
            return $client->query($edit)->read();
            //$response = $client->query($edit)->read();
            //return dd('response');
        }
    }
    /*
    public function addPaymentSecret($mikrotik, $profile, $comment, $secretId)
    {
        // return dd($profile);
        $client = $this->getMikrotik($mikrotik);
        $pppsecrets = new Query('/ppp/secret/print');
        $pppsecrets->where('.id', $secretId);
        $editPppSecrets = $client->query($pppsecrets)->read();
        foreach ($editPppSecrets as $editPppSecret) {
            $edit = (new Query('/ppp/secret/set'))
                ->where('.id', $secretId)
                ->equal('.id', $editPppSecret['.id'])
                ->equal('profile', $profile)
                ->equal('comment', $comment);
            return $client->query($edit)->read();
            //$response = $client->query($edit)->read();
            //return dd('response');
        }
    }
*/

    public function enableSecret($mikrotik, $username)
    {
        $toEnable = 'false';
        $client = $this->getMikrotik($mikrotik);
        $pppsecrets = new Query('/ppp/secret/print');
        $pppsecrets->where('name', $username);
        //return dd($pppsecrets);
        $enablePppSecrets = $client->query($pppsecrets)->read();
        if ($enablePppSecrets == []) {
            return ($response = 'error');
        }

        foreach ($enablePppSecrets as $enablePppSecret) {
            $enable = (new Query('/ppp/secret/set'))
                ->where('name', $username)
                ->equal('.id', $enablePppSecret['.id'])
                ->equal('disabled', $toEnable);
            $client->query($enable)->read();
            return ($response = 'success');
        }
    }

    public function enableOneSecret($mikrotik, $secretId): void
    {
        $toEnable = 'false';
        $client = $this->getMikrotik($mikrotik);
        $pppsecrets = new Query('/ppp/secret/print');
        $pppsecrets->where('.id', $secretId);
        //return dd($pppsecrets);
        $enablePppSecrets = $client->query($pppsecrets)->read();

        foreach ($enablePppSecrets as $enablePppSecret) {
            $enable = (new Query('/ppp/secret/set'))
                ->where('.id', $secretId)
                ->equal('.id', $enablePppSecret['.id'])
                ->equal('disabled', $toEnable);
            $client->query($enable)->read();
        }
    }

    public function disableSecret($mikrotik, $username)
    {
        $toNoDisable = 'true';
        $client = $this->getMikrotik($mikrotik);
        $pppsecrets = new Query('/ppp/secret/print');
        $pppsecrets->where('name', $username);
        $disablePppSecrets = $client->query($pppsecrets)->read();
        if ($disablePppSecrets == []) {
            return ($response = 'error');
        }
        // return dd($disablePppSecrets);
        foreach ($disablePppSecrets as $disablePppSecret) {
            $dsbl = (new Query('/ppp/secret/set'))
                ->where('name', $username)
                ->equal('.id', $disablePppSecret['.id'])
                ->equal('disabled', $toNoDisable);
            $client->query($dsbl)->read();
            return ($response = 'success');
        }
    }

    public function disableOneSecret($mikrotik, $secretId): void
    {
        $toNoDisable = 'true';
        $client = $this->getMikrotik($mikrotik);
        $pppsecrets = new Query('/ppp/secret/print');
        $pppsecrets->where('.id', $secretId);
        $disablePppSecrets = $client->query($pppsecrets)->read();

        foreach ($disablePppSecrets as $disablePppSecret) {
            $dsbl = (new Query('/ppp/secret/set'))
                ->where('.id', $secretId)
                ->equal('.id', $disablePppSecret['.id'])
                ->equal('disabled', $toNoDisable);
            $client->query($dsbl)->read();
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
        $client = $this->getMikrotik($mikrotik);
        $pppsecret = new Query('/ppp/secret/print');
        $pppsecret->where('name', $username);
        $deleteSecrets = $client->query($pppsecret)->read();

        foreach ($deleteSecrets as $secret) {
            $secret = (new Query('/ppp/secret/remove'))
                ->where('name', $username)
                ->equal('.id', $secret['.id']);
            $client->query($secret)->read();
        }
    }

    public function deleteOneSecret($mikrotik, $secretId)
    {
        $client = $this->getMikrotik($mikrotik);
        $secret = (new Query('/ppp/secret/remove'))
            ->where('.id', $secretId)
            ->equal('.id', $secretId);
        $client->query($secret)->read();
    }
    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */


    //public function getPppSecrests($router)
    public function getAllPppSecrets($mikrotik)
    {
        $client = $this->getMikrotik($mikrotik);
        $query = new Query('/ppp/secret/print');

        return $client->query($query)->read();
    }



    public function getSecretById($mikrotik, $id)
    {
        $client = $this->getMikrotik($mikrotik);
        $pppsecrets = new Query('/ppp/secret/print');
        $pppsecrets->where('.id', $id);
        $getPppSecrets = $client->query($pppsecrets)->read();
        if ($getPppSecrets == []) {
            return ($response = 'error');
        } else {
            return $getPppSecrets;
        }
        // return dd($disablePppSecrets);
        // foreach ($getPppSecrets as $getPppSecret) {
        //      $secret = (new Query('/ppp/secret/print'))
        //          ->where('.id', $getPppSecret['.id']);
        //      return $client->query($secret)->read();
        //  }


        // $client = $this->getMikrotik($mikrotik);
        //  $query = (new Query('/ppp/secret/print'))
        //      ->where('.id', $id);
        //  return $client->query($query)->read();
    }

    public function getSecretByName($mikrotik, $username)
    {
        $client = $this->getMikrotik($mikrotik);
        // $query = new Query('/ppp/secret/print');
        $pppsecrets = (new Query('/ppp/secret/print'))
            ->where('name', $username);
        $getPppSecrets = $client->query($pppsecrets)->read();
        if ($getPppSecrets == []) {
            return ($response = 'error');
        } else {
            return $getPppSecrets;
        }
    }

    public function getSecretByProfile($mikrotik, $profile)
    {
        $client = $this->getMikrotik($mikrotik);
        $pppsecrets = new Query('/ppp/secret/print');
        $pppsecrets->where('profile', $profile);
        $getPppSecrets = $client->query($pppsecrets)->read();
        if ($getPppSecrets == []) {
            return ($response = 'error');
        } else {
            return $getPppSecrets;
        }
    }

    public function getSecretByNameProfile($mikrotik, $name, $profile)
    {
        $client = $this->getMikrotik($mikrotik);
        $pppsecrets = new Query('/ppp/secret/print');
        $pppsecrets->where('name', $name)->where('profile', $profile);
        $getPppSecrets = $client->query($pppsecrets)->read();
        if ($getPppSecrets == []) {
            return ($response = 'error');
        } else {
            return $getPppSecrets;
        }
    }

    public function deleteActiveSecret($mikrotik, $username): void
    {
        $client = $this->getMikrotik($mikrotik);
        $pppsecret = new Query('/ppp/active/print');
        $pppsecret->where('name', $username);
        $activeSecrets = $client->query($pppsecret)->read();
        foreach ($activeSecrets as $actv) {
            $remove = (new Query('/ppp/active/remove'))
                ->where('name', $username)
                ->equal('.id', $actv['.id']);
            $client->query($remove)->read();
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
        $client = $this->getMikrotik($mikrotik);
        $query = new Query('/ppp/active/print');

        return $client->query($query)->read();
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
        $client = $this->getMikrotik($mikrotik);
        $query = (new Query('/ppp/active/print'))
            ->where('name', $username);
        return $client->query($query)->read();
        // $response = $client->query($query)->read();
        // return dd($response);
    }
    public function getPppSecretActiveByName($mikrotik)
    {
        $name = request()->username;
        $client = $this->getMikrotik($mikrotik);
        $query = (new Query('/ppp/active/print'))
            ->where('name', $name);
        return $client->query($query)->read();
        // $result = $client->query($query)->read();
        //  $pppActive = SecretActiveResource::collection($result);

        // return response()->json($pppActive);
        // $response = $client->query($query)->read();
        // return dd($response);
    }


    public function getPppSecretActiveByIp($mikrotik, $ipAddress)
    {
        $client = $this->getMikrotik($mikrotik);
        $query = (new Query('/ppp/active/print'))
            ->where('address', $ipAddress);
        return $client->query($query)->read();
        // $response = $client->query($query)->read();
        // return dd($response);
    }




    //PPP Profile 
    public function getPppProfile($mikrotik)
    {
        $client = $this->getMikrotik($mikrotik);
        $query = new Query('/ppp/profile/print');

        return $client->query($query)->read();
    }


    public function getPppProfileByName($mikrotik, $profileName)
    {
        $client = $this->getMikrotik($mikrotik);
        // $query = new Query('/ppp/secret/print');
        $pppsecrets = (new Query('/ppp/profile/print'))
            ->where('name', $profileName);
        $getPppProfiles = $client->query($pppsecrets)->read();
        if ($getPppProfiles == []) {
            return ($response = 'error');
        } else {
            return $getPppProfiles;
        }
    }

    public function updateRateLimitProfileSecret($mikrotik, $rateLimit, $id)
    {
        // return dd($rateLimit);
        $client = $this->getMikrotik($mikrotik);
        $pppProfile = (new Query('/ppp/profile/print'))
            ->where('.id', $id);
        $pppProfiles = $client->query($pppProfile)->read();
        foreach ($pppProfiles as $pppProfile) {
            $edit = (new Query('/ppp/profile/set'))
                ->where('.id', $id)
                ->equal('.id', $pppProfile['.id'])
                ->equal('rate-limit', $rateLimit);
            return $client->query($edit)->read();
        }
    }

    public function createPppProfile($mikrotik, $name, $comment)
    {
        $client = $this->getMikrotik($mikrotik);
        $query = (new Query('/ppp/profile/add'))
            ->equal('name', $name)
            ->equal('comment', $comment);
        return $client->query($query)->read();
    }
}
