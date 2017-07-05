<?php
/**
 * sql.php
 *
 * Version 1 20/10/2016
 */

/**
 * Provides sql functionality
 * 
 * @package OPGSLIB\SQL
 */
class SQL
{
	/**
	 * Stored sqlsrv object
	 */
	private $con;

	/**
	 * Runs the passed query on the connection
	 *
	 * @param string $query SQL query as a string to execute
	 * @param mixed[] $params Params to be parsed into query string
	 * @return queryresource The result of the query
	 */
	function query($query, $params = null)
	{
		return sqlsrv_query($this->con, $query, $params);
	}

	/**
	 * Creates a wrapped and stored connection object
	 *
	 * @param string $server SQL server address
	 * @param string $dbname Database name to connect to
	 * @param string $username Username to connect with
	 * @param string $password Password for username
	 */
	function __construct($server, $dbname, $username, $password)
	{
		$this->con = sqlsrv_connect($server, array('Database'=>$dbname, 'UID'=>$username, 'PWD'=>$password));
		
		if(!$this->con)
		{
			print_r(sqlsrv_errors());
			exit();
		}
	}
	
	/**
	 * Destroys the object
	 */
	function __destruct()
	{
		sqlsrv_close($this->con);
	}
}

?>
