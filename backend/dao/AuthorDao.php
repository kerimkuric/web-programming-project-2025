<?php

require_once __DIR__ . '/BaseDao.php';

class AuthorDao extends BaseDao {
    public function __construct() {
        parent::__construct('authors');
    }
}

?>

