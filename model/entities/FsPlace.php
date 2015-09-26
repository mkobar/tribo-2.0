<?php

    require_once(__DIR__.'/../../config/database.php');
 
    class FsPlace extends Illuminate\Database\Eloquent\Model {
        
        protected $table = 'fs_places';

        public function build($id, $venueName, $latitude, $longitude, $city, $countryCode, $postalCode = null, 
        	$address = null, $country = null, $phoneNumber = null, $urlFs = null, $url = null){
			$this->id = $id;
			$this->venue_name = $venueName;
			$this->latitude = $latitude;
			$this->longitude = $longitude;
			$this->city = $city;
			$this->country_code = $countryCode;
			$this->postal_code = $postalCode;
			$this->address = $address;
			$this->country = $country;
			$this->phone_number = $phoneNumber;
			$this->url_fs = $urlFs;
			$this->url = $url;
		}

		public function categories()
    	{
        	return $this->belongsToMany('FsCategory', 'fs_places_categories', 
        		'id_fs_place', 'id_fs_category');
    	}

    	public function customers()
    	{
        	return $this->belongsToMany('FsUser', 'fs_places_customers', 
        		'id_fs_place', 'id_user')->withPivot('type');
    	}

    }

?>
 