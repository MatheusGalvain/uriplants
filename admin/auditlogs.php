<?php
session_start();
include_once('includes/config.php');

if (!isset($_SESSION['id']) || strlen($_SESSION['id']) == 0) {
    header('location:logout.php');
    exit();
}

function display_value($value) {
    return isset($value) ? htmlspecialchars($value) : 'N/A';
}

// Definir o número de registros por página
$limit = 20;

// Obter a página atual a partir dos parâmetros GET, padrão é 1
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;

// Calcular o OFFSET para a consulta SQL
$offset = ($page - 1) * $limit;

// Consulta para obter o total de logs
$count_query = "
    SELECT COUNT(*) AS total
    FROM auditlogs AS al
    WHERE al.deleted_at IS NULL
";
$count_result = mysqli_query($con, $count_query);
$total_logs = $count_result ? mysqli_fetch_assoc($count_result)['total'] : 0;

// Calcular o número total de páginas
$total_pages = ceil($total_logs / $limit);

// Modificar a consulta principal para ordenar do mais novo para o mais velho e adicionar LIMIT e OFFSET
$query = "
    SELECT 
        al.id, 
        al.table_name, 
        al.plant_id, 
        al.action_id, 
        a.name AS actionName, 
        al.changed_by, 
        al.change_time, 
        al.old_value, 
        al.new_value, 
        u.fname AS userName, 
        u.email AS email, 
        p.name AS plantName
    FROM auditlogs AS al
    INNER JOIN actions AS a ON al.action_id = a.id
    INNER JOIN users AS u ON al.changed_by = u.id
    LEFT JOIN plants AS p ON al.plant_id = p.id
    WHERE al.deleted_at IS NULL
    ORDER BY al.change_time DESC
    LIMIT $limit OFFSET $offset
";

$result = mysqli_query($con, $query);

$logs = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Gerenciamento de Logs</title>
    <!-- Adicionar estilos para a paginação -->
    <style>
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a, .pagination span {
            margin: 0 5px;
            padding: 8px 12px;
            text-decoration: none;
            border: 1px solid #ddd;
            color: #007bff;
        }
        .pagination a:hover {
            background-color: #f1f1f1;
        }
        .pagination .active {
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
        }
        .pagination .disabled {
            color: #ccc;
            pointer-events: none;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>

    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <!-- Adicionar o Botão "Baixar Relatório" -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1 class="mt-4 mb-4 h1">Histórico de Logs</h1>
                        <a href="download_logs.php" class="btn btn-success">
                            <i class="fas fa-download"></i> Baixar Relatório
                        </a>
                    </div>
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
                                    <?php if (count($logs) > 0): ?>
                                        <?php foreach ($logs as $row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                                <td><?php echo display_value($row['table_name']); ?></td>
                                                <td>
                                                    <?php 
                                                    if (!empty($row['plant_id']) && !empty($row['plantName'])) {
                                                        echo htmlspecialchars($row['plant_id']) . ' - ' . htmlspecialchars($row['plantName']);
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo display_value($row['actionName']); ?></td>
                                                <td>
                                                    <?php 
                                                    if (!empty($row['userName']) && !empty($row['email'])) {
                                                        echo htmlspecialchars($row['userName']) . ' - ' . htmlspecialchars($row['email']);
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo display_value($row['change_time']); ?></td>
                                                <td><?php echo display_value($row['old_value']); ?></td>
                                                <td><?php echo display_value($row['new_value']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Nenhum log encontrado.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            
                            <!-- Paginação -->
                            <?php if ($total_pages > 1): ?>
                                <div class="pagination">
                                    <!-- Link para a página anterior -->
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>">&laquo; Anterior</a>
                                    <?php else: ?>
                                        <span class="disabled">&laquo; Anterior</span>
                                    <?php endif; ?>

                                    <!-- Links para páginas individuais -->
                                    <?php
                                    // Definir o intervalo de páginas a serem exibidas
                                    $range = 2;
                                    for ($i = max(1, $page - $range); $i <= min($page + $range, $total_pages); $i++):
                                        if ($i == $page):
                                    ?>
                                            <span class="active"><?php echo $i; ?></span>
                                        <?php else: ?>
                                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        <?php endif; ?>
                                    <?php endfor; ?>

                                    <!-- Link para a próxima página -->
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>">Próxima &raquo;</a>
                                    <?php else: ?>
                                        <span class="disabled">Próxima &raquo;</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <!-- Fim da Paginação -->

                        </div>
                    </div>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

</body>
</html>
