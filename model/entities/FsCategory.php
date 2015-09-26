<?php

    require_once(__DIR__.'/../../config/database.php');
 
    class FsCategory extends Illuminate\Database\Eloquent\Model {
        
        protected $table = 'fs_categories';

        public function build($id, $description){
			$this->id = $id;
			$this->description = $description;
		}

		public function places()
    	{
        	return $this->belongsToMany('FsPlace', 'fs_places_categories',
        		'id_fs_category', 'id_fs_place');
    	}
    }

?>
 