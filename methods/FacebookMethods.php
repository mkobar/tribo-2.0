<?php

require_once('facebook-php-sdk-master/src/facebook.php');

class FacebookMethods {

    static $facebookAPI;

    public static function initAPI($token){
        
        self::$facebookAPI = new Facebook(array(
          'appId'  => $token['app_id'],
          'secret' => $token['app_secret']
        ));

        //self::$facebookAPI->setAccessToken($token['access_token']);
        
        return self::$facebookAPI;
    }

    public static function getMyLikes(){
        $user_likes = self::$facebookAPI->api('/me/likes');
        return $user_likes;
    }

    public static function getUserProfile($userId){
        //self::$facebookAPI->setAccessToken("CAACEdEose0cBADZA8XahPtY7ZBfGQ7g9rDyyIuANB4ooxGHOeRU2flFAPrbaEZCfRxMM24pVxXGKXeEZB7iNl1BMEzebqol2By3nvy5ZCiBFvjXUkzfTuy9hnDKD8iT8qUyqFgCQG36rNFPy4OD2TG6JvJuaV9H6vlBKd59jxU6IxPeEaeJIUiPdNYrrSVSYZD");

        try {

            $user_profile = self::$facebookAPI->api('/'.$userId);
            
            //not returning likes and music
            $user_likes = self::$facebookAPI->api('/'.$userId.'/likes');
            $user_music = self::$facebookAPI->api('/'.$userId.'/music');
            
            return array('profile' => $user_profile, 'likes' => $user_likes, 'music' => $user_music);
        }
        catch(Exception $e){
            throw $e;
        }
    } 
}

?>