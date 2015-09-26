<?php

    require_once(__DIR__.'/../../config/database.php');
 
    class MusicArtist extends Illuminate\Database\Eloquent\Model {
        
        protected $table = 'music_artists';

        public function build($mbid, $artistName, $artistLink){
			$this->id = $mbid;
			$this->artist_name = $artistName;
			$this->artist_link = $artistLink;
		}

		public function users()
    	{
        	return $this->belongsToMany('FsUser', 'fs_users_artists', 
        		'mbid_artist', 'id_fs_user');
    	}
    }

?>
 