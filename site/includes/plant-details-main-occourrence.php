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
    });
</script>

<main class="plant-details-home">
    <div class="plant-details-top-img">
        <img src="images/img-test/baoba.jpg" alt="Imagem">
    </div>
    <section class="plant-content">
    <section class="carousel-container">
        <div class="plant-details-carousel-imgs">
                <img class="arrow left-arrow" src="images/img-test/left-arrow.png" alt="Seta esquerda">
                <img src="images/img-test/little-plants.jpeg" alt="imgs-carousel-plants-details">
                <img src="images/img-test/little-plants.jpeg" alt="imgs-carousel-plants-details">
                <img src="images/img-test/little-plants.jpeg" alt="imgs-carousel-plants-details">
                <img src="images/img-test/little-plants.jpeg" alt="imgs-carousel-plants-details">
                <img class="arrow right-arrow" src="images/img-test/right-arrow.png" alt="Seta direita">
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
        <section class="climatic-conditions">
            <div class="name-container">
                <h1>Condições Climáticas</h1>
                <h2 id="plant-name">NOME PLANTA LALAL</h2>
            </div>
        </section>
        <section class="climatic-conditions-content">
            <div class="climatic-conditions-seasons">
                <img src="https://picsum.photos/800/500" alt="">
                <div class="climatic-conditions-seasons-text">
                <h1>Estação Climática</h1>
                <h2 id="climatic-season">VERÃO</h2>
                <h1>Descrição</h1>
                <p class="climatic-conditions-seasons-description">Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatum hic ad quasi tenetur, accusantium unde atque suscipit cumque vitae eaque doloremque tempora ut sunt provident autem. Quas odit cum aliquam?</p>

                </div>
            </div>
        </section>


    </section>
</main>