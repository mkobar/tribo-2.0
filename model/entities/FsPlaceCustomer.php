<?php

    require_once(__DIR__.'/../../config/database.php');
 
    class FsPlaceCustomer extends Illuminate\Database\Eloquent\Model {
        
        protected $table = 'fs_places_customers';

        public function build($idFsPlace, $idUser, $type){
			$this->id_fs_place = $idFsPlace;
			$this->id_user = $idUser;
			$this->type = $type;
		}	


    }

?>
 