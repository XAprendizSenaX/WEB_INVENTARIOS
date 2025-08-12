<?php
// categorias.php
// Este script gestiona la creación y eliminación de categorías (tablas).
include 'includes/db.php'; // Archivo de conexión a la base de datos
include 'includes/header.php'; // Cabecera HTML

$mensaje = '';
$categorias = [];


// --- Funciones auxiliares para la creación dinámica de tablas y triggers ---

/**
 * Crea una nueva tabla para una categoría en la base de datos.
 *
 * @param PDO $pdo Objeto de conexión PDO.
 * @param string $nombre_tabla El nombre de la nueva tabla a crear.
 * @return bool Retorna true si la tabla se creó con éxito, false en caso contrario.
 */
function crearTablaCategoria($pdo, $nombre_tabla)
{
    if (!preg_match('/^[a-z0-9_]+$/i', $nombre_tabla)) {
        return false;
    }

    // He corregido el tipo de dato de la unidad para que sea consistente
    $sql = "
        CREATE TABLE IF NOT EXISTS `$nombre_tabla` (
        CODIGO varchar(255) primary key,
        CODIGO_BARRAS varchar(255), 
        PRODUCTO varchar(255) not null,
        CANT bigint(255) NOT NULL,
        UNIDAD varchar(50)
        )";
    try {
        $pdo->exec($sql);
        $stmt = $pdo->query("SHOW TABLES LIKE '{$nombre_tabla}'");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Crea un trigger para generar el código de barras en una tabla específica.
 *
 * @param PDO $pdo Objeto de conexión PDO.
 * @param string $nombre_tabla El nombre de la tabla para el trigger.
 * @return bool Retorna true si el trigger se creó con éxito, false en caso contrario.
 */
function crearTriggerCodigoBarras($pdo, $nombre_tabla)
{
    if (!preg_match('/^[a-z0-9_]+$/i', $nombre_tabla)) {
        return false;
    }
    $trigger_name = "generar_codBarras_" . $nombre_tabla;
    $sql = "
        CREATE TRIGGER `$trigger_name`
        BEFORE INSERT ON `$nombre_tabla`
        FOR EACH ROW
        BEGIN
            SET NEW.CODIGO_BARRAS = CONCAT('*', NEW.CODIGO, '*');
        END
    ";
    try {
        $pdo->exec($sql);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}


// --- Lógica para manejar la creación y eliminación de categorías ---

// Procesar el formulario de "Crear nueva categoría"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nueva_categoria'])) {
    $nueva_categoria_nombre = trim($_POST['nueva_categoria_nombre']);
    if (empty($nueva_categoria_nombre)) {
        $mensaje = "<p class='btn-danger'>El nombre de la categoría no puede estar vacío.</p>";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $nueva_categoria_nombre)) {
        $mensaje = "<p class='btn-danger'>El nombre de la categoría solo puede contener letras, números y guiones bajos.</p>";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE nombre_categoria = ?");
            $stmt->execute([$nueva_categoria_nombre]);
            if ($stmt->fetchColumn() > 0) {
                $mensaje = "<p class='btn-danger'>La categoría '$nueva_categoria_nombre' ya existe.</p>";
            } else {
                $pdo->beginTransaction();
                if (!crearTablaCategoria($pdo, $nueva_categoria_nombre)) {
                    throw new Exception("Error: No se pudo crear la tabla. Verifique los permisos del usuario de la base de datos.");
                }
                if (!crearTriggerCodigoBarras($pdo, $nueva_categoria_nombre)) {
                    throw new Exception("Error: No se pudo crear el trigger. Verifique los permisos del usuario de la base de datos.");
                }
                $stmt = $pdo->prepare("INSERT INTO categorias (nombre_categoria) VALUES (?)");
                $stmt->execute([$nueva_categoria_nombre]);
                $pdo->commit();
                $mensaje = "<p class='btn-success'>Categoría '$nueva_categoria_nombre' creada correctamente.</p>";
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $mensaje = "<p class='btn-danger'>Error: " . $e->getMessage() . "</p>";
        }
    }
}


// Procesar el formulario de "Eliminar categoría"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar_categoria'])) {
    $eliminar_categoria_nombre = trim($_POST['eliminar_categoria_nombre']);
    if (empty($eliminar_categoria_nombre)) {
        $mensaje = "<p class='btn-danger'>Debe seleccionar una categoría para eliminar.</p>";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $eliminar_categoria_nombre)) {
        $mensaje = "<p class='btn-danger'>Nombre de categoría inválido.</p>";
    } else {
        try {
            $pdo->beginTransaction();
            // Eliminar triggers asociados
            $trigger_name = "generar_codBarras_" . $eliminar_categoria_nombre;
            $pdo->exec("DROP TRIGGER IF EXISTS `$trigger_name`");
            // Eliminar la tabla de la categoría
            $pdo->exec("DROP TABLE IF EXISTS `$eliminar_categoria_nombre`");
            // Eliminar el registro de la categoría
            $stmt = $pdo->prepare("DELETE FROM categorias WHERE nombre_categoria = ?");
            $stmt->execute([$eliminar_categoria_nombre]);
            $pdo->commit();
            $mensaje = "<p class='btn-success'>Categoría '$eliminar_categoria_nombre' eliminada correctamente.</p>";
            // Recargar la página para actualizar la lista
            echo "<script>window.location.href='categorias.php';</script>";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $mensaje = "<p class='btn-danger'>Error al eliminar la categoría: " . $e->getMessage() . "</p>";
        }
    }
}


// --- Lógica para mostrar la interfaz de usuario ---

// Obtener todas las categorías de la tabla 'categorias'
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

    <!-- Sección para crear una nueva categoría -->
    <h3>Crear Nueva Categoría</h3>
    <form action="categorias.php" method="POST">
        <label for="nueva_categoria_nombre">Nombre de la Categoría (Ej: electronicos):</label>
        <input type="text" id="nueva_categoria_nombre" name="nueva_categoria_nombre" required>
        <button type="submit" name="nueva_categoria">Crear Categoría</button>
    </form>
    <hr>

    <!-- Sección para listar categorías existentes con todas sus acciones -->
    <h3>Categorías Existentes</h3>
    <?php if (!empty($categorias)): ?>
        <ul>
            <?php foreach ($categorias as $cat): ?>
                <li>
                    <?php echo htmlspecialchars($cat); ?>
                    <!-- Enlace para ver los productos de la categoría -->
                    <a href="ver_productos.php?categoria=<?php echo htmlspecialchars($cat); ?>">Ver Productos</a>
                    <!-- Enlace para agregar un producto a la categoría -->
                    <a href="agregar_producto.php?categoria=<?php echo htmlspecialchars($cat); ?>">Agregar Producto</a>
                    <!-- Formulario para eliminar la categoría -->
                    <form action="categorias.php" method="POST" style="display:inline;"
                        onsubmit="return confirm('¿Está seguro de que desea eliminar la categoría <?php echo htmlspecialchars($cat); ?>? Esta acción no se puede deshacer.');">
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

<?php include 'includes/footer.php'; ?>
