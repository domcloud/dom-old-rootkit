# dom-nginx

This a tool to manage NginX config file in per-server basis. It also validates
every input config it receives so there's no way to broke the whole server or
circumvent the limited config available to individual.

## Running your own

Virtualmin and R/W access to your nginx.conf file.

## Config

It receives config using JSON, but because DOM Cloud config uses yaml, so we use YAML here. You can also see [available config here](src/nginx/validator.php)

Available values:

```yaml
nginx:
  ssl: on
  passenger:
    enabled: off
    # all other passenger config
  index: index.html index.htm index.php
  locations:
  - match: /
    try_files: $uri
    return: 301 http://example.com$request_uri
  error_pages:
  - 403 404 /404.html
```

