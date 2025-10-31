<?php

require_once __DIR__ . '/BaseDao.php';

class GenreDao extends BaseDao {
    public function __construct() {
        parent::__construct('genres');
    }
}

?>

