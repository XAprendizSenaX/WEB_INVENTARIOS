<?php
// solicitudes.php
// Este archivo permite a los usuarios con rol 'user' solicitar productos.
session_start();
require_once 'includes/db.php';
require_once 'includes/header.php';

$mensaje = '';
$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['user_role'] ?? 'guest';
$solicitudes = [];
$productos_encontrados = [];
$productos_temp = [];

// Redirigir si no hay un usuario logueado
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Lógica para procesar la solicitud de productos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_solicitud'])) {
    $area_solicitante = trim($_POST['area_solicitante']);
    $productos_json = $_POST['productos_solicitados'];

    if (empty($area_solicitante) || empty($productos_json) || $productos_json == '[]') {
        $mensaje = "<p class='btn-danger'>❌ Por favor, ingrese el área solicitante y agregue al menos un producto.</p>";
    } else {
        try {
            $productos = json_decode($productos_json, true);

            // Insertar la solicitud en la tabla 'solicitudes'
            $stmt = $pdo->prepare("INSERT INTO solicitudes (user_id, area_solicitante, productos_solicitados) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $area_solicitante, $productos_json]);

            $mensaje = "<p class='btn-success'>✅ Solicitud enviada correctamente. Se notificará al administrador.</p>";
        } catch (PDOException $e) {
            $mensaje = "<p class='btn-danger'>❌ Error de base de datos al enviar la solicitud: " . $e->getMessage() . "</p>";
        }
    }
}

// Lógica para manejar el estado de los productos seleccionados a través de la recarga
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['productos_solicitados_temp'])) {
    $productos_temp = json_decode($_POST['productos_solicitados_temp'], true);
}

// Lógica de Búsqueda de Productos
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['buscar_producto'])) {
    $termino_busqueda = trim($_POST['termino_busqueda']);
    if (!empty($termino_busqueda)) {
        try {
            $stmt = $pdo->query("SELECT nombre_categoria FROM categorias ORDER BY nombre_categoria");
            $categorias_para_buscar = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($categorias_para_buscar as $cat) {
                $sql = "SELECT CODIGO, PRODUCTO FROM `$cat` WHERE CODIGO LIKE CONCAT('%', ?, '%') OR PRODUCTO LIKE CONCAT('%', ?, '%')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$termino_busqueda, $termino_busqueda]);
                $resultados_categoria = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $productos_encontrados = array_merge($productos_encontrados, $resultados_categoria);
            }
        } catch (PDOException $e) {
            $mensaje = "<p class='btn-danger'>❌ Error de base de datos durante la búsqueda: " . $e->getMessage() . "</p>";
        }
    }
}

// Lógica para mostrar solicitudes del usuario
try {
    $stmt = $pdo->prepare("SELECT * FROM solicitudes WHERE user_id = ? ORDER BY fecha_solicitud DESC");
    $stmt->execute([$user_id]);
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje = "<p class='btn-danger'>❌ Error al cargar sus solicitudes: " . $e->getMessage() . "</p>";
}
?>

<div class="container mt-5">
    <h2>Solicitar Productos</h2>
    <?php echo $mensaje; ?>

    <!-- Formulario combinado de búsqueda y solicitud -->
    <form action="solicitudes.php" method="POST" id="form-solicitud">
        <div class="card mb-4">
            <div class="card-header">
                <h4>Paso 1: Buscar y Seleccionar Productos</h4>
            </div>
            <div class="card-body">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="termino_busqueda" placeholder="Buscar producto por código o nombre..." >
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit" name="buscar_producto">Buscar</button>
                    </div>
                </div>

                <!-- Resultados de la búsqueda -->
                <?php if (!empty($productos_encontrados)): ?>
                    <h5>Resultados de la búsqueda:</h5>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos_encontrados as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['CODIGO']); ?></td>
                                    <td><?php echo htmlspecialchars($p['PRODUCTO']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-success btn-add-product"
                                            data-codigo="<?php echo htmlspecialchars($p['CODIGO']); ?>"
                                            data-nombre="<?php echo htmlspecialchars($p['PRODUCTO']); ?>">
                                            Añadir a la lista
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php elseif (isset($_POST['buscar_producto'])): ?>
                    <p>No se encontraron productos con ese término de búsqueda.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sección de la solicitud -->
        <div class="card">
            <div class="card-header">
                <h4>Paso 2: Completar la Solicitud</h4>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="area_solicitante">Área Solicitante:</label>
                    <input type="text" class="form-control" id="area_solicitante" name="area_solicitante">
                </div>

                <h5>Productos en tu lista:</h5>
                <div id="lista-productos-container" class="mb-3">
                    <table class="table table-bordered" id="tabla-productos">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Cantidad</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Filas de productos se agregarán aquí con JS -->
                            <?php foreach ($productos_temp as $p): ?>
                                <tr data-codigo-lista="<?php echo htmlspecialchars($p['codigo']); ?>">
                                    <td><?php echo htmlspecialchars($p['codigo']); ?></td>
                                    <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm cantidad-input" value="<?php echo htmlspecialchars($p['cantidad']); ?>" min="1" required>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm btn-remove-product">Eliminar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <input type="hidden" name="productos_solicitados_temp" id="productos_solicitados_temp_input">
                <input type="hidden" name="productos_solicitados" id="productos_solicitados_input">
                <button type="submit" name="enviar_solicitud" class="btn btn-primary btn-block">
                    Enviar Solicitud
                </button>
            </form>
        </div>
    </div>

    <hr>

    <!-- Historial de Solicitudes del Usuario -->
    <div class="card mt-5">
        <div class="card-header">
            <h4>Mi Historial de Solicitudes</h4>
        </div>
        <div class="card-body">
            <?php if (!empty($solicitudes)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Área</th>
                            <th>Productos</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $s): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($s['area_solicitante']); ?></td>
                                <td>
                                    <ul class="list-unstyled">
                                        <?php
                                            $productos_solicitados = json_decode($s['productos_solicitados'], true);
                                            foreach ($productos_solicitados as $p) {
                                                echo "<li>" . htmlspecialchars($p['nombre']) . " (" . htmlspecialchars($p['codigo']) . ") - Cantidad: " . htmlspecialchars($p['cantidad']) . "</li>";
                                            }
                                        ?>
                                    </ul>
                                </td>
                                <td><?php echo htmlspecialchars($s['fecha_solicitud']); ?></td>
                                <td>
                                    <span class="badge badge-pill badge-<?php
                                        switch ($s['estado']) {
                                            case 'pendiente': echo 'warning'; break;
                                            case 'entregado': echo 'success'; break;
                                            case 'cancelado': echo 'danger'; break;
                                            default: echo 'badge-info';
                                        }
                                    ?>">
                                        <?php echo ucfirst(htmlspecialchars($s['estado'])); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No ha enviado ninguna solicitud aún.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('form-solicitud');
        const btnsAddProduct = document.querySelectorAll('.btn-add-product');
        const tablaProductosBody = document.querySelector('#tabla-productos tbody');
        const productosSolicitadosInput = document.getElementById('productos_solicitados_input');
        const productosSolicitadosTempInput = document.getElementById('productos_solicitados_temp_input');

        function updateHiddenInputs() {
            const productos = [];
            document.querySelectorAll('#tabla-productos tbody tr').forEach(row => {
                const codigo = row.getAttribute('data-codigo-lista');
                const nombre = row.querySelector('td:nth-child(2)').textContent;
                const cantidad = row.querySelector('.cantidad-input').value;
                productos.push({
                    codigo: codigo,
                    nombre: nombre,
                    cantidad: parseInt(cantidad)
                });
            });
            const productosJson = JSON.stringify(productos);
            productosSolicitadosInput.value = productosJson;
            productosSolicitadosTempInput.value = productosJson;
        }

        // Cargar la lista inicial de productos si existen en el campo oculto
        updateHiddenInputs();

        // Escuchar el clic en los botones "Añadir a la lista"
        btnsAddProduct.forEach(btn => {
            btn.addEventListener('click', function() {
                const codigo = this.getAttribute('data-codigo');
                const nombre = this.getAttribute('data-nombre');

                // Prevenir duplicados
                if (document.querySelector(`[data-codigo-lista="${codigo}"]`)) {
                    // Reemplaza alert() con una notificación amigable
                    const mensajeContainer = document.querySelector('.container .mt-5');
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-warning alert-dismissible fade show mt-3';
                    alertDiv.role = 'alert';
                    alertDiv.innerHTML = `
                        Este producto ya está en tu lista de solicitud.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    `;
                    mensajeContainer.insertBefore(alertDiv, mensajeContainer.firstChild);

                    return;
                }

                // Crear la fila del producto en la tabla
                const row = document.createElement('tr');
                row.setAttribute('data-codigo-lista', codigo);
                row.innerHTML = `
                    <td>${codigo}</td>
                    <td>${nombre}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm cantidad-input" value="1" min="1" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm btn-remove-product">Eliminar</button>
                    </td>
                `;
                tablaProductosBody.appendChild(row);
                updateHiddenInputs();
            });
        });

        // Escuchar el clic en los botones "Eliminar" de la lista
        tablaProductosBody.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remove-product')) {
                e.target.closest('tr').remove();
                updateHiddenInputs();
            }
        });

        // Escuchar los cambios en las cantidades
        tablaProductosBody.addEventListener('input', function(e) {
            if (e.target.classList.contains('cantidad-input')) {
                updateHiddenInputs();
            }
        });

        // Asegurarse de que el formulario de búsqueda actualice el campo oculto
        form.addEventListener('submit', function(e) {
            // Evitar que el botón de búsqueda submita el formulario completo si no es la acción de envío principal
            if (e.target.querySelector('button[name="buscar_producto"]') === e.submitter) {
                updateHiddenInputs();
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
