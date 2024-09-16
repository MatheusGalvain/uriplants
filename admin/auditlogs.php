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
                            <table id="logsTable" class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Table Name</th>
                                        <th>Plant ID</th>
                                        <th>Action ID</th>
                                        <th>Changed By</th>
                                        <th>Change Time</th>
                                        <th>Old Value</th>
                                        <th>New Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetch('fetch_logs.php')
                .then(response => {
                    if (response.status === 403) {
                        throw new Error('Acesso negado.');
                    }
                    return response.json();
                })
                .then(data => {
                    const tableBody = document.querySelector('#logsTable tbody');
                    data.forEach(log => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${log.id}</td>
                            <td>${log.table_name}</td>
                            <td>${log.plant_id}</td>
                            <td>${log.action_id}</td>
                            <td>${log.changed_by}</td>
                            <td>${log.change_time}</td>
                            <td>${log.old_value}</td>
                            <td>${log.new_value}</td>
                        `;
                        tableBody.appendChild(row);
                    });

                    new DataTable('#logsTable');
                })
                .catch(error => {
                    console.error('Erro:', error.message);
                    const tableBody = document.querySelector('#logsTable tbody');
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="8">Erro ao carregar dados: ${error.message}</td>`;
                    tableBody.appendChild(row);
                });
        });
    </script>
</body>
</html>
