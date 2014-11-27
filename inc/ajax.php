<?php
require_once 'application.php';
require_once 'util.php';
session_start();

$arr = [];
$app = new Application();

switch($_POST['f']){
    case "numPending"://this is pretty slow..
        try {
            if ($app->userExists(getUserName())) {
                $pages = $app->getSearchResults(null, getUserName(), null);
                if($pages != null) {
                    foreach($pages as $row){
                      $context = $app->loadPage($row[0]);
                      $arr[] = array(
                        "id" => "numPend".$row[0],
                        "num" => $context->numPendingChanges(getUserName())
                        );
                    }
                }
            }
        } catch (Exception $e) {
            $arr[] =[$e->getMessage()];
        }
        break;
}

//send data
echo json_encode($arr);
?>