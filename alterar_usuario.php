<?php

session_start();
require_once 'conexao.php';

// VERIFICA SE O USUARIO TEM PERMISSAO DE ADM
if ($_SESSION['perfil'] != 1){
    echo"<script>alert('Acesso Negado');window.location.href='principal.php';</script>";
    exit;
}

// INICIALIZA AS VARIAVEIS
$usuario = null;
$busca = null; 

if ($_SERVER["REQUEST_METHOD"]=="POST" ){
    if (!empty($_POST['busca_usuario'])) 
        $busca = trim($_POST['busca_usuario']);

    // VERIFICA SE A BUSCA É UM NUMERO (ID) OU UM NOME
    if($busca !== null && is_numeric($busca)){ 
       $sql =  "SELECT * FROM usuario WHERE id_usuario = :busca";
       $stmt =$pdo->prepare($sql);
       $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    } elseif($busca !== null) { 
       $sql = "SELECT * FROM usuario WHERE nome LIKE :busca_nome";
       $stmt =$pdo->prepare($sql);
       $stmt->bindValue(':busca_nome', "$busca%", PDO::PARAM_STR);
    }
    if (isset($stmt)) {
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // SE O USUARIO NÃO FOR ENCONTRADO, EXIBE UM ALERTA 
        if (!$usuario) {
            echo"<script>alert('Usuário não encontrado');</script>";
        }
    }
}

$id_perfil = $_SESSION['perfil'];
$sqlPerfil = "SELECT nome_perfil FROM perfil WHERE id_perfil = :id_perfil";
$stmtPerfil = $pdo->prepare($sqlPerfil);
$stmtPerfil->bindParam(':id_perfil', $id_perfil);
$stmtPerfil->execute();
$perfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC);
$nome_perfil = $perfil['nome_perfil'];

$permissoes = [
    
    1=>
[
    "Cadastrar"=>["cadastro_usuario.php","cadastro_perfil.php","cadastro_cliente.php","cadastro_fornecedor.php","cadastro_produto.php","cadastro_funcionario.php"],
    "Buscar"=>["buscar_usuario.php","buscar_perfil.php","buscar_cliente.php","buscar_fornecedor.php","buscar_produto.php","buscar_funcionario.php"],
    "Alterar"=>["alterar_usuario.php","alterar_perfil.php","alterar_cliente.php","alterar_fornecedor.php","alterar_produto.php","alterar_funcionario.php"],
    "Excluir"=>["excluir_usuario.php","excluir_perfil.php","excluir_cliente.php","excluir_fornecedor.php","excluir_produto.php","excluir_funcionario.php"]],

    2=>
[
    "Cadastrar"=>["cadastro_cliente.php"],
    "Buscar"=>["buscar_cliente.php","buscar_fornecedor.php","buscar_produto.php"],
    "Alterar"=>["alterar_cliente.php","alterar_fornecedor.php"]],

    3=>
[
    "Cadastrar"=>["cadastro_fornecedor.php","cadastro_produto.php"],
    "Buscar"=>["buscar_cliente.php","buscar_fornecedor.php","buscar_produto.php"],
    "Alterar"=>["alterar_fornecedor.php","alterar_produto.php"]],
    "Excluir"=>["excluir_produto.php"],

    4=>
[
    "Cadastrar"=>["cadastro_cliente.php"],
    "Buscar"=>["buscar_produto.php"],
    "Alterar"=>["alterar_cliente.php"]],

];

$opcoes_menu = $permissoes[$id_perfil];

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar usuário</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <!-- CERTIFIQUE-SE DE QUE O JAVASCRIPT ESTÁ SENDO CARREGADO CORRETAMENTE  -->
     <script src="scripts.js"></script>
</head>
    <body>

        <nav>
            <ul class="menu">
                <?php foreach($opcoes_menu as $categoria=>$arquivos): ?>
                <li class="dropdown">
                    <a href="#"><?= $categoria ?></a>
                    <ul class="dropdown-menu">
                        <?php foreach($arquivos as $arquivo): ?>
                        <li>
                            <a href="<?= $arquivo ?>"><?= ucfirst(str_replace("_"," ",basename($arquivo,".php")))?></a>
                        </li>
                            <?php endforeach; ?>
                    </ul>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <center><h2>Alterar de Usuários</h2></center>

    <!-- FORMULARIO PARA ALTERAR USUARIOS -->

    <form action="alterar_usuario.php" method="POST">
        <label for="busca_usuario">Digite o ID ou NOME do usuário:</label>
        <input type="text" id="busca_usuario" name="busca_usuario" required onkeyup="buscarSugestoes()">
        <div id="sugestoes"></div>
        <button type="submit" class="btn btn-primary" >Buscar</button>
    </form>

    <?php if ($usuario): ?>
        <form action="processa_alteracao_usuario.php" method="POST">
            <input type="hidden" name="id_usuario" value="<?=htmlspecialchars($usuario['id_usuario'])?>">

            <label for="nome">Nome:</label>
            <input type="text" name="nome" id="nome" value="<?=htmlspecialchars($usuario['nome'])?>" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?=htmlspecialchars($usuario['email'])?>" required>

            <label for="id_perfil">Perfil:</label>
            <select id="id_perfil" name="id_perfil">
                <option value="1"<?=$usuario['id_perfil'] == 1? 'selected': ''?>>Administrador</option>
                <option value="2"<?=$usuario['id_perfil'] == 2? 'selected': ''?>>Secretaria</option>
                <option value="3"<?=$usuario['id_perfil'] == 3? 'selected': ''?>>Almoxarife</option>
                <option value="4"<?=$usuario['id_perfil'] == 4? 'selected': ''?>>Cliente</option>
            </select>

            <!-- SE O USUÁRIO LOGADO FOR ADMINISTRADOR, EXIBIR OPÇÃO DE ALTERAR SENHA -->

            <?php if ($_SESSION['perfil'] == 1): ?>
                <label for="nova_senha">Nova senha:</label>
                <input type="password" id="nova_senha" name="nova_senha">
            <?php endif; ?>
            <button type="submit" class="btn btn-primary" >Alterar</button>
            <br>
            <button type="reset" class="btn btn-primary" >Cancelar</button>       
        </form>
            <?php endif; ?>
            <center><a href="principal.php" class="btn btn-primary">Voltar</a></center>
    </body>
</html>

