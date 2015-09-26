<?php
    
    error_reporting(E_ERROR | E_PARSE);
    
    require_once(__DIR__.'/../config/LastFmTokens.php');
    require_once(__DIR__.'/../methods/MusicBrainzMethods.php');
    require_once(__DIR__.'/../methods/php-last.fm-api-master/src/lastfm.api.php');
    require_once(__DIR__.'/../Utilities.php');
    require_once(__DIR__.'/../Logger.php');

    $userArtists = json_decode($_GET['user_artists']);
    $venueArtists = json_decode($_GET['venue_artists']);

    $userLen = sizeof($userArtists);

    // set lastfm api key
    CallerFactory::getDefaultCaller()->setApiKey(LastFmTokens::$tokens[0]['api_key']);

    Logger::getInstance()->log($_GET['user_artists']);
    Logger::getInstance()->log($_GET['venue_artists']);

    $similarity = 0.0;
    $simArr = array();

    foreach($userArtists as $userArtist){
        try{
        $userArt = MusicBrainzMethods::mapArtist($userArtist);
        Logger::getInstance()->log( 'USER ARTIST');
        Logger::getInstance()->log( $userArtist);
        Logger::getInstance()->log( $userArt['id']);
        Logger::getInstance()->log( $userArt['name']);
        if($userArt != null && !empty($userArt['name'])){
            $venueArt = Utilities::in_multiarray($userArt['id'], $venueArtists, 'id');

            if($venueArt !== false){
                if($venueArt->type == 'M'){
                    $similarity += 1/$userLen;
                    Logger::getInstance()->log($userLen);
                    Logger::getInstance()->log('Msimilarity='.$similarity);
                }
                elseif($venueArt->type == 'm'){
                    $similarity += 1/$userLen * 0.9;
                    Logger::getInstance()->log($userLen);
                    Logger::getInstance()->log('msimilarity='.$similarity);
                }
                elseif($venueArt->type == 'T'){
                    $similarity += 1/$userLen * 0.8;
                    Logger::getInstance()->log($userLen.PHP_EOL);
                    Logger::getInstance()->log('Tsimilarity='.$similarity);
                }
            }
            else{
                $similarArtists = Artist::getSimilar($userArt['name']);
                
                foreach($similarArtists as $simArtist){
                    
                    Logger::getInstance()->log('----similars----'.PHP_EOL); 
                    Logger::getInstance()->log($simArtist->getName());     
                    Logger::getInstance()->log($simArtist->getMbid());

                    if(strlen($simArtist->getMbid()) == 0){
                        continue;
                    }

                    $venueArt = Utilities::in_multiarray($simArtist->getMbid(), $venueArtists, 'id');
                    if($venueArt !== false){
                        if($venueArt->type == 'M'){
                            Logger::getInstance()->log('similarity');
                            Logger::getInstance()->log($simArtist->getMatch());
                            Logger::getInstance()->log($userLen);
                            $similarity += 1/$userLen * (float) $simArtist->getMatch();
                            Logger::getInstance()->log(strval($similarity));
                            array_push($simArr, array('mbid' => $simArtist->getMbid(), 'artist' => $simArtist->getName()));
                            break;
                        }
                        elseif($venueArt->type == 'm'){
                            Logger::getInstance()->log( 'similarity');
                            Logger::getInstance()->log( $simArtist->getMatch());
                            Logger::getInstance()->log( $userLen.PHP_EOL);
                            $similarity += 1/$userLen * 0.9 * (float) $simArtist->getMatch();
                            Logger::getInstance()->log( strval($similarity));
                            array_push($simArr, array('mbid' => $simArtist->getMbid(), 'artist' => $simArtist->getName()));
                            break;
                        }
                        elseif($venueArt->type == 'T'){
                            Logger::getInstance()->log('similarity');
                            Logger::getInstance()->log($simArtist->getMatch());
                            Logger::getInstance()->log($userLen);
                            $similarity += 1/$userLen * 0.8 * (float) $simArtist->getMatch();
                            Logger::getInstance()->log(strval($similarity));
                            array_push($simArr, array('mbid' => $simArtist->getMbid(), 'artist' => $simArtist->getName()));
                            break;
                        }
                        
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

?>
