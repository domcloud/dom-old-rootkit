<?php

class VirtualMinShell
{
    protected function execute($cmd)
    {
        $ch = curl_init($cmd);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $_SERVER['DOMAINS_AUTH']);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
    public function listDomainsInfo($domain)
    {
        $data = $this->execute($_SERVER['DOMAINS_PATH'].$domain, NULL);

        $data = explode("\n", $data);
        $result = [];
        $neskey = null;
        $nesval = [];
        foreach ($data as $line) {
            $line = rtrim($line);
            if (strlen($line) >= 4 && $line[0] === ' ') {
                $line = explode(':', ltrim($line), 2);
                $nesval[$line[0]] = ltrim($line[1]);
            } else if (strlen($line) >= 0) {
                if ($neskey) {
                    $result[$neskey] = $nesval;
                    $nesval = [];
                }
                $neskey = $line;
            } else {
                $result[$neskey] = $nesval;
                break;
            }
        }
        return $result[$domain] ?? null;
    }
}
