<?php
/**
 * Página de Login
 * Sistema de Gestão para Vendedores Autônomos
 */

require_once __DIR__ . '/../../src/Models/Database.php';
require_once __DIR__ . '/../../src/Models/Model.php';
require_once __DIR__ . '/../../src/Models/Usuario.php';
require_once __DIR__ . '/../../src/Middleware/AuthMiddleware.php';

// Requer que usuário NÃO esteja autenticado
AuthMiddleware::requireGuest();

$errors = [];
$success = '';
$email = '';

// Processa login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '';
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Valida token CSRF
    if (!AuthMiddleware::validateCsrfToken($csrf_token)) {
        $errors[] = 'Token de segurança inválido. Tente novamente.';
    }
    
    // Valida campos
    if (empty($email) || empty($password)) {
        $errors[] = 'Preencha e-mail e senha.';
    }
    
    if (empty($errors)) {
        try {
            $usuario = new Usuario();
            $user = $usuario->findByEmail($email);
            
            if ($user && $user['ativo']) {
                // Verifica se é login por e-mail/senha
                if ($user['auth_provider'] === 'email' && !empty($user['senha_hash'])) {
                    if ($usuario->verifyPassword($password, $user['senha_hash'])) {
                        AuthMiddleware::login($user);
                        
                        // Redireciona baseado no tipo de usuário
                        if ($user['tipo_usuario'] === 'moderador') {
                            header('Location: /admin/dashboard.php');
                        } else {
                            // Verifica assinatura
                            if ($usuario->hasActiveSubscription($user['id'])) {
                                header('Location: /dashboard.php');
                            } else {
                                header('Location: /assinatura_pendente.php');
                            }
                        }
                        exit;
                    }
                }
                
                $errors[] = 'E-mail ou senha incorretos.';
            } else {
                $errors[] = 'E-mail ou senha incorretos.';
            }
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            $errors[] = 'Erro ao realizar login. Tente novamente.';
        }
    }
}

$csrf_token = AuthMiddleware::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gestão de Vendedores</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="flex items-center justify-center" style="min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card fade-in" style="width: 100%; max-width: 420px; margin: 20px;">
        <div class="text-center mb-6">
            <h1 style="font-size: var(--font-size-2xl); color: var(--primary); margin-bottom: var(--space-2);">
                Gestão de Vendedores
            </h1>
            <p class="text-muted">Faça login para acessar sua conta</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: var(--space-4);">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <div class="form-group">
                <label for="email" class="form-label">E-mail</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    placeholder="seu@email.com"
                    value="<?= htmlspecialchars($email) ?>"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Senha</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="Sua senha"
                    required
                >
            </div>

            <div class="flex items-center justify-between mb-4">
                <label style="display: flex; align-items: center; gap: var(--space-2); font-size: var(--font-size-sm);">
                    <input type="checkbox" name="remember" style="width: auto;">
                    Lembrar-me
                </label>
                <a href="/auth/recuperar_senha.php" style="font-size: var(--font-size-sm);">Esqueceu a senha?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-block mb-4">
                Entrar
            </button>
        </form>

        <div style="text-align: center; margin-top: var(--space-4); padding-top: var(--space-4); border-top: 1px solid var(--border);">
            <p class="text-muted mb-4" style="font-size: var(--font-size-sm);">Ou entre com</p>
            
            <a href="/auth/google/login.php" class="btn btn-secondary btn-block" style="gap: var(--space-3);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Continuar com Google
            </a>
        </div>

        <div class="text-center mt-6">
            <p class="text-muted" style="font-size: var(--font-size-sm);">
                Não tem uma conta?
                <a href="/auth/registro.php">Cadastre-se gratuitamente</a>
            </p>
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
