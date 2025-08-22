<?php

include 'includes/db.php';
include 'includes/header.php';

$mensaje = '';
$categoria_seleccionada = isset($_GET['categoria']) ? $_GET['categoria'] : null;
$productos = [];

// --- Validacion Inicial de la cartegoria seleccionada---

if (empty($categoria_seleccionada)) {
    echo "<p class='btn-danger'>❌ Error: No se ha especificado una categoria para mostrar productos.</p>";
    include 'includes/footer.php';
    exit();
} else {
    //Asegurar de que ele nombre de la tabla sea seguro para Evitar SQL Injection
    if (!preg_match('/^[a-zA-Z0-9_]+$/i', $categoria_seleccionada)) {
        echo "<p class='btn-danger'>❌ Error: Nombre de categoria no valido.</p>";
        include 'includes/footer.php';
        exit();
    }
}

// --- Logica para eliminar un producto ---
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $CODIGO = $_GET['id'];
    try {
        //La consulta de eliminacion del producto
        $stmt = $pdo->prepare("DELETE FROM $categoria_seleccionada WHERE CODIGO = ?");
        $stmt->execute([$CODIGO]);
        $mensaje = "<p class='btn-succes'>✅ Producto eliminado correctamenta.</p>";
    } catch (PDOException $e) {
        $mensaje = "<p class='btn-danger'> Error al eliminar el producto: " . $e->getMessage() . "</p>";
    }
}

// --- Obtener los productos de la categoria seleccionada --- 
try {
    //La consulta ahora usa la tabla dinamica
    $stmt = $pdo->prepare("SELECT * FROM `$categoria_seleccionada` ORDER BY CODIGO ASC");
    $stmt->execute();
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    //Si la tabla no existe o hay un error , mostrar mensaje
    $mensaje = "<p class='btn-danger'>Error al cargar los productos de la categoria. '" . htmlspecialchars($categoria_seleccionada) . "':  " . $e->getMessage() . "</p>";
    $productos = [];
}
?>

<h2>Gestion de <?php echo htmlspecialchars($categoria_seleccionada); ?></h2>

<?php echo $mensaje; ?>

<p><a href="agregar_producto.php?categoria=<?php echo htmlspecialchars($categoria_seleccionada); ?>" class="btn btn-success">Agergar Nuevo Producto</a></p>
<p><a href="categorias.php" class="btn">Volver a Categorías</a></p>

<?php if (count($productos) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>CODIGO</th>
                <th>CODIGO DE BARRAS</th>
                <th>DESCRIPCION</th>
                <th>CANTIDAD</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $producto): ?>
            <tr>
                <td><?php echo htmlspecialchars($producto ['CODIGO']); ?></td>
                <td><?php echo htmlspecialchars($producto ['CODIGO_BARRAS']); ?></td>
                <td><?php echo htmlspecialchars($producto ['PRODUCTO']); ?></td>
                <td><?php echo htmlspecialchars($producto ['CANT']); ?></td>
                <td>
                    <a href="editar_producto.php?categoria=<?php echo htmlspecialchars($categoria_seleccionada); ?>&id=<?php echo htmlspecialchars($producto['CODIGO']); ?>" class="btn btn-warning">Ediatr</a>
                    <a href="ver_productos.php?categoria=<?php echo htmlspecialchars($categoria_seleccionada); ?>&action=eliminar&id=<?php echo htmlspecialchars($producto['CODIGO']); ?>" class="btn btn-danger" onclick="return confirm ('¿Está seguro de que desea eliminar el producton?');">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No hay productos registrados en el inventario para la categoria '<?php echo htmlspecialchars($categoria_seleccionada); ?>'.</p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>