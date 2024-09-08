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

    // Verifica que el tweet pertenece al usuario
    $sql_check = "SELECT * FROM tweets WHERE id = '$tweet_id' AND user_id = '$user_id'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        // Eliminar el tweet
        $sql_delete = "DELETE FROM tweets WHERE id = '$tweet_id' AND user_id = '$user_id'";
        if ($conn->query($sql_delete) === TRUE) {
            header("Location: index.php"); // Redirige a la página de inicio después de eliminar
            exit();
        } else {
            echo "Error al eliminar el tweet: " . $conn->error;
        }
    } else {
        echo "No tienes permiso para eliminar este tweet.";
    }
} else {
    echo "Solicitud inválida.";
}
?>
