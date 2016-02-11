<?php

date_default_timezone_set('Asia/Calcutta');
class DB
     {
        ///Declaration of variables
        const DB_SERVER = "localhost";
        const DB_USER = "root";
        const DB_PASSWORD = "";
        const DB = "Online_store";
        
        private $db = NULL; 

        public function __construct(){

            $this->dbConnect();
        }

        private function dbConnect(){
            $this->db = mysql_connect(self::DB_SERVER,self::DB_USER,self::DB_PASSWORD);
            if($this->db)
                mysql_select_db(self::DB,$this->db); 

        }

        public function insertID()
        {
            return (@mysql_insert_id($this->db));
        }

       
        public function getPrimaryKey($table)
        {
        try
        {
           $sql = "SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'";
           $gp = mysql_query($sql);
           $cgp = mysql_num_rows($gp);
           if($cgp > 0){
                $agp = mysql_fetch_array($gp);
                extract($agp);
                return($Column_name);
            }
        }
        catch(PDOException $e)
        {
            echo 'ERROR: ' . $e->getMessage();
        }
        }

 
        public function getQueryString($tableName, $fields= '*',  $conditionParams, $limit = '', $sort = NULL){

            $query =  "SELECT $fields FROM $tableName";

            if(!empty($conditionParams)){
                $query.= " WHERE ";
                $keys = array_keys($conditionParams);
                for($i= 0; $i<count($keys); $i++){
                    $query.=  $keys[$i];
                    $query.= '=';
                    $query.= ($i == count($keys)-1)? "'".$conditionParams[$keys[$i]]."'" : "'".$conditionParams[$keys[$i]]."'" . ' and ';
                }
            }

            if(isset($sort)){
                $query.= " order by $sort";
            }

            $query.= " $limit ";
               // echo $query;
            return $query;


        }
        

     
        public function selectQuery($tableName, $fields = '*' ,  $conditionParams, $limit = '', $sort = NULL){

            $queryString = $this->getQueryString($tableName, $fields,  $conditionParams, $limit,$sort);

            $sql = mysql_query($queryString, $this->db);
           
            $result = array();
            if(mysql_num_rows($sql) > 0){
                        while($rlt = mysql_fetch_assoc($sql)){
                        $result[] = $rlt;
                     }
                     
                     return $result;
                }
        }


        public function insertQuery($table, $params){
            $query = "INSERT INTO $table(";
        
            $keys = array_keys($params);
            for($i=0;$i<count($keys);$i++)
            {
                $query.= ($i==count($keys)-1)? $keys[$i] : $keys[$i].',';
            }
        
            $query.=") VALUES (";
            for($i=0;$i<count($keys);$i++)
            {
                $query.= ($i==count($keys)-1)? "'".$params[$keys[$i]]."'" : "'".$params[$keys[$i]]."',";
            }
            $query.=")";
           // echo $query;
           
            $sql = mysql_query($query, $this->db);


            return mysql_insert_id($this->db);
        }

        public function updateQuery($table,$updateParams,$conditionParams){
            $query = "UPDATE $table SET ";
        
            $keys=array_keys($updateParams);
            for($i=0;$i<count($keys);$i++)
            {
                $query.= $keys[$i];
                $query.= ' = ';
                $query.= ($i==count($keys)-1)? "'".$updateParams[$keys[$i]]."'" : "'".$updateParams[$keys[$i]]."',";
            }
            
            $query.=" where ";
        
            $conKeys=array_keys($conditionParams);
            for($i=0;$i<count($conKeys);$i++)
            {
                $query.= $conKeys[$i];
                $query.= ' = ';
                $query.= ($i==count($conKeys)-1)? $conditionParams[$conKeys[$i]] : $conditionParams[$conKeys[$i]].' and ';
            }
            
            $sql = mysql_query($query, $this->db);
            
            return mysql_affected_rows($this->db);
        }


        public function deleteQuery($table, $conditionParams){
             $query = "DELETE FROM $table";
            
            if(count($conditionParams)>0){
            $query.= " WHERE "; 
            $keys=array_keys($conditionParams);
            
            for($i=0;$i<count($keys);$i++){
                $query.= $keys[$i];
                $query.= ' = ';
                $query.= ($i==count($keys)-1)? $conditionParams[$keys[$i]] : $conditionParams[$keys[$i]].' and ';
                }
            }
            echo $query;
            $sql = mysql_query($query, $this->db);
            return mysql_affected_rows($this->db);
        }
        }
?>