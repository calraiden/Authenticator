<?php
return array(
		'service_manager' => array (
				'factories' => array (
						'Authenticator\Adapter\PdoAdapter' => 'Authenticator\Factory\PdoAdapterFactory' 
				) 
		), 
);
