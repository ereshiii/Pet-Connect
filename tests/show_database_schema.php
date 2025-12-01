<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();


$pdo = DB::connection()->getPdo();

// Get all tables
$stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$output = "DATABASE SCHEMA\n";
$output .= str_repeat("=", 120) . "\n\n";

foreach ($tables as $table) {
    // Skip SQLite internal tables
    if (strpos($table, 'sqlite_') === 0) {
        continue;
    }
    
    $output .= "TABLE: $table\n";
    $output .= str_repeat("-", 120) . "\n";
    
    // Get column information
    $stmt = $pdo->query("PRAGMA table_info($table)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Header
    $output .= sprintf(
        "%-30s | %-20s | %-10s | %-15s | %-20s\n",
        "COLUMN NAME",
        "TYPE",
        "NOT NULL",
        "DEFAULT",
        "PRIMARY KEY"
    );
    $output .= str_repeat("-", 120) . "\n";
    
    // Rows
    foreach ($columns as $col) {
        $output .= sprintf(
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
        $output .= "\n  Foreign Keys:\n";
        foreach ($foreignKeys as $fk) {
            $output .= sprintf(
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
        $output .= "\n  Indexes:\n";
        foreach ($indexes as $index) {
            $indexName = $index['name'];
            $unique = $index['unique'] ? 'UNIQUE' : 'NON-UNIQUE';
            
            // Get columns in this index
            $stmt = $pdo->query("PRAGMA index_info($indexName)");
            $indexCols = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $colNames = array_map(fn($c) => $c['name'], $indexCols);
            
            $output .= sprintf(
                "    %s (%s) on columns: %s\n",
                $indexName,
                $unique,
                implode(', ', $colNames)
            );
        }
    }
    
    $output .= "\n" . str_repeat("=", 120) . "\n\n";
}

$output .= "\nTotal tables: " . count(array_filter($tables, fn($t) => strpos($t, 'sqlite_') !== 0)) . "\n";

// Write to file
$filePath = __DIR__ . DIRECTORY_SEPARATOR . 'database_schema.txt';
file_put_contents($filePath, $output);

// Also echo a message to the console
echo "Database schema written to $filePath\n";
