<?php
include '../controller/funcoes.php';

// Verifica as informações do formulário de criação e atualização das ordens de serviço
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ordem_id = $_POST['id'] ?? null;
    $numero_ordem = $_POST['numero_ordem'];
    $data_abertura = $_POST['data_abertura'];
    $nome_consumidor = $_POST['nome_consumidor'];
    $cpf_consumidor = $_POST['cpf_consumidor'];
    $produto_id = $_POST['produto'];

    // Verifica se o cliente já existe no banco de dados
    $cliente = read('clientes', ['cpf' => $cpf_consumidor]);
    if (!$cliente) {
        // Faz o cadastro automaticamente do cliente que não está cadastrado
        $cliente_id = create('clientes', [
            'nome' => $nome_consumidor,
            'cpf' => $cpf_consumidor,
            'endereco' => ''
        ]);
    } else {
        $cliente_id = $cliente[0]['id'];
    }

    // Verificar se o numero da ordem de serviço já existe no banco.
    $sql = "SELECT id FROM ordens WHERE numero_ordem = :numero_ordem";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['numero_ordem' => $numero_ordem]);
    $ordem_existente = $stmt->fetch();

    if ($ordem_existente && (!$ordem_id || $ordem_existente['id'] != $ordem_id)) {
        echo "<script>
                alert('Erro: Este número da ordem de serviço já está cadastrado.');
                window.history.back(); // Retorna à página anterior
              </script>";
        exit;
    }

    $dados_ordem = [
        'numero_ordem' => $numero_ordem,
        'data_abertura' => $data_abertura,
        'cliente_id' => $cliente_id,
        'produto_id' => $produto_id
    ];

    if ($ordem_id) {
        //  Verifica se caso for edição de uma ordem ded serviço ja existente.
        update('ordens', $dados_ordem, ['id' => $ordem_id]);
    } else {
        // Verifica se caso for criação de um nova ordem de serviço para adicionar.
        create('ordens', $dados_ordem);
    }

    header('Location: ordens.php');
    exit;
}

// Exclui a ordem de serviço selecionada
if (isset($_GET['delete'])) {
    $ordem_id = $_GET['delete'];
    delete('ordens', ['id' => $ordem_id]);
    header('Location: ordens.php');
    exit;
}

// Lista todos as ordens de serviço com seu devido produtos e localiza o nome do cliente existentes;
$ordens = read('ordens');
$produtos = read('produtos');
$ordens = readOrdensServico();

// Carregar as ordens de serviço para fazer a edição
$ordem_editar = null;
if (isset($_GET['edit'])) {
    $ordem_id = $_GET['edit'];
    $ordem_editar = read('ordens', ['id' => $ordem_id])[0] ?? null;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD de Ordens de Serviço</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<!-- Chama o header da pasta css -->
<div id="header-container"></div>
<body>
    <div class="container mt-5">
        <a href="../index.php" class="btn btn-outline-secondary btn-sm mb-4 float-right">
            <i class="bi bi-arrow-left-circle"></i> Voltar
        </a>
        <h1 class="text-center">Gerenciamento de Ordens de Serviço</h1>
        
        
        <!-- Formulário para cadastrar e editar as ordens de serviço -->
        <form method="POST" class="mb-4">
            <input type="hidden" name="id" value="<?= $ordem_editar['id'] ?? '' ?>">

            <div class="form-group">
                <label for="numero_ordem">Número da Ordem:</label>
                <input type="text" name="numero_ordem" id="numero_ordem" class="form-control" value="<?= $ordem_editar['numero_ordem'] ?? '' ?>" required>
            </div>

            <div class="form-group">
                <label for="data_abertura">Data de Abertura:</label>
                <input type="date" name="data_abertura" id="data_abertura" class="form-control" value="<?= $ordem_editar['data_abertura'] ?? date('Y-m-d') ?>" required>
            </div>

            <div class="form-group">
                <label for="nome_consumidor">Nome do Consumidor:</label>
                <input type="text" name="nome_consumidor" id="nome_consumidor" class="form-control" value="<?= $ordem_editar['cliente_nome'] ?? '' ?>" required>
            </div>

            <div class="form-group">
                <label for="cpf_consumidor">CPF do Consumidor:</label>
                <input type="text" name="cpf_consumidor" id="cpf_consumidor" class="form-control" value="<?= $ordem_editar['cliente_cpf'] ?? '' ?>" required oninput="mascaraCPF(this)">
            </div>

            <div class="form-group">
                <label for="produto">Produto:</label>
                <select name="produto" id="produto" class="form-control" required>
                    <option value="">Selecione um Produto</option>
                    <?php foreach ($produtos as $produto): ?>
                        <option value="<?= $produto['id'] ?>" <?= isset($ordem_editar['produto_id']) && $ordem_editar['produto_id'] == $produto['id'] ? 'selected' : '' ?>>
                            <?= $produto['descricao'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <?= $ordem_editar ? 'Atualizar Ordem' : 'Adicionar Ordem' ?>
            </button>
            <?php if ($ordem_editar): ?>
                <a href="ordens.php" class="btn btn-secondary">Cancelar</a>
            <?php endif; ?>
        </form>

        <!-- Lista as ordens de serviço ja cadastradas -->
        <h2 class="text-center">Lista de Ordens de Serviço</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Número Ordem</th>
                    <th>Data Abertura</th>
                    <th>Cliente</th>
                    <th>Produto</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ordens as $ordem): ?>
                    <tr>
                        <td><?= $ordem['id'] ?></td>
                        <td><?= $ordem['numero_ordem'] ?></td>
                        <td><?= $ordem['data_abertura'] ?></td>
                        <td><?= $ordem['cliente_nome'] ?? 'N/A' ?></td>
                        <td><?= $ordem['produto_nome'] ?? 'N/A' ?></td>
                        <td>
                            <a href="ordens.php?edit=<?= $ordem['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="ordens.php?delete=<?= $ordem['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta ordem?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
<!-- Chama o header da pasta css -->
<div id="footer-container"></div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Função criada para carregar o header e footer que foram feitas separados -->
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
    loadHTML("#header-container", "../components/header.html");
    loadHTML("#footer-container", "../components/footer.html");
</script>
<!-- funciona como uma mascara de CPF para que possua apenas 11 numeros no campo e eles sejam separados no padrão CPF -->
<script>
    function mascaraCPF(campo) {
        var cpf = campo.value.replace(/\D/g, '');

        if (cpf.length > 11) {
            cpf = cpf.substring(0, 11);
        }

        if (cpf.length <= 11) {
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        }

        campo.value = cpf;
    }
</script>
</html>
