<?php
	/******************************************************************
	DB.class.php
	Database access layer.

	Originally written for MySQL (MySQLi). It now runs on SQLite via PDO so
	the whole app works from a single file with no database server to install
	or configure. The public API (select_query / update_query) and its return
	values are unchanged, so the rest of the app did not need to be rewritten.
	******************************************************************/
class DB
{
	/****************************************************************************
	* ATTRIBUTES                                                                *
	****************************************************************************/

	var $id;
	var $db;              // path to the SQLite file
	var $pdo;             // PDO connection (reused across queries)
	var $debugmode;
	var $logfile;

	/****************************************************************************
	* CONSTRUCTOR                                                               *
	****************************************************************************/

	function __construct($id = "")
	{
		$this->id = $id;
		$this->pdo = null;
	}

	/**
	 * Connect to the SQLite database, creating and seeding it from $schemaFile
	 * on first run if the file does not exist yet.
	 */
	function connectSqlite($dbFile, $schemaFile)
	{
		$this->db = $dbFile;
		$needsSeed = !file_exists($dbFile) || filesize($dbFile) === 0;

		$dir = dirname($dbFile);
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}

		$this->pdo = new PDO("sqlite:" . $dbFile);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->exec("PRAGMA foreign_keys = ON;");

		if ($needsSeed && file_exists($schemaFile)) {
			$this->pdo->exec(file_get_contents($schemaFile));
		}
	}

	/****************************************************************************
	* QUERIES                                                                   *
	****************************************************************************/

	// FOR SELECTION OF TABLES ONLY AND RETURN OF DATA
	// Default $numberOfResults is 0 = expect more than one row (returns array of rows).
	// Pass $numberOfResults = 1 to return a single row (associative array).
	function select_query($querystring, $numberOfResults = 0)
	{
		try {
			$stmt = $this->pdo->query($querystring);

			if ($numberOfResults == 0):
				// returns array of rows
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			else:
				// returns a single row
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				// Check for NULL / no-row return (kept from original contract)
				$result = ($row === false) ? 505 : $row;
			endif;

			return $result;
		} catch (PDOException $e) {
			// error from DB / SQL
			return 500;
		}
	}

	function update_query($querystring)
	{
		try {
			$affected = $this->pdo->exec($querystring);

			if ($affected > 0):
				$id = $this->pdo->lastInsertId();
				return array('update' => 200, 'id' => $id);
			else:
				return 503;
			endif;
		} catch (PDOException $e) {
			// error from DB / SQL
			return 500;
		}
	}

	//-----------------------------------------------------------------------------
	function logerrors($logfile)
	{
		$this->logfile = $logfile;
	}

} // end Class

?>
