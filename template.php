server {
	server_name <?= $d['dom'] ?>;
	listen <?= $d['ip'] ?>;
	listen <?= $d['ip6'] ?>;
	root <?= $d['root'] ?>;
	index <?= $c['index'] ?>;
	access_log <?= $d['access_log'] ?>;
	error_log <?= $d['error_log'] ?>;
	fastcgi_param GATEWAY_INTERFACE CGI/1.1;
	fastcgi_param SERVER_SOFTWARE nginx;
	fastcgi_param QUERY_STRING $query_string;
	fastcgi_param REQUEST_METHOD $request_method;
	fastcgi_param CONTENT_TYPE $content_type;
	fastcgi_param CONTENT_LENGTH $content_length;
	fastcgi_param SCRIPT_FILENAME <?= $d['root'] ?>$fastcgi_script_name;
	fastcgi_param SCRIPT_NAME $fastcgi_script_name;
	fastcgi_param REQUEST_URI $request_uri;
	fastcgi_param DOCUMENT_URI $document_uri;
	fastcgi_param DOCUMENT_ROOT <?= $d['root'] ?>;
	fastcgi_param SERVER_PROTOCOL $server_protocol;
	fastcgi_param REMOTE_ADDR $remote_addr;
	fastcgi_param REMOTE_PORT $remote_port;
	fastcgi_param SERVER_ADDR $server_addr;
	fastcgi_param SERVER_PORT $server_port;
	fastcgi_param SERVER_NAME $server_name;
	fastcgi_param PATH_INFO $fastcgi_path_info;
	fastcgi_param HTTPS $https;
    <?php foreach ($c['error_pages'] as $e) : ?>
    error_page <?= $e ?>;
    <?php endforeach ?>
    <?php foreach ($c['locations'] as $l) : ?>
    location <?= $l['match'] ?> {
        <?= isset($l['try_files']) ? "try_files $l[try_files];" : '' ?>

        <?= isset($l['fastcgi_pass']) ? "fastcgi_pass $l[fastcgi_pass];" : '' ?>

        <?= isset($l['return']) ? "return $l[return];" : '' ?>

	}
    <?php endforeach ?>
	fastcgi_split_path_info ^(.+\.php)(/.+)$;
	listen <?= $d['ip'] ?>:443 ssl;
	listen <?= $d['ip6'] ?>:443 ssl;
    <?php if (isset($d['ssl'])) : ?>
	ssl_certificate <?= $d['ssl']['cert'] ?>;
	ssl_certificate_key <?= $d['ssl']['key'] ?>;
    <?php endif ?>
}