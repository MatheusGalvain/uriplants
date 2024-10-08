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
function sanitize_input($data)
{
    if (is_null($data)) {
        return '';
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Inicializar variáveis
$plantName = "";
$plantDescription = "";
$plantImage = "../images/plant-placeholder.png";
$plantImageAlt = "";
$errorMessage = "";
$familyName = "";
$commonName = "";
$orderName = "";
$divisionName = "";
$className = "";
$genusName = "";
$speciesName = "";
$ecologyName = "";
$applicationsName = "";
$propertyName = "";

// Verificar se o parâmetro 'id' foi passado na URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($id > 0) {
        $plant = $plantController->getSinglePlant($id);
        if (isset($plant['id'])) {
            $plantName = sanitize_input($plant['name']);
            $plantDescription = sanitize_input($plant['ecology']);
            if (!empty($plant['image_blob'])) {
                $plantImage = 'data:image/jpeg;base64,' . $plant['image_blob'];
            }
            $plantImageAlt = "Imagem de " . $plantName;

            // Extrair família, ordem, classe e propriedades
            $familyName = sanitize_input($plant['family_name']);
            $divisionName = sanitize_input($plant['division_name']);
            $orderName = sanitize_input($plant['order_name']);
            $className = sanitize_input($plant['class_name']);
            $genusName = sanitize_input($plant['genus_name']);
            $speciesName = sanitize_input($plant['species']);
            $commonName= sanitize_input($plant['common_names']);
            $ecologyName = sanitize_input($plant['ecology']);
            $applicationsName = sanitize_input($plant['applications']);
            $propertyName = sanitize_input($plant['property_name']);

            $otherPlants = $plantController->getOtherPlants($id);
        } else {
            $errorMessage = isset($plant['message']) ? $plant['message'] : "Erro desconhecido.";
            if (isset($plant['error'])) {
                error_log("Erro ao buscar planta: " . $plant['error']);
            }
        }
    } else {
        $errorMessage = "ID da planta inválido.";
    }
} else {
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../css/reset.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/listplant.css">
    <link rel="stylesheet" href="../css/listplant_responsive.css">
    <!-- TODO: MUDAR LINK FONTAWESOME -->
    <script src="https://kit.fontawesome.com/70aed2b9f4.js" crossorigin="anonymous"></script>

</head>

<header id="header" class="header">
    <div class="topheader">
        <div class="boxheader">
            <ul class="topheaderul">
                <li><a class="reficon" href="https://www.instagram.com/urierechim/" target="_BLANK" aria-label="Acesso ao instagram"><img src="../images/instagram-icon.png" alt="instagram"></a></li>
                <li><a class="reficon" href="https://www.youtube.com/user/urierechim/videos" target="_BLANK" aria-label="Acesso ao youtube"><img src="../images/youtube-icon.png" alt="youtube"></a></li>
                <li><a class="reficon" href="https://www.facebook.com/uricampuserechim/?locale=pt_BR" target="_BLANK" aria-label="Acesso ao facebook"><img src="../images/facebook-icon.png" alt="facebook"></a></li>
                <li class="lifont">+55 (54) 3520-9000</li>
            </ul>
            <ul class="topheaderullinks">
                <li class="lifont">@ 2024 URI Câmpus de Erechim</li>
                <li class="lihover"><a class="acesslink" href="#" target="_BLANK" alt="acesso a politica de privacidade">Política de privacidade</a></li>
                <li class="lihover"><a class="acesslink" href="#" target="_BLANK" alt="acesso a reitoria">Reitoria</a></li>
                <li class="lihover"><a class="acesslink" href="https://www.uricer.edu.br/site/informacao?uri=000139000000000000000000000" target="_BLANK" alt="acesso a uri sustentabilidade">URI Sustentabilidade</a></li>
            </ul>
        </div>
    </div>
    <div class="bottomheader">
        <div class="boxheader">
            <a href="#" alt="Logo da uri" class="logoheader"><img src="https://www.uricer.edu.br/site/images/setembro_amarelo.png" alt="Logo URI Erechim"></a>
            <ul class="ulheader">
                <li class="liheaderhover"><a href="#">URI Quiz</a></li>
                <li class="liheaderhover"><a href="#">Uri Plantas</a></li>
                <li>
                    <form action="../list/list.php" method="GET" class="search-form">
                        <input type="text" name="query" placeholder="Pesquise alguma coisa..." aria-label="Pesquisar plantas">
                        <button type="submit"><i class="fa-solid fa-magnifying-glass" style="color: #ffffff;"></i></button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
    <nav id="main-menu-mobile">
        <div id="button-menu" class="button-menu">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>
        <ul id="main-menu-mobile-items">
            <div class="hamburguer-wrapp">
                <h1>Fechar menu...</h1>
                <div id="button-menu" class="button-menu">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
            </div>
            <li class="item-menu">
                <a alt="Uri Plants" href="#" class="menu-mobile-link ">URI Plants</a>
            </li>
            <li class="item-menu">
                <a alt="Uri Quiz" href="#" class="menu-mobile-link">URI Quiz</a>
            </li>
        </ul>
        <form action="../list/list.php" method="GET" class="search-form-mobile">
            <input type="text" name="query" placeholder="Pesquise alguma coisa..." aria-label="Pesquisar plantas">
            <button type="submit"><i class="fa-solid fa-magnifying-glass" style="color: #ffffff;"></i></button>
        </form>

    </nav>
</header>

<body>
    <main>
        <div class="navigatorcontainer">
            <div class="box">
                <div class="header-container">
                    <a href="../list/list.php" class="icon-container">
                        <i class="fas fa-angle-left"></i>
                    </a>
                    <div class="text-container">
                        <span>Você está em</span>
                        <h1>URI Plantas</h1>
                    </div>
                </div>
            </div>
        </div>

        <section id="main-section" class="plantinfo">
            <div class="boximage">
                <article class="articletitle">
                    <h1><?php echo $plantName; ?></h1>
                    <h2>Conheça mais sobre a planta!</h2>
                </article>
                <section class="photosplant">
                    <div class="photocentral-wrapp">
                        <div class="photocentral">
                            <?php
                            // Verifica se a imagem da planta está disponível
                            if (!empty($plantImage)) {
                                $mainImageSrc = $plantImage;
                            } else {
                                $mainImageSrc = '../images/notfound.png'; // Imagem padrão
                            }
                            ?>
                            <img id="mainImage" class="photoImg" src="<?php echo $mainImageSrc; ?>"
                                alt="<?php echo sanitize_input($plantImageAlt); ?>">
                        </div>
                    </div>
                    <div class="otherphotos-wrapp">
                        <?php
                        $plantImages = $plantController->getPlantImages($id);
                        $maxPhotos = 4;
                        $count = 0;
                        ?>
                        <?php if (!empty($plantImages)): ?>
                            <?php foreach ($plantImages as $image): ?>
                                <?php if ($count < $maxPhotos && $image['image_blob'] !== base64_encode($mainImageSrc)): ?>
                                    <div class="otherphotodiv"
                                        onclick="changeMainImage('data:image/jpeg;base64,<?php echo $image['image_blob']; ?>')">
                                        <img class="otherphoto" src="data:image/jpeg;base64,<?php echo $image['image_blob']; ?>"
                                            alt="<?php echo sanitize_input($plantName); ?>">
                                    </div>
                                    <?php $count++; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </section>

        <section id="main-section" class="navigator">
            <div class="box">
                <div class="navigatordiv">
                    <ul class="navigatorList">
                        <li><a class="navigatorref" href="javascript:void(0);" onclick="showSection('description')">Descrição</a></li>
                        <li><a class="navigatorref" href="javascript:void(0);" onclick="showSection('taxonomy')">Taxonomia</a></li>
                        <li><a class="navigatorref" href="javascript:void(0);" onclick="showSection('ecology')">Ecologia</a></li>
                        <li><a class="navigatorref" href="javascript:void(0);" onclick="showSection('occourrence')">Occorrência</a></li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Sessão de informações da planta que está em Ecologia -->
        <section id="main-section" class="ecology">
            <div class="box">
                <article class="informationsart">
                    <h1>Produtos e Usos</h1>
                    <h2><?php echo $applicationsName; ?></h2>
                </article>
            </div>
        </section>

        <!-- Sessão de informações da planta que está em Taxonomia -->
        <section id="main-section" class="taxonomy">
            <div class="box">
                <article class="informationsart">
                    <h1>Nome</h1>
                    <h2><?php echo $plantName; ?></h2>
                </article>
                <article class="informationsart">
                    <h1>Divisão</h1>
                    <h2><?php echo $divisionName; ?></h2>
                </article>
                <article class="informationsart">
                    <h1>Classe</h1>
                    <h2><?php echo $className; ?></h2>
                </article>
                <article class="informationsart">
                    <h1>Ordem</h1>
                    <h2><?php echo $orderName; ?></h2>
                </article>
                <article class="informationsart">
                    <h1>Família</h1>
                    <h2><?php echo $familyName; ?></h2>
                </article>
                <article class="informationsart">
                    <h1>Gênero</h1>
                    <h2><?php echo $genusName; ?></h2>
                </article>
                <article class="informationsart">
                    <h1>Espécie</h1>
                    <h2><?php echo $speciesName; ?></h2>
                </article>
            </div>
        </section>


        <!-- Sessão de informações da planta que está em Descrição-->
        <section id="main-section" class="description">
            <div class="box">
                <article class="informationsart">
                    <h1>Nome</h1>
                    <h2><?php echo $plantName; ?></h2>
                </article>
                <article class="informationsart">
                    <h1>Nome's Poulares</h1>
                    <h2><?php echo $commonName; ?></h2>
                </article>
                <article class="informationsart">
                    <h1>Descrição</h1>
                    <h2><?php echo $plantDescription; ?></h2>
                </article>
                <article class="informationsart">
                    <h1>Forma Biológica</h1>
                    <h2>A fazer</h2>
                    <div class="containerimgs-wrapp">
                        <div class="otherphotodiv"
                        onclick="changeMainImage('data:image/jpeg;base64,<?php echo $image['image_blob']; ?>')">
                        <img class="otherphoto" src="data:image/jpeg;base64,<?php echo $image['image_blob']; ?>"
                        alt="<?php echo sanitize_input($plantName); ?>">
                        </div>
                    </div>
                </article>
                <article class="informationsart">
                    <h1>Tronco</h1>
                    <h2><?php echo $plantPropertyDescription; ?></h2>
                </article>
                <article class="informationsart">
                    <h1>Casca</h1>
                    <h2><?php echo $barkDescription; ?></h2>
                </article>
                <article class="informationsart">
                    <h1>Folhas</h1>
                    <h2><?php echo $leavesDescription; ?></h2>
                </article>
                <article class="informationsart">
                    <h1>Flores</h1>
                    <h2><?php echo $flowersDescription; ?></h2>
                </article>
                <article class="informationsart">
                    <h1>Frutos</h1>
                    <h2><?php echo $fruitsDescription; ?></h2>
                </article>
                <article class="informationsart">
                    <h1>Sementes</h1>
                    <h2><?php echo $seedsDescription; ?></h2>
                </article>
            </div>
        </section>

        <!-- Sessão de informações da planta que está em Ocorrência-->
        <section id="main-section" class="occourrence">
            <div class="box">
                <article class="informationsart">
                    <h1>Condições Climáticas</h1>
                    <h2><?php echo $plantName; ?></h2>
                    <div class="climate-info">
                        <article class="informationsart">

                            <img src="https://picsum.photos/800/500" alt="Estação Climática">
                            <div class="occourrence-text-container">

                                <article class="informationsart">
                                    <h1>Estação Climática</h1>
                                    <h2>LLALALALAL</h2>
                                    <!-- <h2><?php echo $seasonName; ?></h2> -->
                                    <h1>Descrição</h1>
                                    <!-- <p><?php echo $seasonDescription; ?></p> -->
                                     <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Veritatis quo tempora suscipit quibusdam ut adipisci facere aspernatur dolor illum. Iure facilis sint exercitationem odio nobis mollitia ab tempore repellat alias!</p>
                                </article>

                        </article>
                </article>
            </div>
        </section>

        <section id="main-section" class="otherplants-wrapp">
            <div class="box">
                <h1>Outras Plantas</h1>
                <div class="otherplants">
                    <?php if (!empty($otherPlants)): ?>
                        <?php foreach ($otherPlants as $otherPlant): ?>
                            <div class="otherplant" onclick="goToPlant(<?php echo $otherPlant['id']; ?>)">
                                <div class="centerplant">
                                    <div class="otherplantimg">
                                        <?php if (!empty($otherPlant['image_blob'])): ?>
                                            <img src="data:image/jpeg;base64,<?php echo $otherPlant['image_blob']; ?>"
                                                alt="<?php echo sanitize_input($otherPlant['name']); ?>">
                                        <?php else: ?>
                                            <img src="../images/notfound.png"
                                                alt="<?php echo sanitize_input($otherPlant['name']); ?>">
                                        <?php endif; ?>
                                    </div>
                                    <div class="titleothersplants">
                                        <h1>Nome</h1>
                                        <h2><?php echo sanitize_input($otherPlant['name']); ?></h2>
                                    </div>
                                </div>
                                <a class="btnotherplant" href="#">ACESSE</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Não há outras plantas disponíveis.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-left">
            <div class="redes-sociais">
                <span>Redes Sociais</span>
                <div class="display-icons">
                    <a href="https://www.facebook.com/uricampuserechim"><img src="../images/facebook-icon.png" alt="Facebook" /></a>
                    <a href="https://www.instagram.com/urierechim/"><img src="../images/instagram-icon.png" alt="Instagram" /></a>
                    <a href="https://twitter.com/urierechim"><img src="../images/x-icon.png" alt="Twitter" /></a>
                    <a href="https://www.youtube.com/urierechim"><img src="../images/youtube-icon.png" alt="YouTube" /></a>
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
            <a href="https://www.uricer.edu.br/"><img src="../images/uri-logo.png" alt="Logo URI" /></a>
        </div>
    </footer>

    <script>
        function changeMainImage(imageSrc) {
            document.getElementById('mainImage').src = imageSrc;
        }

        function goToPlant(plantId) {
            window.location.href = 'plant.php?id=' + plantId;
        }

        // Função que vai fazer a navegação do navigator
        function showSection(section) {
            // Deixa as classes com o display none
            document.querySelector('.ecology').style.display = 'none';
            document.querySelector('.taxonomy').style.display = 'none';
            document.querySelector('.description').style.display = 'none';
            document.querySelector('.occourrence').style.display = 'none';

            // Exibe a seção correta
            if (section === 'ecology') {
                document.querySelector('.ecology').style.display = 'block';
            }
            if (section === 'taxonomy') {
                document.querySelector('.taxonomy').style.display = 'block';
            }
            if (section === 'description') {
                document.querySelector('.description').style.display = 'block';
            }
            if (section === 'occourrence') {
                document.querySelector('.occourrence').style.display = 'block';
            }

            // Atualiza a classe 'active' nos links
            const links = document.querySelectorAll('.navigatorref');
            links.forEach(link => {
                link.classList.remove('active');
            });

            // Adiciona a classe 'active' ao link correspondente
            const activeLink = document.querySelector(`.navigatorref[onclick*='${section}']`);
            if (activeLink) {
                activeLink.classList.add('active');
            }
        }

        $('.button-menu').click(function() {
            $(this).toggleClass('button-menu-close');
            $('#main-menu-mobile-items').toggleClass('main-menu-mobile-items-open');
        });

        $('.item-menu').click(function() {
            // Desativar todos os menus ativos quando clicar em um novo menu
            $('.menu-link').removeClass('activeheader');
            // Ativar o menu sublinhado quando for clicado
            $(this).children().addClass('activeheader');
        });

        // Chama a função showSection para exibir a seção "Descrição" ao carregar a página
        window.onload = function() {
            showSection('description');
        };
    </script>
</body>

</html>