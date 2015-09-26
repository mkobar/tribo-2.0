<?php

  require_once(__DIR__.'/../Utilities.php');

  class MusicBrainzMethods {

    public static function mapArtist($artist){

    	try{
        	$url = 'http://musicbrainz.org/ws/2/artist/?query=artist:'.urlencode($artist);
            
        	$response = file_get_contents($url);
        	$xml = new SimpleXMLElement($response);

            if(sizeof($xml->{'artist-list'}) > 0){
                return array('id' => (string) $xml->{'artist-list'}[0]->artist['id'],
                    'name' => (string) $xml->{'artist-list'}[0]->artist->name);
            }
            else{
                return null;
            }
            
        }
        catch (Exception $e){
        	return null;                                                                                                                                                                                          
        }                                                                                                                                                       

    }
  }
?>                                                                                             