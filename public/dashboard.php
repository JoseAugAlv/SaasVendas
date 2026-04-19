<?php
/**
 * Dashboard do Vendedor
 * Visão geral do sistema
 */

require_once __DIR__ . '/../src/Models/Database.php';
require_once __DIR__ . '/../src/Models/Model.php';
require_once __DIR__ . '/../src/Models/Usuario.php';
require_once __DIR__ . '/../src/Middleware/AuthMiddleware.php';

// Requer autenticação e assinatura ativa
AuthMiddleware::requireActiveSubscription();

$user = AuthMiddleware::user();

if (!$user) {
    header('Location: /auth/login.php');
    exit;
}

$db = Database::getInstance();

// Busca estatísticas do dashboard
$vendedorId = $user['id'];

// Total de produtos
$totalProdutos = $db->fetchOne("SELECT COUNT(*) as total FROM produtos WHERE vendedor_id = :vendedor_id", ['vendedor_id' => $vendedorId])['total'] ?? 0;

// Pedidos pendentes
$pedidosPendentes = $db->fetchOne("SELECT COUNT(*) as total, SUM(valor_total) as valor FROM pedidos WHERE vendedor_id = :vendedor_id AND status = 'pendente'", ['vendedor_id' => $vendedorId]);

// Produtos com estoque baixo
$estoqueBaixo = $db->fetchAll("SELECT COUNT(*) as total FROM produtos WHERE vendedor_id = :vendedor_id AND estoque_atual <= estoque_minimo", ['vendedor_id' => $vendedorId])[0]['total'] ?? 0;

// Faturamento do mês
$faturamentoMes = $db->fetchOne("SELECT SUM(valor_total) as total FROM pedidos WHERE vendedor_id = :vendedor_id AND status IN ('entregue', 'enviado') AND MONTH(data_pedido) = MONTH(CURRENT_DATE()) AND YEAR(data_pedido) = YEAR(CURRENT_DATE())", ['vendedor_id' => $vendedorId]);

// Últimos pedidos
$ultimosPedidos = $db->fetchAll("SELECT p.*, c.nome as cliente_nome 
                                  FROM pedidos p 
                                  LEFT JOIN clientes c ON p.cliente_id = c.id 
                                  WHERE p.vendedor_id = :vendedor_id 
                                  ORDER BY p.data_pedido DESC 
                                  LIMIT 5", ['vendedor_id' => $vendedorId]);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestão de Vendedores</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="no-print" style="background: var(--card); border-bottom: 1px solid var(--border); padding: var(--space-4) 0;">
        <div class="container flex items-center justify-between">
            <h1 style="font-size: var(--font-size-xl); color: var(--primary); margin: 0;">
                Gestão de Vendedores
            </h1>
            
            <nav class="flex items-center gap-4">
                <a href="/dashboard.php" style="color: var(--text); font-weight: 500;">Dashboard</a>
                <a href="/produtos/index.php" style="color: var(--text-secondary);">Produtos</a>
                <a href="/pedidos/index.php" style="color: var(--text-secondary);">Pedidos</a>
                <a href="/insumos/index.php" style="color: var(--text-secondary);">Insumos</a>
                <a href="/financeiro/index.php" style="color: var(--text-secondary);">Financeiro</a>
                
                <div style="display: flex; align-items: center; gap: var(--space-3); margin-left: var(--space-4);">
                    <span style="font-size: var(--font-size-sm); color: var(--text-secondary);">
                        <?= htmlspecialchars($user['nome']) ?>
                    </span>
                    <?php if ($user['avatar_url']): ?>
                        <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="Avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                    <?php endif; ?>
                    <a href="/auth/logout.php" class="btn btn-sm btn-secondary">Sair</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container" style="padding: var(--space-8) var(--space-4);">
        <div class="mb-6">
            <h2 style="font-size: var(--font-size-2xl); margin-bottom: var(--space-2);">
                Olá, <?= htmlspecialchars($user['nome']) ?>! 👋
            </h2>
            <p class="text-muted">Aqui está o resumo do seu negócio hoje.</p>
        </div>

        <!-- Cards de Estatísticas -->
        <div class="flex" style="flex-wrap: wrap; gap: var(--space-4); margin-bottom: var(--space-8);">
            <div class="card" style="flex: 1; min-width: 200px; margin: 0;">
                <div class="text-muted" style="font-size: var(--font-size-sm); margin-bottom: var(--space-2);">Produtos Cadastrados</div>
                <div style="font-size: var(--font-size-3xl); font-weight: 700; color: var(--primary);"><?= $totalProdutos ?></div>
            </div>

            <div class="card" style="flex: 1; min-width: 200px; margin: 0;">
                <div class="text-muted" style="font-size: var(--font-size-sm); margin-bottom: var(--space-2);">Pedidos Pendentes</div>
                <div style="font-size: var(--font-size-3xl); font-weight: 700; color: var(--warning);"><?= $pedidosPendentes['total'] ?? 0 ?></div>
                <div class="text-muted" style="font-size: var(--font-size-xs); margin-top: var(--space-1);">
                    Valor: <?= Utils::formatCurrency($pedidosPendentes['valor'] ?? 0) ?>
                </div>
            </div>

            <div class="card" style="flex: 1; min-width: 200px; margin: 0;">
                <div class="text-muted" style="font-size: var(--font-size-sm); margin-bottom: var(--space-2);">Estoque Baixo</div>
                <div style="font-size: var(--font-size-3xl); font-weight: 700; color: var(--danger);"><?= $estoqueBaixo ?></div>
                <div class="text-muted" style="font-size: var(--font-size-xs); margin-top: var(--space-1);">
                    Produtos precisam de atenção
                </div>
            </div>

            <div class="card" style="flex: 1; min-width: 200px; margin: 0;">
                <div class="text-muted" style="font-size: var(--font-size-sm); margin-bottom: var(--space-2);">Faturamento (Mês)</div>
                <div style="font-size: var(--font-size-3xl); font-weight: 700; color: var(--success);">
                    <?= Utils::formatCurrency($faturamentoMes['total'] ?? 0) ?>
                </div>
            </div>
        </div>

        <!-- Últimos Pedidos -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Últimos Pedidos</h3>
                <a href="/pedidos/index.php" class="btn btn-sm btn-primary">Ver Todos</a>
            </div>

            <?php if (!empty($ultimosPedidos)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimosPedidos as $pedido): ?>
                                <tr>
                                    <td>#<?= $pedido['id'] ?></td>
                                    <td><?= htmlspecialchars($pedido['nome_cliente'] ?? 'N/A') ?></td>
                                    <td><?= Utils::formatDate($pedido['data_pedido']) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = match($pedido['status']) {
                                            'pendente' => 'badge-warning',
                                            'preparo' => 'badge-info',
                                            'enviado' => 'badge-info',
                                            'entregue' => 'badge-success',
                                            'cancelado' => 'badge-danger',
                                            default => 'badge-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= ucfirst($pedido['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= Utils::formatCurrency($pedido['valor_total']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center" style="padding: var(--space-8);">
                    <p class="text-muted">Nenhum pedido encontrado.</p>
                    <a href="/pedidos/novo.php" class="btn btn-primary mt-4">Criar Primeiro Pedido</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Ações Rápidas -->
        <div class="card" style="margin-top: var(--space-6);">
            <div class="card-header">
                <h3 class="card-title">Ações Rápidas</h3>
            </div>
            <div class="flex" style="flex-wrap: wrap; gap: var(--space-3);">
                <a href="/pedidos/novo.php" class="btn btn-success">Novo Pedido</a>
                <a href="/produtos/novo.php" class="btn btn-primary">Novo Produto</a>
                <a href="/insumos/novo.php" class="btn btn-secondary">Novo Insumo</a>
                <a href="/despesas/nova.php" class="btn btn-secondary">Nova Despesa</a>
            </div>
        </div>
    </main>

    <script src="/assets/js/app.js"></script>
    <script>
        // Define baseURL para API
        API.baseURL = '/api';
    </script>
</body>
</html>

<?php
// Classe utilitária para formatar moeda (usada no template)
class Utils {
    public static function formatCurrency($value) {
        return 'R$ ' . number_format($value ?? 0, 2, ',', '.');
    }
    
    public static function formatDate($date) {
        return date('d/m/Y', strtotime($date));
    }
}
?>
