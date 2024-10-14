<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Quiz de Plantas</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../site/css/reset.css">
    <link rel="stylesheet" href="../site/css/quiz.css">
    <link rel="stylesheet" href="../site/css/header.css">
    <link rel="stylesheet" href="../site/css/listplant_responsive.css">
</head>
<header id="header" class="header">
    <div class="topheader">
        <div class="boxheader">
            <ul class="topheaderul">
                <li><a class="reficon" href="https://www.instagram.com/urierechim/" target="_BLANK" aria-label="Acesso ao instagram"><img src="../site/images/instagram-icon.png" alt="instagram"></a></li>
                <li><a class="reficon" href="https://www.youtube.com/user/urierechim/videos" target="_BLANK" aria-label="Acesso ao youtube"><img src="../site/images/youtube-icon.png" alt="youtube"></a></li>
                <li><a class="reficon" href="https://www.facebook.com/uricampuserechim/?locale=pt_BR" target="_BLANK" aria-label="Acesso ao facebook"><img src="../site/images/facebook-icon.png" alt="facebook"></a></li>
                <li class="lifont">+55 (54) 3520-9000</li>
            </ul>
            <ul class="topheaderullinks">
                <li class="lifont">@ 2024 URI Câmpus de Erechim</li>
                <li class="lihover"><a class="acesslink" href="#" target="_BLANK" alt="acesso a politica de privacidade">Política de privacidade</a></li>
                <li class="lihover"><a class="acesslink" href="#" target="_BLANK" alt="acesso a reitoria">Reitoria</a></li>
                <li class="lihover"><a class="acesslink" href="https://www.uricer.edu.br/site/informacao?uri=000139000000000000000000000" target="_BLANK" alt="acesso a URI sustentabilidade">URI Sustentabilidade</a></li>
            </ul>
        </div>
    </div>
    <div class="bottomheader">
        <div class="boxheader">
            <a href="../site" alt="Logo da uri" class="logoheader"><img src="https://www.uricer.edu.br/site/images/setembro_amarelo.png" alt="Logo URI Erechim"></a>
            <ul class="ulheader">
                <li class="liheaderhover"><a href="#">URI Quiz</a></li>
                <li class="liheaderhover"><a href="#">URI Plantas</a></li>
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
                <a alt="Uri Plants" href="../site" class="menu-mobile-link ">Home</a>
            </li>
            <li class="item-menu">
                <a alt="Uri Plants" href="#" class="menu-mobile-link ">URI Plants</a>
            </li>
            <li class="item-menu">
                <a alt="Uri Quiz" href="#" class="menu-mobile-link">URI Quiz</a>
            </li>
        </ul>

    </nav>
</header> 
<body>
    <main class="box-content">
        <div id="quiz-container">
            <h1 class="titlequiz">Bem Vindos ao <strong>URI Quiz</strong></h1>
            <h2 class="subtitlequiz">Prepare-se para um desafio épico! Lembre-se, seu maior objetivo é alcançar uma pontuação inigualável sem cometer erros e se tornar o verdadeiro Deus das plantas!</h2>
            <div id="quizCarousel" class="carousel slide" data-bs-interval="false">
                <div class="carousel-inner" id="carousel-inner"></div>
                <button class="custom-carousel-control-prev" type="button" data-bs-target="#quizCarousel" data-bs-slide="prev">
                    <i class="fa-solid fa-angles-left"></i>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="custom-carousel-control-next" type="button" data-bs-target="#quizCarousel" data-bs-slide="next">
                    <i class="fa-solid fa-angles-right"></i>
                    <span class="visually-hidden">Próximo</span>
                </button>
            </div>
            <div class="title">
                <h1 class="title-points">Quadro de pontuações:</h1>
            </div>
            <div id="scoreboard">
                <div id="acertos" class="score-block score-acertos">
                    Acertos: <span id="correct-count">0</span>
                </div>
                <div id="erros" class="score-block score-erros">
                    Erros: <span id="incorrect-count">0</span>
                </div>
                <div id="total" class="score-block score-total">
                    Total: <span id="total-count">0</span>
                </div>
            </div>
            <div id="question">Carregando pergunta...</div>

            <div id="options">
            </div>

            <button id="next-button" class="btn btn-primary">Próxima Pergunta</button>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const QUIZ_URL = '/uriplants/public/quiz';

        const carouselInner = document.getElementById('carousel-inner');
        const questionEl = document.getElementById('question');
        const optionsEl = document.getElementById('options');
        const nextButton = document.getElementById('next-button');

        const correctCountEl = document.getElementById('correct-count');
        const incorrectCountEl = document.getElementById('incorrect-count');
        const totalCountEl = document.getElementById('total-count');

        let correctCount = 0;
        let incorrectCount = 0;
        let totalCount = 0;

        let correctOption = '';

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

        function updateCarouselControls(carousel) {
            const prevButton = document.querySelector('.custom-carousel-control-prev');
            const nextButton = document.querySelector('.custom-carousel-control-next');
            const totalItems = carouselInner.children.length;
            const currentIndex = carousel.getActiveIndex();

            if (totalItems <= 1) {
                prevButton.style.display = 'none';
                nextButton.style.display = 'none';
                return;
            }

            if (currentIndex === 0) {
                prevButton.style.display = 'none';
            } else {
                prevButton.style.display = 'flex'; 
            }

            if (currentIndex === totalItems - 1) {
                nextButton.style.display = 'none';
            } else {
                nextButton.style.display = 'flex';
            }
        }

        bootstrap.Carousel.prototype.getActiveIndex = function () {
            return Array.from(this._element.querySelectorAll('.carousel-item')).indexOf(this._element.querySelector('.carousel-item.active'));
        };

        async function fetchQuiz() {
            try {
                optionsEl.innerHTML = '';
                nextButton.style.display = 'none';
                carouselInner.innerHTML = '';
                questionEl.textContent = 'Carregando pergunta...';

                const response = await fetch(QUIZ_URL);
                if (!response.ok) {
                    throw new Error('Erro ao buscar o quiz');
                }
                const data = await response.json();

                questionEl.textContent = data.question;

                if (data.images && data.images.length > 0) {
                    data.images.forEach((imageUri, index) => {
                        const carouselItem = document.createElement('div');
                        carouselItem.classList.add('carousel-item');
                        if (index === 0) {
                            carouselItem.classList.add('active');
                        }

                        const img = document.createElement('img');
                        img.src = imageUri;
                        img.alt = `Imagem ${index + 1}`;
                        img.style.width = '400px';
                        img.style.height = '300px';

                        carouselItem.appendChild(img);
                        carouselInner.appendChild(carouselItem);
                    });
                } else {
                    const carouselItem = document.createElement('div');
                    carouselItem.classList.add('carousel-item', 'active');

                    const img = document.createElement('img');
                    img.src = 'https://via.placeholder.com/100x100?text=Sem+Imagem';
                    img.classList.add('d-block', 'w-100');
                    img.alt = 'Sem Imagem Disponível';
                    img.style.width = '50px';
                    img.style.height = 'auto';

                    carouselItem.appendChild(img);
                    carouselInner.appendChild(carouselItem);
                }

                const quizCarouselElement = document.getElementById('quizCarousel');
                const quizCarousel = new bootstrap.Carousel(quizCarouselElement, {
                    interval: false,
                    wrap: false,
                    keyboard: false
                });

                correctOption = data.correct_answer; 

                data.options.forEach(option => {
                    const button = document.createElement('button');
                    button.classList.add('option-button', 'btn', 'btn-outline-secondary');
                    button.textContent = option;
                    button.onclick = () => handleAnswer(option, button);
                    optionsEl.appendChild(button);
                });

                updateCarouselControls(quizCarousel);

                quizCarouselElement.addEventListener('slide.bs.carousel', function (event) {
                    const totalItems = carouselInner.children.length;
                    const nextIndex = event.to;

                    const prevButton = document.querySelector('.custom-carousel-control-prev');
                    const nextButton = document.querySelector('.custom-carousel-control-next');

                    if (totalItems <= 1) {
                        prevButton.style.display = 'none';
                        nextButton.style.display = 'none';
                        return;
                    }

                    if (nextIndex === 0) {
                        prevButton.style.display = 'none';
                    } else {
                        prevButton.style.display = 'flex'; 
                    }

                    if (nextIndex === totalItems - 1) {
                        nextButton.style.display = 'none';
                    } else {
                        nextButton.style.display = 'flex';
                    }
                });

            } catch (error) {
                console.error(error);
                questionEl.textContent = 'Erro ao carregar o quiz. Tente novamente mais tarde.';
            }
        }

        function handleAnswer(selectedOption, button) {
            const allButtons = document.querySelectorAll('.option-button');
            allButtons.forEach(btn => btn.disabled = true);

            if (selectedOption === correctOption) {
                button.classList.remove('btn-outline-secondary');
                button.classList.add('correct', 'btn-success');
                correctCount++; 
                correctCountEl.textContent = correctCount; 
            } else {
                button.classList.remove('btn-outline-secondary');
                button.classList.add('incorrect', 'btn-danger');

                allButtons.forEach(btn => {
                    if (btn.textContent === correctOption) {
                        btn.classList.remove('btn-outline-secondary');
                        btn.classList.add('correct', 'btn-success');
                    }
                });

                incorrectCount++;
                incorrectCountEl.textContent = incorrectCount;
            }

            totalCount = correctCount + incorrectCount;
            totalCountEl.textContent = totalCount;

            nextButton.style.display = 'inline-block';
        }

        function loadNextQuiz() {
            fetchQuiz();
        }

        nextButton.addEventListener('click', loadNextQuiz);

        window.onload = fetchQuiz;
    </script>
</body>
</html>
