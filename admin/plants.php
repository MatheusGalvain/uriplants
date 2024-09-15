<?php
    session_start();
    include_once('includes/config.php');

    // Verificar autenticação
    if (!isset($_SESSION['id']) || strlen($_SESSION['id']) == 0) {
        header('location:logout.php');
        exit();
    }

    // Função para filtrar entrada
    function filter_input_data($con, $data) {
        return mysqli_real_escape_string($con, trim($data));
    }

    // Adicionar planta
    if (isset($_POST['add_plant'])) {
        $name = filter_input_data($con, $_POST['name']);
        $common_names = filter_input_data($con, $_POST['common_names']);
        $division_id = intval($_POST['division_id']);
        $class_id = intval($_POST['class_id']);
        $order_id = intval($_POST['order_id']);
        $family_id = intval($_POST['family_id']);
        $genus_id = intval($_POST['genus_id']);
        $region_id = intval($_POST['region_id']);
        $species = filter_input_data($con, $_POST['species']);
        $applications = filter_input_data($con, $_POST['applications']);
        $ecology = filter_input_data($con, $_POST['ecology']);

        // Verifica se a planta já existe
        $query = mysqli_query($con, "SELECT id FROM Plants WHERE name='$name'");
        if (mysqli_num_rows($query) > 0) {
            $error = "Uma planta com esse nome já existe.";
        } else {
            $sql = "INSERT INTO Plants (name, common_names, division_id, class_id, order_id, family_id, genus_id, region_id, species, applications, ecology) 
                    VALUES ('$name', '$common_names', $division_id, $class_id, $order_id, $family_id, $genus_id, $region_id, '$species', '$applications', '$ecology')";
            $success = mysqli_query($con, $sql) ? "Planta adicionada com sucesso." : "Erro ao adicionar planta: " . mysqli_error($con);
        }
    }

    // Atualizar planta
    if (isset($_POST['update_plant'])) {
        $id = intval($_POST['id']);
        $name = filter_input_data($con, $_POST['name']);
        $common_names = filter_input_data($con, $_POST['common_names']);
        $division_id = intval($_POST['division_id']);
        $class_id = intval($_POST['class_id']);
        $order_id = intval($_POST['order_id']);
        $family_id = intval($_POST['family_id']);
        $genus_id = intval($_POST['genus_id']);
        $region_id = intval($_POST['region_id']);
        $species = filter_input_data($con, $_POST['species']);
        $applications = filter_input_data($con, $_POST['applications']);
        $ecology = filter_input_data($con, $_POST['ecology']);

        $sql = "UPDATE Plants SET name='$name', common_names='$common_names', division_id=$division_id, class_id=$class_id, 
                order_id=$order_id, family_id=$family_id, genus_id=$genus_id, region_id=$region_id, species='$species', 
                applications='$applications', ecology='$ecology' WHERE id=$id";
        
        if (mysqli_query($con, $sql)) {
            header('location:plants.php');
            exit();
        } else {
            $error = "Erro ao atualizar planta: " . mysqli_error($con);
        }
    }

    // Excluir planta
    if (isset($_POST['delete_plant'])) {
        $id = intval($_POST['id']);
        $sql = "UPDATE Plants SET deleted_at = NOW() WHERE id = $id";
        $success = mysqli_query($con, $sql) ? "Planta excluída com sucesso." : "Erro ao excluir planta: " . mysqli_error($con);
    }

    // Busca e exibição de plantas
    $search = isset($_POST['search']) ? filter_input_data($con, $_POST['search']) : '';
    $searchQuery = $search ? "AND name LIKE '%$search%'" : '';
    $plantsQuery = mysqli_query($con, "SELECT * FROM Plants WHERE deleted_at IS NULL $searchQuery");

    // Dados para selects
    $divisionsQuery = mysqli_query($con, "SELECT * FROM Divisions WHERE deleted_at IS NULL");
    $classesQuery = mysqli_query($con, "SELECT * FROM Classes WHERE deleted_at IS NULL");
    $ordersQuery = mysqli_query($con, "SELECT * FROM Orders WHERE deleted_at IS NULL");
    $familiesQuery = mysqli_query($con, "SELECT * FROM Families WHERE deleted_at IS NULL");
    $genusQuery = mysqli_query($con, "SELECT * FROM Genus WHERE deleted_at IS NULL");
    $regionsQuery = mysqli_query($con, "SELECT * FROM RegionMap WHERE deleted_at IS NULL");

    // Buscar planta para edição
    $edit_plant = null;
    if (isset($_GET['edit'])) {
        $edit_id = intval($_GET['edit']);
        $edit_query = mysqli_query($con, "SELECT * FROM Plants WHERE id = $edit_id AND deleted_at IS NULL");
        if (mysqli_num_rows($edit_query) > 0) {
            $edit_plant = mysqli_fetch_assoc($edit_query);
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Plantas</title>
    <style>
        .d-flex-end {
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        #plant-form,
        #edit-form {
            display: none;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4 mb-4">Gerenciar Plantas</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item">Você está em: </li>
                        <li class="breadcrumb-item"><a href="welcome.php">dashboard</a></li>
                        <li class="breadcrumb-item active">plantas</li>
                    </ol>

                    <?php if (isset($success)) { ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php } ?>

                    <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php } ?>

                    
                    <!-- Botão para expandir o formulário de cadastro -->
                    <button id="toggleForm" class="btn btn-primary mb-4">Adicionar Nova Planta</button>

                    <!-- Formulário de Adição -->
                    <div id="plant-form" class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Adicionar uma Nova Planta</h5>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="name" class="form-label">*Nome da Planta</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="common_names" class="form-label">Nomes Comuns</label>
                                    <textarea class="form-control" id="common_names" name="common_names"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="division_id" class="form-label">*Divisão</label>
                                    <select class="form-control" id="division_id" name="division_id">
                                        <?php while ($row = mysqli_fetch_array($divisionsQuery)) { ?>
                                            <option value="<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">*Classe</label>
                                    <select class="form-control" id="class_id" name="class_id">
                                        <?php while ($row = mysqli_fetch_array($classesQuery)) { ?>
                                            <option value="<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="order_id" class="form-label">Ordem*</label>
                                    <select class="form-control" id="order_id" name="order_id">
                                        <?php while ($row = mysqli_fetch_array($ordersQuery)) { ?>
                                            <option value="<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="family_id" class="form-label">*Família</label>
                                    <select class="form-control" id="family_id" name="family_id">
                                        <?php while ($row = mysqli_fetch_array($familiesQuery)) { ?>
                                            <option value="<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="genus_id" class="form-label">*Gênero</label>
                                    <select class="form-control" id="genus_id" name="genus_id">
                                        <?php while ($row = mysqli_fetch_array($genusQuery)) { ?>
                                            <option value="<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="region_id" class="form-label">*Região</label>
                                    <select class="form-control" id="region_id" name="region_id">
                                        <?php while ($row = mysqli_fetch_array($regionsQuery)) { ?>
                                            <option value="<?php echo htmlspecialchars($row['id']); ?>"><?php echo htmlspecialchars($row['source']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="species" class="form-label">Espécie</label>
                                    <input type="text" class="form-control" id="species" name="species">
                                </div>
                                <div class="mb-3">
                                    <label for="applications" class="form-label">Aplicações</label>
                                    <textarea class="form-control" id="applications" name="applications"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="ecology" class="form-label">Ecologia</label>
                                    <textarea class="form-control" id="ecology" name="ecology"></textarea>
                                </div>
                                <button type="submit" name="add_plant" class="btn btn-primary">Adicionar Planta</button>
                            </form>
                        </div>
                    </div>

                    <!-- Formulário de Edição -->
                    <button id="toggleEditForm" class="btn btn-warning mb-4" style="display:none;">Editar Planta</button>
                    <?php if ($edit_plant) { ?>
                        <div id="edit-form" class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Editar Planta</h5>
                                <form method="POST" action="">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_plant['id']); ?>">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nome da Planta</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($edit_plant['name']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="common_names" class="form-label">Nomes Comuns</label>
                                        <textarea class="form-control" id="common_names" name="common_names"><?php echo htmlspecialchars($edit_plant['common_names']); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="division_id" class="form-label">Divisão</label>
                                        <select class="form-control" id="division_id" name="division_id">
                                            <?php while ($row = mysqli_fetch_array($divisionsQuery)) { ?>
                                                <option value="<?php echo htmlspecialchars($row['id']); ?>" <?php echo $row['id'] == $edit_plant['division_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($row['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="class_id" class="form-label">Classe</label>
                                        <select class="form-control" id="class_id" name="class_id">
                                            <?php while ($row = mysqli_fetch_array($classesQuery)) { ?>
                                                <option value="<?php echo htmlspecialchars($row['id']); ?>" <?php echo $row['id'] == $edit_plant['class_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($row['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="order_id" class="form-label">Ordem</label>
                                        <select class="form-control" id="order_id" name="order_id">
                                            <?php while ($row = mysqli_fetch_array($ordersQuery)) { ?>
                                                <option value="<?php echo htmlspecialchars($row['id']); ?>" <?php echo $row['id'] == $edit_plant['order_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($row['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="family_id" class="form-label">Família</label>
                                        <select class="form-control" id="family_id" name="family_id">
                                            <?php while ($row = mysqli_fetch_array($familiesQuery)) { ?>
                                                <option value="<?php echo htmlspecialchars($row['id']); ?>" <?php echo $row['id'] == $edit_plant['family_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($row['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="genus_id" class="form-label">Gênero</label>
                                        <select class="form-control" id="genus_id" name="genus_id">
                                            <?php while ($row = mysqli_fetch_array($genusQuery)) { ?>
                                                <option value="<?php echo htmlspecialchars($row['id']); ?>" <?php echo $row['id'] == $edit_plant['genus_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($row['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="region_id" class="form-label">Região</label>
                                        <select class="form-control" id="region_id" name="region_id">
                                            <?php while ($row = mysqli_fetch_array($regionsQuery)) { ?>
                                                <option value="<?php echo htmlspecialchars($row['id']); ?>" <?php echo $row['id'] == $edit_plant['region_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($row['source']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="species" class="form-label">Espécie</label>
                                        <input type="text" class="form-control" id="species" name="species" value="<?php echo htmlspecialchars($edit_plant['species']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="applications" class="form-label">Aplicações</label>
                                        <textarea class="form-control" id="applications" name="applications"><?php echo htmlspecialchars($edit_plant['applications']); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ecology" class="form-label">Ecologia</label>
                                        <textarea class="form-control" id="ecology" name="ecology"><?php echo htmlspecialchars($edit_plant['ecology']); ?></textarea>
                                    </div>
                                    <button type="submit" name="update_plant" class="btn btn-primary">Atualizar Planta</button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>

                    <!-- Botão de buscar e título -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Plantas Cadastradas</h5>
                                <form method="POST" action="" class="d-flex">
                                    <input type="text" class="form-control me-2" name="search" placeholder="Buscar plantas" value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-primary" type="submit">Buscar</button>
                                    <?php if ($search) { ?>
                                        <a href="plants.php" class="btn btn-secondary ms-2 w-100">Remover Filtro</a>
                                    <?php } ?>
                                </form>
                            </div>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nome da Planta</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_array($plantsQuery)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td>
                                                <!-- Botão para abrir o modal de exclusão -->
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                                    Excluir
                                                </button>
                                                <!-- Botão para abrir o formulário de edição -->
                                                <a href="?edit=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-warning btn-sm">Editar</a>
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
                    Você realmente deseja excluir esta planta?
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="delete_plant" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
                // Script para mostrar e esconder o formulário de cadastro
        document.getElementById('toggleForm').addEventListener('click', function() {
            var form = document.getElementById('plant-form');
            var editForm = document.getElementById('edit-form');
            
            if (form.style.display === 'none') {
                form.style.display = 'block';
                editForm.style.display = 'none'; // Esconder formulário de edição ao mostrar o de cadastro
                this.textContent = 'Ocultar Formulário de Cadastro';
            } else {
                form.style.display = 'none';
                this.textContent = 'Adicionar Nova Planta';
            }
        });

        // Script para mostrar e esconder o formulário de edição
        document.getElementById('toggleEditForm').addEventListener('click', function() {
            var form = document.getElementById('edit-form');
            
            if (form.style.display === 'none') {
                form.style.display = 'block';
                document.getElementById('plant-form').style.display = 'none'; // Esconder o formulário de cadastro ao mostrar o de edição
                this.textContent = 'Ocultar Formulário de Edição';
            } else {
                form.style.display = 'none';
                this.textContent = 'Editar Planta';
            }
        });

        function showEditForm() {
            document.getElementById('edit-form').style.display = 'block';
            document.getElementById('plant-form').style.display = 'none';
            document.getElementById('toggleEditForm').style.display = 'block'; // Mostrar botão de editar
            document.getElementById('toggleForm').textContent = 'Adicionar Nova Planta'; // Mostrar sempre o botão de adicionar plantas
        }

        <?php if ($edit_plant) { ?>
            showEditForm();
        <?php } ?>


    </script>
</body>

</html>
