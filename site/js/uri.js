
$(document).ready(function () {
    $.ajax({
        type: 'post',
        url: 'https://uricer.edu.br/requisicoes/cabecalho.php',
        data: 'req=' + true,
        dataType: 'html'
    }).then((result) => {

        $('#resultH').html(result);
    })

    $.ajax({
        type: 'post',
        url: 'https://uricer.edu.br/requisicoes/rodape.php',
        data: 'req=' + true,
        dataType: 'html'
    }).then((result) => {
        $('#resultR').html(result);
    })
});