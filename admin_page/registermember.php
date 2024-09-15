<?php session_start();
require_once('includes/config.php');

//Code for Registration 
if (isset($_POST['submit'])) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $contact = $_POST['contact'];
    $sql = mysqli_query($con, "select id from users where email='$email'");
    $row = mysqli_num_rows($sql);
    if ($row > 0) {
        echo "<script>alert('Email id already exist with another account. Please try with other email id');</script>";
    } else {
        $msg = mysqli_query($con, "insert into users(fname,lname,email,password,contactno) values('$fname','$lname','$email','$password','$contact')");

        if ($msg) {
            echo "<script>alert('Registered successfully');</script>";
        }
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
    <title>Admin | Novo usuário</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"
        crossorigin="anonymous"></script>
    <script type="text/javascript">
        function checkpass() {
            if (document.signup.password.value != document.signup.confirmpassword.value) {
                alert(' Password and Confirm Password field does not match');
                document.signup.confirmpassword.focus();
                return false;
            }
            return true;
        }
    </script>
</head>

<body class="sb-nav-fixed">
    <?php include_once('includes/navbar.php'); ?>
    <div id="layoutSidenav">
        <?php include_once('includes/sidebar.php'); ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Cadastre um novo usúario:</h1>
                        <div class="card mb-4">
                            <form method="post" name="signup" onsubmit="return checkpass();">
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Primeiro Nome:</th>
                                            <td><input class="form-control" id="fname" name="fname" type="text"
                                                    placeholder="Insira seu primeiro nome" required />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Último Nome:</th>
                                            <td> <input class="form-control" id="lname" name="lname" type="text"
                                            placeholder="Insira seu último nome" required />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Seu E-mail:</th>
                                            <td><input class="form-control" id="email" name="email" type="email"
                                            placeholder="exemplo@gmail.com" required />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Número de Telefone:</th>
                                            <td><input class="form-control" id="contact" name="contact" type="text"
                                            placeholder="(54) 999999-9999" required pattern="[0-9]{10}"
                                            title="10 numeric characters only" maxlength="10" required />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Senha:</th>
                                            <td><input class="form-control" id="password" name="password"
                                                    type="password" placeholder="Senha"
                                                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}"
                                                    title="at least one number and one uppercase and lowercase letter, and at least 6 or more characters"
                                                    required />
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Confirmar senha:</th>
                                            <td><input class="form-control" id="confirmpassword"
                                                    name="confirmpassword" type="password"
                                                    placeholder="Confirme a senha"
                                                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}"
                                                    title="at least one number and one uppercase and lowercase letter, and at least 6 or more characters"
                                                    required />
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                <div class="d-grid"><button type="submit" class="btn btn-primary btn-block" name="submit">Criar Conta</button></div>
                            </form>
                        </div>
                </div>
            </main>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

</body>

</html>