<?php
/**
 * Middleware de Autenticação e Autorização
 * Controla acesso às rotas do sistema
 */

class AuthMiddleware {
    
    /**
     * Inicializa a sessão com configurações de segurança
     */
    public static function initSession(): void {
        $config = require __DIR__ . '/../../config/app.php';
        
        if (session_status() === PHP_SESSION_NONE) {
            session_name($config['session']['name']);
            ini_set('session.cookie_httponly', $config['session']['httponly'] ? '1' : '0');
            ini_set('session.cookie_secure', $config['session']['secure'] ? '1' : '0');
            ini_set('session.cookie_samesite', $config['session']['samesite']);
            ini_set('session.gc_maxlifetime', $config['session']['lifetime']);
            session_start();
        }
        
        // Regenera ID da sessão periodicamente para segurança
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } elseif (time() - $_SESSION['created'] > 900) { // 15 minutos
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
        
        // Timeout de sessão
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $config['session']['lifetime'])) {
            session_unset();
            session_destroy();
            session_start();
        }
        $_SESSION['last_activity'] = time();
    }

    /**
     * Verifica se o usuário está autenticado
     * @return bool
     */
    public static function check(): bool {
        self::initSession();
        return isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] > 0;
    }

    /**
     * Requer autenticação. Redireciona para login se não autenticado.
     */
    public static function requireAuth(): void {
        if (!self::check()) {
            header('Location: /auth/login.php');
            exit;
        }
    }

    /**
     * Requer que o usuário NÃO esteja autenticado (para login/registro)
     */
    public static function requireGuest(): void {
        self::initSession();
        if (isset($_SESSION['usuario_id'])) {
            header('Location: /dashboard.php');
            exit;
        }
    }

    /**
     * Obtém dados do usuário autenticado
     * @return array|null
     */
    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }
        
        $usuario = new Usuario();
        $user = $usuario->findWithSubscription($_SESSION['usuario_id']);
        
        if ($user) {
            $usuario->updateLastActivity($_SESSION['usuario_id']);
        }
        
        return $user;
    }

    /**
     * Obtém ID do usuário autenticado
     * @return int|null
     */
    public static function userId(): ?int {
        return $_SESSION['usuario_id'] ?? null;
    }

    /**
     * Verifica se o usuário é moderador
     * @return bool
     */
    public static function isModerator(): bool {
        return self::check() && ($_SESSION['tipo_usuario'] ?? '') === 'moderador';
    }

    /**
     * Requer papel de moderador
     */
    public static function requireModerator(): void {
        self::requireAuth();
        if (!self::isModerator()) {
            http_response_code(403);
            die('Acesso negado. Apenas moderadores podem acessar esta página.');
        }
    }

    /**
     * Verifica se o usuário tem assinatura ativa
     * @return bool
     */
    public static function hasActiveSubscription(): bool {
        if (!self::check()) {
            return false;
        }
        
        $usuario = new Usuario();
        return $usuario->hasActiveSubscription($_SESSION['usuario_id']);
    }

    /**
     * Requer assinatura ativa
     */
    public static function requireActiveSubscription(): void {
        self::requireAuth();
        if (!self::hasActiveSubscription()) {
            header('Location: /assinatura_pendente.php');
            exit;
        }
    }

    /**
     * Faz logout do usuário
     */
    public static function logout(): void {
        self::initSession();
        session_unset();
        session_destroy();
        
        // Limpa cookie da sessão
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    /**
     * Gera token CSRF
     * @return string
     */
    public static function generateCsrfToken(): string {
        self::initSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Valida token CSRF
     * @param string $token Token enviado
     * @return bool
     */
    public static function validateCsrfToken(string $token): bool {
        self::initSession();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Registra login do usuário
     * @param array $user Dados do usuário
     */
    public static function login(array $user): void {
        self::initSession();
        session_regenerate_id(true);
        
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['nome'] = $user['nome'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
        
        // Atualiza última atividade
        $usuario = new Usuario();
        $usuario->updateLastActivity($user['id']);
        
        // Log de auditoria
        self::logAudit($user['id'], 'login', 'usuarios', $user['id'], 'Login realizado com sucesso');
    }

    /**
     * Registra ação em log de auditoria
     * @param int $userId
     * @param string $action
     * @param string $objectType
     * @param int|null $objectId
     * @param string|null $details
     */
    private static function logAudit(int $userId, string $action, string $objectType, ?int $objectId = null, ?string $details = null): void {
        try {
            $sql = "INSERT INTO logs_auditoria (id_usuario, tipo_acao, objeto_afetado_tipo, objeto_afetado_id, detalhes_alteracao, ip_origem, user_agent)
                    VALUES (:id_usuario, :tipo_acao, :objeto_tipo, :objeto_id, :detalhes, :ip, :user_agent)";
            
            $db = Database::getInstance();
            $db->execute($sql, [
                'id_usuario' => $userId,
                'tipo_acao' => $action,
                'objeto_tipo' => $objectType,
                'objeto_id' => $objectId,
                'detalhes' => $details ? json_encode(['mensagem' => $details], JSON_UNESCAPED_UNICODE) : null,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Erro ao registrar log de auditoria: " . $e->getMessage());
        }
    }
}
