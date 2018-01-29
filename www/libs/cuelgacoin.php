<?php
class Cuelgacoin
{
    private $host;
    private $user;
    private $pass;

    public function __construct($host, $user, $pass)
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
    }

    public function __destruct()
    {
        unset($this->host);
        unset($this->user);
        unset($this->pass);
    }

    private function request($method, $params = null)
    {
        $headers = array(
            'content-type: text/plain'
        );
        $data = new stdClass();
        $data->jsonrpc = "1.0";
        $data->id = "cuelgatest";
        $data->method = strtolower($method);
        $data->params = $params;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->host);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERPWD, $this->user . ":" . $this->pass);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $res = curl_exec($ch);
        return $res;
    }

    public function getInfo()
    {
        return json_decode($this->request('getInfo'));
    }

    public function getAccountAddress($account = "")
    {
        return json_decode($this->request('getAccountAddress', array("account" => $account)));
    }

    public function listAddressGroupings()
    {
        return json_decode($this->request('listAddressGroupings'));
    }

    public function getBalance($account = "")
    {
        return json_decode($this->request('getBalance', array("account" => $account)));
    }

    public function sendFrom($fromaccount, $toaddress, $amount)
    {
        return json_decode($this->request('sendFrom', array("fromaccount" => $fromaccount, "toaddress" => $toaddress, "amount" => $amount)));
    }

    public function validateAddress($address)
    {
        return json_decode($this->request('validateAddress', array("address" => $address)));
    }
}
