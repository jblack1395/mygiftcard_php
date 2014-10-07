<?php
require_once("Rest.inc.php");

class API extends REST
{
    
    public $data = "";
    
    const DB_SERVER = "127.0.0.1";
    const DB_USER = "root";
    const DB_PASSWORD = "";
    const DB = "mygiftcard";
    
    private $db = NULL;
    private $mysqli = NULL;
    public function __construct()
    {
        parent::__construct(); // Init parent contructor
        $this->dbConnect(); // Initiate Database connection
    }
    
    /*
     *  Connect to Database
     */
    private function dbConnect()
    {
        $this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
    }
    
    /*
     * Dynmically call the method based on the query string
     */
    public function processApi()
    {
        $func = strtolower(trim(str_replace("/", "", $_REQUEST['x'])));
        if ((int) method_exists($this, $func) > 0)
            $this->$func();
        else
            $this->response('', 404); // If the method not exist with in this class "Page not found".
    }
    
    private function login()
    {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $customer    = $this->_request['customer'];
	  $username = $this->_request['username'];
        $password = $this->_request['pwd'];
        if (!empty($customer) and !empty($username) and !empty($password)) {
            if (filter_var($customer, FILTER_SANITIZE_STRING) and filter_var($username, FILTER_SANITIZE_STRING)) {
                $query = "SELECT uid, name, email FROM users WHERE email = '$email' AND password = '" . md5($password) . "' LIMIT 1";
                $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
                
                if ($r->num_rows > 0) {
                    $result = $r->fetch_assoc();
                    // If success everythig is good send header as "OK" and user details
                    $this->response($this->json($result), 200);
                }
                $this->response('', 204); // If no records "No Content" status
            }
        }
        
        $error = array(
            'status' => "Failed",
            "msg" => "Invalid Email address or Password"
        );
        $this->response($this->json($error), 400);
    }
    
    private function orders()
    {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $query = "SELECT distinct c.exerciseNumber, c.exerciseName, c.exerciseDescription FROM exercise c order by c.exerciseName desc";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        
        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $this->response($this->json($result), 200); // send user details
        }
        $this->response('', 204); // If no records "No Content" status
    }
    private function order()
    {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $id = (int) $this->_request['id'];
        if ($id > 0) {
            $query = "SELECT distinct c.exerciseNumber, c.exerciseName, c.exerciseDescription FROM exercise c where c.exerciseNumber=$id";
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            if ($r->num_rows > 0) {
                $result = $r->fetch_assoc();
                $this->response($this->json($result), 200); // send user details
            }
        }
        $this->response('', 204); // If no records "No Content" status
    }
    
    private function insertOrder()
    {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        
        $customer     = json_decode(file_get_contents("php://input"), true);
        $column_names = array(
            'exerciseName',
            'exerciseDescription'
        );
        $keys         = array_keys($customer);
        $columns      = '';
        $values       = '';
        foreach ($column_names as $desired_key) { // Check the customer received. If blank insert blank into the array.
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $customer[$desired_key];
            }
            $columns = $columns . $desired_key . ',';
            $values  = $values . "'" . $$desired_key . "',";
        }
        $query = "INSERT INTO exercise(" . trim($columns, ',') . ") VALUES(" . trim($values, ',') . ")";
        if (!empty($customer)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array(
                'status' => "Success",
                "msg" => "Customer Created Successfully.",
                "data" => $customer
            );
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); //"No Content" status
    }
    
    private function customers()
    {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $query = "SELECT distinct c.customerNumber, c.customerName, c.email, c.address, c.city, c.state, c.postalCode, c.country FROM customers c order by c.country, c.state, c.city, c.customerName desc";
        $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        
        if ($r->num_rows > 0) {
            $result = array();
            while ($row = $r->fetch_assoc()) {
                $result[] = $row;
            }
            $this->response($this->json($result), 200); // send user details
        }
        $this->response('', 204); // If no records "No Content" status
    }
    private function customer()
    {
        if ($this->get_request_method() != "GET") {
            $this->response('', 406);
        }
        $id = (int) $this->_request['id'];
        if ($id > 0) {
            $query = "SELECT distinct c.customerNumber, c.customerName, c.email, c.address, c.city, c.state, c.postalCode, c.country FROM customers c where c.customerNumber=$id";
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            if ($r->num_rows > 0) {
                $result = $r->fetch_assoc();
                $this->response($this->json($result), 200); // send user details
            }
        }
        $this->response('', 204); // If no records "No Content" status
    }
    
    private function insertCustomer()
    {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        
        $customer     = json_decode(file_get_contents("php://input"), true);
        $column_names = array(
            'customerName',
            'email',
            'city',
            'address',
            'country'
        );
        $keys         = array_keys($customer);
        $columns      = '';
        $values       = '';
        foreach ($column_names as $desired_key) { // Check the customer received. If blank insert blank into the array.
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $customer[$desired_key];
            }
            $columns = $columns . $desired_key . ',';
            $values  = $values . "'" . $$desired_key . "',";
        }
        $query = "INSERT INTO customers(" . trim($columns, ',') . ") VALUES(" . trim($values, ',') . ")";
        if (!empty($customer)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array(
                'status' => "Success",
                "msg" => "Customer Created Successfully.",
                "data" => $customer
            );
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); //"No Content" status
    }
    private function updateCustomer()
    {
        if ($this->get_request_method() != "POST") {
            $this->response('', 406);
        }
        $customer     = json_decode(file_get_contents("php://input"), true);
        $id           = (int) $customer['id'];
        $column_names = array(
            'customerName',
            'email',
            'city',
            'address',
            'country'
        );
        $keys         = array_keys($customer['customer']);
        $columns      = '';
        $values       = '';
        foreach ($column_names as $desired_key) { // Check the customer received. If key does not exist, insert blank into the array.
            if (!in_array($desired_key, $keys)) {
                $$desired_key = '';
            } else {
                $$desired_key = $customer['customer'][$desired_key];
            }
            $columns = $columns . $desired_key . "='" . $$desired_key . "',";
        }
        $query = "UPDATE customers SET " . trim($columns, ',') . " WHERE customerNumber=$id";
        if (!empty($customer)) {
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array(
                'status' => "Success",
                "msg" => "Customer " . $id . " Updated Successfully.",
                "data" => $customer
            );
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); // "No Content" status
    }
    
    private function deleteCustomer()
    {
        if ($this->get_request_method() != "DELETE") {
            $this->response('', 406);
        }
        $id = (int) $this->_request['id'];
        if ($id > 0) {
            $query = "DELETE FROM customers WHERE customerNumber = $id";
            $r = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            $success = array(
                'status' => "Success",
                "msg" => "Successfully deleted one record."
            );
            $this->response($this->json($success), 200);
        } else
            $this->response('', 204); // If no records "No Content" status
    }
    
    private function uploadLogo($data)
    {
        header('Content-Type: text/plain; charset=utf-8');
        
        try {
            
            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (!isset($_FILES['upfile']['error']) || is_array($_FILES['upfile']['error'])) {
                throw new RuntimeException('Invalid parameters.');
            }
            
            // Check $_FILES['upfile']['error'] value.
            switch ($_FILES['upfile']['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException('No file sent.');
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException('Exceeded filesize limit.');
                default:
                    throw new RuntimeException('Unknown errors.');
            }
            
            // You should also check filesize here. 
            if ($_FILES['upfile']['size'] > 1000000) {
                throw new RuntimeException('Exceeded filesize limit.');
            }
            
            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            if (false === $ext = array_search($finfo->file($_FILES['upfile']['tmp_name']), array(
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif'
            ), true)) {
                throw new RuntimeException('Invalid file format.');
            }
            
            // You should name it uniquely.
            // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from its binary data.
            if (!move_uploaded_file($_FILES['upfile']['tmp_name'], sprintf('./uploads/%s.%s', sha1_file($_FILES['upfile']['tmp_name']), $ext))) {
                throw new RuntimeException('Failed to move uploaded file.');
            }
            
            echo 'File is uploaded successfully.';
            
        }
        catch (RuntimeException $e) {
            
            echo $e->getMessage();
            
        }
    }
    private function json($data)
    {
        if (is_array($data)) {
            return json_encode($data);
        }
    }
}

$api = new API;
$api->processApi();
?>