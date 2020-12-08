server {
	server_name <?= $d['dom'] ?>;
	<?php if ($c['ssl'] !== 'enforce') : ?>
	listen <?= $d['ip'] ?>;
	listen <?= $d['ip6'] ?>;
	<?php endif ?>
	root <?= $d['root'] ?>;
	index <?= $c['index'] ?>;
	access_log <?= $d['access_log'] ?>;
	error_log <?= $d['error_log'] ?>;
	<?php if (!empty($c['gzip'])) : ?>
	gzip on;
	<?php
	foreach ([
		'types', 'min_length', 'proxied',
	] as $key) {
		if (!empty($c['gzip'][$key])) {
			echo "\tgzip_$key ".$c['gzip'][$key].";\n";
		}
	}
	?>
	<?php endif ?>
	<?php if (!empty($c['passenger'])) {
		foreach ([
			'enabled', 'app_env', 'app_type',
			'startup_file', 'ruby', 'nodejs', 'python',
			'meteor_app_settings', 'friendly_error_pages',
		] as $key) {
			if (!empty($c['passenger'][$key])) {
				echo "\tpassenger_$key ".$c['passenger'][$key].";\n";
			}
		}
		foreach (($c['passenger']['env_vars'] ?? []) as $env) {
			echo "\tpassenger_env_var ".$env.";\n";
		}
		if (!empty($c['passenger']['app_start_command'])) {
			echo "\tpassenger_app_start_command ".escapeshellarg($c['passenger']['app_start_command']).";\n";
		}
	} ?>
    <?php foreach ($c['error_pages'] as $e) : ?>
    error_page <?= $e ?>;
    <?php endforeach ?>
    <?php foreach ($c['locations'] as $l) : ?>
    location <?= $l['match'] ?> {
		<?php
		foreach ([
			'try_files', 'fastcgi_pass', 'return',
		] as $key) {
			if (isset($l[$key])) {
				echo "\t$key ".$l[$key].";\n";
			}
		}
		foreach ([
			'root', 'alias',
		] as $key) {
			if (isset($l[$key])) {
				echo "\t$key ".$d['root'].'/'.trim(str_replace('..', '', $l[$key]), '/').";\n";
			}
		}
		?>
	}
    <?php endforeach ?>
	<?php if ($c['ssl'] !== 'off') : ?>
	listen <?= $d['ip'] ?>:443 ssl http2;
	listen <?= $d['ip6'] ?>:443 ssl http2;
    <?php endif ?>
    <?php if (isset($d['ssl'])) : ?>
	ssl_certificate <?= $d['ssl']['cert'] ?>;
	ssl_certificate_key <?= $d['ssl']['key'] ?>;
    <?php endif ?>
}