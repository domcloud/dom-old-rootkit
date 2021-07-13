<?php

if (!isset($_GET['secret'])) exit;
if ($_GET['secret'] !== $_SERVER['SECRET_TOKEN']) exit;

$programs = [
    // gnu packages
    'centos' => 'cat /etc/centos-release',
    'bash' => 'bash --version',
    'curl' => 'curl -V',
    'wget' => 'wget -V',
    'nano' => 'nano --version',
    'vim' => 'vim --version',
    'git' => 'git --version',
    'openssl' => 'openssl version',
    // services
    'webmin' => 'cat /etc/webmin/version',
    'virtualmin' => 'cat /usr/libexec/webmin/virtual-server/module.info | grep version',
    'logrotate' => 'cat /etc/webmin/logrotate/version',
    'ssh' => 'cat /etc/webmin/sshd/version',
    'named' => '/usr/sbin/named -v 2>&1',
    'nginx' => '/usr/local/sbin/nginx -v 2>&1',
    'mysql' => 'mysql -V',
    'psql' => 'psql -V',
    'passenger' => 'passenger -v',
    'passenger-config' => 'passenger-config about version',
    // language services
    'php' => 'php -v',
    'php56' => 'php56 -v',
    'php80' => 'php80 -v',
    'composer' => 'composer -V',
    'python' => 'python -V',
    'python3.6' => 'python3.6 -V',
    'python3.8' => 'python3.8 -V',
    'pip' => 'pip -V',
    'pip3.6' => 'pip3.6 -V',
    'pip3.8' => 'pip3.8 -V',
    'node' => 'node -v',
    'npm' => 'npm -v',
    'yarn' => 'yarn -v',
    'ruby' => 'ruby -v',
    'rake' => 'rake -V',
    'gem' => 'gem -v',
    'go' => 'go version',
    'rustc' => 'rustc -V',
    'cargo' => 'cargo -V',
    'gcc' => 'gcc --version',
    'g++' => 'g++ --version',
];
foreach ($programs as $key => $value) {
    preg_match('/[\d.]+\d+/', shell_exec($value), $match);
    $programs[$key] = $match[0];
}
header('content-type: application/json');
echo json_encode($programs);
