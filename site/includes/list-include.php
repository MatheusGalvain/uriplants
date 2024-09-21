<script>

document.addEventListener('DOMContentLoaded', function() {
    fetch('http://localhost/uriplants/public/plants')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('plants-container');
            let html = '';

            data.forEach(plant => {
                html += `
                    <div class="plant-box">
                        <div class="plant-info">
                            <h1>${plant.name}</h1>
                            <p>${plant.ecology}</p>
                        </div>
                        <div class="plant-photo">
                            <img src="${plant.image_source}" alt="Foto da planta">
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        })
        .catch(error => console.error('Erro ao buscar as plantas:', error));
    }
);

</script>
<div class="list-page-container">
    <div class="list-page-content">
        <div class="location-container">
            <p class="location-text">
                Você está em:
            </p>
            <h1 id="location">
                URI Plantas
            </h1>
        </div>
        <section class="plant-list" id="plants-container"></section>
    </div>
</div>