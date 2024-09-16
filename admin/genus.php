<?php
session_start();
include_once('includes/config.php');

// Verificar se o usuário está autenticado
if (strlen($_SESSION['id']) == 0) {
    header('location:logout.php');
    exit();
}

// Adicionar novo gênero
if (isset($_POST['add_genus'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);

    // Verificar se o nome do gênero já existe
    $query = mysqli_query($con, "SELECT * FROM genus WHERE name='$name'");
    if (mysqli_num_rows($query) > 0) {
        $error = "Um gênero com esse nome já existe.";
    } else {
        // Inserir novo gênero
        $sql = "INSERT INTO genus (name) VALUES ('$name')";
        if (mysqli_query($con, $sql)) {
            $success = "Gênero adicionado com sucesso.";
        } else {
            $error = "Erro ao adicionar gênero: " . mysqli_error($con);
        }
    }
}

// Processar a exclusão de um gênero
if (isset($_POST['delete_genus'])) {
    $id = intval($_POST['id']);

    // Marcar o gênero como excluído
    $sql = "UPDATE genus SET deleted_at = NOW() WHERE id = $id";
    if (mysqli_query($con, $sql)) {
        $success = "Gênero excluído com sucesso.";
    } else {
        $error = "Erro ao excluir gênero: " . mysqli_error($con);
    }
}

// Processar a edição de um gênero
if (isset($_POST['edit_genus'])) {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($con, $_POST['name']);

    // Atualizar o nome do gênero
    $sql = "UPDATE genus SET name = '$name' WHERE id = $id";
    if (mysqli_query($con, $sql)) {
        $success = "Gênero atualizado com sucesso.";
    } else {
        $error = "Erro ao atualizar gênero: " . mysqli_error($con);
    }
}

// Processar a busca
$search = isset($_POST['search']) ? mysqli_real_escape_string($con, $_POST['search']) : '';

// Obter todos os gêneros com base na busca
$searchQuery = $search ? "AND name LIKE '%$search%'" : "";
$genusQuery = mysqli_query($con, "SELECT * FROM genus WHERE deleted_at IS NULL $searchQuery");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Gêneros</title>
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4">Gerenciar Gêneros</h1>
                    
                    <?php if (isset($success)) { ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php } ?>

                    <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Adicionar um Novo Gênero</h5>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome do Gênero</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <button type="submit" name="add_genus" class="btn btn-primary">Adicionar gênero</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Botão de buscar e título -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Gêneros Cadastrados</h5>
                                <form method="POST" action="" class="d-flex">
                                    <input type="text" class="form-control me-2" name="search" placeholder="Buscar gêneros" value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-primary" type="submit">Buscar</button>
                                    <?php if ($search) { ?>
                                        <a href="genus.php" class="btn btn-secondary ms-2 w-100">Remover Filtro</a>
                                    <?php } ?>
                                </form>
                            </div>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nome do Gênero</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_array($genusQuery)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td>
                                                <!-- Botão para abrir o modal de edição -->
                                                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#editGenusModal" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>">
                                                    Editar
                                                </button>
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
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
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
                    Você realmente deseja excluir este gênero?
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="delete_genus" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição de Gênero -->
    <div class="modal fade" id="editGenusModal" tabindex="-1" aria-labelledby="editGenusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editGenusModalLabel">Editar Gênero</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Nome do Gênero</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <button type="submit" name="edit_genus" class="btn btn-primary">Atualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Script para preencher o ID e nome do gênero nos modais
        document.addEventListener('DOMContentLoaded', function() {
            var editButtons = document.querySelectorAll('[data-bs-target="#editGenusModal"]');
            var deleteButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#confirmDeleteModal"]');
            var editIdInput = document.getElementById('editId');
            var editNameInput = document.getElementById('editName');
            var deleteIdInput = document.getElementById('deleteId');
            
            editButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var id = this.getAttribute('data-id');
                    var name = this.getAttribute('data-name');
                    editIdInput.value = id;
                    editNameInput.value = name;
                });
            });

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
