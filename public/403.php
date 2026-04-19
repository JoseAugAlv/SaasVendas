<?php
/**
 * Página de Erro 403 - Acesso Negado
 */
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Negado - 403</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .error-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 2rem;
            background: var(--bg);
        }
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: #dc2626;
            margin: 0;
        }
        .error-message {
            font-size: 1.5rem;
            color: var(--text);
            margin: 1rem 0 2rem;
        }
        .btn-home {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .btn-home:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">403</h1>
        <p class="error-message">Acesso negado. Você não tem permissão para acessar esta página.</p>
        <a href="/" class="btn-home">Voltar ao Início</a>
    </div>
</body>
</html>
