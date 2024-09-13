<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Admin | Lobby</title>
    <!-- Includes -->
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/index.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</head>

<body>
    <main>
        <section id="main-admin">
            <div class="main-admin-left">
                <?php
                $nomeImagem = 'loginhome.jpg';
                $caminhoPasta = 'images/';
                $caminhoImagem = $caminhoPasta . $nomeImagem;

                if (file_exists($caminhoImagem)) {
                    echo '<img class="loginImg" src="' . htmlspecialchars($caminhoImagem) . '" alt="Imagem login uri">';
                } else {
                    echo 'Imagem não encontrada.';
                }
                ?>
            </div>
            <div class="main-admin-right">
                <div class="right-wrapp">
                    <div class="right-content"> 
                        <h1>Bem-Vindo ao Painel Administrador</h1>
                        <h2>da URI Plantas, em parceria com o curso de Biologia!</h2>
                    </div>
                    <div id="content-btns">
                        <ul>
                            <li><a class="button-admin-lobby" href="login.php">Faça seu login!</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>