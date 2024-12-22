<?php
    require 'connection.php';

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $user = $input['username'] ?? null;
    $month = $input['month'] ?? null;

    if (!$user || $month === null) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "缺少必要參數"]);
        exit;
    }

    try {
        $startDate = "2024-$month-01";
        $endDate = date("Y-m-t", strtotime($startDate));

        $stmt = $pdo->prepare("SELECT DATE(time) as date, SUM(amount) as total 
                               FROM transactions 
                               WHERE user = :user AND time BETWEEN :startDate AND :endDate 
                               GROUP BY DATE(time)");
        $stmt->execute(['user' => $user, 'startDate' => $startDate, 'endDate' => $endDate]);
        $results = $stmt->fetchAll();

        echo json_encode(["status" => "success", "data" => $results]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "資料庫查詢失敗"]);
    }
?>