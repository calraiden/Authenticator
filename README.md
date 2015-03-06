# Authenticator
PdoAdapter customizada para Apigility/Zend 2

Crie esse modulo para customizar o PDOAdapter usando o Apigility, pois precisava obter o USER_ID ao inves do Username.


* Crie um novo modulo para as classe
``` ./zftools create module Authenticator ```

ou

``` git clone https://github.com/calraiden/Authenticator.git ``` 

* Confirme se o modulo foi adicionado (config/application.config.php)
```
	return array(
		'modules' => array(
			'Application',
			...
			'Authenticator'
		),
		'module_listener_options' => array(
			'module_paths' => array(
				'./module',
				'./vendor'
			),
			'config_glob_paths' => array(
				'config/autoload/{,*.}{global,local}.php'
			),
			'config_cache_key' => 'application.config.cache',
			'config_cache_enabled' => true,
			'module_map_cache_key' => 'application.module.cache',
			'module_map_cache_enabled' => true,
			'cache_dir' => 'data/cache/'
		)
	);
```

*Altere o PDO Storage no (config/autoload/local.php)
```
	return array (
			...
			'zf-oauth2' => array (
					//'storage' => 'ZF\\OAuth2\\Adapter\\PdoAdapter',
					'storage' => 'Authenticator\\Adapter\\PdoAdapter',
					'storage_settings' => array(
							'user_table' => 'oauth_users',
							'client_table'=>'oauth_clients'
					),
					'db' => array (
							'dsn_type' => 'PDO',
							'dsn' => 'mysql:dbname=DATABASE;host=localhost',
							'username' => 'root',
							'password' => '' 
					) 
			) 
	);
```

*Adicione a columa user_id em sua tabela, caso não possua.
```
	ALTER TABLE `mydb`.`oauth_users` 
	ADD COLUMN `user_id` VARCHAR(100) NOT NULL DEFAULT '0:0:0' AFTER `last_name`
```
