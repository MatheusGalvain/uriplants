<div id ="resultH"></div>
<div id ="resultR"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"
  integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
  crossorigin="anonymous"></script>
<script>
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
</script>