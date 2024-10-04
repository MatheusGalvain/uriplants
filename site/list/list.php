<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URI Plantas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,600;1,400&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/footer.css">
    <style>
        body {
            background: none;
            position: relative;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(150deg, rgb(14 75 22 / 80%) 0%, rgba(57, 91, 65, 0.4) 10%, rgba(34, 49, 36, 0) 40%);
            z-index: 1;
        }

        body::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('../images/logoURIsus.png');
            background-size: contain;
            background-position: right;
            background-repeat: no-repeat;
            background-attachment: fixed;
            opacity: 0.15;
            z-index: 0;
            pointer-events: none;
        }

        section {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            min-height: 700px;
        }

        .header {
            background-color: rgba(255, 255, 255, 0.9);
            border-bottom: 2px solid #ccc;
            position: relative;
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
            border-bottom: 2px solid #006838;
        }

        .header-container {
            display: flex;
            margin-top: 30px;
            min-width: 1200px;
        }

        .icon-container {
            background-color: #1d4535;
            border-radius: 8px;
            font-size: 24px;
            cursor: pointer;
            width: 50px;
            display: flex;
            justify-content: center;
            color: white;
            align-items: center;
            margin-right: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .icon-container:hover {
            background-color: #18392B;
        }

        .text-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-left: 10px;
            border-left: 2px solid black;
        }

        .text-container h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #000;
        }

        .text-container span {
            font-size: 16px;
            color: #6e6e6e;
        }

        .plant-container {
            padding: 20px;
            max-width: 900px;
            margin: 0 auto;
        }

        .plant-card {
            display: flex;
            justify-content: space-between;
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
            border-radius: 10px;
            z-index: 1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            align-items: center;
            cursor: pointer; /* Adicionado para indicar que o cartão é clicável */
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .plant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .plant-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
            border-radius: 10px;
            padding: 2px;
            background: linear-gradient(170deg, #19AF66 0%, #18392B 92%, #0F5332 100%);
            -webkit-mask:
                linear-gradient(#fff 0 0) content-box,
                linear-gradient(#fff 0 0);
            mask:
                linear-gradient(#fff 0 0) content-box,
                linear-gradient(#fff 0 0);
            -webkit-mask-composite: destination-out;
            mask-composite: exclude;
        }

        .plant-card img {
            width: 100px;
            height: 100px;
            min-width: 100px;
            min-height: 100px;
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
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            color: black;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f9f9f9;
        }

        .pagination a:hover {
            background-color: #18392B;
            color: white;
        }

        .pagination a.active {
            background-color: #1d4535;
            color: white;
            cursor: default;
        }

        .pagination a.arrow {
            color: black;
        }

        .pagination a.arrow:hover {
            background-color: #18392B;
            color: white;
        }

        .pagination-info {
            margin: 0 10px;
            font-weight: bold;
        }

        footer {
            background: linear-gradient(to right, #13673f, #1d4535);
            padding: 20px;
            color: white;
            text-align: center;
            z-index: 999;
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

    <section>
        <div>
            <div class="header-container">
                <a href="../" class="icon-container">
                    <i class="fas fa-angle-left"></i>
                </a>
                <div class="text-container">
                    <span>Você está em</span>
                    <h1>URI Plantas</h1>
                </div>
            </div>
        </div>

        <div>
            <div class="plant-container">
                <div id="plants"></div>
            </div>
            <div class="pagination" id="pagination"></div>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const plantsContainer = document.getElementById('plants');
            const paginationContainer = document.getElementById('pagination');

            let currentPage = 1;
            const limit = 10;
            let totalPages = 1;

            async function fetchPlants(page, limit) {
                try {
                    const response = await fetch(`http://localhost/uriplants/public/plants?limit=${limit}&page=${page}`);
                    if (!response.ok) {
                        throw new Error('Erro na requisição');
                    }
                    const data = await response.json();
                    console.log('Dados recebidos:', data);
                    return data;
                } catch (error) {
                    console.error(error);
                    plantsContainer.innerHTML = '<p>Ocorreu um erro ao carregar as plantas.</p>';
                }
            }

            function renderPlants(plants) {
                plantsContainer.innerHTML = '';
                if (!plants || plants.length === 0) {
                    plantsContainer.innerHTML = '<p>Nenhuma planta encontrada.</p>';
                    return;
                }

                plants.forEach(plant => {
                    const plantCard = document.createElement('div');
                    plantCard.classList.add('plant-card');

                    // Adiciona um atributo data-id para armazenar o ID da planta
                    plantCard.setAttribute('data-id', plant.id);

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
                        plantImage.src = `data:image/jpeg;base64,${plant.image_blob}`;
                    } else {
                        plantImage.src = 'plant-placeholder.png';
                    }

                    plantImage.alt = `Imagem de ${plant.name}`;

                    plantCard.appendChild(plantInfo);
                    plantCard.appendChild(plantImage);

                    // Adiciona o listener de clique ao cartão da planta
                    plantCard.addEventListener('click', () => {
                        const plantId = plant.id; // Obtém o ID da planta
                        
                        // Redireciona para a página plant.php com o ID da planta na URL
                        window.location.href = `../plant/plant.php?id=${plantId}`;
                    });

                    plantsContainer.appendChild(plantCard);
                });
            }

            function renderPagination() {
                paginationContainer.innerHTML = '';

                if (currentPage > 1) {
                    const prevLink = document.createElement('a');
                    prevLink.innerHTML = '<i class="fas fa-angle-double-left"></i>';
                    prevLink.href = '#';
                    prevLink.setAttribute('aria-label', 'Página Anterior');
                    prevLink.classList.add('arrow');
                    prevLink.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (currentPage > 1) {
                            currentPage--;
                            loadPlants();
                        }
                    });
                    paginationContainer.appendChild(prevLink);
                }

                for (let i = 1; i <= totalPages; i++) {
                    const pageLink = document.createElement('a');
                    pageLink.textContent = i;
                    pageLink.href = '#';
                    if (i === currentPage) {
                        pageLink.classList.add('active');
                        pageLink.setAttribute('aria-current', 'page');
                    }
                    pageLink.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (i !== currentPage) {
                            currentPage = i;
                            loadPlants();
                        }
                    });
                    paginationContainer.appendChild(pageLink);
                }

                if (currentPage < totalPages) {
                    const nextLink = document.createElement('a');
                    nextLink.innerHTML = '<i class="fas fa-angle-double-right"></i>';
                    nextLink.href = '#';
                    nextLink.setAttribute('aria-label', 'Próxima Página');
                    nextLink.classList.add('arrow');
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

            async function loadPlants() {
                const data = await fetchPlants(currentPage, limit);
                if (!data) return;

                renderPlants(data.plants);

                totalPages = data.totalPages || 1;
                currentPage = data.currentPage || 1;

                renderPagination();
            }

            loadPlants();
        });
    </script>
</body>

</html>
