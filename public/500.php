<?php
/**
 * Página de Erro 500 - Erro Interno do Servidor
 */
http_response_code(500);

// Em produção, não mostrar detalhes do erro
$mostrar_detalhes = false; // Mudar para true apenas em desenvolvimento
$erro_details = error_get_last();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro Interno - 500</title>
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
        .error-details {
            margin-top: 2rem;
            padding: 1rem;
            background: #fee2e2;
            border-radius: 6px;
            max-width: 600px;
            text-align: left;
            font-family: monospace;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code">500</h1>
        <p class="error-message">Ops! Algo deu errado no servidor.</p>
        <a href="/" class="btn-home">Voltar ao Início</a>
        
        <?php if ($mostrar_detalhes && $erro_details): ?>
        <div class="error-details">
            <strong>Erro:</strong> <?php echo htmlspecialchars($erro_details['message']); ?><br>
            <strong>Arquivo:</strong> <?php echo htmlspecialchars($erro_details['file']); ?><br>
            <strong>Linha:</strong> <?php echo htmlspecialchars($erro_details['line']); ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
