<?php

function stripFirstLine($text)
{
    return substr($text, strpos($text, "\n") + 1);
}

if (!isset($_GET['secret'])) exit;
if ($_GET['secret'] !== $_SERVER['SECRET_TOKEN']) exit;

# services
$check = [
    'nginx', 'named',
    'iptables', 'ip6tables',
    'mariadb', 'postgresql',
    'php-fpm', 'php56-fpm', 'php80-fpm',
    'proftpd', 'sshd', 'webmin', 'do-agent',
];
$services = [];
foreach ($check as $s) {
    $services[$s] = rtrim(shell_exec("systemctl is-active $s"));
    if ($services[$s] === 'failed') {
        $error = 1;
    }
}
# uptime
preg_match("/(\S+?) up (.+),  .+? users?,  load average: (.+)/", shell_exec("uptime"), $matches);
$uptime = [
    'time' => $matches[1] ?? '',
    'up' => $matches[2] ?? '',
    'core' => intval(rtrim(shell_exec('nproc'))),
    'load' => array_map(function ($x) {
        return floatval(trim($x));
    }, explode(',', $matches[3] ?? '0,0,0')),
];
# free
preg_match("/Mem:\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+Swap:\s+(\d+)\s+(\d+)\s+(\d+)/", stripFirstLine(shell_exec('free')), $matches);
$free = [
    'mem' => [
        'total' => intval($matches[1]),
        'used' => intval($matches[2]),
        'free' => intval($matches[3]),
        'shared' => intval($matches[4]),
        'cache' => intval($matches[5]),
        'available' => intval($matches[6]),
    ],
    'swap' => [
        'total' => intval($matches[7]),
        'used' => intval($matches[8]),
        'free' => intval($matches[9]),
    ],
];
# df
$df = [
    'usage' => stripFirstLine(shell_exec('df -x tmpfs -x devtmpfs')),
    'inode' => stripFirstLine(shell_exec('df -x tmpfs -x devtmpfs -i')),
];

foreach ($df as $dfk => $dfv) {
    preg_match_all("/(.+?)\s+(\d+?)\s+(\d+?)\s+(\d+?)\s+(\d+?%)\s+(.+)/", $dfv, $matches, PREG_SET_ORDER);
    $df[$dfk] = array_map(function ($x) {
        return [
            'name' => $x[1],
            'total' => $x[2],
            'used' => $x[3],
            'free' => $x[4],
            'usage' => $x[5],
            'mount' => $x[6],
        ];
    }, $matches);
}

if (isset($error)) {
    header("HTTP/1.1 500 Internal Server Error");
}
header('content-type: application/json');
echo json_encode([
    'uptime' => $uptime,
    'free' => $free,
    'df' => $df,
    'services' => $services,
]);
