<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pdo = DB::connection()->getPdo();

// Get all tables
$stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "DATABASE SCHEMA\n";
echo str_repeat("=", 120) . "\n\n";

foreach ($tables as $table) {
    // Skip SQLite internal tables
    if (strpos($table, 'sqlite_') === 0) {
        continue;
    }
    
    echo "TABLE: $table\n";
    echo str_repeat("-", 120) . "\n";
    
    // Get column information
    $stmt = $pdo->query("PRAGMA table_info($table)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Header
    echo sprintf(
        "%-30s | %-20s | %-10s | %-15s | %-20s\n",
        "COLUMN NAME",
        "TYPE",
        "NOT NULL",
        "DEFAULT",
        "PRIMARY KEY"
    );
    echo str_repeat("-", 120) . "\n";
    
    // Rows
    foreach ($columns as $col) {
        echo sprintf(
            "%-30s | %-20s | %-10s | %-15s | %-20s\n",
            $col['name'],
            $col['type'],
            $col['notnull'] ? 'YES' : 'NO',
            $col['dflt_value'] ?? 'NULL',
            $col['pk'] ? 'YES' : 'NO'
        );
    }
    
    // Get foreign keys
    $stmt = $pdo->query("PRAGMA foreign_key_list($table)");
    $foreignKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($foreignKeys)) {
        echo "\n  Foreign Keys:\n";
        foreach ($foreignKeys as $fk) {
            echo sprintf(
                "    %s -> %s(%s)\n",
                $fk['from'],
                $fk['table'],
                $fk['to']
            );
        }
    }
    
    // Get indexes
    $stmt = $pdo->query("PRAGMA index_list($table)");
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($indexes)) {
        echo "\n  Indexes:\n";
        foreach ($indexes as $index) {
            $indexName = $index['name'];
            $unique = $index['unique'] ? 'UNIQUE' : 'NON-UNIQUE';
            
            // Get columns in this index
            $stmt = $pdo->query("PRAGMA index_info($indexName)");
            $indexCols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $colNames = array_map(fn($c) => $c['name'], $indexCols);
            
            echo sprintf(
                "    %s (%s) on columns: %s\n",
                $indexName,
                $unique,
                implode(', ', $colNames)
            );
        }
    }
    
    echo "\n" . str_repeat("=", 120) . "\n\n";
}

echo "\nTotal tables: " . count(array_filter($tables, fn($t) => strpos($t, 'sqlite_') !== 0)) . "\n";
