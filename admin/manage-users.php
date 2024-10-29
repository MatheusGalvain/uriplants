<?php 
include_once('includes/config.php');

check_user_session();

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $fname = mysqli_real_escape_string($con, $_POST['fname']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $userid = intval($_POST['userid']);

        $updateQuery = "UPDATE users SET fname='$fname', email='$email' WHERE id='$userid'";
        $msg = mysqli_query($con, $updateQuery);

        if ($msg) {
            $message = 'Perfil atualizado com sucesso';
        } else {
            $message = 'Erro ao atualizar o perfil.';
        }
    }

    if (isset($_POST['delete'])) {
        $deleteUserId = intval($_POST['delete']);
        
        if ($deleteUserId != 1) {
            $deleteQuery = "DELETE FROM users WHERE id='$deleteUserId'";
            $msg = mysqli_query($con, $deleteQuery);
            
            if ($msg) {
                $message = 'Usuário excluído com sucesso';
            } else {
                $message = 'Erro ao excluir o usuário.';
            }
        }
    }
}

$editUserId = isset($_GET['uid']) ? intval($_GET['uid']) : null;

if ($editUserId) {
    $query = mysqli_query($con, "SELECT * FROM users WHERE id='$editUserId'");
    $editUser = mysqli_fetch_array($query);
    if (!$editUser) {
        $message = 'Usuário não encontrado.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Gerenciamento de Usuários</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>

    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4 h1">Gerenciamento de Usuários</h1>

                    <?php if ($editUserId && isset($editUser)): ?>
                
                        <div class="d-flex justify-content-between mb-3">
                            <h2>Editar Usuário</h2>
                            <a href="manage-users.php" class="btn btn-secondary">Voltar</a>
                        </div>

                 
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
                                            <a href="manage-users.php" class="btn btn-secondary">Voltar</a>
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>

                    <?php else: ?>

                        <a href="registermember.php" class="btn bg-primary text-white mb-3">Novo Usuário</a>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Detalhes dos Usuários
                            </div>
                            <div class="card-body">
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
                                                    <?php if ($row['id'] == 1): ?>
                                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm" disabled>
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <a href="?uid=<?php echo $row['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="#" class="btn btn-danger btn-sm delete-user-btn" data-user-id="<?php echo $row['id']; ?>">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                            </tr>
                                        <?php $cnt++; } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    <?php endif; ?>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>


    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" >
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" id="deleteForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Exclusão</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        Você realmente deseja excluir este usuário?
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="delete" id="deleteUserId" value="">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mensagem</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body" id="messageModalBody">
             
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
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

        function showMessageModal(message) {
            const modalBody = document.getElementById('messageModalBody');
            modalBody.textContent = message;
            const modalElement = document.getElementById('messageModal');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-user-btn');
            const confirmDeleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            const deleteUserIdInput = document.getElementById('deleteUserId');
            const deleteForm = document.getElementById('deleteForm');

            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault(); 

                    const userId = this.getAttribute('data-user-id');
                    deleteUserIdInput.value = userId;

                    confirmDeleteModal.show();
                });
            });
        });
    </script>

    <?php if (!empty($message)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showMessageModal('<?php echo addslashes($message); ?>');
            });
        </script>
    <?php endif; ?>

</body>
</html>
