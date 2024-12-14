<?php
//------------------------------------------------------------------------------------------------------------------------------------------
// DataCenter:
//	- DataBase management
//------------------------------------------------------------------------------------------------------------------------------------------

class DataCenter {
	
	//------------------------------------------------------------------------------------------------------------------------------------------
	// Database Management
	//------------------------------------------------------------------------------------------------------------------------------------------
	private $DB;
	public $SQLite_DB;
	
	//----------------------------------------------------------------------------------------------------------------------------------
	// Data
	public $operator_and_priority = array('and' => 1, 'or' => 1);//, 'not' => 2);
	public $operator_inject = 'and';	// when there is no operator between two operands => inject this operator between them
	
	// Word prefixes
	// ..grouped with the 2 beginning characters
	public $prefixes_list = array(
		//'co', 
		'dis', 
		'in', 
		'mis', 
		'non', 
		'over', 
		'pre', 
		'quasi', 
		're', 
		'twice',
		'un', 
		'under', 
		'well'
	);
	public $prefixes = array();
	
	// allowed short root words from 1-2 char long words
	public $allowed_2_chars_words = array('be'=>1, 'do'=>1, 'go'=>1, 'is'=>1, 'up'=>1);
	
	
	
	//-----------------------------------------------------------------------------------------------------------------------
	// SQLite
	//-----------------------------------------------------------------------------------------------------------------------
	function SQLite_Connect() {
		$this->SQLite_DB = new PDO('sqlite:' . SQLITE_FILE_TO_USE);//, '', '', array(PDO::MYSQL_ATTR_LOCAL_INFILE => 1));
		$this->SQLite_DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->SQLite_DB->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$this->SQLite_DB->exec("PRAGMA temp_store=MEMORY");
		$this->SQLite_DB->exec("PRAGMA journal_mode=MEMORY");
		$this->SQLite_DB->exec("PRAGMA synchronous=OFF");
	}
	
	
	//-------------------------------------------------------------------------------------------------------------------
	// Constructor and settings
	//-------------------------------------------------------------------------------------------------------------------
	public function __construct($dbConnectionData) {
		$this->DB = $dbConnectionData;
		
		// SQLite DB connect
		$this->SQLite_Connect();
		
		// Set prefix groups
		foreach ($this->prefixes_list as $prefix) {
			$this->prefixes[substr($prefix, 0, 2)][] = array($prefix => strlen($prefix));
		}
	} // end: DataCenter()
} // end: class DataCenter {}
?>