<?php
session_start();
include_once('includes/config.php');

// Verificar se o usuário está autenticado
if (strlen($_SESSION['id']) == 0) {
    header('location:logout.php');
    exit();
}

// Adicionar nova classe
if (isset($_POST['add_class'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);

    // Verificar se o nome da classe já existe
    $query = mysqli_query($con, "SELECT * FROM classes WHERE name='$name'");
    if (mysqli_num_rows($query) > 0) {
        $error = "Uma classe com esse nome já existe.";
    } else {
        // Inserir nova classe
        $sql = "INSERT INTO classes (name) VALUES ('$name')";
        if (mysqli_query($con, $sql)) {
            $success = "Classe adicionada com sucesso.";
        } else {
            $error = "Erro ao adicionar classe: " . mysqli_error($con);
        }
    }
}

// Processar a exclusão de uma classe
if (isset($_POST['delete_class'])) {
    $id = intval($_POST['id']);

    // Marcar a classe como excluída
    $sql = "UPDATE classes SET deleted_at = NOW() WHERE id = $id";
    if (mysqli_query($con, $sql)) {
        $success = "Classe excluída com sucesso.";
    } else {
        $error = "Erro ao excluir classe: " . mysqli_error($con);
    }
}

// Processar a busca
$search = isset($_POST['search']) ? mysqli_real_escape_string($con, $_POST['search']) : '';

// Obter todas as classes com base na busca
$searchQuery = $search ? "AND name LIKE '%$search%'" : "";
$classesQuery = mysqli_query($con, "SELECT * FROM classes WHERE deleted_at IS NULL $searchQuery");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Classes</title>
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4">Gerenciar Classes</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item">Você está em: </li>
                        <li class="breadcrumb-item"><a href="welcome.php">dashboard</a></li>
                        <li class="breadcrumb-item active">classes</li>
                    </ol>

                    <?php if (isset($success)) { ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php } ?>

                    <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Adicionar uma Nova Classe</h5>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome da Classe</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <button type="submit" name="add_class" class="btn btn-primary">Adicionar classe</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Botão de buscar e título -->
                    <div class="card mb-4">
                        <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Classes Cadastradas</h5>
                            <form method="POST" action="" class="d-flex">
                                <input type="text" class="form-control me-2" name="search" placeholder="Buscar classes" value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit">Buscar</button>
                                <?php if ($search) { ?>
                                    <a href="classes.php" class="btn btn-secondary ms-2 w-100">Remover Filtro</a>
                                <?php } ?>
                            </form>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nome da Classe</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_array($classesQuery)) { ?>
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
                    Você realmente deseja excluir esta classe?
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="delete_class" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Script para preencher o ID da classe no modal
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
