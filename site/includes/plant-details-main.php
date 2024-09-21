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
    }
);
</script>

<main class="plant-details-home">
    <div class="plant-details-top-img">
        <img src="images/img-test/baoba.jpg" alt="Imagem">
    </div>
    <section class="plant-content">
        <section class="carousel-container">
            <div class="plant-details-carousel-imgs">
                <img src="images/img-test/little-plants.jpeg" alt="imgs-carousel-plants-details">
                <img src="images/img-test/little-plants.jpeg" alt="imgs-carousel-plants-details">
                <img src="images/img-test/little-plants.jpeg" alt="imgs-carousel-plants-details">
                <img src="images/img-test/little-plants.jpeg" alt="imgs-carousel-plants-details">
            </div>
        </section>
        <img class="arrow left-arrow" src="images/img-test/left-arrow.png" alt="Seta esquerda">
        <img class="arrow right-arrow" src="images/img-test/right-arrow.png" alt="Seta direita">

        <section class="floating-menu">
            <div class="buttons-floating-menu">
                <a href="">DESCRIÇÃO</a>
                <a href="">TAXONOMIA</a>
                <a href="">ECOLOGIA</a>
                <a href="">OCORRÊNCIA</a>
            </div>
        </section>
        <section class="resume">
            <div class="name-container">
                <h1>Nome</h1>
                <h2 id="plant-name"></h2>
            </div>

            <div class="scientific-name-container">
                <h1>Nome Científico</h1>
                <h2 id="scientific-name">RENAN CIENTÍFICO</h2>
            </div>

            <div class="resume-description">
                <h1>Descrição</h1>
                <p id="summary-description">Lorem ipsum dolor sit amet consectetur adipisicing elit. Adipisci laudantium laboriosam molestias neque mollitia maiores laborum et odio repellat tenetur, fuga ipsam eveniet! Animi, ea. Id maxime voluptatibus ex labore?</p>
            </div>
        </section>

        <section class="detailed-description">
            <div class="detailed-description-flag">
                <img src="images/planty-tree.png" alt="">
                <p href="">Descrições detalhadas</p>
            </div>

            <div class="plant-details-wrapper">
                <div class="growth-form">
                    <h1>Forma Biológica</h1>
                    <p id="growth-form">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nihil optio vero animi recusandae magni velit sed ducimus inventore repellat fugiat quae in corrupti, nulla voluptatibus culpa eos autem iste iure.</p>
                    <div class="growth-form-photos">
                        <img src="images/img-test/trunk.png" alt="growth-form-photo">
                        <img src="images/img-test/trunk.png" alt="growth-form-photo">
                        <img src="images/img-test/trunk.png" alt="growth-form-photo">
                        <img src="images/img-test/trunk.png" alt="growth-form-photo">
                    </div>
                </div>

                <div class="tree-trunk">
                    <h1>Tronco</h1>
                    <p id="tree-trunk">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nihil optio vero animi recusandae magni velit sed ducimus inventore repellat fugiat quae in corrupti, nulla voluptatibus culpa eos autem iste iure.</p>
                    <div class="tree-trunk-photos">
                        <img src="images/img-test/trunk.png" alt="tree-trunk-photo">
                        <img src="images/img-test/trunk.png" alt="tree-trunk-photo">
                        <img src="images/img-test/trunk.png" alt="tree-trunk-photo">
                        <img src="images/img-test/trunk.png" alt="tree-trunk-photo">
                    </div>
                </div>

                <div class="tree-bark">
                    <h1>Casca</h1>
                    <p id="tree-bark">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nihil optio vero animi recusandae magni velit sed ducimus inventore repellat fugiat quae in corrupti, nulla voluptatibus culpa eos autem iste iure.</p>
                    <div class="tree-bark-photos">
                        <img src="images/img-test/trunk.png" alt="tree-bark-photo">
                        <img src="images/img-test/trunk.png" alt="tree-bark-photo">
                        <img src="images/img-test/trunk.png" alt="tree-bark-photo">
                        <img src="images/img-test/trunk.png" alt="tree-bark-photo">
                    </div>
                </div>

                <div class="leaf-blades">
                    <h1>Folhas</h1>
                    <p id="leaf-blades">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nihil optio vero animi recusandae magni velit sed ducimus inventore repellat fugiat quae in corrupti, nulla voluptatibus culpa eos autem iste iure.</p>
                    <div class="leaf-blades-photos">
                        <img src="images/img-test/trunk.png" alt="leaf-blades-photo">
                        <img src="images/img-test/trunk.png" alt="leaf-blades-photo">
                        <img src="images/img-test/trunk.png" alt="leaf-blades-photo">
                        <img src="images/img-test/trunk.png" alt="leaf-blades-photo">
                    </div>
                </div>

                <div class="flower-buds">
                    <h1>Flores</h1>
                    <p id="flower-buds">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nihil optio vero animi recusandae magni velit sed ducimus inventore repellat fugiat quae in corrupti, nulla voluptatibus culpa eos autem iste iure.</p>
                    <div class="flower-buds-photos">
                        <img src="images/img-test/trunk.png" alt="flower-buds-photo">
                        <img src="images/img-test/trunk.png" alt="flower-buds-photo">
                        <img src="images/img-test/trunk.png" alt="flower-buds-photo">
                        <img src="images/img-test/trunk.png" alt="flower-buds-photo">
                    </div>
                </div>

                <div class="fruit-bodies">
                    <h1>Frutos</h1>
                    <p id="fruit-bodies">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nihil optio vero animi recusandae magni velit sed ducimus inventore repellat fugiat quae in corrupti, nulla voluptatibus culpa eos autem iste iure.</p>
                    <div class="fruit-bodies-photos">
                        <img src="images/img-test/trunk.png" alt="fruit-bodies-photo">
                        <img src="images/img-test/trunk.png" alt="fruit-bodies-photo">
                        <img src="images/img-test/trunk.png" alt="fruit-bodies-photo">
                        <img src="images/img-test/trunk.png" alt="fruit-bodies-photo">
                    </div>
                </div>

                <div class="seed-pods">
                    <h1>Sementes</h1>
                    <p id="seed-pods">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nihil optio vero animi recusandae magni velit sed ducimus inventore repellat fugiat quae in corrupti, nulla voluptatibus culpa eos autem iste iure.</p>
                    <div class="seed-pods-photos">
                        <img src="images/img-test/trunk.png" alt="seed-pods-photo">
                        <img src="images/img-test/trunk.png" alt="seed-pods-photo">
                        <img src="images/img-test/trunk.png" alt="seed-pods-photo">
                        <img src="images/img-test/trunk.png" alt="seed-pods-photo">
                    </div>
                </div>
            </div>

        </section>
    </section>
</main>