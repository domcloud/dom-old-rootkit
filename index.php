<?php

require_once "vendor/autoload.php";
require "validator.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// validate request

if (!isset($_GET['secret'], $_GET['domain'])) exit;
if ($_GET['secret'] !== $_SERVER['SECRET_TOKEN']) exit;
$nginx_file = file_get_contents($_SERVER['NGINX_PATH']);
if (!$nginx_file) {
    die('ERROR: config not found');
}

$found = false;
$target = $_GET['domain'];

// normalize config files
$nginx_file = str_replace("\r", "", $nginx_file);
$nginx_file = str_replace('    ', "\t", $nginx_file);
$matches = [];
if (!preg_match('/\n	server {\n\t\tserver_name ' . str_replace('.', '\.', $target) . '.+?\n\t}/s', $nginx_file, $matches)) {
    die('ERROR: domain not found');
}
$serv = $matches[0];
$serv_substart = strpos($nginx_file, $serv);
$serv_subend = $serv_substart + strlen($serv);

// is this GET or POST?
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo preg_replace('/^\t/m', "", substr($serv, 1));
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

// extract data
$d['dom'] = $target;
$matches = [];
if (preg_match_all('/^\t\tlisten (.+?)( ssl)?( http2)?;/m', $serv, $matches) === false) {
    die('ERROR: No "listen" was detected');
}
foreach ($matches[1] as $ips) {
    $ip = str_replace(':443', '', $ips);
    $d[$ip[0] === '[' ? 'ip6' : 'ip'] = $ip;
}
foreach (['root', 'index', 'access_log', 'error_log'] as $variable) {
    $matches = [];
    if (preg_match('/^\t\t' . $variable . ' (.+);/m', $serv, $matches) === false) {
        die("ERROR: No '$variable' was detected");
    }
    $d[$variable] = $matches[1];
}
$d['user'] = explode('/', $d['root'])[2];
// extract location
$matches = [];
if (preg_match('/^\t\t\tfastcgi_pass (.+);/m', $serv, $matches) === false) {
    die("ERROR: No 'fastcgi_pass' was detected");
}
$d['fcgi'] = $matches[1];
$c = mergeConfig($_POST['data'] ?? file_get_contents('php://input'));
$c['locations'][] = [
    'match' => '~ \.php(/|$)',
    'try_files' => '$uri =404',
    'fastcgi_pass' => $d['fcgi'],
];
// all necessary data in, now cut
ob_start();
include "template.php";
$serv = ob_get_clean();


// dump
$nginx_new = substr($nginx_file, 0, $serv_substart) . $serv . substr($nginx_file, $serv_subend);

if (($_GET['preview'] ?? '') === 'all') {
    die($nginx_new);
}
if (file_put_contents($_SERVER['NGINX_PATH'], $nginx_new, LOCK_EX) === false) {
    die('ERROR: can\'t write to config');
}

// validate
exec($_SERVER['NGINX_TEST'], $output, $status);
if ($status === 0) {
    // restart
    exec($_SERVER['NGINX_RELOAD']);
    echo 'Config applied successfully';
} else {
    // oops. fallback.
    file_put_contents($_SERVER['NGINX_PATH'], $nginx_file, LOCK_EX);
    echo implode("\n", $output ?: []);
    echo "\n\nError: YOUR NGINX CONFIGURATION IS INVALID! IT HAS BEEN ROLLED BACK.\n";
}
