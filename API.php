<?php 
require_once 'ApiUtility.php';

abstract class API
{
    
    
    protected $method = '';
    protected $resource = '';
    protected $queryParams = Array();
    protected $file = Null;

    public function __construct($request) {
       
        header("Content-Type: application/json");
        $this->resource = rtrim($request,'/');


        //$this->method= 'POST';

      $this->method = $_SERVER['REQUEST_METHOD'];



        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }

        switch($this->method) {

        case 'GET':
                        $this->queryParams = ApiUtility::_cleanInputs($_GET);
                        break;

        case 'POST':       
                        $this->file = file_get_contents("php://input");
                        if(!(ApiUtility::isJson($this->file))){
                        $this->_response(array('error'=>'Input not in correct format','415'));
                        exit;
                        }
                        break;

        
        case 'PUT':
                        $this->file = file_get_contents("php://input");
                        if(!(ApiUtility::isJson($this->file))){
                        $this->_response(array('error'=>'Input not in correct format','415'));
                        exit;
                        }
                        break;
        
        case 'DELETE' : 
                        break;

        default:
                        $this->_response('Invalid Method', 405);
                        break;
        }

    }


    function processAPI() { 
        $this->APIcontroller();
    }

    function _response($data, $status = 200) {

        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        return json_encode($data);
    }

    
    function _requestStatus($code) {
        $status = array( 
            200 => 'OK', 
            201 => 'Created',   
            204 => 'No Content',   
            400 => 'Bad Request',   
            401 => 'Unauthorized',   
            404 => 'Not Found',   
            405 => 'Method Not Allowed',  
            409 => 'Conflict', 
            415 => 'Unsupported Media Type',   
            500 => 'Internal Server Error'   
            ); 
          
        return ($status[$code])?$status[$code]:$status[500]; 
    }
}
?>