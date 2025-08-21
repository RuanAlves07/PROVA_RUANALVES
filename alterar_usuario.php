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
       $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
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

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar usuário</title>
    <link rel="stylesheet" href="styles.css">
    <!-- CERTIFIQUE-SE DE QUE O JAVASCRIPT ESTÁ SENDO CARREGADO CORRETAMENTE  -->
     <script src="scripts.js"></script>
</head>
    <body>
        <h2>Alterar de Usuários</h2>

    <!-- FORMULARIO PARA ALTERAR USUARIOS -->

    <form action="alterar_usuario.php" method="POST">
        <label for="busca_usuario">Digite o ID ou NOME do usuário:</label>
        <input type="text" id="busca_usuario" name="busca_usuario" required onkeyup="buscarSugestoes()">
        <div id="sugestoes"></div>
        <button type="submit">Buscar</button>
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
            <button type="submit">Alterar</button>
            <button type="reset">Cancelar</button>       
        </form>
            <?php endif; ?>
            <a href="principal.php">Voltar</a>
    </body>
</html>

<!--
Warning: Undefined variable $busca in C:\xampp\htdocs\PROVA_RUANALVES\alterar_usuario.php on line 24

Warning: Undefined variable $busca in C:\xampp\htdocs\PROVA_RUANALVES\alterar_usuario.php on line 31-->