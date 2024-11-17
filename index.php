<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Ordem de Serviço</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<!-- Chama o header da pasta css -->
<div id="header-container"></div>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Sistema de Gerenciamento de Ordem de Serviço</h1>
        <p class="text-center">Escolha uma das opções abaixo para gerenciar os dados:</p>

        <div class="row">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Clientes</h5>
                        <p class="card-text">Gerencie o cadastro de clientes.</p>
                        <a href="view/clientes.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Produtos</h5>
                        <p class="card-text">Gerencie o cadastro de produtos.</p>
                        <a href="view/produtos.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Ordens de Serviço</h5>
                        <p class="card-text">Gerencie as ordens de serviço.</p>
                        <a href="view/ordens.php" class="btn btn-primary">Acessar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>  
</body>
<!-- Chama o header dad pasta css -->
<div id="footer-container"></div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Função criada para carregar o header e footer que foram feitas separas -->
<script>
    function loadHTML(selector, file) {
        fetch(file)
            .then(response => response.text())
            .then(data => {
                document.querySelector(selector).innerHTML = data;
            })
            .catch(error => console.error('Erro ao carregar o arquivo:', error));
    }
    // Usado para carregar o header e o footer
    loadHTML("#header-container", "components/header.html");
    loadHTML("#footer-container", "components/footer.html");
</script>

</html>
