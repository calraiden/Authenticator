# Authenticator
PdoAdapter customizada para Apigility/Zend 2

Crie esse modulo para customizar o PDOAdapter usando o Apigility, pois precisava obter o USER_ID ao inves do Username.

1. Crie um novo modulo para as clases
./zftools create module Authenticator

2. Confirme se o modulo foi adicionado (config/application.config.php)
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


3. Altere o PDO Storage no (config/autoload/local.php)
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


4. Adicione o aliases do "Factory" para o service manager (Authenticator/config/module.config.php).
<?php
return array(
		'service_manager' => array (
				'factories' => array (
						'Authenticator\Adapter\PdoAdapter' => 'Authenticator\Factory\PdoAdapterFactory' 
				) 
		), 
);

5. Crie a Classe customizada do Adapter (Authenticator/src/Authenticator/Adapter/PdoAdapter.php)
namespace Authenticator\Adapter;

use ZF\OAuth2\Adapter\PdoAdapter as ZFOAuthPdoAdapter;
/**
 * Extension of ZF\OAuth2\Adapter\PdoAdapter that provides Bcrypt client_secret/password
 * encryption
 */
class PdoAdapter extends ZFOAuthPdoAdapter
{
	/**
	 * (non-PHPdoc)
	 * @see \OAuth2\Storage\Pdo::getUser()
	 */
    public function getUser($username)
    {
        $stmt = $this->db->prepare($sql = sprintf('SELECT * from %s where username=:username', $this->config['user_table']));
        $stmt->execute(array('username' => $username));

        if (!$userInfo = $stmt->fetch(\PDO::FETCH_BOTH)) {
            return false;
        }

        return $userInfo;
    }
}

PS: Lembre-se, isso é um exemplo, vc pode aumentar a segurança implementando sua solução, por exemplo, criptografando o user_id se achar necessario.

6. Crie o Factory para usar o seu Adapter customizado (Authenticator/src/Authenticator/Factory/PdoAdapterFactory.php). 
namespace Authenticator\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use \Authenticator\Adapter\PdoAdapter;
use ZF\OAuth2\Controller\Exception;

class PdoAdapterFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $services
     * @throws \ZF\OAuth2\Controller\Exception\RuntimeException
     * @return \Authenticator\Adapter\PdoAdapter
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config = $services->get('Config');

        if (!isset($config['zf-oauth2']['db']) || empty($config['zf-oauth2']['db'])) {
            throw new Exception\RuntimeException(
                'The database configuration [\'zf-oauth2\'][\'db\'] for OAuth2 is missing'
            );
        }

        $username = isset($config['zf-oauth2']['db']['username']) ? $config['zf-oauth2']['db']['username'] : null;
        $password = isset($config['zf-oauth2']['db']['password']) ? $config['zf-oauth2']['db']['password'] : null;
        $options  = isset($config['zf-oauth2']['db']['options']) ? $config['zf-oauth2']['db']['options'] : array();

        $oauth2ServerConfig = array();
        if (isset($config['zf-oauth2']['storage_settings']) && is_array($config['zf-oauth2']['storage_settings'])) {
            $oauth2ServerConfig = $config['zf-oauth2']['storage_settings'];
        }

        return new PdoAdapter(array(
            'dsn'      => $config['zf-oauth2']['db']['dsn'],
            'username' => $username,
            'password' => $password,
            'options'  => $options,
        ), $oauth2ServerConfig);
    }
}


7. Adicione a columa user_id em sua tabela, caso não possua.
ALTER TABLE `mydb`.`oauth_users` 
ADD COLUMN `user_id` VARCHAR(100) NOT NULL DEFAULT '0:0:0' AFTER `last_name`