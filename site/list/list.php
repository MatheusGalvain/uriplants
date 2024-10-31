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
    <script src="https://kit.fontawesome.com/70aed2b9f4.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/listplant.css">
    <link rel="stylesheet" href="../css/listplant_responsive.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
  integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
  crossorigin="anonymous"></script>
  <script>
    $(document).ready(function() {
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
    });
</script>
   
</head>
<body>
    
    <div id ="resultH"></div>
    <section>
        <div class="container-wrapp">
            <div class="container-infos">
                <div class="header-container">
                    <a href="../../" class="icon-container">
                        <i class="fas fa-angle-left"></i>
                    </a>
                    <div class="text-container">
                        <span>Você está em</span>
                        <h1>URI Plantas</h1>
                    </div>
                </div>
            </div>
            <div class="containerbtns">
            <form action="../list/list.php" method="GET" class="search-form">
                <input type="text" name="query" placeholder="Buscar..." aria-label="Pesquisar plantas" value="<?= htmlspecialchars($_GET['query'] ?? '', ENT_QUOTES) ?>">
                <button type="submit"><i class="fa-solid fa-magnifying-glass" style="color: #141414;"></i></button>
            </form>
                <div id="search-result" style="display: none;">
                    <span class="searchspan" id="search-text"></span>
                    <a class="searchhref" href="../list/list.php"><i class="far fa-times-circle"></i></a>
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

    <div id ="resultR"></div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const plantsContainer = document.getElementById('plants');
        const paginationContainer = document.getElementById('pagination');
        const searchResult = document.getElementById('search-result');
        const searchText = document.getElementById('search-text');

        let currentPage = 1;
        const limit = 10;
        let totalPages = 1;

        const urlParams = new URLSearchParams(window.location.search);
        const query = urlParams.get('query') || '';

        if (query) {
            searchResult.style.display = 'flex';
        } else {
            searchResult.style.display = 'none';
        }

        async function fetchPlants(page, limit, query) {
            try {
                const response = await fetch(`http://localhost/uriplants/public/plants?limit=${limit}&page=${page}&query=${encodeURIComponent(query)}`);
                if (!response.ok) {
                    throw new Error('Erro na requisição');
                }
                const data = await response.json();
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
                plantCard.setAttribute('data-id', plant.id);

                const plantInfo = document.createElement('div');
                plantInfo.classList.add('plant-info');

                const plantName = document.createElement('h3');
                plantName.textContent = `${plant.name}`;

                const plantDescription = document.createElement('p');
                plantDescription.textContent = `${plant.common_names}` + ` ${plant.description}` ;

                plantInfo.appendChild(plantName);
                plantInfo.appendChild(plantDescription);

                const plantImage = document.createElement('img');
                plantImage.src = plant.image_blob ? `data:image/jpeg;base64,${plant.image_blob}` : 'plant-placeholder.png';
                plantImage.alt = `Imagem de ${plant.name}`;

                plantCard.appendChild(plantInfo);
                plantCard.appendChild(plantImage);

                plantCard.addEventListener('click', () => {
                    window.location.href = `../plant/plant.php?id=${plant.id}`;
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
            const data = await fetchPlants(currentPage, limit, query);
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

