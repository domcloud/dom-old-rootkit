<?php

require "vendor/autoload.php";
require "validator.php";

use Madkom\NginxConfigurator\Builder;
use Madkom\NginxConfigurator\Config\Location;
use Madkom\NginxConfigurator\Config\Server;
use Madkom\NginxConfigurator\Node\Directive;
use Madkom\NginxConfigurator\Node\Node;
use Madkom\NginxConfigurator\Node\Param;
use Madkom\NginxConfigurator\Parser;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// validate request

if (!isset($_GET['secret'], $_GET['domain'])) exit;
if ($_GET['secret'] !== $_SERVER['SECRET_TOKEN']) exit;
$nginx_file = file_get_contents($_SERVER['NGINX_PATH']);
if (!$nginx_file) {
    die('ERROR: config not found');
}

/**
 * @param Node $server
 * @return Location[]
 * */
function extractLocations($server)
{
    return iterator_to_array($server->search(function ($x) {
        return $x instanceof Location;
    }));
}

/**
 * @param Node $server
 * @return Directive[]
 * */
function extractDirective($server, $directive)
{
    return iterator_to_array($server->search(function ($x) use ($directive) {
        return $x instanceof Directive && $x->getName() === $directive;
    }));
}
/**
 * @param Directive $directive
 * @return mixed[]
 * */
function extractParameters($directive)
{
    return array_map(function ($x) {
        return $x instanceof Param ? $x->getValue() : null;
    }, iterator_to_array($directive->getParams()));
}


// parse config files
$found = false;
$target = $_GET['domain'];
$nginx_builder = new Builder();
$nginx_head = explode("server {", $nginx_file, 2);
$nginx_body = "server {" . trim($nginx_head[1]);
$nginx_body = substr($nginx_body, 0, -1);
$nginx_foot = "\n}";
$nginx_head = $nginx_head[0];
$nginx_conf = (new Parser())->parse(str_replace("\t", '', $nginx_body));
foreach ($nginx_conf->getIterator() as $n) {
    if ($n instanceof Server) {
        $dir = $n->search(function ($node) use ($target) {
            if ($node instanceof Directive && $node->getName() === 'server_name') {
                foreach ($node->getParams() as $p) {
                    if ($p instanceof Param && $p->getValue() === $target) {
                        return true;
                    }
                }
            }
            return false;
        });
        if ($dir->count()) {
            // is this GET or POST?
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $nginx_builder = new Builder();
                $nginx_builder->appendServerNode($n);
                echo $nginx_builder->dump();
            }
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return;
            }
            // extract data
            $d['dom'] = $target;
            foreach (extractDirective($n, 'listen') as $ips) {
                $ip = str_replace(':443', '', extractParameters($ips)[0]);
                $d[$ip[0] === '[' ? 'ip6' : 'ip'] = $ip;
            }
            $d['root'] = extractParameters(extractDirective($n, 'root')[0])[0];
            $d['index'] = extractParameters(extractDirective($n, 'index')[0])[0];
            $d['access_log'] = extractParameters(extractDirective($n, 'access_log')[0])[0];
            $d['error_log'] = extractParameters(extractDirective($n, 'error_log')[0])[0];
            $ssl = extractDirective($n, 'ssl_certificate');
            if (count($ssl)) {
                $d['ssl']['cert'] = extractParameters($ssl[0])[0];
                $d['ssl']['key'] = extractParameters(extractDirective($n, 'ssl_certificate_key')[0])[0];
            }
            // find the location who hass fcgi
            foreach (extractLocations($n) as $l) {
                if (count($fcgi = extractDirective($l, 'fastcgi_pass'))) {
                    $d['fcgi'] = extractParameters($fcgi[0])[0];
                    break;
                }
            }
            // process config
            $c = mergeConfig(file_get_contents('php://input'));
            $c['locations'][] = [
                'match' => '~ \.php(/|$)',
                'try_files' => '$uri =404',
                'fastcgi_pass' => $d['fcgi'],
            ];
            // all necessary data in, now cut
            ob_start();
            include "template.php";
            $file = str_replace("\t", "", ob_get_clean());
            $nginx_builder->appendServerNode(iterator_to_array((new Parser())->parse($file)->getIterator())[0]);
            $found = true;
        } else {
            $nginx_builder->appendServerNode($n);
        }
    }
}

if (!$found) {
    die('ERROR: domain not found');
}
// dump
$nginx_body = str_replace("\n", "\n\t", trim($nginx_builder->dump()));
$nginx_new = $nginx_head . $nginx_body . $nginx_foot;

if (($_GET['preview'] ?? '') === 'all') {
    die($nginx_new);
}
if (file_put_contents($_SERVER['NGINX_PATH'], $nginx_new, LOCK_EX) === false) {
    die('ERROR: can\'t write to config');
}

// validate
$validation = shell_exec($_SERVER['NGINX_TEST']);
if (strpos($validation, 'test is successful') !== false) {
    // restart
    exec($_SERVER['NGINX_RELOAD']);
    echo 'OK';
} else {
    // oops. fallback.
    file_put_contents($_SERVER['NGINX_PATH'], $nginx_file, LOCK_EX);
    echo "\n\nError: YOUR NGINX CONFIGURATION IS INVALID! IT HAS BEEN ROLLED BACK.\n";
    echo $validation;
}
