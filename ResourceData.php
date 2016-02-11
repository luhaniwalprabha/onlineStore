<?php
require_once("db.inc.php");

class ResourceData{
	private $db;
	public $tableName;
	public $primaryKey;
	public $UrlCollectionName;

	public function __construct($tableName, $db, $UrlCollectionName){ 
		$this->db = $db;
		$this->tableName = $tableName;	
		$this->primaryKey = $this->db->getPrimaryKey($this->tableName);
		$this->UrlCollectionName = $UrlCollectionName;
	}

}
?>