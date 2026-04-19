<?php
/**
 * Página de Erro 404 - Não Encontrado
 */
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Não Encontrada - 404</title>
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
            color: var(--primary);
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
        <h1 class="error-code">404</h1>
        <p class="error-message">Ops! Página não encontrada.</p>
        <a href="/" class="btn-home">Voltar ao Início</a>
    </div>
</body>
</html>
