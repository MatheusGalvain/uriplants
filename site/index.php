<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UriPlants</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        h1 {
            margin-bottom: 40px;
            color: #333;
        }

        .button-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            padding: 15px 30px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-align: center;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        /* Cores específicas para cada botão */
        .btn-admin {
            background-color: #28a745; /* Verde */
        }

        .btn-admin:hover {
            background-color: #1e7e34;
        }

        .btn-plants {
            background-color: #17a2b8; /* Azul Claro */
        }

        .btn-plants:hover {
            background-color: #117a8b;
        }

        .btn-quizz {
            background-color: #ffc107; /* Amarelo */
            color: #212529;
        }

        .btn-quizz:hover {
            background-color: #e0a800;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Bem-vindo ao UriPlants</h1>
        <div class="button-group">
            <a href="/uriplants/admin" class="btn btn-admin">Admin</a>
            <a href="/uriplants/plants" class="btn btn-plants">Lista de Plantas</a>
            <a href="/uriplants/quizz" class="btn btn-quizz">Quizz</a>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>
</body>

</html>
