<?php

require "vendor/autoload.php";
require "virtualmin.php";

use Madkom\NginxConfigurator\Builder;
use Madkom\NginxConfigurator\Config\Server;
use Madkom\NginxConfigurator\Parser;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// validate request

if (!isset($_GET['secret'], $_GET['domain'])) exit;
if ($_GET['secret'] !== $_SERVER['SECRET_TOKEN']) exit;
$domain_conf = (new VirtualMinShell())->listDomainsInfo($_GET['domain']);
$nginx_file = file_get_contents($_SERVER['NGINX_PATH']);
if (!$domain_conf || !$nginx_file) {
    echo 'not found';
    exit;
}

// parse config files
$nginx_parser = new Parser();
$nginx_builder = new Builder();
$nginx_head = explode("### -----BEGIN AUTOMATED SERVER BLOCK-----\n", $nginx_file, 2);
$nginx_foot = explode("### -----END AUTOMATED SERVER BLOCK-----\n", $nginx_head[1], 2);
$nginx_body = $nginx_foot[1];
$nginx_foot = $nginx_foot[0];
$nginx_head = $nginx_head[0];
$nginx_conf = $nginx_parser->parse($nginx_body);
foreach ($nginx_conf->getIterator() as $n) {
    if ($n instanceof Server) {
        $nginx_builder->appendServerNode($n);
    }
}

// dump

echo $nginx_builder->dump();
