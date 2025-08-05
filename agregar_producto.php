<?php
// agregar_producto.php
// Este script agrega un nuevo producto a la tabla 'papeleria'
include 'includes/db.php';
include 'includes/header.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recolectar datos del formulario
    $codigo = $_POST['CODIGO'];
    $producto = $_POST['PRODUCTO'];
    $cant = $_POST['CANT'];
    $unidad = $_POST['UNIDAD'];

    if (!empty($codigo) && !empty($producto) && !empty($cant) && !empty($unidad)) {
        // Preparar la consulta SQL para agregar un nuevo producto
        $sql = 'INSERT INTO papeleria (CODIGO, PRODUCTO, CANT, UNIDAD) VALUES (?, ?, ?, ?)';
        $stmt = $pdo->prepare($sql);

        // Ejecutar la consulta con un array de parámetros
        if ($stmt->execute([$codigo, $producto, $cant, $unidad])) {
            $mensaje = "<p class='btn-success'>✅ Producto agregado correctamente.</p>";
        } else {
            $mensaje = "<p class='btn-danger'>❌ Error al agregar el producto.</p>";
        }
    } else {
        $mensaje = "<p class='btn-warning'>⚠️ Por favor, complete todos los campos.</p>";
    }
}
?>

<h2>Agregar Nuevo Producto</h2>
<?php echo $mensaje; ?>

<form action="agregar_producto.php" method="POST">
    <label for="codigo">Código:</label>
    <input type="text" id="codigo" name="CODIGO" required>

    <label for="producto">Nombre del Producto:</label>
    <input type="text" id="producto" name="PRODUCTO" required>
    
    <label for="cant">Cantidad:</label>
    <input type="number" id="cant" name="CANT" step="1" required>

    <label for="unidad">Medida:</label>
    <input type="text" id="unidad" name="UNIDAD" required>

    <button type="submit" class="btn btn-primary">Agregar Producto</button>
    <a href="producto_categoria.php" class="btn">Volver a Productos</a>
</form>

<?php include 'includes/footer.php'; ?>