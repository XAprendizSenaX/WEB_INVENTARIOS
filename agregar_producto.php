<?php
// editar_producto.php  
// Este script permite editar un producto de la tabla 'papeleria'
include 'includes/db.php';
include 'includes/header.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Recolectar datos del formulario
    $codigo = $_POST['CODIGO'];
    $producto = $_POST['PRODUCTO'];
    $cant = $_POST['CANT'];
    $unidad = $_POST['UNIDAD'];

    if (!empty($producto) && !empty($cant) && !empty ($unidad)) {
    //Preparar la consulta SQL para actualizar el producto
        $sql = 'INSERT INTO papeleria (PRODUCTO, CANT, UNIDAD) VALUES (?,?,?,?)';
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssis', $codigo, $producto, $cant, $unidad);


        if ($stmt->execute()) {
            $mensaje = "<p class='btn-success'>Producto actualizado correctamente.</p>";
        } else {
            $mensaje = "<p class='btn-danger'>Error al actualizar el producto: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        $mensaje = "<div class='alert alert-warning'>⚠️ Por favor, complete todos los campos.</div>";
    }
}
?>

<h2>Editar Producto</h2>

<?php echo $mensaje; ?>

<?php if ($producto): ?>
    <form action="editar_producto.php?CODIGO=<?php echo htmlspecialchars($producto['CODIGO']); ?>" method="POST">
        <!-- Campo oculto para pasar el CODIGO sin que el usuario lo edite -->

        <label for="codigo">Código:</label>
        <input type="text" id="codigo" name="codigo_display" value="<?php echo htmlspecialchars($producto['CODIGO']); ?>" disabled>

        <label for="producto">Nombre del Producto:</label>
        <input type="text" id="producto" name="PRODUCTO" value="<?php echo htmlspecialchars($producto['PRODUCTO']); ?>" required>
        
        <label for="cant">Cantidad:</label>
        <input type="number" id="cant" name="CANT" step="1" value="<?php echo htmlspecialchars($producto['CANT']); ?>" required>

        <label for="unidad">Medida:</label>
        <input type="text" id="unidad" name="UNIDAD" value="<?php echo htmlspecialchars($producto['UNIDAD']); ?>" required>
    
        <button type="submit" class="btn btn-warning">Actualizar Producto</button>
        <a href="producto_Papeleria.php" class="btn">Volver a Productos</a>
    </form>
<?php else: ?>
    <p>No se pudo cargar el producto para edición.</p>
    <a href="producto_Papeleria.php" class="btn">Volver a Productos</a>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
