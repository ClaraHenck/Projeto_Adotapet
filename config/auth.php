<?php
// config/auth.php
session_start();

// Verifica se o usuário não está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Projeto_Adotapet/login/login.php"); // Redireciona para o login
    exit;
}

/**
 * Função para verificar permissão específica (ex: apenas ONGs podem cadastrar)
 * Você pode usar isso nas suas páginas assim: 
 * if (!verificarPermissao('pode_cadastrar')) { die("Acesso negado"); }
 */
function verificarPermissao($permissao) {
    return isset($_SESSION[$permissao]) && $_SESSION[$permissao] === true;
}
?>