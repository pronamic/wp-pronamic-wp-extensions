# [Pronamic WordPress Extensions](http://www.pronamic.eu/plugins/pronamic-wp-extensions/)

WordPress plugin wich allows your to create your own WordPress extensions directory.

## WordPress update 4.0 to 4.1

*	https://github.com/WordPress/WordPress/blob/4.0/wp-admin/update-core.php#L243
*	https://github.com/WordPress/WordPress/blob/4.1/wp-admin/update-core.php#L243-L246
*	https://github.com/WordPress/WordPress/blob/4.1/wp-admin/includes/plugin-install.php#L55-L66

## WordPress REST API

- https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/
- https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/#strings
- https://httpie.io/docs/cli/basic-auth

## Tests

### Plugins

```
http --follow POST https://api.pronamic.eu/plugins/update-check/1.2/ plugins=@tests/plugins.json
```

```
http POST https://wp.pronamic.directory/wp-json/pronamic-wp-extensions/v1/plugins/update-check plugins=@tests/plugins.json
```

### Themes

```
http POST https://api.pronamic.eu/themes/update-check/1.2/ themes=@tests/themes.json
```

```
http POST https://wp.pronamic.directory/wp-json/pronamic-wp-extensions/v1/themes/update-check themes=@tests/themes.json
```
