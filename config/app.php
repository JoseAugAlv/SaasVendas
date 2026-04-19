<?php
/**
 * Configurações Gerais do Sistema
 */

return [
    'app_name' => 'Gestão de Vendedores',
    'app_url' => getenv('APP_URL') ?: 'http://localhost',
    'environment' => getenv('APP_ENV') ?: 'development',
    
    // Sessão
    'session' => [
        'name' => 'GESTAO_VENDEDOR_SESSION',
        'lifetime' => 1800, // 30 minutos
        'secure' => false, // true em produção com HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ],
    
    // Segurança
    'security' => [
        'csrf_token_name' => 'csrf_token',
        'password_algorithm' => PASSWORD_ARGON2ID,
        'password_options' => [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 1
        ]
    ],
    
    // Upload
    'upload' => [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp'],
        'path' => __DIR__ . '/../public/uploads/'
    ],
    
    // Paginação
    'pagination' => [
        'per_page' => 20
    ],
    
    // Google OAuth
    'google_oauth' => [
        'client_id' => getenv('GOOGLE_CLIENT_ID') ?: '',
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
        'redirect_uri' => getenv('APP_URL') ?: 'http://localhost' . '/auth/google/callback.php',
        'scopes' => ['email', 'profile']
    ]
];
