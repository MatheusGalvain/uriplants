<?php
session_start();
include_once('includes/config.php');

// Verificar se o usuário está autenticado
if (strlen($_SESSION['id']) == 0) {
    header('location:logout.php');
    exit();
}

// Adicionar nova propriedade de planta
if (isset($_POST['add_property'])) {
    $plant_id = intval($_POST['plant_id']);
    $property_id = intval($_POST['property_id']);
    $description = mysqli_real_escape_string($con, $_POST['description']);
    $source = mysqli_real_escape_string($con, $_POST['source']);

    // Inserir nova propriedade de planta
    $sql = "INSERT INTO PlantsProperties (plant_id, property_id, description, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'iis', $plant_id, $property_id, $description);

    if (mysqli_stmt_execute($stmt)) {
        $plants_property_id = mysqli_insert_id($con);

        // Inserir imagem, se fornecida
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = file_get_contents($_FILES['image']['tmp_name']);

            $image_sql = "INSERT INTO images (imagem, source, plants_property_id) VALUES (?, ?, ?)";
            $image_stmt = mysqli_prepare($con, $image_sql);
            mysqli_stmt_bind_param($image_stmt, 'bsi', $image, $source, $plants_property_id);

            if (mysqli_stmt_execute($image_stmt)) {
                $success = "Propriedade da planta adicionada com sucesso, incluindo a imagem.";
            } else {
                $error = "Propriedade adicionada, mas erro ao adicionar a imagem: " . mysqli_error($con);
            }
        } else {
            $success = "Propriedade da planta adicionada com sucesso.";
        }
    } else {
        $error = "Erro ao adicionar propriedade da planta: " . mysqli_error($con);
    }
}

// Processar a exclusão de uma propriedade de planta
if (isset($_POST['delete_property'])) {
    $id = intval($_POST['id']);

    // Excluir imagens relacionadas
    $delete_images_sql = "DELETE FROM images WHERE plants_property_id = ?";
    $delete_images_stmt = mysqli_prepare($con, $delete_images_sql);
    mysqli_stmt_bind_param($delete_images_stmt, 'i', $id);
    mysqli_stmt_execute($delete_images_stmt);

    // Excluir a propriedade de planta
    $sql = "DELETE FROM PlantsProperties WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);

    if (mysqli_stmt_execute($stmt)) {
        $success = "Propriedade da planta excluída com sucesso.";
    } else {
        $error = "Erro ao excluir propriedade da planta: " . mysqli_error($con);
    }
}

// Processar a busca
$search = isset($_POST['search']) ? mysqli_real_escape_string($con, $_POST['search']) : '';

// Obter todas as propriedades de plantas com base na busca
$searchQuery = $search ? "AND (plants.name LIKE '%$search%' OR properties.name LIKE '%$search%' OR images.source LIKE '%$search%')" : "";
$propertiesQuery = mysqli_query($con, "
    SELECT pp.*, p.name as plant_name, pr.name as property_name, i.imagem, i.source as image_source
    FROM PlantsProperties pp
    LEFT JOIN plants p ON pp.plant_id = p.id
    LEFT JOIN properties pr ON pp.property_id = pr.id
    LEFT JOIN images i ON pp.id = i.plants_property_id
    WHERE pp.deleted_at IS NULL $searchQuery
    ORDER BY pp.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Propriedades das Plantas</title>
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4">Gerenciar Imagens</h1>

                    <?php if (isset($success)) { ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php } ?>

                    <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Adicionar uma Nova Imagem</h5>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="plant_id" class="form-label">Planta</label>
                                    <select class="form-select" id="plant_id" name="plant_id" required>
                                        <option value="">Selecione uma planta</option>
                                        <?php
                                        $plants = mysqli_query($con, "SELECT id, name FROM plants ORDER BY name ASC");
                                        while ($plant = mysqli_fetch_assoc($plants)) {
                                            echo "<option value=\"" . htmlspecialchars($plant['id']) . "\">" . htmlspecialchars($plant['name']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="property_id" class="form-label">Propriedade</label>
                                    <select class="form-select" id="property_id" name="property_id" required>
                                        <option value="">Selecione uma propriedade</option>
                                        <?php
                                        $properties = mysqli_query($con, "SELECT id, name FROM properties ORDER BY name ASC");
                                        while ($property = mysqli_fetch_assoc($properties)) {
                                            echo "<option value=\"" . htmlspecialchars($property['id']) . "\">" . htmlspecialchars($property['name']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Descrição</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="source" class="form-label">Fonte da Imagem</label>
                                    <input type="text" class="form-control" id="source" name="source" required>
                                </div>
                                <div class="mb-3">
                                    <label for="image" class="form-label">Imagem</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                </div>
                                <button type="submit" name="add_property" class="btn btn-primary">Adicionar Propriedade</button>
                            </form>
                        </div>
                    </div>

                    <!-- Botão de buscar e título -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Propriedades das Plantas Cadastradas</h5>
                                <form method="POST" action="" class="d-flex">
                                    <input type="text" class="form-control me-2" name="search" placeholder="Buscar propriedades" value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-primary" type="submit">Buscar</button>
                                    <?php if ($search) { ?>
                                        <a href="plants_properties.php" class="btn btn-secondary ms-2">Remover Filtro</a>
                                    <?php } ?>
                                </form>
                            </div>

                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Planta</th>
                                        <th>Propriedade</th>
                                        <th>Descrição</th>
                                        <th>Imagem</th>
                                        <th>Fonte da Imagem</th>
                                        <th>Criado em</th>
                                        <th>Deletado em</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($propertiesQuery) > 0) {
                                        while ($row = mysqli_fetch_assoc($propertiesQuery)) {
                                            // Formatar datas
                                            $created_at = date("d/m/Y H:i", strtotime($row['created_at']));
                                            $deleted_at = $row['deleted_at'] ? date("d/m/Y H:i", strtotime($row['deleted_at'])) : 'Ativo';
                                    ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['plant_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['property_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                                <td>
                                                    <?php if ($row['imagem']) { ?>
                                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($row['imagem']); ?>" alt="Imagem" style="width: 100px; height: auto;">
                                                    <?php } else { ?>
                                                        Sem Imagem
                                                    <?php } ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['image_source']); ?></td>
                                                <td><?php echo htmlspecialchars($created_at); ?></td>
                                                <td><?php echo htmlspecialchars($deleted_at); ?></td>
                                                <td>
                                                    <!-- Botão para abrir o modal de confirmação -->
                                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                                        Excluir
                                                    </button>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' class='text-center'>Nenhuma propriedade encontrada.</td></tr>";
                                    }
                                    ?>
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
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    Você realmente deseja excluir esta propriedade da planta?
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="delete_property" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Script para preencher o ID da propriedade no modal
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
