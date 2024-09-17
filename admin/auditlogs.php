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
$result = mysqli_query($con,"SELECT id, table_name, plant_id, action_id, changed_by, change_time, old_value, new_value FROM auditlogs WHERE deleted_at IS NULL");

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
                                        <th>Nome da Tabela</th>
                                        <th>ID da Planta</th>
                                        <th>ID da Ação</th>
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
                                        echo '<td>' . htmlspecialchars($row['plant_id']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['action_id']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['changed_by']) . '</td>';
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
