<?php
  
  require_once(__DIR__.'/../config/TwitterTokens.php');
  require_once(__DIR__.'/../methods/MusicBrainzMethods.php');
  require_once(__DIR__.'/../Utilities.php');

  class TwitterParser {

    public static function getUserMusic($idFsUser, $idTwUser){

      $userTimeline = TwitterMethods::getUserTimeline($idTwUser);
        
        if($userTimeline != null && (is_array($userTimeline) && !isset($userTimeline['error'])
            && !isset($userTimeline['errors']))){

            foreach($userTimeline as $userTweet){
            
                if(Utilities::strfind_arr(strtolower($userTweet->text), TwitterTokens::$musicTags)){
                    $artists = self::filterBands($userTweet->text);
    
                    foreach($artists as $artist){
                      
                      //insert artist (if doesn't exist)
                      if(MusicArtist::find($artist['mbid']) == null){
                        $musicArtist = new MusicArtist();
                        $musicArtist->build($artist['mbid'], $artist['artist'], $artist['link']);
                        $musicArtist->save();                            
                      }
                      //insert user artist
                      if(FsUserArtist::whereRaw('id_fs_user = ? and mbid_artist = ?', array($idFsUser, $artist['mbid']))->first() == null){
                        $fsUserArtist = new FsUserArtist();
                        $fsUserArtist->build($idFsUser, $artist['mbid'], 'TW');
                        $fsUserArtist->save();
                      }
                    }
                }
            }
        }
    }

    public static function filterBands($twText){

      //song title – artist name FORMAT (CONSIDER INVERSE FORMAT IN CASE MUSIC BRAINZ DOESN'T HAVE PERFECT MATCH???)
      //use FREEBASE???
      $regexArtist = '/(\.|)(.*?)\s–\s(.*)\s/';
      preg_match($regexArtist, $twText, $matches);

      if(is_array($matches) && sizeof($matches) > 0){
        
        $artistChunk = $matches[sizeof($matches)-1];
        //check link  
        $regexLink = '/(.*)http(.*)/';
        preg_match($regexLink, $artistChunk, $matches);

        if(is_array($matches) && sizeof($matches) > 0){
          $artistName = $matches[1];
          $artistLink = $matches[2];
        }
        else{
          $artistName = trim($artistChunk);
        }

        try{
          //map with musicbrainz
          $art = MusicBrainzMethods::mapArtist($artistName);

          if($art != null){
            if($artistLink != null){
              return array(array('mbid' => $art['id'], 'artist' => $artistName, 'link' => 'http'.$artistLink));
            }
            else{
              return array(array('mbid' => $art['id'], 'artists' => $artistName, 'link' => null));
            }
          }
          //else{
          //  return null;
          //}
        }
        catch(Exception $e){
          // write log file???
        }
      }

      //song title by artist name FORMAT
      $regexArtist = '/by\s(.*)/';
      preg_match($regexArtist, $twText, $matches);

      if(is_array($matches) && sizeof($matches) > 0){
        
        $artistChunk = $matches[sizeof($matches)-1];
        //check link  
        $regexLink = '/(.*)http(.*)/';
        preg_match($regexLink, $artistChunk, $matches);

        if(is_array($matches) && sizeof($matches) > 0){
          $artistName = $matches[1];
          $artistLink = $matches[2];
        }
        else{
          $artistName = trim($artistChunk);
        }

        try{
          //map with musicbrainz
          $art = MusicBrainzMethods::mapArtist($artistName);

          if($art != null){
            if($artistLink != null){
              return array(array('mbid' => $art['id'], 'artist' => $artistName, 'link' => 'http'.$artistLink));
            }
            else{
              return array(array('mbid' => $art['id'], 'artist' => $artistName, 'link' => null));
            }
            }
        }
        catch(Exception $e){
          // write log file???
        }
      }

      //Artists: artist1 (x), artist2 (y), ... FORMAT
      $regexArtistsList = '/Artists:\s(.*)/';
      preg_match($regexArtistsList, $twText, $matches);

      if(is_array($matches) && sizeof($matches) > 0){
        
        $artistsList = $matches[1];
        $artistsArr = array();

        $regexArtists = '/(.*?)\(\d*\)/';
        preg_match_all($regexArtists, $artistsList, $matches);
        
        $regexArtist = '/(&amp;|&|,|^)(.*)/';
        foreach($matches[sizeof($matches)-1] as $match){
          
          preg_match($regexArtist, trim($match), $artistFiltered);
          $artist = $artistFiltered[sizeof($artistFiltered)-1];
          try{
            //map with musicbrainz
            $art = MusicBrainzMethods::mapArtist($artist);
            if($art != null){
              array_push($artistsArr, array('mbid' => $art['id'], 'artist' => $artist, 'link' => null));
            }
          }
          catch(Exception $e){
            continue;
            //write log file
          }
        }

        return $artistsArr;
      }

      return null;

    }

  }
?>