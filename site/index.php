<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UriPlants</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/main.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            overflow-x: hidden;
        }

        h1 {
            margin-bottom: 40px;
            color: #333;
        }

        .button-group {
            display: flex;
            width: 900px;
            justify-content: space-around;
        }

        .btn {
            padding: 15px 30px;
            margin: auto;
            background-color: white; 
            color: black;
            border: 2px solid #228B22; 
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-align: center;
            width: 90%;
            display: inline-block;
        }

        .btn:hover {
            background-color: #f0f0f0;
        }

        button {
            width: 90%;
            background: none;
            border: none;
            padding: 0;
        }

        .button-title {
            font-weight: bold;
            font-size: 1.4em;
            transform: translateY(8px);
            color: black;
        }

        .subtitle {
            font-size: 0.9em;
            color: gray;
            margin-left: 5px;
        }

        .slider {
            position: relative;
            width: 100%;
            height: 55vh; 
            overflow: hidden;
        }

        .slider::before {
            left: 0;
            background: linear-gradient(to right, black, rgba(0, 0, 0, 0));
        }

        .slider::after {
            right: 0;
            background: linear-gradient(to left, black, rgba(0, 0, 0, 0));
        }

        .slides {
            display: flex;
            transition: transform 2s cubic-bezier(0.25, 0.1, 0.25, 1); 
        }

        .slide {
            min-width: 100%;
            background-size: cover;
            background-position: center;
            height: 55vh;
            position: relative;
        }

        .slide-text {
            position: absolute;
            bottom: 40px;
            right: 50px; 
            color: white;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.7);
            z-index: 3; 
        }

        .slide-text h2 {
            margin: 0;
        }

        .slide-text p {
            margin: 0;
            max-width: 200px;
        }

        .controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 3; 
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
            /* position: fixed; */
            top: 0;
            width: 100%;
            z-index: 999;
            background-color: transparent;
        }

        .header {
            display: flex;
            flex-direction: column;
            top: 0;
            position: absolute;
            width: 100%;
            z-index: 1000; 
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            padding: 10px 20px;
            background-color: rgba(51, 51, 51, 0.8); 
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

        
        footer {

            width: 100vw;
            background-color: #00668F;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-around;
            align-items: center;
            font-family: Arial, sans-serif;
        }

        @media (max-width: 480px) {
                  
            .slide {
                border-bottom-left-radius: 60px; 
            }

            .slide-text {
                bottom: 40px;
                right: 25px; 
            }

            .slide-text  p, h2 {
                text-align: end;
            }
                
            .button-group {
                margin-top: 20px;
                flex-direction: column;
                width: 100%;
                max-width: 100%;
                align-items: center;
            }

            .btn {
                width: 100%;
                margin-bottom: 20px; 

            }

            .btn:last-child {
                margin-bottom: 40px; 
            }

            .controls .dot {
                width: 5px;
                height: 5px;
            }
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

            const firstSlideClone = slides[0].cloneNode(true);
            slidesContainer.appendChild(firstSlideClone);

            const updatedTotalSlides = slidesContainer.querySelectorAll('.slide').length;

            function updateSlide(index) {
                if (isTransitioning) return; 
                isTransitioning = true;
                slidesContainer.style.transition = 'transform 2s cubic-bezier(0.25, 0.1, 0.25, 1)'; 
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
                    
                    slidesContainer.style.transition = 'none';
                    slidesContainer.style.transform = `translateX(0)`;
                    currentSlide = 0;
                    updateDots();
                    
                    void slidesContainer.offsetWidth;
                    
                    slidesContainer.style.transition = 'transform 2s cubic-bezier(0.25, 0.1, 0.25, 1)';
                }
                isTransitioning = false;
            });

            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    updateSlide(index);
                    resetInterval(); 
                });
            });

            function startSlideShow() {
                slideInterval = setInterval(() => {
                    updateSlide(currentSlide + 1);
                }, 10000); 
            }

            function resetInterval() {
                clearInterval(slideInterval);
                startSlideShow();
            }

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
                    <a href="#"><img src="" alt="LinkedIn"></a>
                    <a href="#"><img src="" alt="Instagram"></a>
                    <a href="#"><img src="" alt="Facebook"></a>
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
                <div class="slide" style="background-image: linear-gradient(to right, black, rgba(0, 0, 0, 0) 20%), linear-gradient(to left, black, rgba(0, 0, 0, 0) 20%), url('images/bg1.jpg'); ">
                    <div class="slide-text">
                        <h2>URI Plantas</h2>
                        <p>Uri Plantas oferece uma diversidade<br> gigante sobre as plantas</p>
                    </div>
                </div>
                <div class="slide" style="background-image: linear-gradient(to right, black, rgba(0, 0, 0, 0) 20%), linear-gradient(to left, black, rgba(0, 0, 0, 0) 20%),  url('images/bg2.jpg'); ">
                    <div class="slide-text">
                        <h2>URI Plantas</h2>
                        <p>Leia o QR Code<br> e conheça mais sobre variadas espécies!</p>
                    </div>
                </div>
                <div class="slide" style="  background-image: linear-gradient(to right, black, rgba(0, 0, 0, 0) 20%), linear-gradient(to left, black, rgba(0, 0, 0, 0) 20%),  url('images/bg3.jpg');">
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
    <div class="button-group">
        <div style="width: 300px">
            <div class="button-title">URI Plantas</div>
            <button onclick="window.location.href='/uriplants/plants'" class="btn">Plantas Cadastradas</button>
        </div>
        <div style="width: 300px">
            <div class="button-title">URI Quiz</div>
            <button onclick="window.location.href='/uriplants/quizz'" class="btn">Quiz Universitário</button>
        </div>
        <div style="width: 300px">
            <div class="button-title">URI Admin</div>
            <button onclick="window.location.href='/uriplants/admin'" class="btn">Painel Administrador</button>
        </div>
    </div>




    <footer>
        <div class="footer-left">
            <div class="redes-sociais">
                <span>Redes Sociais</span>
                <div class="display-icons">
                    <a href="https://www.facebook.com/uricampuserechim"><img src="images/facebook-icon.png"/></a>
                    <a href="https://www.instagram.com/urierechim/"><img src="images/instagram-icon.png"/></a>
                    <a href="https://twitter.com/urierechim"><img src="images/x-icon.png"/></a>
                    <a href="https://www.youtube.com/urierechim"><img src="images/youtube-icon.png"/></a>
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
            <a href="https://www.uricer.edu.br/"><img src="images/uri-logo.png" /></a>
        </div>
    </footer>
      
</body>

</html>
