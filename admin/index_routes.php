<?php
    // Variavel com caminho completo do site, para usar em includes
    // C:\xampp\htdocs\uriplants\admin
    define("ROOT_DIR", __DIR__);

    // Verifica se est'a indo para uma rota
    if (isset($_GET['rota'])) {
        $rota = $_GET['rota'];

        if ($rota == "login"){
            require_once( ROOT_DIR . "/controllers/divisionController.php");
        } 
        if ($rota == "divisoes"){
            require_once( ROOT_DIR . "/controllers/divisionController.php");
        } 
        else if ($rota == "profile"){
            require_once( ROOT_DIR . "/controllers/divisionController.php");
        } 
        // Manda para Pagina inicial se passar uma rota que nao existe
        // TODO pag 404
        else{
            require_once( ROOT_DIR . "/views/paginaIncial.php");
        }
    } 
    // Manda para Pagina inicial se nao passar rota
    else { 
        require_once( ROOT_DIR . "/views/paginaIncial.php");
    }