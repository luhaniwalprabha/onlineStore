<?php

include_once("db.inc.php");
require_once("API.php");
require_once("ResourceData.php");
require_once("ApiUtility.php");


class MyAPI extends API{		
	private $db;
	private $resourceData;

    public function __construct($request, $db, $resourceData) {
       	parent::__construct($request);

       	$this->db = $db;
       	$this->resourceData = $resourceData;
       	$this->authenticate();

    }

	function authenticate(){
		if(!isset($_SERVER['PHP_AUTH_USER'])){
			header('WWW-Authenticate: Basic realm="Please login with admin credentials."');
    		header('HTTP/1.0 401 Unauthorized');
    		echo 'Authentication Failed';
            exit;
		}
		 $userData=Array();
		$userData['username'] = $_SERVER['PHP_AUTH_USER'];
		 $userData['password'] = $_SERVER['PHP_AUTH_PW'];
		//  $userData['username'] = 'prabha';
		// $userData['password'] = '123456';

		 

		 if(!($this->authorizedUser($userData))){
		 	header('WWW-Authenticate: Basic realm="User Credentials are incorrect."');
    		header('HTTP/1.0 401 Unauthorized');
    		echo 'Authentication Failed';
            exit;

		 }
	}

	function authorizedUser($userData)
     {
        $resultArray = $this->db->selectQuery('users','*',$userData,'',null);
        return (count($resultArray)==0) ? false : true;
     }


	function APIcontroller(){
		// $this->resource = '/categories/1';
		$resourceHierarchy = explode('/',$this->resource);

		 	$count = count($resourceHierarchy);
		 	//$this->resourceData->UrlCollectionName = 'categories';
		 	//$this->resourceData->tableName = 'categories';


		if(is_numeric($resourceHierarchy[$count-1])){ 
			if($resourceHierarchy[$count-2] ==  $this->resourceData->UrlCollectionName){

            	switch($this->method){

					Case 'GET'  	:   
								    	$this->getResource($resourceHierarchy[$count-1]); 
								    	break;

					Case 'POST' 	:
										$this->_response(array('error'=>'Method not allowed on this resource!','405'));
										break;

					Case 'PUT' 		:
										$this->updateResource($resourceHierarchy[$count-1]);
										break;

					Case 'DELETE'   :
										$this->deleteResource($resourceHierarchy[$count-1]);
										break;

					default   		:
										$this->_response(array('error'=>'Any other method not allowed!','405'));
										break;

				}
			}else {
             
                echo $this->_response(array('error' => "Bad Request"),'400');
				}

        } else{

		if($resourceHierarchy[$count-1] == $this->resourceData->UrlCollectionName){

				switch($this->method){

					Case 'GET'  	:   
								    	$this->getResource(); 
								    	break;

					Case 'POST' 	:
										$this->insertResource();
										break;

					Case 'PUT' 		:
										$this->_response(array('error'=>'Method not allowed on Collection!','405'));
										break;

					Case 'DELETE'   :
										$this->_response(array('error'=>'Method not allowed on Collection!','405'));
										break;

					default   		:
										$this->_response(array('error'=>'Any other method not allowed!','405'));
										break;


				}
			}else {
             
                echo $this->_response(array('error' => "Bad Request"),'400');
				}	
       		}
		}


	function GetResource($id=NULL){ 
		$fields = '*';
		$page = 1;
		$per_page =10;
		$sort = $this->resourceData->primaryKey.' asc';
		$conditionParams = array();

		if($id!= NULL){  
			$conditionParams[$this->resourceData->primaryKey] = $id;
			$resultArray = $this->db->selectQuery($this->resourceData->tableName, '*', $conditionParams, '', NULL);

			if(empty($resultArray)){ 
				echo $this->_response(array('error' => "Resouces Not Found."), '404');
				exit;
			} 
		}


		if(array_key_exists('sort', $this->queryParams)){
			$sort = ApiUtility::sortSerialize($this->queryParams['sort']);
			unset($this->queryParams['sort']); 
		}


		if(array_key_exists('fields', $this->queryParams)){
			$fields = $this->queryParams['fields'];
			unset($this->queryParams['fields']); 

		}

		
		if(array_key_exists('page', $this->queryParams)){
			$page = $this->queryParams['page'];
			unset($this->queryParams['page']); 

		}

		if(array_key_exists('per_page', $this->queryParams)){
			$per_page = $this->queryParams['per_page'];
			unset($this->queryParams['per_page']); 

		}

		$limit = 'limit ' .(($page-1)*$per_page).','.$per_page;

		if(array_key_exists('request', $this->queryParams)){
			unset($this->queryParams['request']); 
		}

		array_values($this->queryParams);

		if(count($this->queryParams)>0){
			$paramsKey = array_keys($this->queryParams);
			for($i=0; $i<count($paramsKey);$i++){
			$conditionParams[$paramsKey[$i]] = $this->queryParams[$paramsKey[$i]];
			}
		}
		
		echo $this-> _response($this->db->selectQuery($this->resourceData->tableName, $fields, $conditionParams, $limit, $sort),'200');
	}


	function insertResource(){ 
		$fileArray = json_decode($this->file,true);
	
		$fileArray = ApiUtility::fetchSingleDimentionalArray($fileArray);
		//print_r($array); exit;

		$insertId = $this->db->insertQuery($this->resourceData->tableName,$fileArray);

		if($insertId)
        {
        $conditionParams = array();
        $conditionParams[$this->resourceData->primaryKey] = $insertId;
        $sort = $this->resourceData->primaryKey.' asc';
        
        echo $this->_response($this->db->selectQuery($this->resourceData->tableName,'*',$conditionParams,'limit 0,10',$sort),'201');
        }
        else {
          echo $this->_response(array('error' => "resource could not get inserted"),'400');
        }

	}

	function updateResource($id = NULL){
		$fileArray = json_decode($this->file,true);
		$fileArray = ApiUtility::fetchSingleDimentionalArray($fileArray);
		// if($fileArray[$this->resourceData->primaryKey]!=$id)
  //       {
  //               $this->_response(array('error' => "Bad Request"),'400');
  //               exit;
  //       }
        $conditionParams = array();
        $conditionParams[$this->resourceData->primaryKey]=$id;
        
        $resultArray = $this->db->selectQuery($this->resourceData->tableName,'*',$conditionParams,'',NULL);
        if(!empty($resultArray))
        {
            $count = $this->db->updateQuery($this->resourceData->tableName,$fileArray,$conditionParams);
            
            if($count>0)
            {
                echo $this->_response($this->db->selectQuery($this->resourceData->tableName,'*',$conditionParams,'limit 0,10',$this->resourceData->primaryKey.' asc'),'200');
            }
            else 
            {
                echo $this->_response(array('error' => "resource could not get updated"),'400');
            }
        }
        else {
           echo $this->_response(array('resource' => "not found"),'404');
        }

	}

	function deleteResource($id){
        $conditionParams = array();
        $conditionParams[$this->resourceData->primaryKey]=$id;
        
       	$resultArray = $this->db->selectQuery($this->resourceData->tableName,'*',$conditionParams,'',NULL);
        if(!empty($resultArray))
        {
            $count = $this->db->deleteQuery($this->resourceData->tableName,$conditionParams);
            if($count==0)
            {
            $this->_response(array('error' => "Could not get deleted"),'400');
            }
            else 
            {
               echo $this->_response(array('deleted' => "true"),'200');
            }
        }
        else {
            echo $this->_response(array('error' => "resource not found"),'404');
        }
    }

}

	try { 
            
            $db = new DB();

            $resourceObj = new ResourceData('categories', $db, 'categories');
           
            $APIobj = new MyAPI($_SERVER['PATH_INFO'], $db, $resourceObj);
            
            $APIobj->processAPI();
        } 
    
        catch (Exception $e) {
            echo json_encode(Array('error' => $e->getMessage()));
        }
?>			



