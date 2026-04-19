<?php
/**
 * Arquivo de Diagnóstico do Sistema
 * Acesse: http://localhost/sua-pasta/diagnostico.php
 */

echo "<h1>Diagnóstico do Sistema</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .erro{color:red;} table{border-collapse:collapse;width:100%;} td,th{border:1px solid #ddd;padding:8px;}</style>";

echo "<h2>✅ Informações do Servidor</h2>";
echo "<table>";
echo "<tr><th>Item</th><th>Valor</th></tr>";
echo "<tr><td>PHP Version</td><td class='ok'>" . phpversion() . "</td></tr>";
echo "<tr><td>Server Software</td><td>" . $_SERVER['SERVER_SOFTWARE'] . "</td></tr>";
echo "<tr><td>Document Root</td><td>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "</td></tr>";
echo "<tr><td>Script Path</td><td>" . (__FILE__) . "</td></tr>";
echo "<tr><td>Current Dir</td><td>" . getcwd() . "</td></tr>";
echo "</table>";

echo "<h2>✅ Extensões PHP</h2>";
$extensoes = ['pdo', 'pdo_mysql', 'json', 'session', 'openssl', 'mbstring'];
echo "<table>";
echo "<tr><th>Extensão</th><th>Status</th></tr>";
foreach ($extensoes as $ext) {
    $status = extension_loaded($ext) ? '<span class="ok">✓ Instalada</span>' : '<span class="erro">✗ Não instalada</span>';
    echo "<tr><td>$ext</td><td>$status</td></tr>";
}
echo "</table>";

echo "<h2>✅ Permissões de Arquivos</h2>";
$dirs = [
    __DIR__,
    __DIR__ . '/assets',
    __DIR__ . '/uploads',
    dirname(__DIR__) . '/config'
];
echo "<table>";
echo "<tr><th>Diretório</th><th>Legível?</th><th>Gravável?</th></tr>";
foreach ($dirs as $dir) {
    $legivel = is_readable($dir) ? '<span class="ok">✓ Sim</span>' : '<span class="erro">✗ Não</span>';
    $gravavel = is_writable($dir) ? '<span class="ok">✓ Sim</span>' : '<span class="erro">✗ Não</span>';
    echo "<tr><td>$dir</td><td>$legivel</td><td>$gravavel</td></tr>";
}
echo "</table>";

echo "<h2>✅ Teste de Sessão</h2>";
session_start();
$_SESSION['teste_diagnostico'] = 'funcionou';
echo "<p>Status da sessão: <span class='ok'>✓ Iniciada com sucesso</span></p>";
echo "<p>ID da sessão: " . session_id() . "</p>";

echo "<h2>✅ Próximos Passos</h2>";
echo "<ol>";
echo "<li>Se todas as extensões estão OK, o problema pode ser o <strong>.htaccess</strong></li>";
echo "<li>No XAMPP, verifique se <code>AllowOverride All</code> está no httpd.conf</li>";
echo "<li>Tente acessar diretamente: <code>http://localhost/sua-pasta/public/auth/login.php</code></li>";
echo "<li>Reinicie o Apache após alterações no httpd.conf</li>";
echo "</ol>";

echo "<hr><p><small>Se precisar de ajuda, mostre esta página no suporte.</small></p>";
