<?php

require_once(__DIR__.'/../config/DbConfig.php');

class DbOperations {

    static $conn;
    static $logFile;

    public static function createConnection($logFile){
        
        self::$conn = mysqli_connect(DbConfig::$host, DbConfig::$user, DbConfig::$pass, DbConfig::$db);
        self::$logFile = $logFile;

        if (mysqli_connect_errno())
        {   
            $ret = array('success' => false, 'error' => "Failed to connect to MySQL: " . mysqli_connect_error());
            print_r($ret['error']);
            fwrite(self::$logFile, $ret['error'].PHP_EOL);
            return $ret; 
        }
    }

    public static function cleanData(){

        $sql = "DELETE FROM music_tweets;DELETE FROM foursquare_tweets;DELETE FROM foursquare_places_categories;DELETE FROM foursquare_places;DELETE FROM users";

        try{

        if (mysqli_query(self::$conn,$sql))
        {   
            return array('success' => true);
        }
        else{
            $ret = array('success' => false, 'error' => 'Error deleting tables: ' . mysqli_error(self::$conn));
            print_r($ret['error']);
            fwrite(self::$logFile, $ret['error'].PHP_EOL);
            return $ret;
        }

        } catch(Exception $e){
            var_dump('Exception while deleting tables: '.$e);
            fwrite(self::$logFile, 'Exception while deleting tables: '.$e.PHP_EOL);
        }
    }

    public static function getMusicCategories(){

        $sql = "SELECT id from fs_music_categories;";

        try{

        if ($result = mysqli_query(self::$conn,$sql))
        {   
            return array('success' => true, 'result' => $result);
        }
        else{
            $ret = array('success' => false, 'error' => 'Error getting music categories tables: ' . mysqli_error(self::$conn));
            print_r($ret['error']);
            fwrite(self::$logFile, $ret['error'].PHP_EOL);
            return $ret;
        }

        } catch(Exception $e){
            var_dump('Exception while getting music categories: '.$e);
            fwrite(self::$logFile, 'Exception while deleting tables: '.$e.PHP_EOL);
            $ret = array('success' => false, 'error' => 'Error getting music categories tables: ' . mysqli_error(self::$conn));
            return $ret;
        }
    }

    public static function insertFsPlace($idPlace, $venueName, $latitude, $longitude, $city, $countryCode, 
        $postalCode = null, $address = null, $country = null, $phoneNumber = null, $urlFs = null, $url = null){

        $sql = "INSERT INTO fs_places values ('".$idPlace."','".$venueName."',".$latitude.",".$longitude.", 
            '".$city."','".$countryCode."'";
        
        if($postalCode != null){
            $sql = $sql.",'".$postalCode."'";
        }
        else{
            $sql = $sql.",null";   
        }

        if($address != null){
            $sql = $sql.",'".$address."'";
        }
        else{
            $sql = $sql.",null";   
        }

        if($country != null){
            $sql = $sql.",'".$country."'";
        }
        else{
            $sql = $sql.",null";   
        }

        if($phoneNumber != null){
            $sql = $sql.",'".$phoneNumber."'";
        }
        else{
            $sql = $sql.",null";   
        }

        if($urlFs != null){
            $sql = $sql.",'".$urlFs."'";
        }
        else{
            $sql = $sql.",null";   
        }

        if($url != null){
            $sql = $sql.",'".$url."'";
        }
        else{
            $sql = $sql.",null";   
        }

        //close statement
        $sql = $sql.");";

        try{

        $result = mysqli_query(self::$conn,$sql);
        print_r(mysql_result($result, 1));
        die;

        if (mysqli_query(self::$conn,$sql))
        {
            return array('success' => true);
        }
        else{
            $ret = array('success' => false, 'error' => 'Error inserting foursquare place: ' . mysqli_error(self::$conn));
            print_r($ret['error']);
            fwrite(self::$logFile, $ret['error'].PHP_EOL);
            return $ret;
        }

        } catch(Exception $e){
            fwrite(self::$logFile, 'Exception while inserting foursquare place: '.$e.PHP_EOL);
            var_dump('Exception while inserting foursquare place: '.$e);
            $ret = array('success' => false, 'error' => 'Error inserting foursquare place: ' . mysqli_error(self::$conn));
            return $ret;
        }
    }

    public static function insertFsPlaceCategory($idPlace, $idCategory, $catDescription){

        $sql = "INSERT INTO fs_places_categories values(null,'".$idPlace."','".$idCategory."','".$catDescription."')";
        
        try{

        if (mysqli_query(self::$conn,$sql))
        {
            return array('success' => true);
        }
        else{
            $ret =  array('success' => false, 'error' => 'Error inserting place category: ' . mysqli_error(self::$conn).PHP_EOL);
            print_r($ret['error']);
            fwrite(self::$logFile, $ret['error'].PHP_EOL);
            return $ret;
        }

        } catch(Exception $e){
            fwrite(self::$logFile, 'Exception while inserting place category: '.$e.PHP_EOL);
            var_dump('Exception while inserting place category: '.$e);
            $ret =  array('success' => false, 'error' => 'Error inserting place category: ' . mysqli_error(self::$conn).PHP_EOL);
            return $ret;
        }
    }

    public static function getFsPlace($idFsPlace){

        $sql = "SELECT * from fs_places where id='".$idFsPlace."'";
        
        try{

        if ($result = mysqli_query(self::$conn,$sql))
        {
            return mysqli_fetch_array($result, MYSQLI_ASSOC);
        }
        else{
            return false;
        }

        } catch(Exception $e){
            fwrite(self::$logFile, 'Exception while getting user: '.$e.PHP_EOL);
            var_dump('Exception while getting user: '.$e);
            return false;
        }
    }

    public static function getFsUser($idFsUser){

        $sql = "SELECT * from fs_users where id='".$idFsUser."'";
        
        try{

        if ($result = mysqli_query(self::$conn,$sql))
        {
            return mysqli_fetch_array($result);
        }
        else{
            return false;
        }

        } catch(Exception $e){
            fwrite(self::$logFile, 'Exception while getting user: '.$e.PHP_EOL);
            var_dump('Exception while getting user: '.$e);
            return false;
        }
    }

    public static function insertFsPlaceCustomer($idPlace, $idFsUser, $userType){

        $sql = "INSERT INTO fs_places_customers values(null,'".$idPlace."','".$idFsUser."','".$userType."')";
        
        try{

        if (mysqli_query(self::$conn,$sql))
        {
            return array('success' => true);
        }
        else{
            $ret =  array('success' => false, 'error' => 'Error inserting place customer: ' . mysqli_error(self::$conn).PHP_EOL);
            print_r($ret['error']);
            fwrite(self::$logFile, $ret['error'].PHP_EOL);
            return $ret;
        }

        } catch(Exception $e){
            fwrite(self::$logFile, 'Exception while inserting place customer: '.$e.PHP_EOL);
            var_dump('Exception while inserting place customer: '.$e);
            $ret =  array('success' => false, 'error' => 'Error inserting place customer: ' . mysqli_error(self::$conn).PHP_EOL);
            return $ret;
        }
    }

    public static function insertFsUser($idFsUser, $name, $gender, $home_city, $fbContact = null, $twContact = null){

        $sql = "INSERT INTO fs_users values('".$idFsUser."','".$name."','".$gender."','".$home_city."'";
        
        if($fbContact != null){
            $sql = $sql.",'".$fbContact."'";
        }
         else{
            $sql = $sql.",null";   
        }

        if($twContact != null){
            $sql = $sql.",'".$twContact."'";
        }
         else{
            $sql = $sql.",null";   
        }

        $sql = $sql.");";
        
        try{

        if (mysqli_query(self::$conn,$sql))
        {
            return array('success' => true);
        }
        else{
            $ret =  array('success' => false, 'error' => 'Error inserting user: ' . mysqli_error(self::$conn).PHP_EOL);
            print_r($ret['error']);
            fwrite(self::$logFile, $ret['error'].PHP_EOL);
            return $ret;
        }

        } catch(Exception $e){
            fwrite(self::$logFile, 'Exception while inserting user: '.$e.PHP_EOL);
            var_dump('Exception while inserting user: '.$e);
            $ret =  array('success' => false, 'error' => 'Error inserting user: ' . mysqli_error(self::$conn).PHP_EOL);
            return $ret;
        }
    }

    public static function getUsers(){

        $sql = "SELECT * from fs_users;";

        try{

        if ($result = mysqli_query(self::$conn,$sql))
        {   
            return array('success' => true, 'result' => $result);
        }
        else{
            $ret = array('success' => false, 'error' => 'Error getting users table: ' . mysqli_error(self::$conn));
            print_r($ret['error']);
            fwrite(self::$logFile, $ret['error'].PHP_EOL);
            return $ret;
        }

        } catch(Exception $e){
            var_dump('Exception while getting users: '.$e);
            fwrite(self::$logFile, 'Exception while getting users: '.$e.PHP_EOL);
            $ret = array('success' => false, 'error' => 'Error getting users table: ' . mysqli_error(self::$conn));
            return $ret;
        }
    }

    public static function insertMusicArtist($mbid, $artistName, $artistLink){

        $sql = "INSERT INTO music_artists values('".$mbid."','".$artistName."','".$artistLink."')";
        
        try{

        if (mysqli_query(self::$conn,$sql))
        {
            return array('success' => true);
        }
        else{
            $ret =  array('success' => false, 'error' => 'Error inserting music artist: ' . mysqli_error(self::$conn).PHP_EOL);
            print_r($ret['error']);
            fwrite(self::$logFile, $ret['error'].PHP_EOL);
            return $ret;
        }

        } catch(Exception $e){
            fwrite(self::$logFile, 'Exception while inserting music artist: '.$e.PHP_EOL);
            var_dump('Exception while inserting music artist: '.$e);
            $ret =  array('success' => false, 'error' => 'Error inserting music artist: ' . mysqli_error(self::$conn).PHP_EOL);
            return $ret;
        }
    }

    public static function insertFsUserArtist($fsUserId, $mbidArtist, $connType){

        $sql = "INSERT INTO fs_users_artists values(null, '".$fsUserId."','".$mbidArtist."','".$connType."')";
        
        try{

        if (mysqli_query(self::$conn,$sql))
        {
            return array('success' => true);
        }
        else{
            $ret =  array('success' => false, 'error' => 'Error inserting user artist: ' . mysqli_error(self::$conn).PHP_EOL);
            print_r($ret['error']);
            fwrite(self::$logFile, $ret['error'].PHP_EOL);
            return $ret;
        }

        } catch(Exception $e){
            fwrite(self::$logFile, 'Exception while inserting user artist: '.$e.PHP_EOL);
            var_dump('Exception while inserting music artist: '.$e);
            $ret =  array('success' => false, 'error' => 'Error inserting user artist: ' . mysqli_error(self::$conn).PHP_EOL);
            return $ret;
        }
    }

    
	
}

?>
