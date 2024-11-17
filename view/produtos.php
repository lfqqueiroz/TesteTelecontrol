<?php
include '../controller/funcoes.php';

// Verifica as informações do formulário de criação e atualização dos produtos.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto_id = $_POST['id'] ?? null;
    $codigo = $_POST['codigo'];
    $dados_produto = [
        'codigo' => $codigo,
        'descricao' => $_POST['descricao'],
        'status' => $_POST['status'],
        'garantia' => $_POST['garantia']
    ];

    // Verificar se o código do produto já existe no banco.
    $sql = "SELECT id FROM produtos WHERE codigo = :codigo";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['codigo' => $codigo]);
    $produto_existente = $stmt->fetch();

    // Se o código do produto já existe no banco e não é do produto que está sendo editado.
    if ($produto_existente && (!$produto_id || $produto_existente['id'] != $produto_id)) {
        echo "<script>
                alert('Erro: Este código do produto já está cadastrado.');
                window.history.back(); // Retorna para a página anterior
              </script>";
        exit;
    }

    //  Verifica se caso for edição de um produto ja existente.
    if ($produto_id) {
        update('produtos', $dados_produto, ['id' => $produto_id]);
    } else {
        // Verifica se caso for criação de um novo produto para adicionar.
        create('produtos', $dados_produto);
    }

    header('Location: produtos.php');
    exit;
}

// Exclui o produto selecionado
if (isset($_GET['delete'])) {
    $produto_id = $_GET['delete'];
    try {
        delete('produtos', ['id' => $produto_id]);
        echo "<script>
                alert('Produto excluído com sucesso.');
                window.location.href = 'produtos.php';
              </script>";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), '1451') !== false) {
            echo "<script>
                    alert('Não é possível excluir este produto, pois existem ordens de serviço abertas vinculadas a ele.');
                    window.location.href = 'produtos.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Erro ao excluir o produto: " . addslashes($e->getMessage()) . "');
                    window.location.href = 'produtos.php';
                  </script>";
        }
    }
    exit;
}

// Lista todos os produtos existentes
$produtos = read('produtos');

// Carregar os produtos para fazer a edição
$produto_editar = null;
if (isset($_GET['edit'])) {
    $produto_id = $_GET['edit'];
    $produto_editar = read('produtos', ['id' => $produto_id])[0] ?? null;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD de Produtos</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<!-- Chama o header da pasta css -->
<div id="header-container"></div>
<body>
    <div class="container mt-5">
        <a href="../index.php" class="btn btn-outline-secondary btn-sm mb-4 float-right">
            <i class="bi bi-arrow-left-circle"></i> Voltar
        </a>

        <h1 class="text-center">Gerenciamento de Produtos</h1>

        <!-- Formulário para cadastrar e editar os produtos -->
        <form method="POST" class="mb-4">
            <input type="hidden" name="id" value="<?= $produto_editar['id'] ?? '' ?>">

            <div class="form-group">
                <label for="codigo">Código:</label>
                <input type="text" name="codigo" id="codigo" class="form-control" value="<?= $produto_editar['codigo'] ?? '' ?>" required>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição:</label>
                <input type="text" name="descricao" id="descricao" class="form-control" value="<?= $produto_editar['descricao'] ?? '' ?>" required>
            </div>

            <div class="form-group">
                <label for="status">Status:</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="1" <?= isset($produto_editar['status']) && $produto_editar['status'] == 1 ? 'selected' : '' ?>>Ativo</option>
                    <option value="0" <?= isset($produto_editar['status']) && $produto_editar['status'] == 0 ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>

            <div class="form-group">
                <label for="garantia">Tempo de Garantia (em meses):</label>
                <input type="number" name="garantia" id="garantia" class="form-control" value="<?= $produto_editar['garantia'] ?? '' ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">
                <?= $produto_editar ? 'Atualizar Produto' : 'Adicionar Produto' ?>
            </button>
            <?php if ($produto_editar): ?>
                <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
            <?php endif; ?>
        </form>

        <!-- Lista os produtos ja cadastrados -->
        <h2 class="text-center">Lista de Produtos</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th>Status</th>
                    <th>Garantia (meses)</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?= $produto['id'] ?></td>
                        <td><?= $produto['codigo'] ?></td>
                        <td><?= $produto['descricao'] ?></td>
                        <td><?= $produto['status'] ? 'Ativo' : 'Inativo' ?></td>
                        <td><?= $produto['garantia'] ?></td>
                        <td>
                            <a href="produtos.php?edit=<?= $produto['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="produtos.php?delete=<?= $produto['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este produto?')">Excluir</a>
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
</html>
