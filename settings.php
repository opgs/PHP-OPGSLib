<?php
/**
 * settings.php
 *
 * Version 1 20/10/2016
 * Version 2 20/10/2016
 * 	- Made generic usage
 * Version 3 24/10/2016
 * 	- Check for wincache extension
 * Version 4 02/11/2016
 * 	- Added getSection
 * 	- Tweaked constructor usage for usecache param
 */

/**
 * Provides ini reading and writing functionality
 * 
 * @package OPGSLIB\INI
 */
class INI
{
	
	/**
	 * @var $cachename Name of cache for wincache if used
	 * @var $filename Filename if used for logfile or 'SQL'
	 * @var $ini The raw ini file
	 */
	private $cachename = '';
	private $filename = '';
	private $ini;

	/**
	 * Returns the raw ini array
	 *
	 * @return Raw ini as array
	 */
	function getIni()
	{
		return $this->ini;
	}
	
	/* 
	Get section from INI
		-nameIn		Section name in ini
		-arrayIn	Takes a reference of an array to put settings into, if empty returns array of settings
	*/
	function getSection($nameIn, &$arrayIn = null)
	{
		if(is_null($arrayIn))
		{
			$newArray = [];
			foreach($this->ini[$nameIn] as $key => $value)
			{
				$newArray[$key] = htmlspecialchars($value);
			}
			return $newArray;
		}else{
			foreach($this->ini[$nameIn] as $key => $value)
			{
				if(is_array($arrayIn))
				{
					$arrayIn[$key] = htmlspecialchars($value);
				}else{
					$arrayIn->$key = htmlspecialchars($value);
				}
			}
		}
	}
	
	function rawWrite($assoc_arr, $path, $has_sections = false)
	{
		$content = ""; 
		if($has_sections)
		{ 
			foreach($assoc_arr as $key=>$elem)
			{ 
				$content .= "[" . $key . "]\n"; 
				foreach($elem as $key2 => $elem2)
				{ 
					if(is_array($elem2)) 
					{ 
						for($i = 0; $i < count($elem2); $i++) 
						{ 
							$content .= $key2 . "[] = \"" . $elem2[$i] . "\"\n"; 
						} 
					} 
					else if($elem2 == "") $content .= $key2 . " = \n"; 
					else $content .= $key2 . " = \"" . $elem2 . "\"\n"; 
				} 
			} 
		}else{ 
			foreach($assoc_arr as $key=>$elem)
			{ 
				if(is_array($elem)) 
				{ 
					for($i = 0; $i < count($elem); $i++) 
					{ 
						$content .= $key . "[] = \"" . $elem[$i] . "\"\n"; 
					} 
				} 
				else if($elem == "") $content .= $key . " = \n"; 
				else $content .= $key . " = \"" . $elem . "\"\n"; 
			} 
		} 

		if(!$handle = fopen($path, 'w'))
		{ 
			return false; 
		}

		$success = fwrite($handle, $content);
		fclose($handle); 

		return $success; 
	}

	function __construct($filenameIn = 'settings.ini', $cachenameIn = null, $usecacheIn = null)
	{
		$this->filename = $filenameIn;
		$this->cachename = $cachenameIn;
		$usecache = !is_null($cachenameIn);
		if(!is_null($usecacheIn)){$usecache = $usecacheIn;}
		if(!$usecache || !function_exists('wincache_ucache_exists')){$usecache = false;}
		if($usecache && wincache_ucache_exists($this->cachename))
		{
			$this->ini = unserialize(wincache_ucache_get($this->cachename));
		}else{
			$this->ini = parse_ini_file($this->filename, true);
			if($usecache){wincache_ucache_set($this->cachename, serialize($this->ini));}
		}
	}
	
	function __destruct()
	{
		unset($this->ini);
	}
}

?>
