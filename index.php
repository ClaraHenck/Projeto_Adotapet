<?php
// Inicia a sessão para ler os dados do usuário logado
session_start();

// Verifica o estado de login através das sessões criadas no login.php
$logado = isset($_SESSION["usuario_id"]);
$usuario_nome = $_SESSION["usuario_nome"] ?? "";
$pode_cadastrar = $_SESSION["pode_cadastrar"] ?? 0;

// Define o tipo de usuário baseado na permissão (1 para ONG, 0 para Adotante comum)
$tipoUsuario = "";
if ($logado) {
    $tipoUsuario = ($pode_cadastrar == 1) ? "ong" : "adotante";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AdotaPet - Plataforma de Adoção</title>
    <link rel="stylesheet" href="index.css" />
  </head>
  <body>
    <header class="navbar">
      <div class="left-brand-box">
        <div class="logo">🐾 AdotaPet</div>
      </div>

      <nav class="menu" id="menu-navegacao">
        <a href="index.php" class="active">Início</a>
        
        <?php if (!$logado || $tipoUsuario === "adotante"): ?>
          <a href="adotar.php" id="link-adotar">Adotar</a>
        <?php endif; ?>

        <?php if ($logado): ?>
          <a href="mapa.html" id="link-mapa">Mapa</a> 
          <a href="candidaturas.php" id="link-candidaturas">
            <?php echo ($tipoUsuario === "ong") ? "Candidaturas Recebidas" : "Candidaturas"; ?>
          </a>
        <?php endif; ?>

        <div id="area-usuario-nav" style="display: flex; align-items: center; gap: 24px;">
            <?php if (!$logado): ?>
                <a href="../projeto_adotapet/login/login.php" class="btn-nav-login">Entrar</a>
                <a href="../projeto_adotapet/login/cadastrar.php" class="btn-nav-cadastro">Cadastrar-se</a>
            <?php else: ?>
                <?php 
                    $linkHref = ($tipoUsuario === "ong") ? "minha_ong.html" : "meu_perfil.html";
                    $textoPerfil = ($tipoUsuario === "ong") ? "MINHA ONG" : "MEU PERFIL";
                    // Define uma foto padrão caso não tenha no banco
                    $fotoPerfil = "img/avatar-default.png"; 
                ?>
                <a href="<?php echo $linkHref; ?>" class="perfil-link-container">
                    <span style="font-weight: bold; color: #1e293b;"><?php echo $textoPerfil; ?></span>
                </a>
                <a href="logout.php" style="color: #ff4d4d; font-weight: 500; text-decoration: none;">Sair</a>
            <?php endif; ?>
        </div>
      </nav>
    </header>

    <main class="hero-container">
      <section class="hero-text">
        <span class="badge">✨ Plataforma de Adoção Responsável</span>
        <h1>
          Encontre seu <br /><span class="highlight">melhor amigo</span>
          <br />para a vida toda
        </h1>
        
        <p id="hero-descricao">
          <?php if ($tipoUsuario === "ong"): ?>
            Gerencie seus animais cadastrados, avalie candidaturas de adoção e encontre tutores responsáveis.
          <?php else: ?>
            A AdotaPet conecta você ao animal perfeito usando inteligência artificial. Um lar amoroso está a poucos clicks de distância.
          <?php endif; ?>
        </p>

        <div class="buttons" id="botoes-container" style="display: flex; flex-direction: column; gap: 12px; align-items: flex-start;">
          
          <div style="display: flex; gap: 10px;">
            <?php if ($tipoUsuario !== "ong"): ?>
              <a href="adotar.php" id="btn-encontrar" class="btn-primary" style="text-decoration: none; display: inline-block; text-align: center; border-radius: 10px;">
                🔍 Encontrar um Animal
              </a>
            <?php else: ?>
              <a href="cadastrarAnimal.php" class="btn-primary" style="text-decoration: none; display: inline-block; text-align: center; background-color: #2196F3; border-radius: 10px;">
                ➕ Cadastrar Novo Animal
              </a>
              <a href="meus_animais.php" class="btn-secondary" style="text-decoration: none; display: inline-block; text-align: center; border-radius: 10px;">
                Gerenciar Animais →
              </a>
            <?php endif; ?>
          </div>

          <?php if ($tipoUsuario === "adotante"): ?>
            <a href="questionario.php" id="btn-questionario" class="btn-secondary" style="text-decoration: none; text-align: center; background-color: #ff6070ce; color: white; border: none; padding: 10px 20px; border-radius: 10px; width: fit-content; font-weight: bold;">
              📝 Responder Questionário de Adoção
            </a>
          <?php endif; ?>

        </div>
      </section>

      <section class="hero-image-box">
        <img src="img/f2792368c_generated_926ddb32.png" alt="Cachorro Feliz" class="main-img" />
      </section>
    </main>

    <script>
      // Mantém a sincronização com o localStorage para que as outras páginas legadas (.html) saibam quem está logado
      const tipoUsuarioSessao = "<?php echo $tipoUsuario; ?>";
      const usuarioNomeSessao = "<?php echo $usuario_nome; ?>";

      if (tipoUsuarioSessao) {
        localStorage.setItem("tipoUsuario", tipoUsuarioSessao);
        localStorage.setItem("usuarioLogadoNome", usuarioNomeSessao);
      } else {
        localStorage.removeItem("tipoUsuario");
        localStorage.removeItem("usuarioLogadoEmail");
        localStorage.removeItem("usuarioLogadoNome");
      }

      // Controle de exibição do questionário baseado no preenchimento local (opcional)
      const btnQuest = document.getElementById("btn-questionario");
      if (btnQuest) {
        const jaRespondeu = localStorage.getItem("questionario_respondido_sinc");
        if (jaRespondeu === "sim") {
          btnQuest.style.display = "none";
        }
      }
    </script>
  </body>
</html>