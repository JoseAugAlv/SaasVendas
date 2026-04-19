<?php
/**
 * Classe de Conexão com Banco de Dados (Singleton)
 * Implementa padrões de segurança contra SQL Injection
 */

class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private array $config;

    private function __construct() {
        $this->config = require __DIR__ . '/../config/database.php';
        $this->connect();
    }

    private function __clone() {}
    
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect(): void {
        $dsn = sprintf(
            "mysql:host=%s;port=%s;dbname=%s;charset=%s",
            $this->config['host'],
            $this->config['port'],
            $this->config['database'],
            $this->config['charset']
        );

        try {
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );
        } catch (PDOException $e) {
            error_log("Erro de conexão com banco de dados: " . $e->getMessage());
            throw new Exception("Erro de conexão com banco de dados. Tente novamente mais tarde.");
        }
    }

    public function getConnection(): PDO {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Executa uma query preparada com bind de valores
     * @param string $sql Query SQL
     * @param array $params Parâmetros para bind
     * @return PDOStatement
     */
    public function prepare(string $sql, array $params = []): PDOStatement {
        $stmt = $this->getConnection()->prepare($sql);
        
        foreach ($params as $key => $value) {
            $paramType = $this->getParamType($value);
            $stmt->bindValue(is_int($key) ? $key + 1 : $key, $value, $paramType);
        }
        
        return $stmt;
    }

    /**
     * Determina o tipo PDO apropriado para o valor
     */
    private function getParamType(mixed $value): int {
        return match (gettype($value)) {
            'boolean' => PDO::PARAM_BOOL,
            'integer' => PDO::PARAM_INT,
            'NULL' => PDO::PARAM_NULL,
            default => PDO::PARAM_STR,
        };
    }

    /**
     * Executa uma query e retorna os resultados
     * @param string $sql Query SQL
     * @param array $params Parâmetros para bind
     * @return array
     */
    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->prepare($sql, $params);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Executa uma query e retorna um único registro
     * @param string $sql Query SQL
     * @param array $params Parâmetros para bind
     * @return array|null
     */
    public function fetchOne(string $sql, array $params = []): ?array {
        $stmt = $this->prepare($sql, $params);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Executa uma query de inserção/atualização/exclusão
     * @param string $sql Query SQL
     * @param array $params Parâmetros para bind
     * @return int Número de linhas afetadas
     */
    public function execute(string $sql, array $params = []): int {
        $stmt = $this->prepare($sql, $params);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Retorna o último ID inserido
     * @return string
     */
    public function lastInsertId(): string {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Inicia uma transação
     * @return bool
     */
    public function beginTransaction(): bool {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit da transação
     * @return bool
     */
    public function commit(): bool {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback da transação
     * @return bool
     */
    public function rollback(): bool {
        return $this->getConnection()->rollBack();
    }
}
