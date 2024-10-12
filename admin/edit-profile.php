<?php 
include_once('includes/config.php');

check_user_session();

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
<head>
    <?php include_once("includes/head.php"); ?>
    <title>Admin | Editar Perfil</title>
</head>
<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>

        <div id="layoutSidenav_content">
            <main>
            <div class="container-fluid px-4">
                <div class="container-fluid px-4">
                    <?php 
                    $userid = $_SESSION['id'];
                    $query = mysqli_query($con, "SELECT * FROM users WHERE id='$userid'");
                    
                    if ($result = mysqli_fetch_array($query)) { ?>
                        <h1 class="mt-4 mb-4">Editar meu perfil</h1>

                        <div class="card mb-4">
                            <form method="post">
                                    <table class="table">
                                        <tr>
                                            <th>Nome:</th>
                                            <td>
                                                <input class="form-control" id="fname" name="fname" type="text" value="<?php echo htmlspecialchars($result['fname']); ?>" required />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td colspan="3">
                                                <input class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($result['email']); ?>" required />
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
                    <?php } ?>
                    <a href="profile.php" class="btn btn-outline-primary mt-4"> Voltar</a>
                </div>
                </div>
            </main>
            
            <?php include('includes/footer.php'); ?>
        </div>
    </div>
</body>
</html>
