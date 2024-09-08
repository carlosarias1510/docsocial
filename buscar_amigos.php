<?php
session_start();
include('includes/db.php');

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['busqueda'])) {
    $busqueda = $conn->real_escape_string($_POST['busqueda']);
    $sql = "SELECT id, username, nombre, apellido FROM usuarios WHERE username LIKE '%$busqueda%' AND id != '$user_id'";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar Amigos - DocSocial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="container mt-5">
        <h2>Buscar Amigos</h2>
        <form method="post" action="buscar_amigos.php">
            <div class="mb-3">
                <input type="text" class="form-control" name="busqueda" placeholder="Buscar por nombre de usuario" required>
            </div>
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>

        <?php if (isset($result) && $result->num_rows > 0): ?>
            <h3 class="mt-4">Resultados</h3>
            <ul class="list-group">
                <?php while ($usuario = $result->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($usuario['username']) . " (" . htmlspecialchars($usuario['nombre']) . " " . htmlspecialchars($usuario['apellido']) . ")"; ?>
                        <form method="post" action="gestionar_amigos.php" class="d-inline">
                            <input type="hidden" name="amigo_id" value="<?php echo $usuario['id']; ?>">
                            <button type="submit" name="agregar" class="btn btn-primary btn-sm">Agregar</button>
                        </form>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php elseif (isset($result)): ?>
            <p>No se encontraron usuarios.</p>
        <?php endif; ?>
    </div>

    <?php include('includes/footer.php'); ?>
</body>
</html>
