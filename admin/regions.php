<?php
session_start();
include_once('includes/config.php');

// Verificar se o usuário está autenticado
if (strlen($_SESSION['id']) == 0) {
    header('location:logout.php');
    exit();
}

// Adicionar nova região
if (isset($_POST['add_region'])) {
    $source = mysqli_real_escape_string($con, $_POST['source']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = file_get_contents($_FILES['image']['tmp_name']);

        // Inserir nova região
        $sql = "INSERT INTO RegionMap (imagem, source, description) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'bss', $image, $source, $description);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Região adicionada com sucesso.";
        } else {
            $error = "Erro ao adicionar região: " . mysqli_error($con);
        }
    } else {
        $error = "Por favor, selecione uma imagem.";
    }
}

// Processar a exclusão de uma região
if (isset($_POST['delete_region'])) {
    $id = intval($_POST['id']);

    // Excluir a região
    $sql = "DELETE FROM RegionMap WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (mysqli_stmt_execute($stmt)) {
        $success = "Região excluída com sucesso.";
    } else {
        $error = "Erro ao excluir região: " . mysqli_error($con);
    }
}

// Processar a busca
$search = isset($_POST['search']) ? mysqli_real_escape_string($con, $_POST['search']) : '';

// Obter todas as regiões com base na busca
$searchQuery = $search ? "AND source LIKE '%$search%'" : "";
$regionsQuery = mysqli_query($con, "SELECT * FROM RegionMap WHERE 1=1 $searchQuery");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Regiões</title>
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4">Gerenciar Regiões</h1>

                    <?php if (isset($success)) { ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php } ?>

                    <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Adicionar uma Nova Região</h5>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="source" class="form-label">Fonte</label>
                                    <input type="text" class="form-control" id="source" name="source" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Descrição</label>
                                    <input type="text" class="form-control" id="description" name="description" required>
                                </div>
                                <div class="mb-3">
                                    <label for="image" class="form-label">Imagem</label>
                                    <input type="file" class="form-control" id="image" name="image" required>
                                </div>
                                <button type="submit" name="add_region" class="btn btn-primary">Adicionar região</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Botão de buscar e título -->
                    <div class="card mb-4">
                        <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">Regiões Cadastradas</h5>
                            <form method="POST" action="" class="d-flex">
                                <input type="text" class="form-control me-2" name="search" placeholder="Buscar regiões" value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-primary" type="submit">Buscar</button>
                                <?php if ($search) { ?>
                                    <a href="regions.php" class="btn btn-secondary ms-2 w-100">Remover Filtro</a>
                                <?php } ?>
                            </form>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Imagem</th>
                                    <th>Fonte</th>
                                    <th>Descrição</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_array($regionsQuery)) { ?>
                                    <tr>
                                        <td>
                                            <?php if ($row['imagem']) { ?>
                                                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['imagem']); ?>" alt="Imagem" style="width: 100px; height: auto;">
                                            <?php } ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['source']); ?></td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
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
                    Você realmente deseja excluir esta região?
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="delete_region" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Script para preencher o ID da região no modal
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
