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
        $name = trim($_POST['name']);
        $common_names = trim($_POST['common_names']);
        $division_id = intval($_POST['division_id']);
        $class_id = intval($_POST['class_id']);
        $order_id = intval($_POST['order_id']);
        $family_id = intval($_POST['family_id']);
        $genus_id = intval($_POST['genus_id']);
        $region_id = intval($_POST['region_id']);
        $species = trim($_POST['species']);
        $applications = trim($_POST['applications']);
        $ecology = trim($_POST['ecology']);

        // Verifica se a planta já existe
        $stmt = $con->prepare("SELECT id FROM Plants WHERE name = ?");
        if (!$stmt) {
            header('location:plants.php?error=Erro na preparação da consulta: ' . urlencode($con->error));
            exit();
        }
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            // Redireciona com mensagem de erro
            header('location:plants.php?error=Uma planta com esse nome já existe.');
            exit();
        } else {
            // Inserir nova planta
            $stmt = $con->prepare("INSERT INTO Plants (name, common_names, division_id, class_id, order_id, family_id, genus_id, region_id, species, applications, ecology) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                header('location:plants.php?error=Erro na preparação da inserção: ' . urlencode($con->error));
                exit();
            }
            $stmt->bind_param("ssiiiiissss", $name, $common_names, $division_id, $class_id, $order_id, $family_id, $genus_id, $region_id, $species, $applications, $ecology);
            if ($stmt->execute()) {
                // Redireciona com mensagem de sucesso
                header('location:plants.php?success=Planta adicionada com sucesso.');
                exit();
            } else {
                // Redireciona com mensagem de erro
                header('location:plants.php?error=Erro ao adicionar planta: ' . urlencode($stmt->error));
                exit();
            }
        }
        $stmt->close();
    }

    // Atualizar planta
    if (isset($_POST['update_plant'])) {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $common_names = trim($_POST['common_names']);
        $division_id = intval($_POST['division_id']);
        $class_id = intval($_POST['class_id']);
        $order_id = intval($_POST['order_id']);
        $family_id = intval($_POST['family_id']);
        $genus_id = intval($_POST['genus_id']);
        $region_id = intval($_POST['region_id']);
        $species = trim($_POST['species']);
        $applications = trim($_POST['applications']);
        $ecology = trim($_POST['ecology']);

        $stmt = $con->prepare("UPDATE Plants SET name = ?, common_names = ?, division_id = ?, class_id = ?, order_id = ?, family_id = ?, genus_id = ?, region_id = ?, species = ?, applications = ?, ecology = ? WHERE id = ?");
        if (!$stmt) {
            header('location:plants.php?error=Erro na preparação da atualização: ' . urlencode($con->error));
            exit();
        }
        $stmt->bind_param("ssiiiiissssi", $name, $common_names, $division_id, $class_id, $order_id, $family_id, $genus_id, $region_id, $species, $applications, $ecology, $id);
        if ($stmt->execute()) {
            header('location:plants.php?success=Planta atualizada com sucesso.');
            exit();
        } else {
            header('location:plants.php?error=Erro ao atualizar planta: ' . urlencode($stmt->error));
            exit();
        }
        $stmt->close();
    }

    // Excluir planta
    if (isset($_POST['delete_plant'])) {
        $id = intval($_POST['id']);
        $sql = "UPDATE Plants SET deleted_at = NOW() WHERE id = $id";
        if (mysqli_query($con, $sql)) {
            header('location:plants.php?success=Planta excluída com sucesso.');
            exit();
        } else {
            header('location:plants.php?error=Erro ao excluir planta: ' . urlencode(mysqli_error($con)));
            exit();
        }
    }

    // Busca e exibição de plantas
    $search = isset($_POST['search']) ? filter_input_data($con, $_POST['search']) : '';
    $searchQuery = $search ? "AND name LIKE '%$search%'" : '';
    $queryString = "SELECT * FROM Plants WHERE deleted_at IS NULL $searchQuery";

    $plantsQuery = mysqli_query($con, $queryString);

    if (!$plantsQuery) {
        // Redireciona com mensagem de erro
        header('location:plants.php?error=Erro na consulta de plantas: ' . urlencode(mysqli_error($con)));
        exit();
    }

    // Dados para selects
    $divisionsQuery = mysqli_query($con, "SELECT * FROM Divisions WHERE deleted_at IS NULL");
    $classesQuery = mysqli_query($con, "SELECT * FROM Classes WHERE deleted_at IS NULL");
    $ordersQuery = mysqli_query($con, "SELECT * FROM Orders WHERE deleted_at IS NULL");
    $familiesQuery = mysqli_query($con, "SELECT * FROM Families WHERE deleted_at IS NULL");
    $genusQuery = mysqli_query($con, "SELECT * FROM Genus WHERE deleted_at IS NULL");
    $regionsQuery = mysqli_query($con, "SELECT * FROM RegionMap WHERE deleted_at IS NULL");

    // Transformando os resultados em arrays
    $divisions = $divisionsQuery ? mysqli_fetch_all($divisionsQuery, MYSQLI_ASSOC) : [];
    $classes = $classesQuery ? mysqli_fetch_all($classesQuery, MYSQLI_ASSOC) : [];
    $orders = $ordersQuery ? mysqli_fetch_all($ordersQuery, MYSQLI_ASSOC) : [];
    $families = $familiesQuery ? mysqli_fetch_all($familiesQuery, MYSQLI_ASSOC) : [];
    $genus = $genusQuery ? mysqli_fetch_all($genusQuery, MYSQLI_ASSOC) : [];
    $regionMap = $regionsQuery ? mysqli_fetch_all($regionsQuery, MYSQLI_ASSOC) : [];


    // Buscar planta para edição
    $edit_plant = null;
    if (isset($_GET['edit'])) {
        $edit_id = intval($_GET['edit']);
        $edit_query = mysqli_query($con, "SELECT * FROM Plants WHERE id = $edit_id AND deleted_at IS NULL");
        if (mysqli_num_rows($edit_query) > 0) {
            $edit_plant = mysqli_fetch_assoc($edit_query);

            // Reexecutar queries para os selects
            $divisionsQuery = mysqli_query($con, "SELECT * FROM Divisions WHERE deleted_at IS NULL");
            $classesQuery = mysqli_query($con, "SELECT * FROM Classes WHERE deleted_at IS NULL");
            $ordersQuery = mysqli_query($con, "SELECT * FROM Orders WHERE deleted_at IS NULL");
            $familiesQuery = mysqli_query($con, "SELECT * FROM Families WHERE deleted_at IS NULL");
            $genusQuery = mysqli_query($con, "SELECT * FROM Genus WHERE deleted_at IS NULL");
            $regionsQuery = mysqli_query($con, "SELECT * FROM RegionMap WHERE deleted_at IS NULL");

            // Atualizar arrays após reconsultas
            $divisions = $divisionsQuery ? mysqli_fetch_all($divisionsQuery, MYSQLI_ASSOC) : [];
            $classes = $classesQuery ? mysqli_fetch_all($classesQuery, MYSQLI_ASSOC) : [];
            $orders = $ordersQuery ? mysqli_fetch_all($ordersQuery, MYSQLI_ASSOC) : [];
            $families = $familiesQuery ? mysqli_fetch_all($familiesQuery, MYSQLI_ASSOC) : [];
            $genus = $genusQuery ? mysqli_fetch_all($genusQuery, MYSQLI_ASSOC) : [];
            $regionMap = $regionsQuery ? mysqli_fetch_all($regionsQuery, MYSQLI_ASSOC) : [];
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
                    <h1 class="mt-4 mb-4 h1">Gerenciar Plantas</h1>

                    <?php if (isset($_GET['success'])) { ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                    <?php } ?>

                    <?php if (isset($_GET['error'])) { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php } ?>

                    <!-- Botão para expandir o formulário de cadastro -->
                    <button id="toggleForm" class="btn btn-primary mb-4">Nova Planta</button>

                    <!-- Formulário de Adição -->
                    <div id="plant-form" class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title h2 mb-3">Adicionar Nova Planta</h5>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">*Nome Científico</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="common_names" class="form-label fw-bold">Nomes Comuns</label>
                                    <textarea class="form-control" id="common_names" name="common_names"></textarea>
                                </div>

                                <!-- Seleção da Divisão -->
                                <div class="mb-3">
                                    <label for="division_id" class="form-label fw-bold">*Divisão</label>
                                    <select class="form-control" id="division_id" name="division_id" required>
                                        <option value="">Selecione a divisão</option>
                                        <?php foreach ($divisions as $division) { ?>
                                            <option value="<?php echo htmlspecialchars($division['id']); ?>">
                                                <?php echo htmlspecialchars($division['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Seleção da Classe -->
                                <div class="mb-3">
                                    <label for="class_id" class="form-label fw-bold">*Classe</label>
                                    <select class="form-control" id="class_id" name="class_id" required>
                                        <option value="">Selecione a classe</option>
                                        <?php foreach ($classes as $class) { ?>
                                            <option value="<?php echo htmlspecialchars($class['id']); ?>">
                                                <?php echo htmlspecialchars($class['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Seleção da Ordem -->
                                <div class="mb-3">
                                    <label for="order_id" class="form-label fw-bold">*Ordem</label>
                                    <select class="form-control" id="order_id" name="order_id" required>
                                        <option value="">Selecione a ordem</option>
                                        <?php foreach ($orders as $order) { ?>
                                            <option value="<?php echo htmlspecialchars($order['id']); ?>">
                                                <?php echo htmlspecialchars($order['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Seleção da Família -->
                                <div class="mb-3">
                                    <label for="family_id" class="form-label fw-bold">*Família</label>
                                    <select class="form-control" id="family_id" name="family_id" required>
                                        <option value="">Selecione a família</option>
                                        <?php foreach ($families as $family) { ?>
                                            <option value="<?php echo htmlspecialchars($family['id']); ?>">
                                                <?php echo htmlspecialchars($family['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Seleção do Gênero -->
                                <div class="mb-3">
                                    <label for="genus_id" class="form-label fw-bold">Gênero</label>
                                    <select class="form-control" id="genus_id" name="genus_id" required>
                                        <option value="">Selecione o gênero</option>
                                        <?php foreach ($genus as $genusItem) { ?>
                                            <option value="<?php echo htmlspecialchars($genusItem['id']); ?>">
                                                <?php echo htmlspecialchars($genusItem['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Seleção da Região -->
                                <div class="mb-3">
                                    <label for="region_id" class="form-label fw-bold">Região</label>
                                    <select class="form-control" id="region_id" name="region_id">
                                        <option value="">Selecione a região</option>
                                        <?php foreach ($regionMap as $region) { ?>
                                            <option value="<?php echo htmlspecialchars($region['id']); ?>">
                                                <?php echo htmlspecialchars($region['name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <!-- Outros campos opcionais -->
                                <div class="mb-3">
                                    <label for="species" class="form-label fw-bold">Espécie</label>
                                    <input type="text" class="form-control" id="species" name="species">
                                </div>
                                <div class="mb-3">
                                    <label for="applications" class="form-label fw-bold">Aplicações</label>
                                    <textarea class="form-control" id="applications" name="applications"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="ecology" class="form-label fw-bold">Ecologia</label>
                                    <textarea class="form-control" id="ecology" name="ecology"></textarea>
                                </div>

                                <button type="submit" name="add_plant" class="btn btn-primary" style="width: 10%;">Adicionar</button>
                                <button type="button" id="cancelAddPlant" class="btn btn-secondary">Cancelar</button>
                            </form>

                        </div>
                    </div>

                    <!-- Formulário de Edição -->
                    <?php if ($edit_plant) { ?>
                        <div id="edit-form" class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title h2 mb-3">Editar</h5>
                                <form method="POST" action="">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_plant['id']); ?>">
                                    <div class="mb-3">
                                        <label for="name" class="form-label fw-bold">Nome Científico</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($edit_plant['name']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="common_names" class="form-label fw-bold">Nomes Comuns</label>
                                        <textarea class="form-control" id="common_names" name="common_names"><?php echo htmlspecialchars($edit_plant['common_names']); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="division_id" class="form-label fw-bold">Divisão</label>
                                        <select class="form-control" id="division_id" name="division_id" required>
                                            <option value="">Selecione a divisão</option>
                                            <?php foreach ($divisions as $division) { ?>
                                                <option value="<?php echo htmlspecialchars($division['id']); ?>" <?php echo $division['id'] == $edit_plant['division_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($division['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="class_id" class="form-label fw-bold">Classe</label>
                                        <select class="form-control" id="class_id" name="class_id" required>
                                            <option value="">Selecione a classe</option>
                                            <?php foreach ($classes as $class) { ?>
                                                <option value="<?php echo htmlspecialchars($class['id']); ?>" <?php echo $class['id'] == $edit_plant['class_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($class['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="order_id" class="form-label fw-bold">Ordem</label>
                                        <select class="form-control" id="order_id" name="order_id" required>
                                            <option value="">Selecione a ordem</option>
                                            <?php foreach ($orders as $order) { ?>
                                                <option value="<?php echo htmlspecialchars($order['id']); ?>" <?php echo $order['id'] == $edit_plant['order_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($order['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="family_id" class="form-label fw-bold">Família</label>
                                        <select class="form-control" id="family_id" name="family_id" required>
                                            <option value="">Selecione a família</option>
                                            <?php foreach ($families as $family) { ?>
                                                <option value="<?php echo htmlspecialchars($family['id']); ?>" <?php echo $family['id'] == $edit_plant['family_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($family['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="genus_id" class="form-label fw-bold">Gênero</label>
                                        <select class="form-control" id="genus_id" name="genus_id" required>
                                            <option value="">Selecione o gênero</option>
                                            <?php foreach ($genus as $genusItem) { ?>
                                                <option value="<?php echo htmlspecialchars($genusItem['id']); ?>" <?php echo $genusItem['id'] == $edit_plant['genus_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($genusItem['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="region_id" class="form-label fw-bold">Região</label>
                                        <select class="form-control" id="region_id" name="region_id">
                                            <option value="">Selecione a região</option>
                                            <?php foreach ($regionMap as $region) { ?>
                                                <option value="<?php echo htmlspecialchars($region['id']); ?>" <?php echo $region['id'] == $edit_plant['region_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($region['name']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="species" class="form-label fw-bold">Espécie</label>
                                        <input type="text" class="form-control" id="species" name="species" value="<?php echo htmlspecialchars($edit_plant['species']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="applications" class="form-label fw-bold">Aplicações</label>
                                        <textarea class="form-control" id="applications" name="applications"><?php echo htmlspecialchars($edit_plant['applications']); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ecology" class="form-label fw-bold">Ecologia</label>
                                        <textarea class="form-control" id="ecology" name="ecology"><?php echo htmlspecialchars($edit_plant['ecology']); ?></textarea>
                                    </div>
                                    <button type="submit" name="update_plant" class="btn btn-primary" style="width: 10%;">Salvar</button>
                                    <button type="button" id="cancelEditPlant" class="btn btn-secondary">Cancelar</button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>

                    <!-- Botão de buscar e título -->
                    <div id="plant-list" class="card mb-4">
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
                                    <?php 
                                        $plantCount = 0;
                                        while ($row = mysqli_fetch_array($plantsQuery)) { 
                                            $plantCount++;
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td>
                                                <a href="?edit=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-success btn-m" style="width: 15%;">Editar</a>
                                                <button type="button" class="btn btn-primary btn-m" data-bs-toggle="modal" data-bs-target="#qrCodeModal" data-id="<?php echo htmlspecialchars($row['id']); ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>">QR Code</button>
                                                <button type="button" class="btn btn-danger btn-m" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id="<?php echo htmlspecialchars($row['id']); ?>">Excluir</button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php if ($plantCount === 0) { ?>
                                        <tr>
                                            <td colspan="2" class="text-center">Nenhuma planta encontrada.</td>
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
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Você realmente deseja excluir esta planta?
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="submit" name="delete_plant" class="btn btn-danger">Confirmar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

        <!-- Modal para QR Code -->
    <div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrCodeModalLabel">QR Code da Planta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="qrCode" style="display: flex; justify-content: center;"></div>
                    <a id="downloadLink" download="qrcode.png" class="btn btn-secondary mt-3">Baixar QR Code</a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>                                    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal de Confirmação de Exclusão
            var confirmDeleteModal = document.getElementById('confirmDeleteModal');
            if (confirmDeleteModal) {
                confirmDeleteModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var id = button.getAttribute('data-id');
                    var deleteIdInput = confirmDeleteModal.querySelector('#deleteId');
                    if (deleteIdInput) {
                        deleteIdInput.value = id;
                    }
                });
            }

            // Botão para mostrar o formulário de cadastro
            var toggleFormButton = document.getElementById('toggleForm');
            if (toggleFormButton) {
                toggleFormButton.addEventListener('click', function() {
                    var form = document.getElementById('plant-form');
                    var plantList = document.getElementById('plant-list');
                    var toggleButton = document.getElementById('toggleForm');            

                    if (form.style.display === 'none' || form.style.display === '') {
                        form.style.display = 'block';
                        plantList.style.display = 'none';
                        toggleButton.style.display = 'none';    
                    }
                });
            }

            // Função para esconder formulário e mostrar lista de plantas
            function hideFormAndShowList(formId) {
                var form = document.getElementById(formId);
                var plantList = document.getElementById('plant-list');
                var toggleButton = document.getElementById('toggleForm');

                if (form && plantList && toggleButton) {
                    form.style.display = 'none';
                    plantList.style.display = 'block';
                    toggleButton.style.display = 'block';
                }
            }

            // Botão de Cancelar no formulário de adição
            var cancelAddPlant = document.getElementById('cancelAddPlant');
            if (cancelAddPlant) {
                cancelAddPlant.addEventListener('click', function() {
                    hideFormAndShowList('plant-form');
                });
            }

            // Botão de Cancelar no formulário de edição
            var cancelEditPlant = document.getElementById('cancelEditPlant');
            if (cancelEditPlant) {
                cancelEditPlant.addEventListener('click', function() {
                    hideFormAndShowList('edit-form');
                });
            }

            // Qr Code
            var qrCodeModal = document.getElementById('qrCodeModal');
            qrCodeModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var plantId = button.getAttribute('data-id');
                var plantName = button.getAttribute('data-name');
                var qrCodeContainer = document.getElementById('qrCode');
                var downloadLink = document.getElementById('downloadLink');

               
                qrCodeContainer.innerHTML = '';

                // Modificar aqui para a url que for definida
                var url = 'http://localhost/uriplants/public/plants/' + plantId;
                var qrCode = new QRCode(qrCodeContainer, {
                    text: url,
                    width: 256,
                    height: 256,
                });

                setTimeout(function() {
                    var qrCodeImg = qrCodeContainer.querySelector('img');
                    if (qrCodeImg) {
                        var sanitizedPlantName = plantName.replace(/[^a-z0-9]/gi, '_').toLowerCase();
                        downloadLink.href = qrCodeImg.src;
                        downloadLink.download = sanitizedPlantName + '_' + plantId + '.png';
                    }
                }, 500); 
            });

            <?php if ($edit_plant) { ?>
                var editForm = document.getElementById('edit-form');
                var plantList = document.getElementById('plant-list');
                var toggleButton = document.getElementById('toggleForm');  

                if (editForm && plantList && toggleButton) {
                    editForm.style.display = 'block';
                    plantList.style.display = 'none';
                    toggleButton.style.display = 'none';  
                }
            <?php } ?>
        });
    </script>
</body>

</html>
