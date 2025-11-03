<?php

require_once __DIR__ . '/BaseDao.php';

class BookDao extends BaseDao {
    public function __construct() {
        parent::__construct('books');
    }
}

?>

