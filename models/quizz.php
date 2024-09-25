<?php
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Quiz de Plantas</title>
    <!-- Inclusão do Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Inclusão do Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            padding: 20px;
            text-align: center;
        }

        #quiz-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative; /* Para posicionar o placar dentro do contêiner */
        }
        
        /* Estilos do Placar */
        #scoreboard {
            display: flex;
            gap: 10px;
            justify-content: space-around;
            margin-bottom: 20px;
        }

        .score-block {
            padding: 10px 20px;
            border-radius: 5px;
            color: #fff;
            font-size: 1em;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
            min-width: 120px;
            text-align: center;
        }

        .score-acertos {
            background-color: #28a745; /* Verde */
        }

        .score-erros {
            background-color: #dc3545; /* Vermelho */
        }

        .score-total {
            background-color: #007bff; /* Azul */
        }

        #question {
            font-size: 1.5em;
            margin: 20px 0;
        }

        .option-button {
            display: block;
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            font-size: 1.1em;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .option-button:hover {
            background-color: #ddd;
        }

        .correct {
            background-color: #28a745 !important;
            color: #fff;
            animation: fadeIn 0.5s;
            border-color: #28a745 !important;
        }

        .incorrect {
            background-color: #dc3545 !important;
            color: #fff;
            animation: fadeIn 0.5s;
            border-color: #dc3545 !important;
        }

        #next-button {
            padding: 10px 20px;
            font-size: 1.1em;
            margin-top: 20px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            display: none;
            transition: background-color 0.3s ease;
        }

        #next-button:hover {
            background-color: #0056b3;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 800px) {
            #quiz-container {
                padding: 20px;
            }

            .option-button {
                font-size: 1em;
            }

            #next-button {
                width: 100%;
            }

            /* Ajuste do placar em telas menores */
            #scoreboard {
                flex-direction: column;
                align-items: center;
            }

            .score-block {
                width: 100%;
                max-width: 300px;
            }
        }

        .carousel-item img {
            width: 100px;
            height: 100px;
            margin: 0 auto; 
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            display: none; 
        }

        .custom-carousel-control-prev,
        .custom-carousel-control-next {
            background-color: rgba(0, 0, 0, 0.5);
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #fff;
            transition: background-color 0.3s ease;
        }

        .custom-carousel-control-prev:hover,
        .custom-carousel-control-next:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }

        .custom-carousel-control-prev {
            left: 10px;
        }

        .custom-carousel-control-next {
            right: 10px;
        }

        .custom-carousel-control-prev i,
        .custom-carousel-control-next i {
            font-size: 1.5em;
        }
    </style>
</head>
<body>
    <div id="quiz-container">
       
        <!-- Placar -->
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

        <div id="quizCarousel" class="carousel slide" data-bs-interval="false">
            <div class="carousel-inner" id="carousel-inner">

            </div>
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
    </div>

    <!-- Inclusão do Bootstrap JS e dependências -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        const QUIZ_URL = 'http://localhost/uriplants/public/quizz';

        const carouselInner = document.getElementById('carousel-inner');
        const questionEl = document.getElementById('question');
        const optionsEl = document.getElementById('options');
        const nextButton = document.getElementById('next-button');

        // Elementos do Placar
        const correctCountEl = document.getElementById('correct-count');
        const incorrectCountEl = document.getElementById('incorrect-count');
        const totalCountEl = document.getElementById('total-count');

        // Variáveis para armazenar a contagem de acertos, erros e total de respostas
        let correctCount = 0;
        let incorrectCount = 0;
        let totalCount = 0;

        let correctOption = '';

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

        // Extensão do Bootstrap Carousel para obter o índice atual
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
                correctCount++; // Incrementa a contagem de acertos
                correctCountEl.textContent = correctCount; // Atualiza o placar de acertos
            } else {
                button.classList.remove('btn-outline-secondary');
                button.classList.add('incorrect', 'btn-danger');

                allButtons.forEach(btn => {
                    if (btn.textContent === correctOption) {
                        btn.classList.remove('btn-outline-secondary');
                        btn.classList.add('correct', 'btn-success');
                    }
                });

                incorrectCount++; // Incrementa a contagem de erros
                incorrectCountEl.textContent = incorrectCount; // Atualiza o placar de erros
            }

            totalCount = correctCount + incorrectCount; // Calcula o total de respostas
            totalCountEl.textContent = totalCount; // Atualiza o placar de total

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
