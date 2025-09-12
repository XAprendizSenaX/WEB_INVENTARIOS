<?php
//solicitudes.php
//Esta pagina permite a los usuarios 'user' solicitar productos.
session_start();
require_once 'includes/db_solicitud.php';
require_once 'includes/header.php';

$mensaje = '';

//Verificar si el usuario está autenticado y es un usuario normal (no admin)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

//Logica para procesar la solicitud del usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_solicitud'])) {
    $codigo_producto = trim($_POST['codigo_producto']);
    $cantidad_solicitada = trim($_POST['cantidad_solicitada']);
    $nombre_producto = trim($_POST['nombre_producto']);

    if (empty($codigo_producto) || empty($cantidad_solicitada) || empty($nombre_producto)) {
        $mensaje = "<p class='btn-danger'>Por favor, Complete todos los campos.</p>";
    } else {
        try {
            //Conectarse a la base de datos de inventario
            $sql = "INSERT INTO `solicitudes` (user_id, codigo_producto, nombre_producto, cantidad_solicitada) VALUES (?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$_SESSION['user_id'], $codigo_producto, $nombre_producto, $cantidad_solicitada])) {
                $mensaje = "<p class='btn-success'>Solicitud envidad correctamente. El administrador la revisará pronto.</p>";
            } else {
                $mensaje = "<p class='btn-danger'>Error al enviar la solicitud.</p>";
            }
        } catch (PDOException $e) {
            $mensaje = "<p class='btn-danger'>Error de base de datos: " . $e->getMessage() . "</p>";
        }
    }
}

//Lógica para mostrar las solicitudes enviadas (opcional)
$solicitudes = [];
try {
    $sql = "SELECT * FROM `solicitudes` WHERE user_id = ? ORDER BY fecha_solicitud DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $solicitudes = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensaje = "<p class='btn-danger'> Error al cargar las solicitudes: " . $e->getMessage() . "</p>";
}
?>

<div class="conrtainer mt-4">
    <h2>Página de Solicitudes</h2>
    <?php echo $mensaje?>

    <?php if ($_SESSION['role'] === 'user'): ?>
        <div class='card mb-4'>
            <h4>Enviar una Nueva Solicitud</h4>
        </div>
        <div class="card-body">
            <form action="solicitudes.php" method="POST">
                <div class="form-group">
                    <label for="codigo_producto">Codigo de Producto:</label>
                    <input type="text" class="form-control" id="codigo_producto" name="codigo_producto" requerid>
                </div>
                <div class="form-group">
                    <label for="nombre_producto">Nombre de Producto:</label>
                    <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" requerid>
                </div>
                <div class="form-group">
                    <label for="cantidad_solicitada">Cantidad Solicitada:</label>
                    <input type="text" class="form-control" id="cantidad_solicitada" name="codigo_producto" requerid>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h4>Mis Solicitudes</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($solicitudes)): ?>
                <table class="table table-bordered table-striped">
                    <tr>
                       <th></th> 
                    </tr>
                </table>
        </div>
    </div>

