<?php

class Utilities {

  public static function escapeMySql($str){
    //single quotes
    return str_replace("'", "''", $str);
  }

  public static function formatDate($dateStr){
    $dateArr = explode(' ', $dateStr);
    $date = $dateArr[2].' '.$dateArr[1].' '.$dateArr[5];
    $time = strtotime($date);
    $mysqldate = date('Y-m-d', $time);  
    return $mysqldate;
  }

  public static function formatUrlSpaces($str){
    $str = preg_replace('/\s+/', '%20', $str);
    return $str;
  }

  public static function strfind_arr($str, $arr) {
    foreach($arr as $needle) {
        if(strpos($str, $needle)!==false) return true;
    }
    return false;
  }

  public static function in_multiarray($needle, $arr, $ind){
    
    //$writeFile = __DIR__.'/venues_report.txt';
    //$handle_w = fopen($writeFile, "w");
    //fwrite($handle_w, 'needle'.PHP_EOL);
    //fwrite($handle_w, $needle.PHP_EOL);
    //fwrite($handle_w, 'list'.PHP_EOL);
    foreach($arr as $item){
      //fwrite($handle_w, json_encode($item).PHP_EOL);
      if($item->$ind == $needle){
        return $item;
      }
    }
    return false;
  }
    
	
}

?>
