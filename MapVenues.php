<?php

require_once('config/FoursquareTokens.php');
require_once('methods/FoursquareMethods.php');
require_once('model/DbOperations.php');
require_once('Utilities.php');

$latitude = $argv[1];
$longitude = $argv[2];
$writeFile = $argv[3];

$handle_w = fopen($writeFile, "w");

fwrite($handle_w, 'STARTED: '.date('Y-m-d H-i-s').PHP_EOL);
DbOperations::createConnection($handle_w);

try{
    FoursquareMethods::initAPI(FoursquareTokens::$tokens[0]);
}
catch (Exception $e){
    fwrite($handle_w, 'Couldnt initialize API: '.$e.PHP_EOL);
    var_dump('Couldnt initialize API: '.$e);
}

//it could be on a static array
$retCategories = DbOperations::getMusicCategories();

if($retCategories && isset($retCategories['success']) && $retCategories['success'] === true){
    $musicCategories = array();
    while($cat = mysqli_fetch_array($retCategories['result'])) {
       array_push($musicCategories, $cat['id']);
   }
}
else{
    //4d4b7105d754a06376d81259 = nightlife category
    $musicCategories = '4d4b7105d754a06376d81259';
}

try{
    $fsPlaces = FoursquareMethods::venuesSearch($latitude, $longitude, $musicCategories);
}
catch (Exception $e){
    var_dump($e);
    fwrite($handle_w, 'Exception while searching venue: '.$e.PHP_EOL);
}

if($fsPlaces != null){

    foreach($fsPlaces as $fsp){

        //get venue
        try{
            $fsVenue = FoursquareMethods::getVenue($fsp->id);
            $retPlaces = DbOperations::insertFsPlace($fsVenue->id, Utilities::escapeMySql($fsVenue->name),
                $fsVenue->location->lat, $fsVenue->location->lng,
                Utilities::escapeMySql($fsVenue->location->city),
                $fsVenue->location->cc, $fsVenue->location->postalCode,
                Utilities::escapeMySql($fsVenue->location->address),
                Utilities::escapeMySql($fsVenue->location->country),
                $fsVenue->contact->phone, $fsVenue->canonicalUrl,
                $fsVenue->url);

            if(isset($retPlaces['success']) && $retPlaces['success'] === true){

                //insert categories
                foreach($fsVenue->categories as $cat){
                    $retDb = DbOperations::insertFsPlaceCategory($fsVenue->id, 
                        $cat->id, Utilities::escapeMySql($cat->name));
                }

                //get mayor
                DbOperations::insertFsPlaceCustomer($fsVenue->id, $fsVenue->mayor->user->id, '1');

                //insert user (if doesn't exist)
                if(!DbOperations::getFsUser($fsVenue->mayor->user->id)){

                    $fsUser = FoursquareMethods::getUser($fsVenue->mayor->user->id);
                    $retUser = DbOperations::insertFsUser($fsVenue->mayor->user->id,
                        Utilities::escapeMySql($fsUser->firstName), $fsUser->gender, Utilities::escapeMySql($fsUser->homeCity),
                        $fsUser->contact->facebook, $fsUser->contact->twitter);

                }   

                //get tippers
                foreach($fsVenue->tips->groups[0]->items as $tip){
                    DbOperations::insertFsPlaceCustomer($fsVenue->id, $tip->user->id, '0');

                    //insert user (if doesn't exist)
                    if(!DbOperations::getFsUser($tip->user->id)){

                        $fsUser = FoursquareMethods::getUser($tip->user->id);
                        $retUser = DbOperations::insertFsUser($tip->user->id,
                            Utilities::escapeMySql($fsUser->firstName), $fsUser->gender, Utilities::escapeMySql($fsUser->homeCity),
                            $fsUser->contact->facebook, $fsUser->contact->twitter);

                }  
                }
            }
            
        }
        catch(Exception $e){
            var_dump($e);
            fwrite($handle_w, 'Exception while getting venue '.$place->id.': '.$e.PHP_EOL);
        }

    }
}

fwrite($handle_w, 'ENDED: '.date('Y-m-d H-i-s').PHP_EOL);

?>
