<?php

require_once('TwitterAPIExchange.php');

class TwitterMethods {

    static $twitterAPI;

    public static function initAPI($token){
         self::$twitterAPI = new TwitterAPIExchange($token);
    }

    public static function getUserTimeline($screenName){

      try{
        $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
        $requestMethod = 'GET';
        $getfield = '?screen_name='.$screenName;
        
        $timeline = self::$twitterAPI->setGetfield($getfield)
              ->buildOauth($url, $requestMethod)
              ->performRequest();  

        return json_decode($timeline);
      }
      catch(Exception $e){
        throw $e;
      }
    }
	
}

?>
