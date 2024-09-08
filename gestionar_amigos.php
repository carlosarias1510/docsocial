<?php
session_start();
include('includes/db.php');

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['amigo_id'])) {
    $amigo_id = $conn->real_escape_string($_POST['amigo_id']);

    if (isset($_POST['eliminar'])) {
        // Eliminar amigo
        $sql_delete = "DELETE FROM seguidores WHERE seguidor_id = '$user_id' AND seguido_id = '$amigo_id'";
        if ($conn->query($sql_delete) === TRUE) {
            header("Location: amigos.php"); // Redirigir a la página de amigos
            exit();
        } else {
            echo "Error al eliminar amigo: " . $conn->error;
        }
    } else {
        // Agregar amigo
        $sql_check = "SELECT * FROM seguidores WHERE seguidor_id = '$user_id' AND seguido_id = '$amigo_id'";
        $result_check = $conn->query($sql_check);

        if ($result_check->num_rows == 0) {
            $sql_insert = "INSERT INTO seguidores (seguidor_id, seguido_id) VALUES ('$user_id', '$amigo_id')";
            if ($conn->query($sql_insert) === TRUE) {
                header("Location: amigos.php"); // Redirigir a la página de amigos
                exit();
            } else {
                echo "Error al agregar amigo: " . $conn->error;
            }
        } else {
            echo "Ya eres amigo de este usuario o ya existe una solicitud.";
        }
    }
}
?>
