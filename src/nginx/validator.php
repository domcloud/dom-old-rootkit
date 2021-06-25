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
        /**
         * Every configuration won't be pertained if you redeploy.
         *  e.g. if last config is "ssl: enforce" then the next deploy you
         *  forgot to set it, it will back to "ssl: on".
         */
        'ssl' => 'on', // [off|on (default)|enforce]
        'fastcgi' => 'on', // [off|on (default)] whether to enable or disable php execution
        'ssl_certificate' => [
            // 'cert' => 'ssl.combined',
            // 'key' => 'ssl.key',
        ],
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
        'gzip' => [
            /* Gzip is on by default. To turn it off use "gzip: off" */
            // 'types' => 'text/css application/javascript image/svg+xml',
            // 'min_length' => 1024,
            // 'proxied' => 'off',
        ],
        'index' => 'index.html index.php',
        'locations' => [
            // [
            //     'match' => '/',
            //     'try_files' => '$uri $uri/ /index.php?$is_args$args',
            //     'return' => '301 http://example.com',
            //     'root' => 'other_public_html',
            //     'alias' => 'other_public_html',
            // ]
        ],
        'error_pages' => [
            /* Usually useful for static file deployment */
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
    $c['index'] = $c['index'] ?: 'index.html index.php';
    if (isset($c['ssl_certificate']['cert'], $c['ssl_certificate']['key'])) {
        // in case some people want 'multi cert' usage
        $c['ssl_certificate'][0] = $c['ssl_certificate'];
    } else if (count($c['ssl_certificate'] ?? []) == 0) {
        unset($c['ssl_certificate']);
    }
    if ($c['fastcgi'] != 'on') {
        $c['index'] = str_replace('index.php', '', $c['index']);
    }
    return $c;
}
