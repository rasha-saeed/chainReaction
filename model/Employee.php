<?php

class Employee {

    private $conn;
    private $table         = 'employee';
    private static $fields = ['id', 'name', 'status', 'type', 'title', 'email', 'pass', 'phone', 'created', 'updated'];

    public function __construct($database) {
        $this->conn = $database->getConnection();
    }

    public function getOne(?string $id): array|false {
        $sql = "SELECT * FROM $this->table WHERE id=:id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getOneByEmail(string $email): array|false {
        $sql = "SELECT * FROM $this->table WHERE email=:email";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":email", $email, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //--------------------------------------------------------------------------
    public function getAll(): array {
        //@todo limit can be added later
        $sql  = "SELECT * FROM $this->table";
        $stmt = $this->conn->query($sql);

        $data['count'] = $stmt->rowCount();

        $data['employees'] = [];
        while ($row               = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data['employees'][] = $row;
        }

        return $data;
    }

    //--------------------------------------------------------------------------
    public function create(array $data): void {
        //sanitize
        $this->sanitize($data);

        $sql = "INSERT INTO $this->table (name, status, title, email, pass, phone, type)
                VALUES (:name, :status, :title, :email, :pass, :phone, :type)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
        $stmt->bindValue(':status', $this->status ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':title', $this->title, PDO::PARAM_STR);
        $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
        $stmt->bindValue(':pass', $this->pass, PDO::PARAM_STR);
        $stmt->bindValue(':phone', $this->phone, PDO::PARAM_STR);
        $stmt->bindValue(':type', $this->type ?? 0, PDO::PARAM_INT);

        $stmt->execute();
        $this->id = $this->conn->lastInsertId();
    }

    //--------------------------------------------------------------------------
    public function updateEmployeeStatus($id, $input) {
        //sanitize
        $this->sanitize($input);

        $sql = "UPDATE $this->table SET status=:status, updated='" . date('Y-m-d H:i:s') . "' WHERE id=:id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':status', $this->status ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return (bool) $stmt->execute();
    }

    //--------------------------------------------------------------------------
    public function updateEmployeeContactInformation($id, $input) {
        //@todo we can use the old data if the new data is not provided
        //sanitize
        $this->sanitize($input);

        $sqlQuery = "UPDATE $this->table SET name=:name, phone=:phone, updated='" . date('Y-m-d H:i:s') . "' WHERE id=:id";

        $stmt = $this->conn->prepare($sqlQuery);

        $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
        $stmt->bindValue(':phone', $this->phone, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return (bool) $stmt->execute();
    }

    //--------------------------------------------------------------------------
    private function sanitize($arr): void {
        foreach ($arr as $key => $val) {
            if (in_array($key, self::$fields)) {
                $this->$key = htmlspecialchars(strip_tags($val));
            }
        }
    }

    public static function checkFieldErrors($fields, $input): array {

        $err = [];
        foreach ($fields as $key) {
            $fun = 'checkField' . ucfirst($key);

            if (!method_exists('Employee', $fun)) {
                continue;
            }

            if (!isset($input[$key])) {
                $err[$key] = "Field $key is required";
                continue;
            }

            $msg = self::{$fun}($input[$key]);
            if ($msg) {
                $err[$key] = $msg;
            }
        }

        return $err;
    }

    public static function checkFieldId($id) {
        if (empty($id)) {
            return 'Employee id can not be empty.';
        }
    }

    public static function checkFieldName($name) {
        if (empty($name)) {
            return 'Employee name can not be empty.';
        } else if (strlen($name) > 256) {
            return 'Employee name should be equal or less than 256 chars.';
        }
    }

    public static function checkFieldStatus($status) {
        if ($status !== 0 && $status !== 1) {
            return 'Employee status should be int (0 or 1) to set status (off, on).';
        }
    }

    public static function checkFieldTitle($title) {
        if (empty($title)) {
            return 'Employee job title can not be empty.';
        } else if (strlen($title) > 100) {
            return 'Employee job title should be equal or less than 100 chars.';
        }
    }

    public static function checkFieldEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email';
        } else if (strlen($email) > 100) {
            return 'Employee email should be equal or less than 100 chars.';
        }
    }

    public static function checkFieldPass($pass) {
        if (empty($pass)) {
            return 'Employee password can not be empty.';
        } else if (strlen($pass) < 5 || strlen($pass) > 10) {
            return 'Employee password lenght should have 5 to 10 chars.';
        }
    }

    public static function checkFieldPhone($phone) {
        if (!preg_match("/^[0-9]{5,20}$/", $phone)) {
            return 'Employee phone should be in english numbers and between 5 to 20 digits.';
        }
    }

    public static function checkFieldType($type) {
        if ($type !== 0 && $type !== 1) {
            return 'Employee type should be int (0 or 1) to be (Employee, HR Manager).';
        }
    }

    //--------------------------------------------------------------------------
}
