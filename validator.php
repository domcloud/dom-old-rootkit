<?php

function array_map_recursive(&$arr, $fn)
{
    return array_map(function ($item) use ($fn) {
        return is_array($item) ? array_map_recursive($item, $fn) : $fn($item);
    }, $arr);
}

function mergeConfig($config)
{
    $c = [
        'ssl' => 'on', // [off|on|enforce]
        'passenger' => [
            /*
                The only config you care is "enabled"
                If you use GLS, you can also specify "app_start_command"
            */
            // 'enabled' => 'off', // [off (default)|on]
            // 'app_env' => 'production', // [production (default)|development]
            // 'app_start_command' =>  'app --port $PORT' // (explicit)
            // 'app_type' =>  '' // (this autodetects)
            // 'startup_file' => '' // (this autodetects)
            // 'env_vars' => [ '', '' ] // (envs)
            // 'ruby' => 'ruby', // (for rvm users)
            // 'nodejs' => 'node', // (for nvm users)
            // 'python' => 'python3', // (for pipenv users)
            // 'meteor_app_settings' => '', // (for meteors users)
            // 'friendly_error_pages' => 'on', // [off|on (default)]
        ],
        'index' => 'index.html index.htm index.php',
        'locations' => [
            // [
            //     'match' => '/',
            //     'try_files' => '$uri $uri/ /index.php?$is_args$args',
            //     'return' => '301 http://example.com',
            // ]
        ],
        'error_pages' => [
            // '404 /404.html',
            // '500 503 /50x.html',
        ],
    ];
    if ($config && ($config = json_decode($config, true))) {
        $c = array_replace_recursive($c, array_map_recursive($config, function ($x) {
            return str_replace(';', '', trim($x));
        }));
    }
    // validate config
    $c['locations'] = array_map(function ($x) {
        unset($x['fastcgi_pass']);
        return $x;
    }, $c['locations']);
    $c['index'] = $c['index'] ?: 'index.html index.htm index.php';
    return $c;
}

function updateNginx($domain, $home)
{
    // Virtualmin can attempt to validate before reload, so let's use it.
    $home = explode('/', '-' . $home, 4)[3] ?? 'public_html';
    $ch = curl_init($_SERVER['VIRTUALMIN_PATH'] . "?program=modify-web&domain=$domain&document-dir=$home");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $_SERVER['VIRTUALMIN_AUTH']);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}
