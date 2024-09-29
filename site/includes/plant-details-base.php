<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('http://localhost/uriplants/public/plants')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('plants-container');
                let html = '';
                let plant = data[0];

                let plant_name_h2 = document.getElementById('plant-name');

                plant_name_h2.innerHTML = plant.name;
            })
            .catch(error => console.error('Erro ao buscar as plantas:', error));
        fetch('https://picsum.photos/v2/list?page=1&limit=10')
            .then(response => response.json())
            .then(data => {
                const imageContainer = document.getElementById('plant-carousel');

                data.forEach(imageData => {
                    const img = document.createElement('img');
                    img.src = imageData.download_url;
                    img.alt = 'imgs-carousel-plants-details';
                    imageContainer.appendChild(img);
                });

                $('#plant-carousel').slick({
                    speed: 300,
                    slidesToShow: 4,
                    slidesToScroll: 4,
                    responsive: [{
                            breakpoint: 1025,
                            settings: {
                                slidesToShow: 4,
                                slidesToScroll: 4,
                            }
                        },
                        {
                            breakpoint: 769,
                            settings: {
                                slidesToShow: 3,
                                slidesToScroll: 3
                            }
                        },
                        {
                            breakpoint: 481,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 2
                            }
                        }
                    ]
                });
            })
            .catch(error => console.error('Erro ao buscar imagens', error));
    });
</script>

<main class="plant-details-home">
    <div class="plant-details-top-img">
        <img src="images/img-test/baoba.jpg" alt="Imagem">
    </div>
    <section class="plant-content">
        <section class="carousel-container">
            <div class="plants-carousel-content">
                <div class="plant-details-carousel-imgs" id="plant-carousel"></div>
            </div>
        </section>
        <section class="floating-menu">
            <div class="buttons-floating-menu">
                <button id="defaultOpen" class="tablinks" onclick="openTab(event, 'description-class-content')">DESCRIÇÃO</button>
                <button class="tablinks" onclick="openTab(event, 'taxonomy-class-content')">TAXONOMIA</button>
                <button class="tablinks" onclick="openTab(event, 'ecology-class-content')">ECOLOGIA</button>
                <button class="tablinks" onclick="openTab(event, 'occourrence-class-content')">OCORRÊNCIA</button>
            </div>
        </section>
        <div id="description-class-content" class="tabcontent">
        <?php include("plant-details-main-description.php"); ?>
        </div>
        <div id="taxonomy-class-content" class="tabcontent">
        <?php include("plant-details-main-taxonomy.php"); ?>
        </div>
        <div id="ecology-class-content" class="tabcontent">
        <?php include("plant-details-main-ecology.php"); ?>
        </div>
        <div id="occourrence-class-content" class="tabcontent">
        <?php include("plant-details-main-occourrence.php"); ?>
        </div>
    </section>
</main>