<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Quiz de Plantas</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        $.ajax({
            type: 'post',
            url: 'https://uricer.edu.br/requisicoes/cabecalho.php',
            data: 'req=' + true,
            dataType: 'html'
        }).then((result) => {
            $('#resultH').html(result);
        });

        $.ajax({
            type: 'post',
            url: 'https://uricer.edu.br/requisicoes/rodape.php',
            data: 'req=' + true,
            dataType: 'html'
        }).then((result) => {
            $('#resultR').html(result);
        });
    </script>
    <link rel="stylesheet" href="../site/css/reset.css">
    <link rel="stylesheet" href="../site/css/header.css">
    <link rel="stylesheet" href="../site/css/footer.css">
    <link rel="stylesheet" href="../site/css/listplant_responsive.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
    crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../site/css/quiz.css">
</head>

<body>
    <main class="box-content">
        <div id="resultH"></div>
        <div class="container-wrapp">
            <div class="container-infos">
                <div class="header-container">
                    <a href="../site/home.php" class="icon-container">
                        <i class="fas fa-angle-left"></i>
                    </a>
                    <div class="text-container">
                        <span>Você está em</span>
                        <h1>URI Plantas</h1>
                    </div>
                </div>
            </div>
        </div>
        <div id="quiz-container">
            <h1 class="titlequiz">Bem Vindos ao <strong>URI Quiz</strong></h1>
            <!-- <h2 class="subtitlequiz">Prepare-se para um desafio épico! Lembre-se, seu maior objetivo é alcançar uma pontuação inigualável sem cometer erros e se tornar o verdadeiro Deus das plantas!</h2> -->
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

            <div id="question">Carregando pergunta...</div>

            <div id="options">
            </div>

            <button id="next-button" class="btn btn-primary">Próxima Pergunta</button>
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
        </div>
    </main>
    <div id="resultR"></div>
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

        let quizCarouselElement = document.getElementById('quizCarousel');
        let quizCarousel = new bootstrap.Carousel(quizCarouselElement, {
            interval: false,
            wrap: false,
            keyboard: false
        });

        $('.button-menu').click(function () {
            $(this).toggleClass('button-menu-close');
            $('#main-menu-mobile-items').toggleClass('main-menu-mobile-items-open');
        });

        $('.item-menu').click(function () {

            $('.menu-link').removeClass('activeheader');

            $(this).children().addClass('activeheader');
        });

        function updateCarouselControls() {
            const prevButton = document.querySelector('.custom-carousel-control-prev');
            const nextButton = document.querySelector('.custom-carousel-control-next');
            const totalItems = carouselInner.children.length;

            const activeItem = carouselInner.querySelector('.carousel-item.active');
            const currentIndex = Array.from(carouselInner.children).indexOf(activeItem);

            if (totalItems <= 1) {
                prevButton.style.display = 'none';
                nextButton.style.display = 'none';
                return;
            }

            prevButton.style.display = currentIndex === 0 ? 'none' : 'flex';
            nextButton.style.display = currentIndex === totalItems - 1 ? 'none' : 'flex';
        }

        bootstrap.Carousel.prototype.getActiveIndex = function () {
            return Array.from(this._element.querySelectorAll('.carousel-item')).indexOf(this._element.querySelector('.carousel-item.active'));
        };

        async function fetchQuiz() {
            try {
                optionsEl.innerHTML = '';
                nextButton.style.display = 'none';
                questionEl.textContent = 'Carregando pergunta...';

                const response = await fetch(QUIZ_URL);
                if (!response.ok) {
                    throw new Error('Erro ao buscar o quiz');
                }
                const data = await response.json();

                questionEl.textContent = data.question;

                carouselInner.innerHTML = '';

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
                    img.alt = 'Sem Imagem Disponível';
                    img.style.width = '50px';
                    img.style.height = 'auto';

                    carouselItem.appendChild(img);
                    carouselInner.appendChild(carouselItem);
                }

                updateCarouselControls();

                correctOption = data.correct_answer;

                data.options.forEach(option => {
                    const button = document.createElement('button');
                    button.classList.add('option-button', 'btn', 'btn-outline-secondary');
                    button.textContent = option;
                    button.onclick = () => handleAnswer(option, button);
                    optionsEl.appendChild(button);
                });
                nextButton.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

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
