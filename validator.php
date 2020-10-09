<?php

function isParamValid($p)
{
    return preg_match("/^[a-zA-Z0-9_\.\/~*^$+=: ]+$/", $p);
}

function mergeConfig($config)
{
    $c = [
        'index' => 'index.html index.htm index.php',
        'locations' => [
            // [
            //     'match' => '/',
            //     'try_files' => '$uri $uri/ /index.php?$is_args$args',
            //     'return' => '301 http://example.com',
            // ]
        ],
        'error_pages' => [
            // '404 /404.html'
        ],
    ];
    if ($config && ($config = json_decode($config, true))) {
        $c = array_merge_recursive($c, $config);
    }
    // validate config
    $c['locations'] = array_values(array_filter(array_map(function ($x) {
        return array_map(function ($y) {
            return trim($y);
        }, $x);
    }, $c['locations']), function ($x) {
        if (empty($x['match']) || !isParamValid($x['match'])) return false;
        if (isset($x['try_files']) && !isParamValid($x['try_files'])) return false;
        if (isset($x['return']) && !isParamValid($x['return'])) return false;
        if (isset($x['fastcgi_pass'])) return false;
        $co = explode(' ', $x['match']);
        if (count($co) > 2 || $co[0][0] === '@') return false;
        if (count($co) === 2 && array_search($co[0], ['=', '~', '~*', '^~']) === false) return false;
        return true;
    }));

    $c['error_pages'] = array_values(array_filter(array_map(function ($x) {
        return trim($x);
    }, $c['error_pages']), function ($x) {
        return !empty($x) && isParamValid($x);
    }));
    $c['index'] = $c['index'] && isParamValid($c['index']) ? trim($c['index']) : 'index.html index.htm index.php';
    return $c;
}

function updateNginx($domain, $home)
{
    // Virtualmin can attempt to validate before reload, so let's use it.
    $home = explode('/', '-'.$home, 4)[3] ?? 'public_html';
    $ch = curl_init($_SERVER['VIRTUALMIN_PATH']."?program=modify-web&domain=$domain&document-dir=$home");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $_SERVER['VIRTUALMIN_AUTH']);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}
