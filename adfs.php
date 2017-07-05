<?php
/**
 * adfs.php
 */
/*
Version 1 20/10/2016
Version 2 24/10/2016
	- Moved simplesaml in
	- Moved out $SITE->adfsAdminGroup to param
	- Moved require path to param
*/

/**
 * Provides ADFS functionality via simplesamlphp
 * 
 * @package OPGSLIB\ADFS
 */
class ADFS
{
	private $sp = '';
	private $adfs;
	private $isUserAdmin = false;
	private $userAttr = '';
		
	function auth()
	{
		$this->adfs->requireAuth();
	}
	
	function isAuth()
	{
		return $this->adfs->isAuthenticated();
	}
	
	function isAdmin()
	{
		return $this->isUserAdmin;
	}
	
	function checkGroup($groupdn)
	{
		return in_array($groupdn, $this->userAttr['Groups']);
	}
	
	function getUser()
	{
		return $this->userAttr['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name'][0];
	}

	function getAttr()
	{
		return $this->userAttr;
	}
	
	function forceAuth()
	{
		if(!$this->isAuth())
		{
			$this->auth();
		}
	}

	function __construct($simplesamlpathIn, $sp, $adfsAdminGroupIn)
	{
		global $SITE;
	
		require($simplesamlpathIn . '/lib/_autoload.php');
	
		$this->adfs = new SimpleSAML_Auth_Simple($sp);
		$this->userAttr = $this->adfs->getAttributes();
		$this->isUserAdmin = ($this->isAuth() && $this->checkGroup($adfsAdminGroupIn));
	}
	
	function __destruct()
	{
		unset($this->adfs);
	}
}

?>
