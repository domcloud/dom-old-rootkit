
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
		gzip <?= $c['gzip'] === 'off' ? 'off' : 'on'?>;
<?php
foreach ([
	'types', 'min_length', 'proxied',
] as $key) {
	if (!empty($c['gzip'][$key])) {
		echo "\t\tgzip_$key ".$c['gzip'][$key].";\n";
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
			echo "\t\tpassenger_$key ".$c['passenger'][$key].";\n";
		}
	}
	foreach (($c['passenger']['env_vars'] ?? []) as $env) {
		echo "\t\tpassenger_env_var ".$env.";\n";
	}
	if (!empty($c['passenger']['app_start_command'])) {
		echo "\t\tpassenger_app_start_command ".escapeshellarg($c['passenger']['app_start_command']).";\n";
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
		echo "\t\t\t$key ".$l[$key].";\n";
	}
}
foreach ([
	'root', 'alias',
] as $key) {
	if (isset($l[$key])) {
		echo "\t\t\t$key ".$d['root'].'/'.trim(str_replace('..', '', $l[$key]), '/').";\n";
	}
}
?>
		}
<?php endforeach ?>
<?php if ($c['ssl'] !== 'off') : ?>
		listen <?= $d['ip'] ?>:443 ssl http2;
		listen <?= $d['ip6'] ?>:443 ssl http2;
<?php endif ?>
<?php if (isset($c['ssl_certificate'])) : foreach ($c['ssl_certificate'] as $cs) : ?>
		ssl_certificate /home/<?= $d['user'] ?>/<?= trim(str_replace('..', '', $cs['cert'] ?? ''), '/') ?>;
		ssl_certificate_key /home/<?= $d['user'] ?>/<?= trim(str_replace('..', '', $cs['key'] ?? ''), '/') ?>;
<?php endforeach; else : ?>
		ssl_certificate /home/<?= $d['user'] ?>/ssl.combined;
		ssl_certificate_key /home/<?= $d['user'] ?>/ssl.key;
<?php endif ?>
	}