window.addEventListener('DOMContentLoaded', event => {
    // Simple-DataTables
    const datatablesSimple = document.getElementById('datatablesSimple');
    if (datatablesSimple) {
        new simpleDatatables.DataTable(datatablesSimple, {
            labels: {
                placeholder: "Buscar...", 
                perPage: "por página",
                noRows: "Nenhum registro encontrado",
                info: "Mostrando {start} a {end} de {rows} entradas",
                noResults: "Nenhum resultado correspondente",
                perPageSelect: "entradas"
            }
        });
    }
});
