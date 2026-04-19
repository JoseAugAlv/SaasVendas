<?php
/**
 * Logout do Usuário
 */

require_once __DIR__ . '/../../src/Middleware/AuthMiddleware.php';

AuthMiddleware::logout();
header('Location: /auth/login.php');
exit;
