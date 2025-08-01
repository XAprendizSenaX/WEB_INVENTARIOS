<?php
// editar_producto.php
include 'includes/db.php';
include 'includes/header.php';

$mensaje = '';
$producto = null;

if (isset($_GET['id'])) {
    $id_producto = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ?");
        $stmt->execute([$id_producto]);
        $producto = $stmt->fetch();

        if (!$producto) {
            $mensaje = "<p class='btn-danger'>Producto no encontrado.</p>";
        }
    } catch (PDOException $e) {
        $mensaje = "<p class='btn-danger'>Error al cargar el producto: " . $e->getMessage() . "</p>";
    }
} else {
    $mensaje = "<p class='btn-danger'>ID de producto no especificado.</p>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $producto) {
    $id_producto = $_POST['id_producto']; // Asegúrate de pasar el ID oculto
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio_compra = trim($_POST['precio_compra']);
    $precio_venta = trim($_POST['precio_venta']);
    $stock = trim($_POST['stock']);
    $stock_minimo = trim($_POST['stock_minimo']);

    // Validaciones básicas
    if (empty($nombre) || empty($precio_compra) || empty($precio_venta) || $stock === '' || $stock_minimo === '') {
        $mensaje = "<p class='btn-danger'>Todos los campos obligatorios deben ser llenados.</p>";
    } elseif (!is_numeric($precio_compra) || !is_numeric($precio_venta) || !is_numeric($stock) || !is_numeric($stock_minimo)) {
        $mensaje = "<p class='btn-danger'>Los precios y el stock deben ser números.</p>";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio_compra = ?, precio_venta = ?, stock = ?, stock_minimo = ? WHERE id_producto = ?");
            $stmt->execute([$nombre, $descripcion, $precio_compra, $precio_venta, $stock, $stock_minimo, $id_producto]);
            $mensaje = "<p class='btn-success'>Producto actualizado correctamente.</p>";
            // Actualizar la variable $producto para mostrar los nuevos datos en el formulario
            $stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto = ?");
            $stmt->execute([$id_producto]);
            $producto = $stmt->fetch();
        } catch (PDOException $e) {
            $mensaje = "<p class='btn-danger'>Error al actualizar el producto: " . $e->getMessage() . "</p>";
        }
    }
}
?>

<h2>Editar Producto</h2>

<?php echo $mensaje; ?>

<?php if ($producto): ?>
    <form action="editar_producto.php?id=<?php echo htmlspecialchars($producto['id_producto']); ?>" method="POST">
        <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($producto['id_producto']); ?>">

        <label for="nombre">Nombre del Producto:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>

        <label for="descripcion">Descripción:</label>
        <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>

        <label for="precio_compra">Precio de Compra:</label>
        <input type="number" id="precio_compra" name="precio_compra" step="0.01" value="<?php echo htmlspecialchars($producto['precio_compra']); ?>" required>

        <label for="precio_venta">Precio de Venta:</label>
        <input type="number" id="precio_venta" name="precio_venta" step="0.01" value="<?php echo htmlspecialchars($producto['precio_venta']); ?>" required>

        <label for="stock">Stock Actual:</label>
        <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($producto['stock']); ?>" required>

        <label for="stock_minimo">Stock Mínimo (Alerta):</label>
        <input type="number" id="stock_minimo" name="stock_minimo" value="<?php echo htmlspecialchars($producto['stock_minimo']); ?>" required>

        <button type="submit" class="btn btn-warning">Actualizar Producto</button>
        <a href="productos.php" class="btn">Volver a Productos</a>
    </form>
<?php else: ?>
    <p>No se pudo cargar el producto para edición.</p>
    <a href="productos.php" class="btn">Volver a Productos</a>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>