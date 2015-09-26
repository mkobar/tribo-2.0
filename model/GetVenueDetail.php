<?php
    
    error_reporting(E_ERROR | E_PARSE);
    
    require_once(__DIR__.'/../config/FacebookTokens.php');
    require_once(__DIR__.'/../config/TwitterTokens.php');
    require_once(__DIR__.'/../methods/TwitterParser.php');
    require_once(__DIR__.'/../methods/FacebookMethods.php');
    require_once(__DIR__.'/../methods/FacebookParser.php');
    require_once(__DIR__.'/../methods/MusicBrainzMethods.php');
    require_once(__DIR__.'/../methods/TwitterMethods.php');
    require_once(__DIR__.'/../config/FoursquareTokens.php');
    require_once(__DIR__.'/../methods/FoursquareMethods.php');
    require_once(__DIR__.'/../model/DbOperations.php');
    require_once(__DIR__.'/../model/entities/FsPlace.php');
    require_once(__DIR__.'/../model/entities/FsCategory.php');
    require_once(__DIR__.'/../model/entities/FsPlaceCategory.php');
    require_once(__DIR__.'/../model/entities/FsUser.php');
    require_once(__DIR__.'/../model/entities/FsPlaceCustomer.php');
    require_once(__DIR__.'/../Utilities.php');
    require_once(__DIR__.'/../Logger.php');

    $venueId = $_GET['venue_id'];
    $update = $_GET['update'];

    Logger::getInstance()->log('GET VENUE DETAIL STARTED: '.date('Y-m-d H-i-s'));

    //DbOperations::createConnection($handle_w);

    //init APIs
    try{
        FoursquareMethods::initAPI(FoursquareTokens::$tokens[0]);
    }
    catch (Exception $e){
        Logger::getInstance()->log('Couldnt initialize FS API: '.$e);
        die;
    }

    try{
        TwitterMethods::initAPI(TwitterTokens::$tokens[0]);
    }
    catch (Exception $e){
        Logger::getInstance()->log('Couldnt initialize TW API: '.$e);
        die;
    }

    try{
        $facebook = FacebookMethods::initAPI(FacebookTokens::$tokens[0]);
    }
    catch (Exception $e){
        Logger::getInstance()->log('Couldnt initialize TW API: '.$e);
        die;
    }

    //check if venue exists in database
    $fsPlace = FsPlace::find($venueId);
    
    //if venue doesn't exist in database
    if(!$fsPlace){
        
        try{
            $fsVenue = FoursquareMethods::getVenue($venueId);
        }
        catch(Exception $e){
            Logger::getInstance()->log('Exception while getting venue '.$venueId.': '.$e);
            die;
        }
        
        $fsPlace = new FsPlace();
        $fsPlace->build($fsVenue->id, Utilities::escapeMySql($fsVenue->name), 
            $fsVenue->location->lat, $fsVenue->location->lng, 
            isset($fsVenue->location->city) ? Utilities::escapeMySql($fsVenue->location->city) : null, 
            isset($fsVenue->location->cc) ? $fsVenue->location->cc : null, 
            isset($fsVenue->location->postalCode) ? $fsVenue->location->postalCode : null, 
            isset($fsVenue->location->address) ? Utilities::escapeMySql($fsVenue->location->address) : null, 
            isset($fsVenue->location->country) ? Utilities::escapeMySql($fsVenue->location->country) : null,
            isset($fsVenue->contact->phone) ? $fsVenue->contact->phone : null , $fsVenue->canonicalUrl, 
            isset($fsVenue->url) ? $fsVenue->url : null, 
            (isset($fsVenue->hours->isOpen) && $fsVenue->hours->isOpen == true) ? '1' : '0');

        $fsPlace->save();

        //insert categories
        foreach($fsVenue->categories as $cat){
            //save category if new
            if(FsCategory::find($cat->id) == null){
                $fsCategory = new FsCategory();
                $fsCategory->build($cat->id, $cat->name);
                $fsCategory->save();
            }

            $fsPlaceCategory = new FsPlaceCategory();
            $fsPlaceCategory->build($fsVenue->id, $cat->id);
            $fsPlaceCategory->save();
        }

        //TIMETABLE?????

         //check if mayor exists
        if(isset($fsVenue->mayor->user->id)){
            
            $user = FoursquareMethods::getUser($fsVenue->mayor->user->id);

            //MAYOR
            //insert user (if doesn't exist)
            if(FsUser::find($fsVenue->mayor->user->id) == null){
                
                $fsUser = new FsUser();
                $fsUser->build($fsVenue->mayor->user->id, Utilities::escapeMySql($user->firstName), 
                    $user->gender, Utilities::escapeMySql($user->homeCity), 
                    isset($user->contact->facebook) ? $user->contact->facebook : null, 
                    isset($user->contact->twitter) ? $user->contact->twitter : null);
                $fsUser->save();
            }

            //insert customer
            $fsPlaceCustomer = new FsPlaceCustomer();
            $fsPlaceCustomer->build($fsVenue->id, $fsVenue->mayor->user->id, 'M');
            $fsPlaceCustomer->save();

            //get music tastes
            FacebookParser::curlLogin();

            if(isset($user->contact->facebook)){
                FacebookParser::getUserMusic($fsVenue->mayor->user->id, $user->contact->facebook);
            }

            if(isset($user->contact->twitter)){
                TwitterParser::getUserMusic($fsVenue->mayor->user->id, $user->contact->twitter);
            }
        }
        
        //TIPPERS
        foreach($fsVenue->tips->groups[0]->items as $tip){

            $user = FoursquareMethods::getUser($tip->user->id);

            //insert user (if doesn't exist)
            if(FsUser::find($tip->user->id) == null){
                
                $fsUser = new FsUser();
                $fsUser->build($tip->user->id, Utilities::escapeMySql($user->firstName), 
                    $user->gender, Utilities::escapeMySql($user->homeCity), 
                    isset($user->contact->facebook) ? $user->contact->facebook : null, 
                    isset($user->contact->twitter) ? $user->contact->twitter : null);
                $fsUser->save();       
            }

            //insert customer
            $fsPlaceCustomer = new FsPlaceCustomer();
            $fsPlaceCustomer->build($fsVenue->id, $tip->user->id, 'T');
            $fsPlaceCustomer->save();

            //get music tastes
            FacebookParser::curlLogin();

            if(isset($user->contact->facebook)){
                FacebookParser::getUserMusic($tip->user->id, $user->contact->facebook);
            }

            if(isset($user->contact->twitter)){
                TwitterParser::getUserMusic($tip->user->id, $user->contact->twitter);
            }
        }
    }

    //if venue already exists in database
    else{

        $fsVenue = FoursquareMethods::getVenue($venueId);

        //UPDATE VENUE DETAILS?????
        
        if($update == '1'){
        //update mayor and tippers
               
            //check if mayor exists
            if(isset($fsVenue->mayor->user->id)){

                $user = FoursquareMethods::getUser($fsVenue->mayor->user->id);
                //MAYOR
                //insert user (if doesn't exist)
                if(FsUser::find($fsVenue->mayor->user->id) == null){
                    $fsUser = new FsUser();
                    $fsUser->build($fsVenue->mayor->user->id, Utilities::escapeMySql($user->firstName), 
                        $user->gender, Utilities::escapeMySql($user->homeCity), 
                        isset($user->contact->facebook) ? $user->contact->facebook : null, 
                        isset($user->contact->twitter) ? $user->contact->twitter : null);
                    $fsUser->save();
                }

                $fsPlaceCustomer = FsPlaceCustomer::whereRaw('id_fs_place = ? and id_user = ?', 
                    array($fsVenue->id, $fsVenue->mayor->user->id))->first();

                //if user/place relation exist, and user was tipper, update to mayor
                if($fsPlaceCustomer != null){
                    if($fsPlaceCustomer->type != 'M'){
                        $fsPlaceCustomer->type = 'M';
                        $fsPlaceCustomer->save();
                    }
                }
                //if user/place relation doesn't exist, write new one as Mayor (verify if there is one)
                else{
                    $fsMayor = FsPlaceCustomer::whereRaw('id_fs_place = ? and type = ?',
                        array($fsVenue->id, 'M'))->first();
                    if($fsMayor != null){
                        $fsMayor->type = 'm';
                        $fsMayor->save();
                    }

                    $fsPlaceCustomer = new FsPlaceCustomer();
                    $fsPlaceCustomer->build($fsVenue->id, $fsVenue->mayor->user->id, 'M');
                    $fsPlaceCustomer->save();
                }

                //get music tastes
                FacebookParser::curlLogin();

                if(isset($user->contact->facebook)){
                    FacebookParser::getUserMusic($fsVenue->mayor->user->id, $user->contact->facebook);
                }

                if(isset($user->contact->twitter)){
                    TwitterParser::getUserMusic($fsVenue->mayor->user->id, $user->contact->twitter);
                }
            }
        

            //TIPPERS
            
            foreach($fsVenue->tips->groups[0]->items as $tip){

                $user = FoursquareMethods::getUser($tip->user->id);
                //insert user (if doesn't exist)
                if(FsUser::find($tip->user->id) == null){
                    $fsUser = new FsUser();
                    $fsUser->build($tip->user->id, Utilities::escapeMySql($user->firstName), 
                        $user->gender, Utilities::escapeMySql($user->homeCity), 
                        isset($user->contact->facebook) ? $user->contact->facebook : null, 
                        isset($user->contact->twitter) ? $user->contact->twitter : null);
                    $fsUser->save();       
                }

                //insert customer
                if(FsPlaceCustomer::whereRaw('id_fs_place = ? and id_user = ?', array($fsVenue->id, $tip->user->id))->first() == null){
                    $fsPlaceCustomer = new FsPlaceCustomer();
                    $fsPlaceCustomer->build($fsVenue->id, $tip->user->id, 'T');
                    $fsPlaceCustomer->save();
                }

                //get music tastes
                if(isset($user->contact->facebook)){
                    FacebookParser::getUserMusic($tip->user->id, $user->contact->facebook);
                }

                if(isset($user->contact->twitter)){
                    TwitterParser::getUserMusic($tip->user->id, $user->contact->twitter);
                }
            }
        }
        //always update isOpen!
        if(isset($fsVenue->hours->isOpen) && $fsVenue->hours->isOpen == true){
            $fsPlace->open = '1';
        }
        else{
            $fsPlace->open = '0';
        }
        $fsPlace->save();
        
    }
    
    $fsPlace = FsPlace::find($venueId);

    //prepare response
    $customers = $fsPlace->customers()->getResults();
    $artists = array();

    foreach($customers as $cust){
        $cust->artists = $cust->artists()->getResults()->toArray();
    }

    $fsPlace->customers = $customers->toArray();

    header('Content-type: application/json');
    echo $_GET['callback']; 
    echo "(".$fsPlace.")";

?>
