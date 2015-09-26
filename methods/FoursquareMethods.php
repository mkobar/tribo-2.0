<?php

require_once('php-foursquare-master/src/FoursquareAPI.class.php');

class FoursquareMethods {

    static $foursquareAPI;

    public static function initAPI($token){
        self::$foursquareAPI = new FoursquareAPI($token['client_key'], $token['client_secret']);
        self::$foursquareAPI->SetAccessToken($token['access_token']);
    }

    public static function venuesSearch($latitude, $longitude, $categoryId = null, $query = null){

      try {
        $params = array("ll" => $latitude.",".$longitude);

        if($query != null){
          $params['query'] = $query;
        }

        if($categoryId != null){
          if(is_array($categoryId)){
            $params['categoryId'] = implode(",", $categoryId);
          }
          else{
            $params['categoryId'] = $categoryId;
          }
        }

        //default to intent check in and max limit
        $params['intent'] = ' checkin';
        $params['limit'] = '50';
        $params['radius'] = '500';

        $response = self::$foursquareAPI->GetPublic("venues/search",$params);
        
        $arrResp = json_decode($response);

        return $arrResp->response->venues;
      }
      catch(Exception $e){
        throw $e;
      }
    }

    public static function getVenue($venueId){

      try {

        $params = array();
        
        $response = self::$foursquareAPI->GetPublic("venues/".$venueId,$params);
        $arrResp = json_decode($response);
        return $arrResp->response->venue;
      }
      catch(Exception $e){
        throw $e;
      }
    }

    public static function getUser($userId){

      try {

        $params = array();
        
        $response = self::$foursquareAPI->GetPrivate("users/".$userId);
        $arrResp = json_decode($response);
        return $arrResp->response->user;
      }
      catch(Exception $e){
        throw $e;
      }
    }

}

?>
