<?php
/**
 * ldap.php
 *
 * Version 1 20/10/2016
 * Version 2 24/10/2016
 * 	- Moved out last $SITE
 * Version 3 25/10/2016
 * 	- Added searchlist,countentries,modify
 * Version 4 09/11/2016
 * 	- Added getSamAccountNameFromName for accounts
 * 	- Added enableDisableUser for accounts
 * Version 5 14/11/2016
 * 	- Added in isEnabled.
 * 	- Refactors get from name into getAttrFromName
 * Version 6 03/07/2017
 *  - Fixed getAttrFromName return values
 */

//define('LDAP_OPT_DIAGNOSTIC_MESSAGE', 0x0032);

/**
 * Provides LDAP functionality
 * 
 * @package OPGSLIB\LDAP
 */
class LDAP
{
	private $ldap;
	private $ldapbind;
	private $searchDN = '';
	
	function bind($ldapuser, $ldappass)
	{
		$this->ldapbind = ldap_bind($this->ldap, $ldapuser, $ldappass);
		
		return $this->ldapbind;
	}
	
	function search($dn, $filter, $what)
	{
		return ldap_search($this->ldap, $dn, $filter, $what);
	}
	
	function searchlist($dn, $filter, $what)
	{
		return ldap_list($this->ldap, $dn, $filter, $what);
	}
	
	function count_entries($sr)
	{
		return ldap_count_entries($this->ldap, $sr);
	}
	
	function modify($dn, $userdata)
	{
		return ldap_modify($this->ldap, $dn, $userdata);
	}
	
	function get_entries($sr)
	{
		return ldap_get_entries($this->ldap, $sr);
	}
	
	function error()
	{
		return ldap_error($this->ldap);
	}
	
	function isEnabled($samname, $base)
	{
		$sr = $this->search($base, "(sAMAccountName=$samname)", array('sAMAccountName', 'userAccountControl'));
		$ent = $this->get_entries($sr);
		
		return (($ent[0]['useraccountcontrol'][0] & 2) == 0);
	}
	
	function enableDisableUser($accountIn, $actionIn)
	{
		$sr = $this->search($this->searchDN, "(sAMAccountName=$accountIn)", array('userAccountControl'));
		$ent = $this->get_entries($sr);
				
		$value = $ent[0]['useraccountcontrol'][0];
		
		if($actionIn < 1)
		{
			$newValue = ($value | 2);
		}else{
			$newValue = ($value & ~2);
		}
	
		echo "<br />Old value : " . $value;
		echo "<br />New value : " . $newValue . "<br />";
		
		$userdata = [];
		$userdata["useraccountcontrol"][0] = $newValue;
		
		print_r($ent);
		
		return ldap_mod_replace($this->ldap, $ent[0]['dn'], $userdata);
	}
	
	function getExtendedError()
	{
		ldap_get_option($this->ldap, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error);
		return $extended_error;
	}
	
	function getDNFromName($nameIn)
	{
		$filter = str_replace(' ', '*', '(displayName=' . $nameIn . ')');
	
		$sr = $this->search($this->searchDN, $filter, array("distinguishedName"));

		$info = $this->get_entries($sr);

		if(!isset($info[0])){return $nameIn;}

		return $info[0]["distinguishedName"][0];
	}
	
	function getAttrFromId($idIn, $attrIn)
	{
		$filter = str_replace(' ', '*', '(opgsSimsPid=' . $idIn . ')');
	
		$sr = $this->search($this->searchDN, $filter, array($attrIn));

		$info = $this->get_entries($sr);

		if(!isset($info[0])){return $idIn;}
		if(!isset($info[0][$attrIn])){return $idIn;}

		return $info[0][$attrIn][0];
	}
	
	function getNameFromId($idIn)
	{
		return $this->getAttrFromId($idIn, "displayname");
	}
	
	function getStaffCodeFromId($idIn)
	{		
		return $this->getAttrFromId($idIn, "opgssimsstaffcode");
	}
	
	function getEmailFromId($idIn)
	{
		return $this->getAttrFromId($idIn, "mail");
	}
	
	function getAttrFromName($nameIn, $attrIn)
	{
		$filter = str_replace(' ', '*', '(displayName=' . $nameIn . ')');
	
		$sr = $this->search($this->searchDN, $filter, array($attrIn));

		$info = $this->get_entries($sr);

		if(!isset($info[0])){return $nameIn;}
		if(!isset($info[0][$attrIn])){return $nameIn;}

		return $info[0][$attrIn][0];
	}
	
	function getIdFromName($nameIn)
	{			
		return $this->getAttrFromName($nameIn, "opgssimspid");
	}
	
	function getEmailFromName($nameIn)
	{
		return $this->getAttrFromName($nameIn, "mail");
	}
	
	function getSamAccountNameFromName($nameIn)
	{
		return $this->getAttrFromName($nameIn, "samaccountname");
	}

	function __construct($server, $binduser = null, $bindpass = null, $searchDNIn = null)
	{
		// connect to ldap server
		$this->ldap = ldap_connect($server);

		// binding to ldap server
		ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->ldap, LDAP_OPT_REFERRALS, 0);

		if($binduser && $bindpass)
		{
			$this->ldapbind = $this->bind($binduser, $bindpass);
	
			if(!$this->ldapbind){die($this->error());}
		}
		
		$this->searchDN = $searchDNIn;
	}
	
	function __destruct()
	{
		if($this->ldap)
		{
			ldap_close($this->ldap);
		}
	}
}

?>
