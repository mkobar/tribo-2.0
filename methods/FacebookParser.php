<?php
  
  require_once(__DIR__.'/../config/FacebookTokens.php');
  require_once(__DIR__.'/../methods/FacebookMethods.php');
  require_once(__DIR__.'/../methods/MusicBrainzMethods.php');
  require_once(__DIR__.'/../model/entities/MusicArtist.php');
  require_once(__DIR__.'/../model/entities/FsUserArtist.php');

  class FacebookParser {

    public static $cookie = null;

    public static function getUserMusic($idFsUser, $idFbUser){

      try{

        $fbProfile = FacebookMethods::getUserProfile($idFbUser);

        if($fbProfile != null && isset($fbProfile['profile']['username'])){
        
          $musicHtml = self::curlPage("http://www.facebook.com/".
              $fbProfile['profile']['username']."/music", null,null,null);

          $artists = self::filterBands($musicHtml);
            
          foreach($artists as $artist){
              
            try{
              //map artist (musicbrainz) || get artist genres through echonest???
              $art = MusicBrainzMethods::mapArtist($artist['artist']);

              if($art != null){

                //insert artist (if doesn't exist)
                if(MusicArtist::find($art['id']) == null){
                  $musicArtist = new MusicArtist();
                  $musicArtist->build($art['id'], $artist['artist'], $artist['link']);
                  $musicArtist->save();                            
                }
                //insert user artist
                if(FsUserArtist::whereRaw('id_fs_user = ? and mbid_artist = ?', array($idFsUser, $art['id']))->first() == null){
                  $fsUserArtist = new FsUserArtist();
                  $fsUserArtist->build($idFsUser, $art['id'], 'FB');
                  $fsUserArtist->save();
                }
              }
            }
            catch(Exception $e){
              continue;
            }
          }
        }
      }
      catch(Exception $e){
        return;
      }

    }

    public static function curlPage($url, $header=NULL, $cookie=NULL, $p=NULL){

      if($cookie == null){
        $cookie = self::$cookie;
      }

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_HEADER, $header);
      curl_setopt($ch, CURLOPT_NOBODY, $header);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_COOKIE, $cookie);
      curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      if ($p) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $p);
      }
      $result = curl_exec($ch);

      if ($result) {
        return $result;
      } else {
        return curl_error($ch);
      }
      curl_close($ch);
    }

    public static function curlLogin(){

      if(self::$cookie != null)
        return

      $a = self::curlPage("https://login.facebook.com/login.php?login_attempt=1",true,null,
        "email=".FacebookTokens::$tokens[0]['email']."&pass=".FacebookTokens::$tokens[0]['password']);
      preg_match('%Set-Cookie: ([^;]+);%',$a,$b);
      $c = self::curlPage("https://login.facebook.com/login.php?login_attempt=1",true,$b[1],
        "email=".FacebookTokens::$tokens[0]['email']."&pass=".FacebookTokens::$tokens[0]['password']);
      preg_match_all('%Set-Cookie: ([^;]+);%',$c,$d);
      for($i=0;$i<count($d[0]);$i++)
        self::$cookie.=$d[1][$i].";";

      /*
      NOW TO JUST OPEN ANOTHER URL EDIT THE FIRST ARGUMENT OF THE FOLLOWING FUNCTION.
      TO SEND SOME DATA EDIT THE LAST ARGUMENT.
      */
      self::curlPage("http://www.facebook.com/",null,self::$cookie,null);
    }

    public static function filterBands($html){

      $artistsArr = array();

      $regexArtists = '/<a(?:.(?!<a))*?(M|m)usician\/(B|b)and/';
      preg_match_all($regexArtists, $html, $matches, PREG_SET_ORDER);
      
      //print_r($matches);
      
      //works!!! (hardcoded) -> extend to albums/musics
      foreach($matches as $match){
      
        $regexArtist = '/title="(.*?)"/';
        preg_match($regexArtist, $match[0], $artist);
        
        $regexLink = '/href="(.*?)"/';
        preg_match($regexLink, $match[0], $link);

        array_push($artistsArr, array('artist' => $artist[1], 'link' => $link[1])) ;

      }

      return $artistsArr;

    }

  }
?>