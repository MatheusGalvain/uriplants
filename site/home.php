<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UriPlants</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/reset.css">
    <link rel="stylesheet" href="./css/footer.css">
    <link rel="stylesheet" href="./css/main.css">
    <!-- TODO: MUDAR LINK FONTAWESOME -->
    <script src="https://kit.fontawesome.com/70aed2b9f4.js" crossorigin="anonymous"></script>
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
            <button onclick="window.location.href='list/list.php'" class="btn">Plantas Cadastradas</button>
        </div>
        <div style="width: 300px">
            <div class="button-title">URI Quiz</div>
            <button onclick="window.location.href='/uriplants/quiz/quiz.php'" class="btn">Quiz Universitário</button>
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
