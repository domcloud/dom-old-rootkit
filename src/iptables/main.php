<?php

require_once "../vendor/autoload.php";
require "validator.php";

$dotenv = Dotenv\Dotenv::createImmutable(realpath(__DIR__ . '/../../'));
$dotenv->load();

// validate request

if (!isset($_GET['secret'], $_GET['action'])) exit;
if ($_GET['secret'] !== $_SERVER['SECRET_TOKEN']) exit;
if ($_GET['action'] === 'refresh') {
    exec($_SERVER['IPTABLES_REFRESH']);
    echo "Updated";
} else if ($_GET['action'] === 'add_user') {
    $iptables_file = file_get_contents($_SERVER['IPTABLES_PATH']);
    if (!$iptables_file) {
        die('ERROR: config not found');
    }
    $replaced_file = str_replace("# Limiter goes down here\n", "# Limiter goes down here\n"."-A OUTPUT -m owner --uid-owner $_SERVER[user] -j REJECT\n", $iptables_file, 1);
    if ($iptables_file === $replaced_file) {
        die('ERROR: can\'t find insert table point');
    }
    if (file_put_contents($_SERVER['IPTABLES_PATH'], $replaced_file, LOCK_EX) === false) {
        die('ERROR: unable to write config');
    }
    exec($_SERVER['IPTABLES_RELOAD']);
    echo "Updated";
} else if ($_GET['action'] === 'del_user') {
    $iptables_file = file_get_contents($_SERVER['IPTABLES_PATH']);
    if (!$iptables_file) {
        die('ERROR: config not found');
    }
    $replaced_file = str_replace("-A OUTPUT -m owner --uid-owner $_SERVER[user] -j REJECT\n", "", $iptables_file, 1);
    if ($iptables_file === $replaced_file) {
        die('ERROR: can\'t remove table point');
    }
    if (file_put_contents($_SERVER['IPTABLES_PATH'], $replaced_file, LOCK_EX) === false) {
        die('ERROR: unable to write config');
    }
    exec($_SERVER['IPTABLES_RELOAD']);
    echo "Updated";
}
