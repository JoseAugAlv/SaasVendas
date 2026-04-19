<?php
/**
 * Sistema de Gestão para Vendedores Autônomos
 * Ponto de entrada principal
 * 
 * Redireciona para:
 * - Dashboard se estiver logado
 * - Login se não estiver autenticado
 */

session_start();

// Configurações de sessão seguras
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

// Timeout de sessão (30 minutos)
if (isset($_SESSION['usuario_id']) && isset($_SESSION['ultima_atividade'])) {
    if (time() - $_SESSION['ultima_atividade'] > 1800) {
        session_unset();
        session_destroy();
        header('Location: auth/login.php?timeout=1');
        exit;
    }
}
$_SESSION['ultima_atividade'] = time();

// Verifica se usuário está logado
if (isset($_SESSION['usuario_id'])) {
    // Usuário logado - redireciona para dashboard
    header('Location: dashboard.php');
    exit;
} else {
    // Usuário não logado - redireciona para login
    header('Location: auth/login.php');
    exit;
}
