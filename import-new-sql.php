<?php
// One-time importer for data/new.sql.
// Usage (CLI): php import-new-sql.php
// Usage (browser): /import-new-sql.php?run=1

require_once __DIR__ . '/db.php';

$isCli = PHP_SAPI === 'cli';

if (!$isCli && (!isset($_GET['run']) || $_GET['run'] !== '1')) {
    http_response_code(400);
    echo "Add ?run=1 to execute import.\n";
    exit;
}

$sqlFile = __DIR__ . '/data/new.sql';
if (!is_file($sqlFile) || !is_readable($sqlFile)) {
    http_response_code(500);
    echo "SQL file not found or not readable: {$sqlFile}\n";
    exit;
}

function emit(string $message): void {
    echo $message . PHP_EOL;
}

$pdo = getDB();
$pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    http_response_code(500);
    emit('Failed to read SQL file.');
    exit;
}

emit('Starting import from data/new.sql ...');

try {
    $pdo->beginTransaction();

    // Execute the SQL dump as a single batch.
    $pdo->exec($sql);

    $pdo->commit();
    emit('Import completed successfully.');
    emit('Tip: delete import-new-sql.php after one-time use.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    emit('Import failed. Transaction rolled back.');
    emit('Error: ' . $e->getMessage());
    exit;
}
