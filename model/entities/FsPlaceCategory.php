<?php

    require_once(__DIR__.'/../../config/database.php');
 
    class FsPlaceCategory extends Illuminate\Database\Eloquent\Model {
        
        protected $table = 'fs_places_categories';

        public function build($idFsPlace, $idFsCategory){
			$this->id_fs_place = $idFsPlace;
			$this->id_fs_category = $idFsCategory;
		}	


    }

?>
 