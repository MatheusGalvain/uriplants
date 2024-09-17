<?php
session_start();
include_once('includes/config.php');

// Verificar autenticação
if (!isset($_SESSION['id']) || strlen($_SESSION['id']) == 0) {
    header('location:logout.php');
    exit();
}

function filter_input_data($con, $data) {
    return mysqli_real_escape_string($con, trim($data));
}

// Consulta à tabela 'auditlogs' usando MySQLi
$result = mysqli_query($con,
"SELECT al.id, al.table_name, al.plant_id, al.action_id, a.name AS actionName, al.changed_by, al.change_time, al.old_value, al.new_value, u.fname AS userName, u.email AS email, p.name AS plantName
FROM auditlogs AS al
INNER JOIN actions AS a ON al.action_id = a.id
INNER JOIN users AS u ON al.changed_by = u.id
INNER JOIN plants AS p ON al.plant_id = p.id
WHERE al.deleted_at IS NULL
");

// Transformar os resultados em um array
$logs = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Gerenciamento de Logs</title>
</head>
<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>

    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4 h1">Histórico de Logs</h1>
                    <div class="card mb-4">
                        <div class="card-body">
                            <table id="logsTable" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tabela</th>
                                        <th>Planta</th>
                                        <th>Ação</th>
                                        <th>Alterado Por</th>
                                        <th>Hora da Alteração</th>
                                        <th>Valor Antigo</th>
                                        <th>Valor Novo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                     foreach ($logs as $row) {
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['table_name']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['plant_id']) . ' - ' . htmlspecialchars($row['plantName']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['actionName']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['userName']) . ' - ' . htmlspecialchars($row['email']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['change_time']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['old_value']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['new_value']) . '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <!-- Scripts adicionais, se necessário -->
</body>
</html>
