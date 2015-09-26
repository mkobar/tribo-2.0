<?php

set_time_limit(0);
error_reporting(E_ALL);
ob_implicit_flush(TRUE);
ob_end_flush();

require_once('config/FacebookTokens.php');
require_once('config/TwitterTokens.php');
require_once('methods/TwitterParser.php');
require_once('methods/FacebookMethods.php');
require_once('methods/FacebookParser.php');
require_once('methods/MusicBrainzMethods.php');
require_once('methods/TwitterMethods.php');
require_once('model/DbOperations.php');
require_once('Utilities.php');

//$writeFile = $argv[1];
$writeFile = '/var/www/tribo-2.0/venues_report.txt';

$handle_w = fopen($writeFile, "w");

$twCalls = 0;
$twTokInd = 0;

//fwrite($handle_w, 'STARTED: '.date('Y-m-d H-i-s').PHP_EOL);
print_r('STARTED: '.date('Y-m-d H-i-s').PHP_EOL);
DbOperations::createConnection($handle_w);

$facebook = FacebookMethods::initAPI(FacebookTokens::$tokens[0]);

TwitterMethods::initAPI(TwitterTokens::$tokens[$twTokInd]);

$retUsers = DbOperations::getUsers();

if($retUsers && isset($retUsers['success']) && $retUsers['success'] === true){
	FacebookParser::curlLogin();
	
    while($row = mysqli_fetch_assoc($retUsers['result'])) {

        //reinit twitter token?
        if($twCalls >= TwitterTokens::MAX_REQ){

            $twTokInd++;
            fwrite($handle_w, 'Changing Twitter API index to: '.$twTokInd.PHP_EOL);

            if(!isset(TwitterTokens::$tokens[$twTokInd])){
                $twTokInd = 0;
            }
            try{
              TwitterMethods::initAPI(TwitterTokens::$tokens[$twTokInd]);
              $twCalls = 0;
            }
            catch (Exception $e){
                fwrite($handle_w, 'Couldnt initialize Twitter API: '.$e.PHP_EOL);
                var_dump('Couldnt initialize API: '.$e);
            }
        }

      //search music tastes on facebook
    	if($row['fb_contact'] != null){

    	try{
    		
    	   $user_profile = FacebookMethods::getUserProfile($row['fb_contact']);

           if($user_profile != null && isset($user_profile['profile']['username'])){
            fwrite($handle_w, $row['fb_contact'].PHP_EOL);
            fwrite($handle_w, $user_profile['profile']['username'].PHP_EOL);

        	$musicHtml = FacebookParser::curlPage("http://www.facebook.com/".$user_profile['profile']['username']."/music",
        				null,null,null);
           	$artists = FacebookParser::filterBands($musicHtml);
            
           	foreach($artists as $artist){
              
               try{
                 //map artist (musicbrainz) || get artist genres through echonest???
                 $art = MusicBrainzMethods::mapArtist($artist['artist']);
                 if($art != null){
                    $retArt = DbOperations::insertMusicArtist($art['id'], $artist['artist'], $artist['link']);
                    $retUserArt = DbOperations::insertFsUserArtist($row['id'], $artistMbid, 'FB');
                 }
               }
               catch(Exception $e){
                 fwrite($handle_w, $e.PHP_EOL);
                 continue;
               }

           	}
           }
         }
     	catch(Exception $e){
     		fwrite($handle_w, $e.PHP_EOL);
     	}
      }

      //search music tastes on twitter
      if($row['tw_contact'] != null){
        //print_r($row['tw_contact']);
        $twCalls++;
        $userTimeline = TwitterMethods::getUserTimeline($row['tw_contact']);
        
        if($userTimeline != null && (is_array($userTimeline) && !isset($userTimeline['error'])
            && !isset($userTimeline['errors']))){

            foreach($userTimeline as $userTweet){
            
                if(Utilities::strfind_arr(strtolower($userTweet->text), TwitterTokens::$musicTags)){
                    fwrite($handle_w, $row['tw_contact'].PHP_EOL);
                    fwrite($handle_w, $userTweet->text.PHP_EOL);
                    $artists = TwitterParser::filterBands($userTweet->text);
                    print_r($artists);
                    foreach($artists as $artist){
                         $retArt = DbOperations::insertMusicArtist($artist['mbid'], $artist['artist'], $artist['link']);
                         $retUserArt = DbOperations::insertFsUserArtist($row['id'], $artist['mbid'], 'TW');
                    }
                }
            }
        }

      }

   	}
}

print_r('ENDED: '.date('Y-m-d H-i-s').PHP_EOL);
?>
