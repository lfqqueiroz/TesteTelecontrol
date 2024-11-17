<?php
include '../controller/funcoes.php';

// Verifica as informações do formulário de criação e atualização dos clientes.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = $_POST['id'] ?? null;
    $cpf = $_POST['cpf'];
    $dados_cliente = [
        'nome' => $_POST['nome'],
        'cpf' => $cpf,
        'endereco' => $_POST['endereco']
    ];

    // Verificar se o CPF já existe no banco.
    $sql = "SELECT id FROM clientes WHERE cpf = :cpf";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['cpf' => $cpf]);
    $cliente_existente = $stmt->fetch();

    // Verifica se o CPF já existe no banco e se não é de um cliente sendo editado.
    if ($cliente_existente && (!$cliente_id || $cliente_existente['id'] != $cliente_id)) {
        echo "<script>
                alert('Erro: Este CPF informado já está cadastrado.');
                window.history.back(); // Retorna para a página anterior
              </script>";
        exit;
    }

    // Verifica se caso for edição de um cliente ja existente.
    if ($cliente_id) {
        update('clientes', $dados_cliente, ['id' => $cliente_id]);
    } else {
        // Verifica se caso for criação de um novo cliente para adicionar.
        create('clientes', $dados_cliente);
    }

    header('Location: clientes.php');
    exit;
}

// Exclui o cliente selecionado
if (isset($_GET['delete'])) {
    $cliente_id = $_GET['delete'];
    try {
        delete('clientes', ['id' => $cliente_id]);
        echo "<script>
                alert('Cliente excluído com sucesso.');
                window.location.href = 'clientes.php';
              </script>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), '1451') !== false) {
            echo "<script>
                    alert('Não é possível excluir este cliente, pois existem ordens de serviço abertas vinculadas a ele.');
                    window.location.href = 'clientes.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Erro ao excluir o cliente: " . addslashes($e->getMessage()) . "');
                    window.location.href = 'clientes.php';
                  </script>";
        }
    }
    exit;
}

// Lista todos os clientes existentes
$clientes = read('clientes');

// Carregar os clientes para fazer a edição
$cliente_editar = null;
if (isset($_GET['edit'])) {
    $cliente_id = $_GET['edit'];
    $cliente_editar = read('clientes', ['id' => $cliente_id])[0] ?? null;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD de Clientes</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<!-- Chama o header da pasta css -->
<div id="header-container"></div>
<body>
    <div class="container mt-5">
        
        <a href="../index.php" class="btn btn-outline-secondary btn-sm mb-4 float-right">
            <i class="bi bi-arrow-left-circle"></i> Voltar
        </a>

        <h1 class="text-center">Gerenciamento de Clientes</h1>
        <div id="message-container"></div>
        <!-- Formulário para cadastrar e editar os clientes -->
        <form method="POST" class="mb-4">
            <input type="hidden" name="id" value="<?= $cliente_editar['id'] ?? '' ?>">

            <div class="form-group">
                <label for="nome">Nome:</label>
                <input type="text" name="nome" id="nome" class="form-control" value="<?= $cliente_editar['nome'] ?? '' ?>" required>
            </div>

            <div class="form-group">
                <label for="cpf">CPF:</label>
                <input type="text" name="cpf" id="cpf" class="form-control" value="<?= $cliente_editar['cpf'] ?? '' ?>" required oninput="mascaraCPF(this)">
            </div>

            <div class="form-group">
                <label for="endereco">Endereço:</label>
                <input type="text" name="endereco" id="endereco" class="form-control" value="<?= $cliente_editar['endereco'] ?? '' ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">
                <?= $cliente_editar ? 'Atualizar Cliente' : 'Adicionar Cliente' ?>
            </button>
            <?php if ($cliente_editar): ?>
                <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
            <?php endif; ?>
        </form>

        <!-- Lista os clientes já cadastrados -->
        <h2 class="text-center">Lista de Clientes</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Endereço</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?= $cliente['id'] ?></td>
                        <td><?= $cliente['nome'] ?></td>
                        <td><?= $cliente['cpf'] ?></td>
                        <td><?= $cliente['endereco'] ?></td>
                        <td>
                            <a href="clientes.php?edit=<?= $cliente['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="clientes.php?delete=<?= $cliente['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este cliente?')">Excluir</a>
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
