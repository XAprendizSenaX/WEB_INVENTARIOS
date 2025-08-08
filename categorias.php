<?php
// categorias.php
// Este script gestiona la creación de nuevas categorías (tablas) y redirige a la página de agregar productos.
include 'includes/db.php'; // Archivo de conexión a la base de datos
include 'includes/header.php'; // Cabecera HTML

$mensaje = '';
$categorias = [];


// --- Funciones auxiliares para la creación dinámica de tablas y triggers ---

// Función para crear una nueva tabla de categoría
function crearTablaCategoria($pdo, $nombre_tabla) {
    // Asegurarse de que el nombre de la tabla sea seguro
    if (!preg_match('/^[a-z0-9_]+$/i', $nombre_tabla)) {
        return false; // Nombre de tabla no válido
    }

    $sql = "
        CREATE TABLE IF NOT EXISTS `$nombre_tabla` (
            CODIGO VARCHAR(255) PRIMARY KEY,
            CODIGO_BARRAS VARCHAR(255),
            PRODUCTO VARCHAR(255) NOT NULL,
            CANT INT NOT NULL,
            UNIDAD enum ('UNIDAD','CAJA','EMPAQUE','PACA', 'PAR','FRASCO') NOT NULL
        )
    ";
    return $pdo->exec($sql);
}

// Función para crear el trigger de código de barras para una tabla específica
function crearTriggerCodigoBarras($pdo, $nombre_tabla) {
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
    return $pdo->exec($sql);
}


// --- Lógica para manejar el envío de formularios ---

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
                $pdo->beginTransaction(); // Se inicia la transacción aquí, dentro del bloque try
                if (crearTablaCategoria($pdo, $nueva_categoria_nombre) === false) {
                    throw new Exception("Error al crear la tabla.");
                }
                if (crearTriggerCodigoBarras($pdo, $nueva_categoria_nombre) === false) {
                     throw new Exception("Error al crear el trigger.");
                }
                $stmt = $pdo->prepare("INSERT INTO categorias (nombre_categoria) VALUES (?)");
                $stmt->execute([$nueva_categoria_nombre]);
                $pdo->commit();
                $mensaje = "<p class='btn-success'>Categoría '$nueva_categoria_nombre' creada correctamente.</p>";
            }
        } catch (Exception $e) {
            // El rollback solo se intenta si la transacción se inició
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $mensaje = "<p class='btn-danger'>Error: " . $e->getMessage() . "</p>";
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

    <!-- Sección para listar categorías -->
    <h3>Categorías Existentes</h3>
    <?php if (!empty($categorias)): ?>
        <ul>
            <?php foreach ($categorias as $cat): ?>
                <li>
                    <?php echo htmlspecialchars($cat); ?>
                    <!-- Enlace que redirige a tu archivo para agregar productos -->
                    <a href="agregar_producto.php?categoria=<?php echo htmlspecialchars($cat); ?>">Agregar Producto</a>
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
