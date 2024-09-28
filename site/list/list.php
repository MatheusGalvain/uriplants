<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URI Plantas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/footer.css">
    <style>

        /* Header section */
        .header {
            background-color: #fff;
            padding: 20px;
            border-bottom: 2px solid #ccc;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
        }

        .social-icons a img {
            width: 24px;
            margin-right: 10px;
        }

        .contact-info span {
            font-weight: bold;
        }

        .logo-slider img {
            width: 150px;
        }

        /* Navigation */
        nav {
            display: flex;
            justify-content: center;
            background-color: #2e2e2e;
            padding: 10px 0;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 0 20px;
            font-size: 16px;
            cursor: pointer;
        }

        nav a.active {
            border-bottom: 2px solid #00ff00;
        }

        /* Plant cards */
        .plant-container {
            padding: 20px;
            max-width: 900px;
            margin: 0 auto; /* Centraliza o container */
        }

        .plant-card {
            display: flex;
            justify-content: space-between;
            background-color: white;
            padding: 15px;
            margin-bottom: 15px;
            border: 2px solid #00ff00;
            border-radius: 8px;
            align-items: center;
        }

        .plant-card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .plant-info {
            flex-grow: 1;
            padding: 0 20px;
        }

        .plant-info h3 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .plant-info p {
            margin: 10px 0 0;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 0;
            flex-wrap: wrap;
        }

        .pagination a {
            text-decoration: none;
            margin: 5px;
            padding: 8px 12px;
            border: 1px solid #00ff00;
            border-radius: 4px;
            color: black;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }

        .pagination a:hover {
            background-color: #00ff00;
            color: white;
        }

        .pagination a.active {
            background-color: #00ff00;
            color: white;
            cursor: default;
        }

        .pagination-info {
            margin: 0 10px;
            font-weight: bold;
        }

        /* Footer */
        footer {
            background-color: #006838;
            padding: 20px;
            color: white;
            text-align: center;
        }

        footer .footer-columns div {
            flex-grow: 1;
        }

        footer a {
            color: white;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        footer .social-icons a img {
            width: 30px;
            margin: 0 10px;
        }

    </style>
</head>

<body>
    <!-- Header -->
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
    </header>

    <!-- Navigation -->
    <nav>
        <a href="#">URI Quiz</a>
        <a href="#">URI Animais</a>
        <a href="#" class="active">URI Plantas</a>
    </nav>

    <!-- Plant cards -->
    <div class="plant-container">
        <h2>Você está em URI Plantas</h2>

        <!-- Plant cards serão inseridos aqui dinamicamente -->
        <div id="plants"></div>

        <!-- Pagination -->
        <div class="pagination" id="pagination">
            <!-- Links de paginação serão inseridos aqui dinamicamente -->
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-left">
            <div class="redes-sociais">
                <span>Redes Sociais</span>
                <div class="display-icons">
                    <a href="https://www.facebook.com/uricampuserechim"><img src="images/facebook-icon.png" alt="Facebook"/></a>
                    <a href="https://www.instagram.com/urierechim/"><img src="images/instagram-icon.png" alt="Instagram"/></a>
                    <a href="https://twitter.com/urierechim"><img src="images/x-icon.png" alt="Twitter"/></a>
                    <a href="https://www.youtube.com/urierechim"><img src="images/youtube-icon.png" alt="YouTube"/></a>
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
            <a href="https://www.uricer.edu.br/"><img src="images/uri-logo.png" alt="Logo URI"/></a>
        </div>
    </footer>

    <!-- Script para carregar dinamicamente as plant cards -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const plantsContainer = document.getElementById('plants');
            const paginationContainer = document.getElementById('pagination');

            let currentPage = 1;
            const limit = 1; // Ajuste conforme necessário
            let totalPages = 1;

            // Função para buscar plantas do endpoint
            async function fetchPlants(page, limit) {
                try {
                    const response = await fetch(`http://localhost/uriplants/public/plants?limit=${limit}&page=${page}`);
                    if (!response.ok) {
                        throw new Error('Erro na requisição');
                    }
                    const data = await response.json();
                    console.log('Dados recebidos:', data); // Log para depuração
                    return data;
                } catch (error) {
                    console.error(error);
                    plantsContainer.innerHTML = '<p>Ocorreu um erro ao carregar as plantas.</p>';
                }
            }

            // Função para renderizar as plantas na página
            function renderPlants(plants) {
                plantsContainer.innerHTML = ''; // Limpa o conteúdo anterior
                if (!plants || plants.length === 0) {
                    plantsContainer.innerHTML = '<p>Nenhuma planta encontrada.</p>';
                    return;
                }

                plants.forEach(plant => {
                    const plantCard = document.createElement('div');
                    plantCard.classList.add('plant-card');

                    const plantInfo = document.createElement('div');
                    plantInfo.classList.add('plant-info');

                    const plantName = document.createElement('h3');
                    plantName.textContent = `Nome: ${plant.name}`;

                    const plantDescription = document.createElement('p');
                    plantDescription.textContent = `Descrição: ${plant.description}`;

                    plantInfo.appendChild(plantName);
                    plantInfo.appendChild(plantDescription);

                    const plantImage = document.createElement('img');

                    if (plant.image_blob) {
                        // Supondo que 'image_blob' contém a string Base64 da imagem
                        // Detecta o tipo da imagem a partir do prefixo do blob
                        // Para simplificar, vamos assumir JPEG. Se precisar de tipos diferentes, ajuste conforme necessário.
                        plantImage.src = `data:image/jpeg;base64,${plant.image_blob}`;
                    } else {
                        // Usa a imagem placeholder se não houver imagem fornecida
                        plantImage.src = 'plant-placeholder.png';
                    }

                    plantImage.alt = `Imagem de ${plant.name}`;

                    plantCard.appendChild(plantInfo);
                    plantCard.appendChild(plantImage);

                    plantsContainer.appendChild(plantCard);
                });
            }

            // Função para renderizar a paginação
            function renderPagination() {
                paginationContainer.innerHTML = ''; // Limpa a paginação anterior

                // Botão "Anterior"
                if (currentPage > 1) {
                    const prevLink = document.createElement('a');
                    prevLink.textContent = 'Anterior';
                    prevLink.href = '#';
                    prevLink.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (currentPage > 1) {
                            currentPage--;
                            loadPlants();
                        }
                    });
                    paginationContainer.appendChild(prevLink);
                }

                // Exibir a informação da página atual e total de páginas
                const infoSpan = document.createElement('span');
                infoSpan.classList.add('pagination-info');
                infoSpan.textContent = `Página ${currentPage} de ${totalPages}`;
                paginationContainer.appendChild(infoSpan);

                // Botão "Próximo"
                if (currentPage < totalPages) {
                    const nextLink = document.createElement('a');
                    nextLink.textContent = 'Próximo';
                    nextLink.href = '#';
                    nextLink.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (currentPage < totalPages) {
                            currentPage++;
                            loadPlants();
                        }
                    });
                    paginationContainer.appendChild(nextLink);
                }
            }

            // Função principal para carregar as plantas e a paginação
            async function loadPlants() {
                const data = await fetchPlants(currentPage, limit);
                if (!data) return;

                renderPlants(data.plants);

                // Atualiza as variáveis de paginação
                totalPages = data.totalPages || 1;
                currentPage = data.currentPage || 1;

                renderPagination();
            }

            // Carrega as plantas na inicialização
            loadPlants();
        });
    </script>
</body>

</html>
