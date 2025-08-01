<?php
    include 'includes/db.php';
    include 'includes/header.php';


    try {
        $stmt_productosA = $pdo->query("SELECT * FROM aseo");
        $stmt_productosP = $pdo->query("SELECT COUNT(*) FROM papeleria");
        $stmt_productosF = $pdo->query("SELECT COUNT(*) FROM ferreteria");
    } catch (PDOException $e) {
        echo "<p class='error'>Error al cargar el dashboard" . $e->getMessage() . "</p>";
        $total_productos = 0;
        $entradas = [
            'aseo' => 0,
            'papeleria' => 0,
            'ferreteria' => 0
        ];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAq6z5l5e5l5e5l5e5l5e5l5e5l5e" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styleIndex.css">
    <link rel="icon" href="https://ncs.edu.co/wp-content/uploads/2023/08/NCS-PNG2.png" type="image/png">  
    <title>NCS INVENTARIOS</title>
</head>
<body>
    <h2>Resumen del Inventario</h2>
    <div class="dashborad-stats">
        <p>Productos de Aseo: <strong><?php echo $stmt_productosA->rowCount(); ?></strong></p>
        <p>Productos de Papelería: <strong><?php echo $stmt_productosP->fetchColumn(); ?></strong></p>
        <p>Productos de Ferretería: <strong><?php echo $stmt_productosF->fetchColumn(); ?></strong></p>
    </div>

    <?php include 'includes/footer.php'; ?>