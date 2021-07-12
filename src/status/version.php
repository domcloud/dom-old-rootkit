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
    'tar' => 'tar --version',
    'zip' => 'zip --version',
    'git' => 'git --version',
    'ssh' => 'ssh -V',
    'openssl' => 'openssl version',
    // services
    'nginx' => 'nginx -v',
    'mysql' => 'mysql -V',
    'psql' => 'psql -V',
    'passenger' => 'passenger -v',
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
    'gem' => 'gem -v',
    'go' => 'go version',
    'rustc' => 'rustc -V',
    'cargo' => 'cargo -V',
    'gcc' => 'gcc --version',
    'g++' => 'g++ --version',
];
foreach ($programs as $key => $value) {
    preg_match('/[\d.]+/', shell_exec($value), $match);
    $programs[$key] = $match[0];
}
header('content-type: application/json');
echo json_encode($programs);
