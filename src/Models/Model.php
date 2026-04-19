<?php
/**
 * Modelo Base para todos os modelos do sistema
 * Implementa operações CRUD básicas e validações
 */

abstract class Model {
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $rules = [];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Busca todos os registros
     * @param array $where Condições WHERE
     * @param string $orderBy Ordenação
     * @param int $limit Limite de registros
     * @return array
     */
    public function all(array $where = [], string $orderBy = 'id DESC', int $limit = 100): array {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "$column = :$column";
                $params[$column] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sql .= " ORDER BY $orderBy LIMIT :limit";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value, $this->getParamType($value));
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Busca um registro por ID
     * @param int $id
     * @return array|null
     */
    public function find(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }

    /**
     * Busca um registro por coluna
     * @param string $column
     * @param mixed $value
     * @return array|null
     */
    public function findBy(string $column, mixed $value): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE $column = :value LIMIT 1";
        return $this->db->fetchOne($sql, ['value' => $value]);
    }

    /**
     * Cria um novo registro
     * @param array $data Dados para inserção
     * @return string ID do registro criado
     * @throws Exception Se houver erro na validação
     */
    public function create(array $data): string {
        $this->validate($data);
        
        $filteredData = $this->filterData($data);
        $columns = implode(', ', array_keys($filteredData));
        $placeholders = ':' . implode(', :', array_keys($filteredData));
        
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $this->db->execute($sql, $filteredData);
        
        return $this->db->lastInsertId();
    }

    /**
     * Atualiza um registro
     * @param int $id ID do registro
     * @param array $data Dados para atualização
     * @return int Número de linhas afetadas
     * @throws Exception Se houver erro na validação
     */
    public function update(int $id, array $data): int {
        $this->validate($data, true);
        
        $filteredData = $this->filterData($data);
        $sets = [];
        foreach (array_keys($filteredData) as $column) {
            $sets[] = "$column = :$column";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$this->primaryKey} = :id";
        $filteredData['id'] = $id;
        
        return $this->db->execute($sql, $filteredData);
    }

    /**
     * Exclui um registro
     * @param int $id ID do registro
     * @return int Número de linhas afetadas
     */
    public function delete(int $id): int {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    /**
     * Valida os dados conforme regras definidas
     * @param array $data Dados para validar
     * @param bool $isUpdate Se é uma operação de atualização
     * @throws Exception Se houver erro de validação
     */
    protected function validate(array $data, bool $isUpdate = false): void {
        foreach ($this->rules as $field => $ruleSet) {
            $rules = explode('|', $ruleSet);
            
            foreach ($rules as $rule) {
                $value = $data[$field] ?? null;
                
                // required
                if ($rule === 'required' && empty($value) && !$isUpdate) {
                    throw new Exception("O campo '$field' é obrigatório.");
                }
                
                // email
                if ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("O campo '$field' deve ser um e-mail válido.");
                }
                
                // numeric
                if ($rule === 'numeric' && !empty($value) && !is_numeric($value)) {
                    throw new Exception("O campo '$field' deve ser numérico.");
                }
                
                // min:X
                if (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    if (!empty($value) && strlen($value) < $min) {
                        throw new Exception("O campo '$field' deve ter no mínimo $min caracteres.");
                    }
                }
                
                // max:X
                if (str_starts_with($rule, 'max:')) {
                    $max = (int) substr($rule, 4);
                    if (!empty($value) && strlen($value) > $max) {
                        throw new Exception("O campo '$field' deve ter no máximo $max caracteres.");
                    }
                }
            }
        }
    }

    /**
     * Filtra dados permitindo apenas campos fillable
     * @param array $data Dados brutos
     * @return array Dados filtrados
     */
    protected function filterData(array $data): array {
        $filtered = [];
        foreach ($this->fillable as $field) {
            if (array_key_exists($field, $data)) {
                $filtered[$field] = $data[$field];
            }
        }
        return $filtered;
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
     * Conta registros com condições
     * @param array $where Condições WHERE
     * @return int
     */
    public function count(array $where = []): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "$column = :$column";
                $params[$column] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return (int) ($result['total'] ?? 0);
    }
}
