<?php
// papeleria.php
include 'includes/db.php';
include 'includes/header.php';

$mensaje = '';

// Lógica para eliminar un producto
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $CODIGO = $_GET['id'];
    try {
        // Eliminar también los movimientos asociados (gracias a ON DELETE CASCADE)
        $stmt = $pdo->prepare("DELETE FROM papeleria WHERE CODIGO = ?");
        $stmt->execute([$CODIGO]);
        $mensaje = "<p class='btn-success'>Producto eliminado correctamente.</p>";
    } catch (PDOException $e) {
        $mensaje = "<p class='btn-danger'>Error al eliminar el producto: " . $e->getMessage() . "</p>";
    }
}

// Obtener todos los papeleria
try {
    $stmt = $pdo->query("SELECT * FROM papeleria ORDER BY CODIGO ASC");
    $papeleria = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "<p class='error'>Error al cargar los papeleria: " . $e->getMessage() . "</p>";
    $papeleria = [];
}
?>

<h2>Gestión de papeleria</h2>

<?php echo $mensaje; ?>

<p><a href="agregar_producto.php" class="btn btn-success">Agregar Nuevo Producto</a></p>

<?php if (count($papeleria) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>CODIGO</th>
                <th>CODIGO DE BARAS</th>
                <th>DESCRIPCION</th>
                <th>CANTIDAD</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($papeleria as $papeleria): ?>
            <tr>
                <td><?php echo htmlspecialchars($papeleria['CODIGO']); ?></td>
                <td><?php echo htmlspecialchars($papeleria['CODIGO_BARRAS']); ?></td>
                <td><?php echo htmlspecialchars($papeleria['PRODUCTO']); ?></td>
                <td><?php echo htmlspecialchars($papeleria['CANT']); ?></td>
                <td>
                    <a href="editar_producto.php?id=<?php echo $papeleria['CODIGO']; ?>" class="btn btn-warning">Editar</a>
                    <a href="producto_Papeleria.php?action=eliminar&id=<?php echo $papeleria['CODIGO']; ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar este producto? Esto también eliminará sus movimientos asociados.');">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No hay papeleria registrados en el inventario.</p>
<?php endif; ?>



<?php

// Tabla Ferrerteria
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $CODIGO = $_GET['id'];
    try {
        // Eliminar también los movimientos asociados (gracias a ON DELETE CASCADE)
        $stmt = $pdo->prepare("DELETE FROM ferreteria WHERE CODIGO = ?");
        $stmt->execute([$CODIGO]);
        $mensaje = "<p class='btn-success'>Producto eliminado correctamente.</p>";
    } catch (PDOException $e) {
        $mensaje = "<p class='btn-danger'>Error al eliminar el producto: " . $e->getMessage() . "</p>";
    }
}

// Obtener todos los Ferreteria
try {
    $stmt = $pdo->query("SELECT * FROM ferreteria ORDER BY CODIGO ASC");
    $ferreteria = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "<p class='error'>Error al cargar los papeleria: " . $e->getMessage() . "</p>";
    $ferreteria = [];
}
?>

<h2>Gestión de Ferreteria</h2>

<?php echo $mensaje; ?>

<p><a href="agregar_producto.php" class="btn btn-success">Agregar Nuevo Producto</a></p>

<?php if (count($ferreteria) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>CODIGO</th>
                <th>CODIGO DE BARAS</th>
                <th>DESCRIPCION</th>
                <th>CANTIDAD</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ferreteria as $ferreteria): ?>
            <tr>
                <td><?php echo htmlspecialchars($ferreteria['CODIGO']); ?></td>
                <td><?php echo htmlspecialchars($ferreteria['CODIGO_BARRAS']); ?></td>
                <td><?php echo htmlspecialchars($ferreteria['PRODUCTO']); ?></td>
                <td><?php echo htmlspecialchars($ferreteria['CANT']); ?></td>
                <td>
                    <a href="editar_producto.php?id=<?php echo $ferreteria['CODIGO']; ?>" class="btn btn-warning">Editar</a>
                    <a href="producto_Papeleria.php?action=eliminar&id=<?php echo $ferreteria['CODIGO']; ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar este producto? Esto también eliminará sus movimientos asociados.');">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No hay papeleria registrados en el inventario.</p>
<?php endif; ?>


<?php
// Tabla Aseo
if (isset($_GET['action']) && $_GET['action'] == 'eliminar' && isset($_GET['id'])) {
    $CODIGO = $_GET['id'];
    try {
        // Eliminar también los movimientos asociados (gracias a ON DELETE CASCADE)
        $stmt = $pdo->prepare("DELETE FROM aseo WHERE CODIGO = ?");
        $stmt->execute([$CODIGO]);
        $mensaje = "<p class='btn-success'>Producto eliminado correctamente.</p>";
    } catch (PDOException $e) {
        $mensaje = "<p class='btn-danger'>Error al eliminar el producto: " . $e->getMessage() . "</p>";
    }
}

// Obtener todos los Ferreteria
try {
    $stmt = $pdo->query("SELECT * FROM aseo ORDER BY CODIGO ASC");
    $aseo = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "<p class='error'>Error al cargar los papeleria: " . $e->getMessage() . "</p>";
    $aseo = [];
}
?>

<h2>Gestión de Aseo</h2>

<?php echo $mensaje; ?>

<p><a href="agregar_producto.php" class="btn btn-success">Agregar Nuevo Producto</a></p>

<?php if (count($aseo) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>CODIGO</th>
                <th>CODIGO DE BARAS</th>
                <th>DESCRIPCION</th>
                <th>CANTIDAD</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($aseo as $aseo): ?>
            <tr>
                <td><?php echo htmlspecialchars($aseo['CODIGO']); ?></td>
                <td><?php echo htmlspecialchars($aseo['CODIGO_BARRAS']); ?></td>
                <td><?php echo htmlspecialchars($aseo['PRODUCTO']); ?></td>
                <td><?php echo htmlspecialchars($aseo['CANT']); ?></td>
                <td>
                    <a href="editar_producto.php?id=<?php echo $aseo['CODIGO']; ?>" class="btn btn-warning">Editar</a>
                    <a href="producto_Papeleria.php?action=eliminar&id=<?php echo $aseo['CODIGO']; ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar este producto? Esto también eliminará sus movimientos asociados.');">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No hay papeleria registrados en el inventario.</p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>