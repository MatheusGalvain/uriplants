<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UriPlants</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin-top: 350px; /* Ajuste para compensar o header fixo */
        }

        h1 {
            margin-bottom: 40px;
            color: #333;
        }

        .button-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            padding: 15px 30px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-align: center;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn-admin {
            background-color: #28a745;
        }

        .btn-admin:hover {
            background-color: #1e7e34;
        }

        .btn-plants {
            background-color: #17a2b8;
        }

        .btn-plants:hover {
            background-color: #117a8b;
        }

        .btn-quizz {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-quizz:hover {
            background-color: #e0a800;
        }

        .slider {
            position: relative;
            width: 100%;
            height: 55vh; /* Ajusta o slider para ocupar toda a altura do header */
            overflow: hidden;
        }

        .slides {
            display: flex;
            transition: transform 2s cubic-bezier(0.25, 0.1, 0.25, 1); /* Função personalizada com duração aumentada */
        }

        .slide {
            min-width: 100%;
            background-size: cover;
            background-position: center;
            height: 55vh; /* Define a altura da imagem de fundo */
            position: relative;
        }

        .slide-text {
            position: absolute;
            bottom: 40px; /* Ajuste para descer o texto */
            right: 50px; /* Margem extra à direita */
            color: white;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.7);
        }

        .slide-text h2 {
            margin: 0;
        }

        .slide-text p {
            margin: 0;
            max-width: 200px; /* Limitar a largura do texto */
        }

        .controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }

        .controls .dot {
            cursor: pointer;
            height: 15px;
            width: 15px;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .controls .dot.active,
        .controls .dot:hover {
            background-color: rgba(255, 255, 255, 1);
        }

        .background-div {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 999;
            background-color: transparent; /* Torna o fundo transparente para permitir que a imagem do carrossel seja vista */
        }

        .header {
            display: flex;
            flex-direction: column;
            top: 0;
            position: absolute;
            width: 100%;
            z-index: 1000; /* Mantém o header acima do slider */
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            padding: 10px 20px;
            background-color: rgba(51, 51, 51, 0.8); /* Transparência para deixar o fundo do header visível */
            color: white;
        }

        .logo-slider {
            display: flex;
            justify-content: flex-start;
            padding: 20px 0;
            background-color: rgba(0, 0, 0, 0.2);
        }

        .logo-slider img {
            max-height: 80px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let currentSlide = 0;
            const slidesContainer = document.querySelector('.slides');
            const slides = document.querySelectorAll('.slide');
            const totalSlides = slides.length;
            const dots = document.querySelectorAll('.dot');
            let slideInterval;
            let isTransitioning = false;

            // Clone o primeiro slide e adiciona ao final para loop infinito
            const firstSlideClone = slides[0].cloneNode(true);
            slidesContainer.appendChild(firstSlideClone);

            // Atualiza o número total de slides após o clone
            const updatedTotalSlides = slidesContainer.querySelectorAll('.slide').length;

            function updateSlide(index) {
                if (isTransitioning) return; // Evita múltiplas transições simultâneas
                isTransitioning = true;
                slidesContainer.style.transition = 'transform 2s cubic-bezier(0.25, 0.1, 0.25, 1)'; // Duração aumentada
                slidesContainer.style.transform = `translateX(-${index * 100}%)`;
                currentSlide = index;
                updateDots();
            }

            function updateDots() {
                dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === (currentSlide % totalSlides));
                });
            }

            slidesContainer.addEventListener('transitionend', () => {
                if (currentSlide === updatedTotalSlides - 1) {
                    // Remove a transição para reposicionar sem animação
                    slidesContainer.style.transition = 'none';
                    slidesContainer.style.transform = `translateX(0)`;
                    currentSlide = 0;
                    updateDots();
                    // Força o reflow para que a mudança de estilo seja aplicada
                    void slidesContainer.offsetWidth;
                    // Reativa a transição
                    slidesContainer.style.transition = 'transform 2s cubic-bezier(0.25, 0.1, 0.25, 1)';
                }
                isTransitioning = false;
            });

            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    updateSlide(index);
                    resetInterval(); // Reseta o intervalo quando o usuário clica em um dot
                });
            });

            function startSlideShow() {
                slideInterval = setInterval(() => {
                    updateSlide(currentSlide + 1);
                }, 10000); // 10000 milissegundos = 10 segundos
            }

            function resetInterval() {
                clearInterval(slideInterval);
                startSlideShow();
            }

            // Inicializa os dots e o slideshow
            updateDots();
            startSlideShow();
        });
    </script>
</head>

<body>

    <header class="background-div">
        <div class="header">
            <div class="top-bar">
                <div class="social-icons">
                    <a href="#"><img src="img/linkedin.png" alt="LinkedIn"></a>
                    <a href="#"><img src="img/instagram.png" alt="Instagram"></a>
                    <a href="#"><img src="img/facebook.png" alt="Facebook"></a>
                </div>
                <div class="contact-info">
                    <span>+55 (54) 3520-9000</span>
                </div>
            </div>
            <div class="logo-slider">
                <img src="https://www.uricer.edu.br/site/images/setembro_amarelo.png" alt="Logo URI Erechim">
            </div>
        </div>
        <div class="slider">
            <div class="slides">
                <div class="slide" style="background-image: url('images/bg1.jpg');">
                    <div class="slide-text">
                        <h2>URI Plantas</h2>
                        <p>Uri Plantas oferece uma diversidade<br> gigante sobre as plantas</p>
                    </div>
                </div>
                <div class="slide" style="background-image: url('images/bg2.jpg');">
                    <div class="slide-text">
                        <h2>URI Plantas</h2>
                        <p>Leia o QRCode<br> e conheça mais sobre variadas espécies!</p>
                    </div>
                </div>
                <div class="slide" style="background-image: url('images/bg3.jpg');">
                    <div class="slide-text">
                        <h2>URI Plantas</h2>
                        <p>Uma parceria dos cursos de Arquitetura,<br> Biologia e Ciência da Computação</p>
                    </div>
                </div>
            </div>
            <div class="controls">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>
    </header>


    <div class="container">
        <h1>Bem-vindo ao UriPlants</h1>
        <div class="button-group">
            <a href="/uriplants/admin" class="btn btn-admin">Admin</a>
            <a href="/uriplants/plants" class="btn btn-plants">Lista de Plantas</a>
            <a href="/uriplants/quizz" class="btn btn-quizz">Quizz</a>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>
</body>

</html>
