<?php
	/**
	 * AutoDiscover.xml generator for AD-integrated sites
	 * @author Michael Greenhill
	 * @license MIT
	 * @updated 18/09/2014
	 */
	
	ini_set("display_errors", "off");
	
	/**
	 * Check requirements
	 */
	
	if (!function_exists("ldap_connect")) {
		die("PHP LDAP module is not loaded");
	}
	
	function ldap_escape($str = '') {
	 
		$metaChars = array ("\\00", "\\", "(", ")", "*");
		$quotedMetaChars = array ();
		foreach ($metaChars as $key => $value) {
			$quotedMetaChars[$key] = '\\'. dechex (ord ($value));
		}
		$str = str_replace (
			$metaChars, $quotedMetaChars, $str
		); //replace them
		return ($str);
	}
	
	/**
	 * Config
	 */
	
	$config = array(
		"protocols" => array(
			"IMAP" => array(
				"Server" => "imap.gmail.com",
				"Port" => 993, 
				"SSL" => "on",
				"SPA" => "off",
				"AuthRequired" => "on"
			),
			
			"POP" => array(
				"Server" => "pop.gmail.com",
				"Port" => 995, 
				"SSL" => "on",
				"SPA" => "off",
				"AuthRequired" => "on"
			
			),
			
			"SMTP" => array(
				"Server" => "smtp.gmail.com",
				"Port" => 465, 
				"SSL" => "on",
				"SPA" => "off",
				"AuthRequired" => "on"
			)
		),
		
		"ad" => array(
			"host" => "ldap://dc01.fqdn",
			"dn" => "DC=f,DC=q,DC=d,DC=n",
			"search" => array(
				"filter" => "(mail=%s)",
				"attrs" => array(
					"ou", 
					"sn",
					"givenname",
					"mail"
				)
			),
			"bind" => array(
				"username" => "CN=binduser,DC=f,DC=q,DC=d,DC=n",
				"password" => "supersecret"
			)
		)
	);
	
	/**
	 * Grab the XML input
	 */
	
	$request = file_get_contents("php://input");
	
	/**
	 * Store some data from the XML input
	 */
	
	$data = array(
		"address" => NULL,
		"schema" => NULL
	);
	
	preg_match("/\<EMailAddress\>(.*?)\<\/EMailAddress\>/i", $request, $data['address']);
	preg_match("/\<AcceptableResponseSchema\>(.*?)\<\/AcceptableResponseSchema\>/i", $request, $data['schema']);
	
	if (count($data['address'])) {
		$data['address'] = $data['address'][1];
	}
	
	if (count($data['schema'])) {
		$data['schema'] = $data['schema'][1];
	}
	
	/**
	 * Connect to AD and find the supplied email address
	 */
	
	if (!empty($data['address'])) {
		$cn = ldap_connect($config['ad']['host']);
		ldap_set_option($cn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($cn, LDAP_OPT_REFERRALS, 0);
		
		$ldapbind = ldap_bind($cn, $config['ad']['bind']['username'], $config['ad']['bind']['password']);
		
		if (!$ldapbind) {
			die("Could not bind to LDAP");
		}
		
		$result = ldap_search($cn, $config['ad']['dn'], sprintf($config['ad']['search']['filter'], ldap_escape($data['address'])), $config['ad']['search']['attrs']);
		
		if ($result) {
			$info = ldap_get_entries($cn, $result);
			
			if ($info['count'] == 1) {
				$data['name'] = trim(sprintf("%s %s", $info[0]['givenname'][0], $info[0]['sn'][0]));
			} else {
				unset($data);
			}
		}
	}
	
	/**
	 * Format and return the data
	 */
	 
	echo '<?xml version="1.0" encoding="utf-8"?>';
	
	if (isset($data) && isset($data['name']) && !preg_match("@mobilesync@", $data['schema'])) : ?>
	<Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006">
		<Response xmlns="<?php echo $data['schema']; ?>">
			<User>
				<DisplayName><?php echo $data['name']; ?></DisplayName>
			</User>
			<Account>
				<AccountType>email</AccountType>
				<Action>settings</Action>
				<?php foreach ($config['protocols'] as $protocol => $settings) : ?>
				
				<Protocol>
					<Type><?php echo $protocol; ?></Type>
					<LoginName><?php echo $data['address']; ?></LoginName>
					
					<?php foreach ($settings as $key => $value) : ?>
					
					<?php echo sprintf("<%s>%s</%s>", $key, $value, $key); ?>
					
					<?php endforeach; ?>
				</Protocol>
				<?php endforeach; ?>
			</Account>
		</Response>
	</Autodiscover>
	<?php  else: ?>
	<Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006">
		<Response>
			<Error Time="<?php echo date('H:i:s'); ?>" Id="2477272013">
				<ErrorCode>600</ErrorCode>
				<Message>Invalid Request</Message>
				<DebugData />
			</Error>
		</Response>
	</Autodiscover>
	<?php endif;
	
	die;
?>