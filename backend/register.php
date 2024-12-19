<?php
    /*
        HTTP request method: POST

        From frontend (Content-Type: application/x-www-form-urlencoded):
        "username"
        "password"

        To frontend (Content-Type: application/json):
        [
            "status": (string) success/error,
            "message": (string),
        ]
    */
    
    // deal with  CORS（跨來源資源共享） problem
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    header('Content-Type: application/json; charset=UTF-8');

    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "請輸入帳號以及密碼！",
        ]);
        exit;
    }

    require_once './connection.php';

    $username = htmlentities($_POST['username']);
    $password = htmlentities($_POST['password']);


    $query = "SELECT COUNT(*) FROM `users` WHERE `username` = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode([
            "status" => "error",
            "message" => "該帳號已存在，請輸入新的帳號！",
        ]);
    } else {
        $hased_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $query = "INSERT INTO `users` (`username`, `password`) VALUES (:username, :password)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hased_password, PDO::PARAM_STR);
            $stmt->execute();
            http_response_code(201);
            echo json_encode([
                "status" => "success",
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "註冊失敗，請再試一次！",
            ]);
            exit;
        }
    }
?>
