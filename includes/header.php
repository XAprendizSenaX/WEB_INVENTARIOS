<?php
// header.php
// Incluye el inicio de la sesión si no ha sido iniciado ya
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener el nombre del archivo de la página actual
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRgcW54AkpoPfFPQacImJCIwpJEctdfJh4t0g&s"
        type="image/png">
    <title>Inventario NCS</title>
    <!-- Incluir Bootstrap CSS para un diseño limpio y responsive -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding-top: 20px;
        }

        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn-logout {
            background-color: #dc3545;
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="header container">
        <h1>Sistema de Inventario NCS</h1>

        <?php
        // Solo muestra el botón de cerrar sesión si el usuario está logueado
        // Y no está en la página de login
        if (isset($_SESSION['user_id']) && $current_page !== 'login.php') {
            echo '
            <nav>
                <ul>
                    <il><a href="index.php">Inicio</a></li>
                    <li><a href="producto_categoria.php">Productos</a></li>
                    <li><a href="categorias.php">Categorías</a></li>
                    <li><a href="reportes.php">Reportes</a></li>
                    <li><a href="buscar_producto.php">Buscar</a></li>
                </ul>
            </nav>
            <a href="logout.php" class="btn btn-danger btn-logout">Cerrar Sesión</a>';
        }
        ?>
    </div>

    <div class="container">
        <!-- El contenido de la página se insertará aquí -->