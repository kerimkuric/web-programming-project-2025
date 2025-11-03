<?php

require_once __DIR__ . '/../config/Database.php';

class BaseDao {
    protected $table;
    protected $connection;

    public function __construct($table) {
        $this->table = $table;
        $this->connection = Database::connect();
    }

    public function getAll() {
        $statement = $this->connection->prepare("SELECT * FROM " . $this->table);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function getById($id) {
        $statement = $this->connection->prepare("SELECT * FROM " . $this->table . " WHERE id = :id");
        $statement->bindParam(':id', $id);
        $statement->execute();
        return $statement->fetch();
    }

    public function insert($data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO " . $this->table . " ($columns) VALUES ($placeholders)";
        $statement = $this->connection->prepare($sql);
        if ($statement->execute($data)) {
            return $this->connection->lastInsertId();
        }
        return false;
    }

    public function update($id, $data) {
        $setFields = "";
        foreach ($data as $key => $value) {
            $setFields .= "$key = :$key, ";
        }
        $setFields = rtrim($setFields, ", ");
        $sql = "UPDATE " . $this->table . " SET $setFields WHERE id = :id";
        $statement = $this->connection->prepare($sql);
        $data['id'] = $id;
        return $statement->execute($data);
    }

    public function delete($id) {
        $statement = $this->connection->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
        $statement->bindParam(':id', $id);
        return $statement->execute();
    }
}

?>

