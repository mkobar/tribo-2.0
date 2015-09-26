<?php

    require_once(__DIR__.'/../../config/database.php');
 
    class FsUser extends Illuminate\Database\Eloquent\Model {
        
        protected $table = 'fs_users';

        public function build($id, $name, $gender, $homeCity, $fbContact, $twContact){
			$this->id = $id;
			$this->name = $name;
			$this->gender = $gender;
			$this->home_city = $homeCity;
			$this->fb_contact = $fbContact;
			$this->tw_contact = $twContact;
		}

		public function places()
    	{
        	return $this->belongsToMany('FsPlace', 'fs_places_customers', 
        		'id_user', 'id_fs_place');
    	}

        public function artists()
        {
            return $this->belongsToMany('MusicArtist', 'fs_users_artists', 
                'id_fs_user', 'mbid_artist');
        }
    }

?>
 