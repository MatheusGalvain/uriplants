<?php
// plant.php

// Ativar a exibição de erros (apenas para desenvolvimento)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir o PlantController
require_once __DIR__ . '/../../controllers/PlantController.php';

// Instanciar o controlador de plantas
$plantController = new PlantController();

// Função para sanitizar a entrada
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Inicializar variáveis
$plantName = "";
$plantDescription = "";
$plantImage = "../images/plant-placeholder.png";
$plantImageAlt = "";
$errorMessage = "";

// Verificar se o parâmetro 'id' foi passado na URL
if (isset($_GET['id'])) {
    // Sanitizar o ID recebido
    $id = intval($_GET['id']);

    // Verificar se o ID é válido
    if ($id > 0) {
        // Buscar os detalhes da planta com o ID fornecido
        $plant = $plantController->getSinglePlant($id);

        // Verificar se a planta foi encontrada
        if (isset($plant['id'])) {
            // Extrair os dados da planta
            $plantName = sanitize_input($plant['name']);
            $plantDescription = sanitize_input($plant['ecology']); // Assumindo que 'ecology' é a descrição
            if (!empty($plant['image_blob'])) {
                $plantImage = 'data:image/jpeg;base64,' . $plant['image_blob'];
            }
            $plantImageAlt = "Imagem de " . $plantName;
        } else {
            // Planta não encontrada ou ocorreu um erro
            $errorMessage = isset($plant['message']) ? $plant['message'] : "Erro desconhecido.";
            if (isset($plant['error'])) {
                // Logar o erro em um arquivo de log ou sistema de logging apropriado
                error_log("Erro ao buscar planta: " . $plant['error']);
            }
        }
    } else {
        // ID inválido
        $errorMessage = "ID da planta inválido.";
    }
} else {
    // Parâmetro 'id' não foi passado
    $errorMessage = "ID da planta não especificado.";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo !empty($plantName) ? $plantName : "Detalhes da Planta"; ?> - URI Plantas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/footer.css">
    <style>
        /* Seu CSS existente */
        /* ... (mantido inalterado) ... */
    </style>
</head>

<body>
    <header class="header">
        <div class="top-bar">
            <div class="social-icons">
                <a href="#"><img src="linkedin-placeholder.png" alt="LinkedIn"></a>
                <a href="#"><img src="instagram-placeholder.png" alt="Instagram"></a>
                <a href="#"><img src="facebook-placeholder.png" alt="Facebook"></a>
            </div>
            <div class="contact-info">
                <span>+55 (54) 3520-9000</span>
            </div>
        </div>
        <div class="logo-slider">
            <img src="https://www.uricer.edu.br/site/images/setembro_amarelo.png" alt="Logo URI Erechim">
        </div>
        <nav>
            <a href="../" class="active">Início</a>
            <a href="#">Sobre</a>
            <a href="#">Contato</a>
        </nav>
    </header>

    <section>
        <div class="plant-details-container">
            <?php if (!empty($errorMessage)): ?>
                <div class="plant-details">
                    <h1>Erro</h1>
                    <p><?php echo sanitize_input($errorMessage); ?></p>
                    <a href="../" class="back-link">&laquo; Voltar para URI Plantas</a>
                </div>
            <?php else: ?>
                <div class="plant-details">
                    <h1><?php echo $plantName; ?></h1>
                    <img src="<?php echo $plantImage; ?>" alt="<?php echo sanitize_input($plantImageAlt); ?>">
                    <p><?php echo nl2br($plantDescription); ?></p>
                    <a href="../" class="back-link">&laquo; Voltar para URI Plantas</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <div class="footer-left">
            <div class="redes-sociais">
                <span>Redes Sociais</span>
                <div class="display-icons">
                    <a href="https://www.facebook.com/uricampuserechim"><img src="images/facebook-icon.png" alt="Facebook" /></a>
                    <a href="https://www.instagram.com/urierechim/"><img src="images/instagram-icon.png" alt="Instagram" /></a>
                    <a href="https://twitter.com/urierechim"><img src="images/x-icon.png" alt="Twitter" /></a>
                    <a href="https://www.youtube.com/urierechim"><img src="images/youtube-icon.png" alt="YouTube" /></a>
                </div>
            </div>
            <div class="telefones">
                <span>Telefones</span>
                <a href="tel:+555435209000">+55 (54) 3520-9000</a>
            </div>
            <div class="localizacoes">
                <span>Localizações</span>
                <a href="https://maps.google.com/?q=Avenida+Sete+de+Setembro,+1621" target="_blank">
                    Avenida Sete de Setembro, 1621
                </a>
            </div>
        </div>
        <div class="logo">
            <a href="https://www.uricer.edu.br/"><img src="images/uri-logo.png" alt="Logo URI" /></a>
        </div>
    </footer>
</body>

</html>
