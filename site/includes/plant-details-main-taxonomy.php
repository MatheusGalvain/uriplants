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
                <a href="">DESCRIÇÃO</a>
                <a href="">TAXONOMIA</a>
                <a href="">ECOLOGIA</a>
                <a href="">OCORRÊNCIA</a>
            </div>
        </section>


        <section class="plant-taxonomy">
            <div class="plant-taxonomy-flag">
                <img src="images/planty-tree.png" alt="">
                <p href="">TAXONOMIA E NOMENCLATURA</p>
            </div>

            <div class="taxonomy-details-wrapper">
                <div class="plant-division">
                    <h1>Divisão</h1>
                    <p id="plant-division">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nihil optio vero animi recusandae magni velit sed ducimus inventore repellat fugiat quae in corrupti, nulla voluptatibus culpa eos autem iste iure.</p>
                </div>

                <div class="plant-class">
                    <h1>Classe</h1>
                    <p id="plant-class">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nihil optio vero animi recusandae magni velit sed ducimus inventore repellat fugiat quae in corrupti, nulla voluptatibus culpa eos autem iste iure.</p>
                </div>

                <div class="plant-order">
                    <h1>Ordem</h1>
                    <p id="plant-order">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nihil optio vero animi recusandae magni velit sed ducimus inventore repellat fugiat quae in corrupti, nulla voluptatibus culpa eos autem iste iure.</p>
                </div>

                <div class="plant-family">
                    <h1>Família</h1>
                    <p id="plant-family">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nihil optio vero animi recusandae magni velit sed ducimus inventore repellat fugiat quae in corrupti, nulla voluptatibus culpa eos autem iste iure.</p>
                </div>

                <div class="plant-gender">
                    <h1>Gênero</h1>
                    <p id="plant-gender">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nihil optio vero animi recusandae magni velit sed ducimus inventore repellat fugiat quae in corrupti, nulla voluptatibus culpa eos autem iste iure.</p>
                </div>

                <div class="plant-species">
                    <h1>Espécie</h1>
                    <p id="plant-species">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nihil optio vero animi recusandae magni velit sed ducimus inventore repellat fugiat quae in corrupti, nulla voluptatibus culpa eos autem iste iure.</p>
                </div>
            </div>

        </section>
    </section>
</main>