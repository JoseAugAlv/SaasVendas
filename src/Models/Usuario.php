<?php
/**
 * Modelo de Usuário
 * Gerencia usuários do sistema (vendedores, moderadores, auxiliares)
 */

class Usuario extends Model {
    protected string $table = 'usuarios';
    protected string $primaryKey = 'id';
    protected array $fillable = [
        'nome',
        'email',
        'senha_hash',
        'auth_provider',
        'google_id',
        'avatar_url',
        'tipo_usuario',
        'ativo'
    ];
    protected array $rules = [
        'nome' => 'required|min:3|max:100',
        'email' => 'required|email|max:150',
        'tipo_usuario' => 'required|in:vendedor,moderador,auxiliar'
    ];

    /**
     * Busca usuário por e-mail
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array {
        return $this->findBy('email', $email);
    }

    /**
     * Busca usuário por Google ID
     * @param string $googleId
     * @return array|null
     */
    public function findByGoogleId(string $googleId): ?array {
        return $this->findBy('google_id', $googleId);
    }

    /**
     * Cria um novo usuário com senha hash
     * @param array $data Dados do usuário
     * @return string ID do usuário criado
     */
    public function create(array $data): string {
        // Hash da senha se fornecida
        if (!empty($data['senha_hash']) && empty($data['google_id'])) {
            $config = require __DIR__ . '/../../config/app.php';
            $data['senha_hash'] = password_hash(
                $data['senha_hash'],
                $config['security']['password_algorithm'],
                $config['security']['password_options']
            );
            $data['auth_provider'] = 'email';
        }
        
        return parent::create($data);
    }

    /**
     * Verifica se a senha está correta
     * @param string $password Senha em texto puro
     * @param string $hashedPassword Senha hash armazenada
     * @return bool
     */
    public function verifyPassword(string $password, string $hashedPassword): bool {
        return password_verify($password, $hashedPassword);
    }

    /**
     * Atualiza a última atividade do usuário
     * @param int $userId
     * @return void
     */
    public function updateLastActivity(int $userId): void {
        $sql = "UPDATE usuarios SET data_ultima_atividade = NOW() WHERE id = :id";
        $this->db->execute($sql, ['id' => $userId]);
    }

    /**
     * Busca usuário com dados de assinatura
     * @param int $userId
     * @return array|null
     */
    public function findWithSubscription(int $userId): ?array {
        $sql = "SELECT u.*, a.status as assinatura_status, a.plano, a.data_fim
                FROM usuarios u
                LEFT JOIN assinaturas a ON u.id = a.usuario_id
                WHERE u.id = :id
                LIMIT 1";
        return $this->db->fetchOne($sql, ['id' => $userId]);
    }

    /**
     * Verifica se o usuário tem assinatura ativa
     * @param int $userId
     * @return bool
     */
    public function hasActiveSubscription(int $userId): bool {
        $sql = "SELECT status FROM assinaturas WHERE usuario_id = :id LIMIT 1";
        $result = $this->db->fetchOne($sql, ['id' => $userId]);
        return ($result['status'] ?? '') === 'ativo';
    }

    /**
     * Lista vendedores para o painel administrativo
     * @param int $page Página atual
     * @param int $perPage Registros por página
     * @return array
     */
    public function listVendedores(int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT u.*, a.status as assinatura_status, a.plano, a.data_ativacao,
                       a.ativado_por_moderador
                FROM usuarios u
                LEFT JOIN assinaturas a ON u.id = a.usuario_id
                WHERE u.tipo_usuario = 'vendedor'
                ORDER BY u.data_cadastro DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Conta total de vendedores
     * @return int
     */
    public function countVendedores(): int {
        return $this->count(['tipo_usuario' => 'vendedor']);
    }
}
