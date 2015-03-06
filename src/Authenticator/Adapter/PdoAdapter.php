<?php

/**
* @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
* @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
* @author calraiden
*/
namespace Authenticator\Adapter;

use ZF\OAuth2\Adapter\PdoAdapter as ZFOAuthPdoAdapter;

/**
 * Extension of ZF\OAuth2\Adapter\PdoAdapter that provides Bcrypt client_secret/password
 * encryption
 */
class PdoAdapter extends ZFOAuthPdoAdapter {
	/**
	 * (non-PHPdoc)
	 * 
	 * @see \OAuth2\Storage\Pdo::getUser()
	 */
	public function getUser($username) {
		$stmt = $this->db->prepare ( $sql = sprintf ( 'SELECT * from %s where username=:username', $this->config ['user_table'] ) );
		$stmt->execute ( array (
				'username' => $username 
		) );
		
		if (! $userInfo = $stmt->fetch ( \PDO::FETCH_BOTH )) {
			return false;
		}
		
		// the default behavior is to use "username" as the user_id, BUT YOU CAN CUSTOM YOUR TABLE IN THE DATABASE
		// $userInfo = array_merge(array(
		// 'user_id' => $username
		// ), $userInfo);
		return $userInfo;
	}
}
