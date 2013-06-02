<?php
require_once 'dbapi.php';

class Table 
{
	const USER = 'users';
	const USER_TABLE_INI_FILE = 'database/tables/user.ini';
	
	const TOKEN = 'tokens';
	const TOKEN_TABLE_INI_FILE = 'database/tables/token.ini';
	
	const VERIFICATION_CODES = 'verification_codes';
	const VERIFICATION_CODES_TABLE_INI_FILE = 'database/tables/verification_codes.ini';
	
	const TUTORIAL = 'tutorials';
	const COMMENT = 'comments';
	const FOLOWER = 'followers';
	const IMAGE = 'images';
}

class MySQLTableManager 
{
	const TAG = 'tables.php, MySQLTableManager:';
	public $db = DB::get_dblink;
	
	final static public function create_table_mysql_script($table_name, $table_ini_file) {
		echo 'TBD';
	}
	
	final static public function execute_mysql_script($script) {
		echo 'TBD';
	}
	
	final static public function restart_db_tables() {
		echo 'TBD';
	}
	
	final static public function add_column_to_table($table_ini_file) {
		echo 'TBD';
	}
}
?>


