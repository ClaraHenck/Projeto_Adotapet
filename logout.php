<?php
session_start();
// Limpa todas as variáveis de sessão
$_SESSION = array();

// Destrói a sessão do servidor
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <script>
        // Limpa o localStorage ao deslogar
        localStorage.removeItem("tipoUsuario");
        localStorage.removeItem("usuarioLogadoEmail");
        localStorage.removeItem("usuarioLogadoNome");
        // Redireciona de volta para a página inicial atualizada
        window.location.href = "index.php";
    </script>
</head>
<body>
</body>
</html>