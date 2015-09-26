<?php
    
    error_reporting(E_ERROR | E_PARSE);
    
    require_once(__DIR__.'/../config/FoursquareTokens.php');
    require_once(__DIR__.'/../methods/FoursquareMethods.php');
    require_once(__DIR__.'/../model/DbOperations.php');
    require_once(__DIR__.'/../model/entities/FsMusicCategory.php');
    require_once(__DIR__.'/../Utilities.php');
    require_once(__DIR__.'/../Logger.php');

    $latitude = $_GET['lat'];
    $longitude = $_GET['lng'];

    Logger::getInstance()->log('GET VENUES STARTED: '.date('Y-m-d H-i-s'));

    //  DbOperations::createConnection($handle_w);

    try{
        FoursquareMethods::initAPI(FoursquareTokens::$tokens[0]);
    }
    catch (Exception $e){
        Logger::getInstance()->log('Couldnt initialize API: '.$e);
    }

    //it could be on a static array
    //$retCategories = DbOperations::getMusicCategories();
    $musicCategories = FsMusicCategory::all()->modelKeys();

    if(sizeof($musicCategories) == 0){
        //4d4b7105d754a06376d81259 = nightlife category
        $musicCategories = '4d4b7105d754a06376d81259';
    }

    try{
        $fsPlaces = FoursquareMethods::venuesSearch($latitude, $longitude, $musicCategories);
    }
    catch (Exception $e){
        Logger::getInstance()->log('Exception while mapping venues: '.$e);
    }

    header('Content-type: application/json');
    echo $_GET['callback']; 
    echo "(" . json_encode($fsPlaces) . ")";

?>
