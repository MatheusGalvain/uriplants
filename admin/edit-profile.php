<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
};
include_once('includes/config.php');

// Verifica se o usuário está autenticado
if (strlen($_SESSION['id']) == 0) {
    header('location:logout.php');
    exit;
}

// Código para atualização do perfil
if (isset($_POST['update'])) {
    $fname = $_POST['fname'];
    $email = $_POST['email'];
    $userid = $_SESSION['id'];

    // Prepara a consulta de atualização com o e-mail
    $updateQuery = "UPDATE users SET fname='$fname', email='$email' WHERE id='$userid'";
    $msg = mysqli_query($con, $updateQuery);

    if ($msg) {
        echo "<script>alert('Perfil atualizado com sucesso');</script>";
        echo "<script type='text/javascript'>document.location = 'profile.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Admin | Editar Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="assets/demo/chart-area-demo.js"></script>
    <script src="assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
    <script src="js/datatables-simple-demo.js"></script>
</head>
<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <?php 
                    $userid = $_SESSION['id'];
                    $query = mysqli_query($con, "SELECT * FROM users WHERE id='$userid'");
                    
                    if ($result = mysqli_fetch_array($query)) { ?>
                        <h1 class="mt-4"><?php echo htmlspecialchars($result['fname']); ?>'s perfil</h1>
                        <div class="card mb-4">
                            <form method="post">
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Nome:</th>
                                            <td>
                                                <input class="form-control" id="fname" name="fname" type="text" value="<?php echo htmlspecialchars($result['fname']); ?>" required />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td colspan="3">
                                                <input class="form-control" id="email" name="email" type="email" value="<?php echo htmlspecialchars($result['email']); ?>" required />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" style="text-align:center;">
                                                <button type="submit" class="btn btn-primary btn-block" name="update">Atualizar</button>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </form>
                        </div>
                    <?php } ?>
                </div>
            </main>
            
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
</body>
</html>
