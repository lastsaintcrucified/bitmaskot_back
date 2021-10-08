<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__.'/classes/Database.php';
require __DIR__.'/middlewares/Auth.php';

$allHeaders = getallheaders();
$db_connection = new Database();
$conn = $db_connection->dbConnection();
$auth = new Auth($conn,$allHeaders);


try{

$sql = "SELECT * FROM users";
$result = $conn->query($sql);
if($result->rowCount()):
   $rows = array();
while($row = $result->fetch(PDO::FETCH_ASSOC)){
  array_push($rows, $row);
}
echo json_encode($rows);

                return [
                    'success' => 1,
                    'status' => 200,
                    'userList' => $rows
                ];
            else:
                return null;
            endif;
}
catch(PDOException $e){
            return null;
 }
