<?php
// editar_producto.php
include 'includes/db.php';
include 'includes/header.php';

$mensaje = '';
$producto = null;

if (isset($_GET['CODIGO'])) {
    $CODIGO = $_GET['CODIGO'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM papeleria WHERE CODIGO = ?");
        $stmt->execute([$CODIGO]);
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
    $id_producto = $_POST['CODIGO']; // Asegúrate de pasar el ID oculto
    $descripcion = trim($_POST['PRODUCTO']);
    $cantidad = trim($_POST['CANT']);
    $unidad = trim($_POST['UNIDAD']);


    // Validaciones básicas
    if (empty($id_producto) || empty($descripcion) || empty($cantidad) || empty($unidad)) {
        $mensaje = "<p class='btn-danger'>Todos los campos obligatorios deben ser llenados.</p>";
    } elseif (!is_numeric($unidad)) {
        $mensaje = "<p class='btn-danger'>Los precios y el stock deben ser números.</p>";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE papeleria SET descripcion = ?, cantidad = ?, unidad = ? WHERE CODIGO = ?");
            $stmt->execute([$descripcion, $cantidad, $unidad, $id_producto]);
            $mensaje = "<p class='btn-success'>Producto actualizado correctamente.</p>";
            // Actualizar la variable $producto para mostrar los nuevos datos en el formulario
            $stmt = $pdo->prepare("SELECT * FROM descripcion WHERE CODIGO = ?");
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
    <form action="editar_producto.php?id=<?php echo htmlspecialchars($producto['CODIGO']); ?>" method="POST">
        <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($producto['CODIGO']); ?>">

        <label for="nombre">CODIGO:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['CODIGO']); ?>" required>
        
        <label for="nombre">PRODUCTO:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['PRODUCTO']); ?>" required>

        <label for="descripcion">CANTIDAD</label>
        <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($producto['CANTIDAD']); ?></textarea>
        
        <label for="descripcion">UNIDAD</label>
        <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($producto['CANTIDAD']); ?></textarea>

        <button type="submit" class="btn btn-warning">Actualizar Producto</button>
        <a href="productos.php" class="btn">Volver a Productos</a>
    </form>
<?php else: ?>
    <p>No se pudo cargar el producto para edición.</p>
    <a href="productos.php" class="btn">Volver a Productos</a>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>