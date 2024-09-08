<?php
session_start();
include('includes/db.php');

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Manejar la búsqueda de amigos
$search_results = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buscar'])) {
    $search_query = $conn->real_escape_string($_POST['search_query']);
    $sql_search = "SELECT id, username FROM usuarios WHERE username LIKE '%$search_query%' AND id != '$user_id'";
    $search_results = $conn->query($sql_search);
}

// Obtener la lista de amigos
$sql_friends = "SELECT u.id, u.username FROM seguidores s JOIN usuarios u ON s.seguido_id = u.id WHERE s.seguidor_id = '$user_id'";
$friends_list = $conn->query($sql_friends);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Amigos - DocSocial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container mt-5">
        <!-- Formulario de búsqueda de amigos -->
        <h2>Buscar Amigos</h2>
        <form method="post" action="amigos.php" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search_query" placeholder="Buscar usuarios..." required>
                <button class="btn btn-primary" type="submit" name="buscar">Buscar</button>
            </div>
        </form>

        <!-- Mostrar resultados de búsqueda -->
        <?php if (!empty($search_results) && $search_results->num_rows > 0): ?>
            <h4>Resultados de Búsqueda</h4>
            <ul class="list-group mb-4">
                <?php while ($user = $search_results->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        @<?php echo htmlspecialchars($user['username']); ?>
                        <form method="post" action="gestionar_amigos.php" class="d-inline">
                            <input type="hidden" name="amigo_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn btn-success btn-sm">Agregar</button>
                        </form>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buscar'])): ?>
            <p>No se encontraron usuarios.</p>
        <?php endif; ?>

        <!-- Mostrar amigos ya agregados -->
        <h2>Mis Amigos</h2>
        <?php if (!empty($friends_list) && $friends_list->num_rows > 0): ?>
            <ul class="list-group">
                <?php while ($friend = $friends_list->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        @<?php echo htmlspecialchars($friend['username']); ?>
                        <form method="post" action="gestionar_amigos.php" class="d-inline">
                            <input type="hidden" name="amigo_id" value="<?php echo $friend['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" name="eliminar">Eliminar</button>
                        </form>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No tienes amigos aún.</p>
        <?php endif; ?>
    </div>

    <?php include('includes/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
