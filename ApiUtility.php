
<?php
class ApiUtility
{    

	public static function isJson($string) 
        {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }
     
    public static function fetchSingleDimentionalArray($array){
            $firstarray=Array();
             $flag=false;
            if(array_key_exists('0', $array))
            {
                $flag=true;
                $keys=array_keys($array[0]);
                for($i=0;$i<count($array[0]);$i++)
                {
                $firstarray[$keys[$i]]=$array[0][$keys[$i]];
                }
            }
        
            if($flag==true)
            {
            $array=$firstarray;
            }
            return $array;

        }
       
 

	public static function sortSerialize($string)
     {
        $sort='';
        $temp=explode(',',$string);
            
        for($i=0;$i<count($temp);$i++)
        {
            $temp2 = str_split($temp[$i]);
            if($temp2[0]=='-')
            {
                array_shift($temp2);
                $sort = ($i==count($temp)-1) ? $sort.implode($temp2).' desc ' : $sort.implode($temp2).' desc ,';
            }   
            else 
            {
                $sort = ($i==count($temp)-1) ? $sort.implode($temp2).' asc ' : $sort.implode($temp2).' asc ,';
            }           
        }
        return $sort;   
     }

    public static function _cleanInputs($data) { 

        $clean_input = Array();

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = ApiUtility::_cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }


    }

 ?>