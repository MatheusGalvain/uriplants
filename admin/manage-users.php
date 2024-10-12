<?php 
include_once('includes/config.php');

check_user_session();

// Verifica se o admin está logado
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Código para atualização do perfil
if (isset($_POST['update'])) {
    $fname = mysqli_real_escape_string($con, $_POST['fname']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $userid = intval($_POST['userid']);

    // Prepara a consulta de atualização com o e-mail
    $updateQuery = "UPDATE users SET fname='$fname', email='$email' WHERE id='$userid'";
    $msg = mysqli_query($con, $updateQuery);

    if ($msg) {
        echo "<script>alert('Perfil atualizado com sucesso');</script>";
        echo "<script type='text/javascript'>document.location = 'manage-users.php';</script>";
    } else {
        echo "<script>alert('Erro ao atualizar o perfil.');</script>";
    }
}

// Código para exclusão do usuário
if (isset($_GET['delete'])) {
    $deleteUserId = intval($_GET['delete']);
    
    if ($deleteUserId) {
        $deleteQuery = "DELETE FROM users WHERE id='$deleteUserId'";
        $msg = mysqli_query($con, $deleteQuery);
        
        if ($msg) {
            echo "<script>alert('Usuário excluído com sucesso');</script>";
        } else {
            echo "<script>alert('Erro ao excluir o usuário.');</script>";
        }
        
        echo "<script type='text/javascript'>document.location = 'manage-users.php';</script>";
    }
}

// Verifica se o ID do usuário para editar foi fornecido
$editUserId = isset($_GET['uid']) ? intval($_GET['uid']) : null;

if ($editUserId) {
    // Recupera os dados do usuário
    $query = mysqli_query($con, "SELECT * FROM users WHERE id='$editUserId'");
    $editUser = mysqli_fetch_array($query);
    if (!$editUser) {
        echo "<script>alert('Usuário não encontrado.');</script>";
        echo "<script type='text/javascript'>document.location = 'manage-users.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Gerenciamento de Usuários</title>

    <!-- Simple-DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css">
</head>
<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>

    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4 h1">Gerenciamento de Usuários</h1>

                    <a href="registermember.php" class="btn bg-primary text-white mb-3">Novo Usuário</a>
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-table me-1"></i>
                            Detalhes dos Usuários
                        </div>
                        <div class="card-body">
                            <?php if ($editUserId): ?>
                                <!-- Formulário de Edição -->
                                <div class="card mb-4">
                                    <form method="post">
                                        <input type="hidden" name="userid" value="<?php echo htmlspecialchars($editUser['id']); ?>" />
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>Nome:</th>
                                                <td>
                                                    <input class="form-control" id="fname" name="fname" type="text" value="<?php echo htmlspecialchars($editUser['fname']); ?>" required />
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Email:</th>
                                                <td colspan="3">
                                                    <input class="form-control" id="email" name="email" type="email" value="<?php echo htmlspecialchars($editUser['email']); ?>" required />
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" style="text-align:center;">
                                                    <button type="submit" class="btn btn-primary btn-block" name="update">Atualizar</button>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            <?php else: ?>
                                <!-- Tabela de Usuários -->
                                <table id="datatablesSimple" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nome</th>
                                            <th>E-mail</th>
                                            <?php if ($isAdmin): ?>
                                                <th>Ação</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $ret = mysqli_query($con, "SELECT * FROM users");
                                        $cnt = 1;
                                        while ($row = mysqli_fetch_array($ret)) { ?>
                                            <tr>
                                                <td><?php echo $cnt; ?></td>
                                                <td><?php echo htmlspecialchars($row['fname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <?php if ($isAdmin): ?>
                                                    <td>
                                                        <a href="?uid=<?php echo $row['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Você realmente deseja excluir?');">
                                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                                        </a>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php $cnt++; } ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" defer></script>

        <script>
        window.addEventListener('DOMContentLoaded', event => {
            const datatablesSimple = document.getElementById('datatablesSimple');
            if (datatablesSimple) {
                new simpleDatatables.DataTable(datatablesSimple, {
                    labels: {
                        placeholder: "Buscar...", 
                        perPage: "por página",
                        noRows: "Nenhum registro encontrado",
                        info: "Mostrando {start} a {end} de {rows} entradas",
                        noResults: "Nenhum resultado correspondente",
                        perPageSelect: "entradas"
                    }
                });
            }
        });
        </script>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
