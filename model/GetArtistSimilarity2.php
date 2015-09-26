<?php
    
    error_reporting(E_ERROR | E_PARSE);
    
    require_once(__DIR__.'/../config/LastFmTokens.php');
    require_once(__DIR__.'/../methods/MusicBrainzMethods.php');
    require_once(__DIR__.'/../methods/php-last.fm-api-master/src/lastfm.api.php');
    require_once(__DIR__.'/../Utilities.php');

    $userArtists = json_decode($_GET['user_artists']);
    $venueArtists = json_decode($_GET['venue_artists']);

    $userLen = sizeof($userArtists);

    // set lastfm api key
    CallerFactory::getDefaultCaller()->setApiKey(LastFmTokens::$tokens[0]['api_key']);
    
    $writeFile = __DIR__.'/../venues_report.txt';
    $handle_w = fopen($writeFile, "w");
    
    $similarity = 0.0;
    $simArr = array();

    foreach($userArtists as $userArtist){
        try{
        $userArt = MusicBrainzMethods::mapArtist($userArtist);
        fwrite($handle_w, $userArtist.PHP_EOL);
        fwrite($handle_w, $userArt['id'].PHP_EOL);
        fwrite($handle_w, $userArt['name'].PHP_EOL);
        if($userArt != null && !empty($userArt['name'])){
            if($venueArt = Utilities::in_multiarray($userArt['id'], $venueArtists, 'id') !== false){
                if($venueArt['type'] == 'M'){
                    $similarity += 1;
                }
                elseif($venueArt['type'] == 'm'){
                    $similarity += 1 * 0.9;
                }
                elseif($venueArt['type'] == 'T'){
                    $similarity += 1 * 0.8;
                }
            }
            else{
                $similarArtists = Artist::getSimilar($userArt['name']);
                
                foreach($similarArtists as $simArt){  
                    fwrite($handle_w, '----similars----'.PHP_EOL);              
                    fwrite($handle_w, $simArt->getName().PHP_EOL);
                    $venueArt = Utilities::in_multiarray($simArt->getMbid(), $venueArtists, 'id');
                    if($venueArt !== false){
                        if($venueArt->type == 'M'){
                            $similarity += 1 * (float) $simArt->getMatch();
                            fwrite($handle_w, strval($similarity).PHP_EOL);
                        }
                        elseif($venueArt->type == 'm'){
                            $similarity += 1 * 0.9 * (float) $simArt->getMatch();
                            fwrite($handle_w, strval($similarity).PHP_EOL);
                        }
                        elseif($venueArt->type == 'T'){
                            $similarity += 1 * 0.8 * (float) $simArt->getMatch();
                            fwrite($handle_w, strval($similarity).PHP_EOL);
                        }
                        fwrite($handle_w, $similarity);
                        array_push($simArr, array('mbid' => $simArt->getMbid(), 'artist' => $simArt->getName()));
                    }
                }
            }
        }     
    } catch (Exception $e){
        continue;
    }
    }

    header('Content-type: application/json');
    echo $_GET['callback']; 
    echo "(".$similarity.")";
    echo "(".json_encode($simArr).")";
    //echo "(".json_encode(array_unique($simArr)).")";

?>
