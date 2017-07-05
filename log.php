<?php
/**
 * log.php
 */
/* 
Version 1 20/10/2016
Version 2 24/10/2016
	- Made generic
	- File or SQL
Version 3 25/10/2016
	- Read log

Log types

1 Add
2 Edit/Overide
3 Delete
4 Close/Open
5 Admin 
*/

/**
 * Provides Log file functionality
 * 
 * @package OPGSLIB\LOG
 */
class LOG
{
	private $logType = '';
	private $logLocation = 'SQL';
	private $AD;
	private $SQL;
	
	private $query = 'INSERT INTO dbo.Log (LG_By, LG_Type, LG_Entry, LG_Date) VALUES (?, ?, ?, ?);';

	function add($log, $type)
	{
		if($this->logLocation == 'SQL')
		{
			$tt = $this->SQL->query($this->query, array($this->AD->getUser(), $type, $log, date('d-m-Y H:i:s')));
			if($tt == false)
			{
				echo "FAIL!!!!";
				print_r(sqlsrv_errors());
			}
			unset($tt);
		}else{
			file_put_contents($this->logLocation, $type . ' - ' . $log . PHP_EOL, FILE_APPEND | LOCK_EX);
		}
	}
	
	function read($count = '1000')
	{
		if($this->logLocation == 'SQL')
		{
			function sortFunction($a, $b){return strtotime($b['LG_Date']) - strtotime($a['LG_Date']);}
		
			$query = 'SELECT TOP ' . $count . ' * FROM dbo.Log ORDER BY LG_Date DESC;';
			$tt = $this->SQL->query($query);

			if($tt !== false)
			{
				echo sqlsrv_num_rows($tt);
			}else{
				echo "FAIL!!!!";
				print_r(sqlsrv_errors());
			}

			$log = [];
			while($entry = sqlsrv_fetch_array($tt, SQLSRV_FETCH_ASSOC))
			{
				$log[] = $entry;
			}

			usort($log, 'sortFunction');
			
			return $log;
		}else{
			return explode(PHP_EOL, file_get_contents($this->logLocation));
		}
	}

	function __construct($adIn, $logLocation = 'SQL', $sqlIn = null)
	{
		$this->AD = $adIn;
		if(($logLocation == 'SQL') && ($sqlIn !== null))
		{
			$this->SQL = $sqlIn;
		}else{
			$this->location = $logLocation;
		}
	}
}

function addLog($log, $type) #Compatibility
{
	global $AD, $SQL;

	$LOG = new LOG($AD, 'SQL', $SQL);
	$LOG->add($log, $type);
}
?>
