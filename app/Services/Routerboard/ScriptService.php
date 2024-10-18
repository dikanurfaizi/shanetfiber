<?php

namespace App\Services\Routerboard;

use App\Http\Resources\TrafficInterfaceResource;
use App\Models\Mikrotik;
use Exception;
use Illuminate\Http\JsonResponse;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\BadCredentialsException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;

class ScriptService
{
    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     * @throws Exception
     */
    public function getMikrotik($routerboard): Client
    {
        $mikrotik = $routerboard;
        if (!$mikrotik) {
            throw new Exception('Mikrotik not found.');
        }

        $config = (new Config())
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
    public function getAllScript($routerboard)
    {
        // $router = $routerboard;
        $client = $this->getMikrotik($routerboard);
        // $inf = new Query('/interface/print');


        $query = new Query('/system/script/print');

        return $client->query($query)->read();

        //return value
        /*
        0 => array:8 [▼
            ".id" => "*1"
            "name" => "test"
            "owner" => "admin"
            "policy" => "ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon"
            "dont-require-permissions" => "false"
            "run-count" => "0"
            "source" => ":local"
            "invalid" => "false"
        ]

        */
    }

    public function getScriptByName($routerboard, $scheduleName)
    {

        $client = $this->getMikrotik($routerboard);
        $query = (new Query('/system/script/print'))
            ->where('name', $scheduleName);

        return $client->query($query)->read();
    }

    public function getScriptById($routerboard, $id)
    {
        // $router = $routerboard;
        $client = $this->getMikrotik($routerboard);
        $query = (new Query('/system/script/print'))
            ->where('.id', $id);

        return $client->query($query)->read();

        //return value
        /*
        0 => array:8 [▼
            ".id" => "*1"
            "name" => "test"
            "owner" => "admin"
            "policy" => "ftp,reboot,read,write,policy,test,password,sniff,sensitive,romon"
            "dont-require-permissions" => "false"
            "run-count" => "0"
            "source" => ":local"
            "invalid" => "false"
        ]

        */
    }

    /**
     * @throws ClientException
     * @throws ConnectException
     * @throws QueryException
     * @throws BadCredentialsException
     * @throws ConfigException
     */
    public function addScript($routerboard, $nameScript, $sourceScript)
    {
        // $router = $routerboard;
        $client = $this->getMikrotik($routerboard);
        // $inf = new Query('/interface/print');


        $query =
            (new Query('/system/script/add'))
            ->equal('name', $nameScript)
            ->equal('source', $sourceScript)
            ->equal('comment', 'create_by_customer_management');

        return $client->query($query)->read();

        //return value
        /*
        "after" => array:1 [▼
            "ret" => "*2"
        ]
        */
    }

    public function addAutoisolirDueDate($routerboard, $rosVersion, $nameScript, $dueDate, $unpayment, $profile)
    {
        if ($rosVersion == '1') {
            $sourceScript = ':local day [:pick [/system clock get date] 4 6]
            :if (day >= "' . $dueDate . '") do={
            :local userppp
            :foreach v in=[/ppp secret find where comment="' . $unpayment . '"] do={
            :set userppp ( userppp [/ppp secret get $v name])
            /ppp active remove [find name=$userppp]
            /ppp secret set profile=' . $profile . ' [find name=$userppp];
            }
            }';
        } else {
            $sourceScript = ':local day [:pick [/system clock get date] 8 10]
            :if (day >= "' . $dueDate . '") do={
            :local userppp
            :foreach v in=[/ppp/secret find comment="' . $unpayment . '"] do={
            :set userppp ( userppp [/ppp secret get $v name])
            /ppp active remove [find name=$userppp]
            /ppp secret set profile=' . $profile . ' [find name=$userppp];
            }
            }';
        }

        $client = $this->getMikrotik($routerboard);
        $query =
            (new Query('/system/script/add'))
            ->equal('name', $nameScript)
            ->equal('source', $sourceScript)
            ->equal('comment', 'create_by_customer_management');

        return $client->query($query)->read();
    }

    public function addAutoisolirActivationDate($routerboard, $rosVersion, $nameScript, $unpayment, $profile)
    {

        if ($rosVersion == '1') {
            //ROS Versi < 7.10
            $sourceScript = '
            :local date [/system clock get date]
            :local yyyy  [:pick $date 7 11]
           :local MM ([:find "janfebmaraprmayjunjulaugsepoctnovdec" [:pick $date 0 3] ] / 3 + 1)
           :if ($MM < 10) do={:set MM "0$MM"}
           :local dd    [:pick $date 4 6]
           
            :for e from 1 to $dd do={
            :if ($e < 10) do={:set e "0$e"}
            :local duedate    "belum_lunas_$e_$MM_$yyyy"
            :local userppp
            :foreach v in=[/ppp secret find where comment=$duedate] do={
            :set userppp ( userppp [/ppp secret get $v name])
            /ppp active remove [find name=$userppp]
            /ppp secret set profile=profile_isolir [find name=$userppp];
            }}';
        } else {
            //ROS Versi 7.10+
            $sourceScript = '
            :local date [/system clock get date]
            :local yyyy  [:pick $date 0 4]
            :local MM    [:pick $date 5 7]
            :local dd    [:pick $date 8 10]
            
            :for e from 1 to $dd do={
            :if ($e < 10) do={:set e "0$e"}
            :local duedate    "' . $unpayment . '_$e_$MM_$yyyy"
            :local userppp
            :foreach v in=[/ppp/secret find comment="$duedate"] do={
            :set userppp ( userppp [/ppp secret get $v name])
            /ppp active remove [find name=$userppp]
            /ppp secret set profile=' . $profile . ' [find name=$userppp];
            }}';
        }



        $client = $this->getMikrotik($routerboard);
        $query =
            (new Query('/system/script/add'))
            ->equal('name', $nameScript)
            ->equal('source', $sourceScript)
            ->equal('comment', 'create_by_customer_management');

        return $client->query($query)->read();
    }



    public function setSourceScript($routerboard, $scriptId, $sourceScript)
    {

        $client = $this->getMikrotik($routerboard);
        $scripts = new Query('/system/script/print');
        $scripts->where('.id', $scriptId);
        $resultScripts = $client->query($scripts)->read();

        foreach ($resultScripts as $script) {
            $client = $this->getMikrotik($routerboard);
            $query = (new Query('/system/script/set'))
                ->where('.id', $scriptId)
                ->equal('.id', $script['.id'])
                ->equal('source', $sourceScript);

            return $client->query($query)->read();
        }
    }

    public function setAutoisolirDueDate($routerboard, $rosVersion, $scriptId, $dueDate, $unpayment, $profile)
    {
        if ($rosVersion == '1') {
            //  $day = '4 6';
            $sourceScript = ':local day [:pick [/system clock get date] 4 6]
        :if (day >= "' . $dueDate . '") do={
        :local userppp
        :foreach v in=[/ppp secret find where comment="' . $unpayment . '"] do={
        :set userppp ( userppp [/ppp secret get $v name])
        /ppp active remove [find name=$userppp]
        /ppp secret set profile=' . $profile . ' [find name=$userppp];
        }
        }';
        } else {
            //$day = '8 10';
            $sourceScript = ':local day [:pick [/system clock get date] 8 10]
            :if (day >= "' . $dueDate . '") do={
            :local userppp
            :foreach v in=[/ppp/secret find comment="' . $unpayment . '"] do={
            :set userppp ( userppp [/ppp secret get $v name])
            /ppp active remove [find name=$userppp]
            /ppp secret set profile=' . $profile . ' [find name=$userppp];
            }
            }';
        }



        $client = $this->getMikrotik($routerboard);
        $scripts = new Query('/system/script/print');
        $scripts->where('.id', $scriptId);
        $resultScripts = $client->query($scripts)->read();

        foreach ($resultScripts as $script) {
            $client = $this->getMikrotik($routerboard);
            $query = (new Query('/system/script/set'))
                ->where('.id', $scriptId)
                ->equal('.id', $script['.id'])
                ->equal('source', $sourceScript);

            return $client->query($query)->read();
        }
    }

    public function setAutoisolirActivationDate($routerboard, $rosVersion, $scriptId, $unpayment, $profile)
    {
        if ($rosVersion == '1') {
            //ROS Versi < 7.10
            $sourceScript = '
            :local date [/system clock get date]
            :local yyyy  [:pick $date 7 11]
           :local MM ([:find "janfebmaraprmayjunjulaugsepoctnovdec" [:pick $date 0 3] ] / 3 + 1)
           :if ($MM < 10) do={:set MM "0$MM"}
           :local dd    [:pick $date 4 6]
           
            :for e from 1 to $dd do={
            :if ($e < 10) do={:set e "0$e"}
            :local duedate    "belum_lunas_$e_$MM_$yyyy"
            :local userppp
            :foreach v in=[/ppp secret find where comment=$duedate] do={
            :set userppp ( userppp [/ppp secret get $v name])
            /ppp active remove [find name=$userppp]
            /ppp secret set profile=profile_isolir [find name=$userppp];
            }}';
        } else {
            //ROS Versi 7.10+
            $sourceScript = '
            :local date [/system clock get date]
            :local yyyy  [:pick $date 0 4]
            :local MM    [:pick $date 5 7]
            :local dd    [:pick $date 8 10]
            
            :for e from 1 to $dd do={
            :if ($e < 10) do={:set e "0$e"}
            :local duedate    "' . $unpayment . '_$e_$MM_$yyyy"
            :local userppp
            foreach v in=[/ppp/secret find comment="$duedate"] do={
            :set userppp ( userppp [/ppp secret get $v name])
            /ppp active remove [find name=$userppp]
            /ppp secret set profile=' . $profile . ' [find name=$userppp];
            }}';
        }

        $client = $this->getMikrotik($routerboard);
        $scripts = new Query('/system/script/print');
        $scripts->where('.id', $scriptId);
        $resultScripts = $client->query($scripts)->read();

        foreach ($resultScripts as $script) {
            $client = $this->getMikrotik($routerboard);
            $query = (new Query('/system/script/set'))
                ->where('.id', $scriptId)
                ->equal('.id', $script['.id'])
                ->equal('source', $sourceScript);

            return $client->query($query)->read();
        }
    }

    public function removeScript($routerboard, $scriptId)
    {

        $client = $this->getMikrotik($routerboard);
        $scripts = new Query('/system/script/print');
        $scripts->where('.id', $scriptId);
        $resultScripts = $client->query($scripts)->read();

        foreach ($resultScripts as $script) {
            $client = $this->getMikrotik($routerboard);
            $query = (new Query('/system/script/remove'))
                ->where('.id', $scriptId)
                ->equal('.id', $script['.id']);

            return $client->query($query)->read();
        }
    }
}
