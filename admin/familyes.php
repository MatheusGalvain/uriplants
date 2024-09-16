<?php
    session_start();
    include_once('includes/config.php');

    // Verificar se o usuário está autenticado
    if (strlen($_SESSION['id']) == 0) {
        header('location:logout.php');
        exit();
    }

    // Adicionar nova família
    if (isset($_POST['add_family'])) {
        $name = mysqli_real_escape_string($con, $_POST['name']);

        // Verificar se o nome da família já existe
        $query = mysqli_query($con, "SELECT * FROM families WHERE name='$name'");
        if (mysqli_num_rows($query) > 0) {
            $error = "Uma família com esse nome já existe.";
        } else {
            // Inserir nova família
            $sql = "INSERT INTO families (name) VALUES ('$name')";
            if (mysqli_query($con, $sql)) {
                $success = "Família adicionada com sucesso.";
            } else {
                $error = "Erro ao adicionar família: " . mysqli_error($con);
            }
        }
    }

    // Processar a exclusão de uma família
    if (isset($_POST['delete_family'])) {
        $id = intval($_POST['id']);

        // Marcar a família como excluída
        $sql = "UPDATE families SET deleted_at = NOW() WHERE id = $id";
        if (mysqli_query($con, $sql)) {
            $success = "Família excluída com sucesso.";
        } else {
            $error = "Erro ao excluir família: " . mysqli_error($con);
        }
    }

    // Processar a busca
    $search = isset($_POST['search']) ? mysqli_real_escape_string($con, $_POST['search']) : '';

    // Obter todas as famílias com base na busca
    $searchQuery = $search ? "AND name LIKE '%$search%'" : "";
    $familiesQuery = mysqli_query($con, "SELECT * FROM families WHERE deleted_at IS NULL $searchQuery");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Famílias</title>
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4">Gerenciar Famílias</h1>

                    <?php if (isset($success)) { ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php } ?>

                    <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Adicionar uma Nova Família</h5>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome da Família</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <button type="submit" name="add_family" class="btn btn-primary">Adicionar família</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Botão de buscar e título -->
                    <div class="card mb-4">
                        <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Famílias Cadastradas</h5>
                            <form method="POST" action="" class="d-flex">
                                <input type="text" class="form-control me-2" name="search" placeholder="Buscar famílias" value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit">Buscar</button>
                                <?php if ($search) { ?>
                                    <a href="families.php" class="btn btn-secondary ms-2 w-100">Remover Filtro</a>
                                <?php } ?>
                            </form>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nome da Família</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_array($familiesQuery)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                                        <td>
                                            <!-- Botão para abrir o modal de confirmação -->
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                                Excluir
                                            </button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Você realmente deseja excluir esta família?
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="delete_family" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Script para preencher o ID da família no modal
        document.addEventListener('DOMContentLoaded', function() {
            var deleteButtons = document.querySelectorAll('[data-bs-toggle="modal"]');
            var deleteIdInput = document.getElementById('deleteId');
            
            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    deleteIdInput.value = id;
                });
            });
        });
    </script>
</body>

</html>
