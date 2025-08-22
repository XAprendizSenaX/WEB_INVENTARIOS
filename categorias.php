<?php
// categorias.php
// Gestión de categorías (creación, listado y eliminación)
session_start(); // Iniciar la sesión
require_once 'includes/db.php';
require_once 'includes/header.php';

$mensaje = '';
$categorias = [];

// --- Funciones auxiliares ---

/**
 * Valida el nombre de la categoría.
 */
function validarNombreCategoria($nombre)
{
    return preg_match('/^[a-zA-Z0-9_]+$/', $nombre);
}

/**
 * Crea una tabla para la nueva categoría.
 */
function crearTablaCategoria(PDO $pdo, $nombre)
{
    $sql = "
        CREATE TABLE IF NOT EXISTS `$nombre` (
            CODIGO VARCHAR(255) PRIMARY KEY,
            CODIGO_BARRAS VARCHAR(255),
            PRODUCTO VARCHAR(255) NOT NULL,
            CANT BIGINT(255) NOT NULL,
            UNIDAD VARCHAR(50)
        )";
    $pdo->exec($sql);
}

/**
 * Elimina la tabla de la categoría.
 */
function eliminarTablaCategoria(PDO $pdo, $nombre)
{
    $pdo->exec("DROP TABLE IF EXISTS `$nombre`");
}

// --- Procesamiento de formularios ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crear categoría
    if (isset($_POST['nueva_categoria'])) {
        $nombre = trim($_POST['nueva_categoria_nombre']);
        if (!$nombre) {
            $mensaje = "<p class='btn-danger'>El nombre de la categoría no puede estar vacío.</p>";
        } elseif (!validarNombreCategoria($nombre)) {
            $mensaje = "<p class='btn-danger'>Solo letras, números y guiones bajos.</p>";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE nombre_categoria = ?");
                $stmt->execute([$nombre]);
                if ($stmt->fetchColumn()) {
                    $mensaje = "<p class='btn-danger'>La categoría '$nombre' ya existe.</p>";
                } else {
                    $pdo->beginTransaction();
                    crearTablaCategoria($pdo, $nombre);
                    $stmt = $pdo->prepare("INSERT INTO categorias (nombre_categoria) VALUES (?)");
                    $stmt->execute([$nombre]);
                    $pdo->commit();
                    $_SESSION['success_message'] = "Categoría '$nombre' creada correctamente.";
                    header("Location: categorias.php");
                    exit;
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $mensaje = "<p class='btn-danger'>Error: " . $e->getMessage() . "</p>";
            }
        }
    }

    // Eliminar categoría
    if (isset($_POST['eliminar_categoria'])) {
        $nombre = trim($_POST['eliminar_categoria_nombre']);
        if (!$nombre) {
            $mensaje = "<p class='btn-danger'>Debe seleccionar una categoría para eliminar.</p>";
        } elseif (!validarNombreCategoria($nombre)) {
            $mensaje = "<p class='btn-danger'>Nombre de categoría inválido.</p>";
        } else {
            eliminarTablaCategoria($pdo, $nombre);
            $stmt = $pdo->prepare("DELETE FROM categorias WHERE nombre_categoria = ?");
            $stmt->execute([$nombre]);
            $_SESSION['success_message'] = "Categoría '$nombre' eliminada correctamente.";
            header("Location: categorias.php");
            exit;
        }
    }
}

// Lógica para mostrar mensajes de sesión
if (isset($_SESSION['success_message'])) {
    $mensaje = "<p class='btn-success'>" . htmlspecialchars($_SESSION['success_message']) . "</p>";
    unset($_SESSION['success_message']); // Limpiar el mensaje de la sesión
}

// --- Obtener categorías ---
try {
    $stmt = $pdo->query("SELECT nombre_categoria FROM categorias ORDER BY nombre_categoria");
    $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $mensaje = "<p class='btn-danger'>Error al cargar las categorías: " . $e->getMessage() . "</p>";
    $categorias = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestor de Categorías</title>
</head>
<body>
    <h2>Gestor de Categorías</h2>
    <?php echo $mensaje; ?>

    <!-- Crear nueva categoría -->
    <h3>Crear Nueva Categoría</h3>
    <form action="categorias.php" method="POST">
        <label for="nueva_categoria_nombre">Nombre de la Categoría:</label>
        <input type="text" id="nueva_categoria_nombre" name="nueva_categoria_nombre" required>
        <button type="submit" name="nueva_categoria">Crear</button>
    </form>
    <hr>

    <!-- Listado de categorías -->
    <h3>Categorías Existentes</h3>
    <?php if ($categorias): ?>
        <ul>
            <?php foreach ($categorias as $cat): ?>
                <li>
                    <?php echo htmlspecialchars($cat); ?>
                    <a href="ver_productos.php?categoria=<?php echo urlencode($cat); ?>">Ver Productos</a>
                    <a href="agregar_producto.php?categoria=<?php echo urlencode($cat); ?>">Agregar Producto</a>
                    <form action="categorias.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar la categoría <?php echo htmlspecialchars($cat); ?>? Esta acción no se puede deshacer.');">
                        <input type="hidden" name="eliminar_categoria_nombre" value="<?php echo htmlspecialchars($cat); ?>">
                        <button type="submit" name="eliminar_categoria" style="color:red; background:none; border:none; cursor:pointer;">Eliminar</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No hay categorías disponibles.</p>
    <?php endif; ?>
    <hr>
</body>
</html>

<?php require_once 'includes/footer.php'; ?>
