<?
class Database {
    private $mysqli;

    public function __construct() {
        $this->mysqli = new mysqli('localhost', 'bilmid', '', 'verification');
        // handle connection errors, etc.
    }

    public function autocommit($mode) {
        return $this->mysqli->autocommit($mode);
    }

    public function commit() {
        return $this->mysqli->commit();
    }

    public function rollback() {
        return $this->mysqli->rollback();
    }

    public function prepare($query) {
        return $this->mysqli->prepare($query);
    }

    // add begin_transaction() if needed:
    public function begin_transaction() {
        return $this->mysqli->begin_transaction();
    }
}
