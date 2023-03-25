<?php

include_once MAIN_PATH . '/config/database.php';
include_once MAIN_PATH . '/model/Employee.php';

class EmployeeController {

    private Employee $model;
    private array $authEmp;

    //--------------------------------------------------------------------------
    public function __construct() {

        $database    = new Database();
        $this->model = new Employee($database);

        if (empty($_SERVER['HTTP_AUTH_USER']) || empty($_SERVER['HTTP_AUTH_PASS'])) {
            $this->unauthRes();
        }

        //@todo pass should be hashed
        $email = $_SERVER['HTTP_AUTH_USER'];
        $pass  = $_SERVER['HTTP_AUTH_PASS'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->unauthRes();
        }
        $emp = $this->model->getOneByEmail($email);

        if (!$emp || $emp['pass'] != $pass || $emp['status'] == 0) {
            $this->unauthRes();
        }

        $this->authEmp = $emp;
    }

    private function unauthRes() {
        http_response_code(401);
        header('WWW-Authenticate: Basic realm="Private Area"');
        exit;
    }

    //--------------------------------------------------------------------------
    public function processRequest(string $method, ?string $id): void {

        if (!method_exists('EmployeeController', $method)) {
            http_response_code(405);
            header('Allow: GET, POST, PUT');
            exit;
        }

        if ($this->authEmp['type'] == 0 && $method == 'PUT' && isset($id) && $id == $this->authEmp['id']) {
            $this->PUT($id, ['name', 'phone'], 'updateEmployeeContactInformation');
        } else if ($this->authEmp['type'] == 1) {
            if ($method == 'PUT') {
                $this->PUT($id, ['status'], 'updateEmployeeStatus');
            } else {
                $this->$method();
            }
        } else {
            $this->unauthRes();
        }
    }

    //--------------------------------------------------------------------------
    private function GET(): void {
        $data = $this->model->getAll();
        new ApiResponse(200, null, null, $data);
    }

    //--------------------------------------------------------------------------
    private function POST(): void {
        $input = (array) json_decode(file_get_contents('php://input'), true);

        $fields = ['name', 'status', 'title', 'email', 'pass', 'phone', 'type'];
        list($isValid, $error) = $this->validateRequest($fields, $input);
        if (!$isValid) {
            new ApiResponse(400, 'Invalid input object.', $error);
            exit();
        }


        $this->model->create($input);
        new ApiResponse(201, 'Employee created successfully.', null, $this->model);
    }

    //--------------------------------------------------------------------------
    private function PUT(?string $id, array $fields, string $fun): void {

        $emp = $this->model->getOne($id);
        if (!$emp) {
            new ApiResponse(404, 'Employee not found.', null, null);
            return;
        }

        $input = (array) json_decode(file_get_contents('php://input'), true);

        list($isValid, $error) = $this->validateRequest($fields, $input);
        if (!$isValid) {
            new ApiResponse(400, 'Invalid input object.', $error);
            exit();
        }

        $this->model->$fun($id, $input);
        new ApiResponse(200, 'Employee data updated.', null, $this->model);
    }

    //--------------------------------------------------------------------------
    private function validateRequest($fields, $input): array {
        if (json_last_error() != JSON_ERROR_NONE) {
            return [false, null];
        }

        $err = Employee::checkFieldErrors($fields, $input);
        return empty($err) ? [true, null] : [false, $err];
    }

    //--------------------------------------------------------------------------
}
