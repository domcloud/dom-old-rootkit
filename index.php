<?php

require "vendor/autoload.php";

use Madkom\NginxConfigurator\Builder;
use Madkom\NginxConfigurator\Config\Server;
use Madkom\NginxConfigurator\Parser;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// validate request

if (!isset($_GET['secret'], $_GET['domain'])) exit;
if ($_GET['secret'] !== $_SERVER['SECRET_TOKEN']) exit;
$domain_file = file_get_contents($_SERVER['DOMAINS_PATH'] . intval($_GET['domain']));
$nginx_file = file_get_contents($_SERVER['NGINX_PATH']);
if (!$domain_file || !$nginx_file) exit;

// parse config files

$domain_conf = array_map(function ($x) {
    return explode('=', $x, 2);
}, explode('\n', $domain_file));
$domain_conf = array_column($domain_conf, 1, 0);
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
