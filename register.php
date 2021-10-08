<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

function msg($success,$status,$message,$extra = []){
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ],$extra);
}

// INCLUDING DATABASE AND MAKING OBJECT
require __DIR__.'/classes/Database.php';
$db_connection = new Database();
$conn = $db_connection->dbConnection();

// GET DATA FORM REQUEST
$data = json_decode(file_get_contents("php://input"));
$returnData = [];

// IF REQUEST METHOD IS NOT POST
if($_SERVER["REQUEST_METHOD"] != "POST"):
    $returnData = msg(0,404,'Page Not Found!');

// CHECKING EMPTY FIELDS
elseif(!isset($data->firstName) 
    ||!isset($data->lastName) 
    ||!isset($data->address) 
    ||!isset($data->phone) 
    ||!isset($data->bday) 
    || !isset($data->email) 
    || !isset($data->password)
    || empty(trim($data->firstName))
    || empty(trim($data->lastName))
    || empty(trim($data->address))
    || empty(trim($data->phone))
    || empty(trim($data->bday))
    || empty(trim($data->email))
    || empty(trim($data->password))
    ):

    $fields = ['fields' => ['firstName','lastName','address','phone','bday','email','password']];
    $returnData = msg(0,422,'Please Fill in all Required Fields!',$fields);

// IF THERE ARE NO EMPTY FIELDS THEN-
else:
    
    $firstName = trim($data->firstName);
    $lastName = trim($data->lastName);
    $address = trim($data->address);
    $phone = trim($data->phone);
    $bday = trim($data->bday);
    $email = trim($data->email);
    $password = trim($data->password);

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)):
        $returnData = msg(0,422,'Invalid Email Address!');
    
    elseif(strlen($password) < 8):
        $returnData = msg(0,422,'Your password must be at least 8 characters long!');

    elseif(strlen($firstName) < 3):
        $returnData = msg(0,422,'Your firstName must be at least 3 characters long!');
    elseif(strlen($lastName) < 3):
        $returnData = msg(0,422,'Your lastName must be at least 3 characters long!');
    elseif(strlen($address) < 3):
        $returnData = msg(0,422,'Your address must be at least 15 characters long!');
    elseif(strlen($phone) < 3):
        $returnData = msg(0,422,'Your phone must be at least 11 characters long!');
    elseif(strlen($bday) < 3):
        $returnData = msg(0,422,'Your bday must be at least 6 characters long!');
    else:
        try{

            $check_email = "SELECT `email` FROM `users` WHERE `email`=:email";
            $check_email_stmt = $conn->prepare($check_email);
            $check_email_stmt->bindValue(':email', $email,PDO::PARAM_STR);
            $check_email_stmt->execute();

            if($check_email_stmt->rowCount()):
                $returnData = msg(0,422, 'This E-mail already in use!');
            
            else:
                $insert_query = "INSERT INTO `users`(`firstName`,`lastName`,`address`,`phone`,`bday`,`email`,`password`) VALUES(:firstName,:lastName,:address,:phone,:bday,:email,:password)";

                $insert_stmt = $conn->prepare($insert_query);

                // DATA BINDING
                $insert_stmt->bindValue(':firstName', htmlspecialchars(strip_tags($firstName)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':lastName', htmlspecialchars(strip_tags($lastName)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':address', htmlspecialchars(strip_tags($address)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':phone', htmlspecialchars(strip_tags($phone)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':bday', htmlspecialchars(strip_tags($bday)),PDO::PARAM_STR);
                $insert_stmt->bindValue(':email', $email,PDO::PARAM_STR);
                $insert_stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT),PDO::PARAM_STR);

                $insert_stmt->execute();

                $returnData = msg(1,201,'You have successfully registered.');

            endif;

        }
        catch(PDOException $e){
            $returnData = msg(0,500,$e->getMessage());
        }
    endif;
    
endif;

echo json_encode($returnData);