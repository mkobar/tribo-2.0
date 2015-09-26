<?php

    require_once(__DIR__.'/../../config/database.php');
 
    class FsUserArtist extends Illuminate\Database\Eloquent\Model {
        
        protected $table = 'fs_users_artists';

        public function build($idFsUser, $mbidArtist, $connType){
			$this->id_fs_user = $idFsUser;
			$this->mbid_artist = $mbidArtist;
			$this->conn_type = $connType;
		}

    }

?>
 