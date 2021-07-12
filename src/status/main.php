<?php

if (!isset($_GET['secret'])) exit;
if ($_GET['secret'] !== $_SERVER['SECRET_TOKEN']) exit;

$services = ['iptables', 'ip6tables', 'mariadb', 'named', 'nginx', 'php-fpm', 'postgresql', 'proftpd', 'sshd', 'webmin'];
$services_ans = [];
foreach ($services as $s) {
    $services_ans[$s] = shell_exec("systemctl is-active $s");
}
header('content-type: application/json');
echo json_encode([
    'uptime' => shell_exec("uptime"),
    'free' => shell_exec('free'),
    'df' => [
        'usage' => shell_exec('df -x tmpfs -x devtmpfs'),
        'inode' => shell_exec('df -x tmpfs -x devtmpfs -i'),
    ],
    'services' => $services_ans,
]);