<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require "db.php";

$method = $_SERVER['REQUEST_METHOD'];

function getJsonInput()
{
    return json_decode(file_get_contents("php://input"), true);
}

switch ($method) {

    case "GET":
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $pdo->query("SELECT * FROM users");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case "POST":
        $data = getJsonInput();

        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(["error" => "All fields are required"]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $success = $stmt->execute([
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT)
        ]);

        echo json_encode([
            "message" => $success ? "User created successfully" : "Failed to create user"
        ]);
        break;

    // ==========================
    // PUT → Update User
    // ==========================
    case "PUT":
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            exit;
        }

        $data = getJsonInput();

        $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
        $success = $stmt->execute([
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $_GET['id']
        ]);

        echo json_encode([
            "message" => $success ? "User updated successfully" : "Failed to update user"
        ]);
        break;

    // ==========================
    // DELETE → Hapus User
    // ==========================
    case "DELETE":
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
        $success = $stmt->execute([$_GET['id']]);

        echo json_encode([
            "message" => $success ? "User deleted successfully" : "Failed to delete user"
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Invalid HTTP Method"]);
}
