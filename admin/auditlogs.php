<?php
include_once('includes/config.php');

check_user_session();

function display_value($value)
{
    return isset($value) ? htmlspecialchars($value) : 'N/A';
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$search_escaped = mysqli_real_escape_string($con, $search);

$limit = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$where = "al.deleted_at IS NULL";

if ($search !== '') {
    $where .= " AND (al.table_name LIKE '%$search_escaped%' OR p.name LIKE '%$search_escaped%')";
}

$count_query = "
    SELECT COUNT(*) AS total
    FROM auditlogs AS al
    LEFT JOIN plants AS p ON al.plant_id = p.id
    WHERE $where
";
$count_result = mysqli_query($con, $count_query);
$total_logs = $count_result ? mysqli_fetch_assoc($count_result)['total'] : 0;
$total_pages = ceil($total_logs / $limit);


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
    WHERE $where
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
    <link href="css/pagination.css" rel="stylesheet" />
    <title>Admin | Gerenciamento de Logs</title>
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h1 class="mt-4 mb-4 h1">Histórico de Logs</h1>
                        <a href="download_logs.php" class="btn btn-success">
                            <i class="fas fa-download"></i> Baixar Relatório
                        </a>
                    </div>
                    <form method="GET" class="mb-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Buscar por Tabela ou Planta" value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">Buscar</button>
                        </div>
                    </form>
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
                            <?php
                            $_GET['search'] = $search;
                            include('includes/pagination.php');
                            ?>
                        </div>
                    </div>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
</body>

</html>