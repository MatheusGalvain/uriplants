<?php
session_start();
include_once('includes/config.php');

// Verificar se o usuário está autenticado
if (!isset($_SESSION['id']) || strlen($_SESSION['id']) == 0) {
    header('location:logout.php');
    exit();
}

// Definir cabeçalhos para download de arquivo CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=relatorio_logs_' . date('Y-m-d') . '.csv');

// Abrir a saída como um arquivo para escrita
$output = fopen('php://output', 'w');

// Adicionar a BOM UTF-8 para que o Excel reconheça corretamente os caracteres acentuados
fwrite($output, "\xEF\xBB\xBF");

// Definir os cabeçalhos das colunas no CSV
fputcsv($output, ['ID', 'Tabela', 'Planta', 'Ação', 'Alterado Por', 'Email', 'Hora da Alteração', 'Valor Antigo', 'Valor Novo']);

// Consulta para obter todos os logs
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
";

// Executar a consulta
$result = mysqli_query($con, $query);

// Verificar se a consulta foi bem-sucedida
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Preparar os dados para o CSV
        $data = [
            $row['id'],
            $row['table_name'] ?? 'N/A',
            (!empty($row['plant_id']) && !empty($row['plantName'])) ? $row['plant_id'] . ' - ' . $row['plantName'] : 'N/A',
            $row['actionName'] ?? 'N/A',
            $row['userName'] ?? 'N/A',
            $row['email'] ?? 'N/A',
            $row['change_time'] ?? 'N/A',
            $row['old_value'] ?? 'N/A',
            $row['new_value'] ?? 'N/A'
        ];

        // Escrever a linha no CSV
        fputcsv($output, $data);
    }
} else {
    // Se a consulta falhar, registrar o erro e informar o usuário
    error_log("Erro ao gerar relatório de logs: " . mysqli_error($con));
    // Escrever uma linha indicando a falha
    fputcsv($output, ['Erro ao gerar relatório de logs. Por favor, tente novamente mais tarde.']);
}

// Fechar o arquivo de saída
fclose($output);
exit();
?>