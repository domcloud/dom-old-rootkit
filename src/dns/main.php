<?php

// validate request

if (!isset($_GET['secret'], $_GET['action'])) exit;
if ($_GET['secret'] !== $_SERVER['SECRET_TOKEN']) exit;
if ($_GET['action'] === 'refresh') {
    exec($_SERVER['DNS_RELOAD']);
    echo "DNS Updated\n";
} else if ($_GET['action'] === 'check') {
    $dns_file = file_get_contents($_SERVER['DNS_PATH']);
    if (!$dns_file) {
        die("ERROR: config not found\n");
    }
    $record = str_contains($_GET['value'], ':') ? 'AAAA' : 'A';
    $theword = "$_GET[host].domcloud.io.\tIN\t$record\t$_GET[value]\n";
    echo str_contains($dns_file, $theword) ? '1' : '0';
    die();
} else if ($_GET['action'] === 'add') {
    $dns_file = file_get_contents($_SERVER['DNS_PATH']);
    if (!$dns_file) {
        die("ERROR: config not found\n");
    }

    $record = str_contains($_GET['value'], ':') ? 'AAAA' : 'A';
    $theword = "$_GET[host].domcloud.io.\tIN\t$record\t$_GET[value]\n";

    $replaced_file = str_replace($theword, "", $dns_file);
    $replaced_file = $replaced_file . $theword;

    if ($dns_file === $replaced_file) {
        die("Updated, nothing changed\n");
    }
    preg_match('/(IN\tSOA.+?)(\d+)/s', $replaced_file, $matches);
    $replaced_file = preg_replace('/(IN\tSOA.+?)(\d+)/s', '$1'.(intval($matches[2]) + 1), $replaced_file, 1);
    if (!$replaced_file || file_put_contents($_SERVER['DNS_PATH'], $replaced_file, LOCK_EX) === false) {
        die("ERROR: unable to write config\n");
    }
    exec($_SERVER['DNS_RELOAD']);
    echo "Updated for $record Record\n";
} else if ($_GET['action'] === 'del') {
    $dns_file = file_get_contents($_SERVER['DNS_PATH']);
    if (!$dns_file) {
        die("ERROR: config not found\n");
    }

    $record = str_contains($_GET['value'], ':') ? 'AAAA' : 'A';
    $theword = "$_GET[host].domcloud.io.\tIN\t$record\t$_GET[value]\n";

    $replaced_file = str_replace($theword, "", $dns_file);

    if ($dns_file === $replaced_file) {
        die("Updated, Nothing changed\n");
    }
    preg_match('/(IN\tSOA.+?)(\d+)/s', $replaced_file, $matches);
    $replaced_file = preg_replace('/(IN\tSOA.+?)(\d+)/s', '$1'.(intval($matches[2]) + 1), $replaced_file, 1);
    if (!$replaced_file || file_put_contents($_SERVER['DNS_PATH'], $replaced_file, LOCK_EX) === false) {
        die("ERROR: unable to write config\n");
    }
    exec($_SERVER['DNS_RELOAD']);
    echo "Updated for $record Record\n";
}
