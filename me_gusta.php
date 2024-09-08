<?php
session_start();
include('includes/db.php');

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tweet_id'])) {
    $tweet_id = $conn->real_escape_string($_POST['tweet_id']);

    // Verificar si el usuario ya ha dado "Me gusta" a este tweet
    $sql_check = "SELECT * FROM likes WHERE tweet_id = '$tweet_id' AND user_id = '$user_id'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        // Si ya ha dado "Me gusta", eliminarlo
        $sql = "DELETE FROM likes WHERE tweet_id = '$tweet_id' AND user_id = '$user_id'";
    } else {
        // Si no ha dado "Me gusta", agregarlo
        $sql = "INSERT INTO likes (tweet_id, user_id) VALUES ('$tweet_id', '$user_id')";
    }

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error al procesar 'Me gusta': " . $conn->error;
    }
}
?>
