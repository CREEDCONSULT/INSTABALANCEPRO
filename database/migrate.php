<?php
/**
 * Database Migration Runner
 * Executes database/schema.sql against the configured MySQL database
 * 
 * Usage:
 *   php database/migrate.php          # Run migrations
 *   php database/migrate.php --reset  # Drop and recreate all tables
 *   php database/migrate.php --seed   # Populate with sample data
 */

define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');

require_once ROOT_PATH . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
if (file_exists(ROOT_PATH . '/.env')) {
    $dotenv = Dotenv::createImmutable(ROOT_PATH);
    $dotenv->load();
}

// Configuration
$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbName = $_ENV['DB_NAME'] ?? 'instabalancepro';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? '';
$dbPort = $_ENV['DB_PORT'] ?? 3306;

// Parse command line arguments
$resetFlag = in_array('--reset', $argv);
$seedFlag = in_array('--seed', $argv);
$helpFlag = in_array('--help', $argv) || in_array('-h', $argv);

// Display help
if ($helpFlag) {
    echo <<<'HELP'
Database Migration Runner

Usage:
  php database/migrate.php          Run schema migration
  php database/migrate.php --reset  Drop all tables and reconstruct (WARNING: deletes data)
  php database/migrate.php --seed   Populate with default/sample data
  php database/migrate.php --help   Show this help message

Environment Variables Required (.env):
  DB_HOST         - Database host (default: localhost)
  DB_NAME         - Database name (default: instabalancepro)
  DB_USER         - Database user (default: root)
  DB_PASS         - Database password (default: empty)
  DB_PORT         - Database port (default: 3306)

HELP;
    exit(0);
}

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         UnfollowIQ Database Migration Runner               ║\n";
echo "║         Version: 1.0                                       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Display configuration
echo "Configuration:\n";
echo "  Host:     $dbHost:$dbPort\n";
echo "  Database: $dbName\n";
echo "  User:     $dbUser\n";
echo "\n";

// Connect to MySQL server (without selecting database)
try {
    $dsn = "mysql:host=$dbHost;port=$dbPort;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "[✓] Connected to MySQL server\n";
} catch (Exception $e) {
    echo "[✗] Failed to connect to MySQL server:\n";
    echo "    {$e->getMessage()}\n";
    exit(1);
}

// Create database if it doesn't exist
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "[✓] Database '$dbName' created or already exists\n";
} catch (Exception $e) {
    echo "[✗] Failed to create database:\n";
    echo "    {$e->getMessage()}\n";
    exit(1);
}

// Select the database
try {
    $pdo->exec("USE `$dbName`");
    echo "[✓] Selected database '$dbName'\n\n";
} catch (Exception $e) {
    echo "[✗] Failed to select database:\n";
    echo "    {$e->getMessage()}\n";
    exit(1);
}

// Drop tables if --reset flag is set
if ($resetFlag) {
    echo "⚠️  WARNING: --reset flag will DROP ALL TABLES and delete all data!\n";
    echo "    Continue? (yes/no): ";
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($response) !== 'yes') {
        echo "\nMigration cancelled.\n";
        exit(0);
    }
    
    echo "\nDropping all tables...\n";
    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        $tables = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$dbName'")->fetchAll();
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE `{$table['TABLE_NAME']}`");
            echo "  [✓] Dropped table '{$table['TABLE_NAME']}'\n";
        }
        
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        echo "\n[✓] All tables dropped successfully\n\n";
    } catch (Exception $e) {
        echo "[✗] Failed to drop tables:\n";
        echo "    {$e->getMessage()}\n";
        exit(1);
    }
}

// Read and execute schema.sql
$schemaFile = ROOT_PATH . '/database/schema.sql';
if (!file_exists($schemaFile)) {
    echo "[✗] Schema file not found: $schemaFile\n";
    exit(1);
}

echo "Running schema migration...\n";
$schema = file_get_contents($schemaFile);

// Split SQL statements and execute
$statements = preg_split('/;\s*$/m', $schema);
$executedCount = 0;
$skippedCount = 0;

foreach ($statements as $statement) {
    $statement = trim($statement);
    
    // Skip empty statements and comments
    if (empty($statement) || strpos($statement, '/*') === 0 || strpos($statement, '--') === 0) {
        $skippedCount++;
        continue;
    }
    
    try {
        $pdo->exec($statement);
        $executedCount++;
    } catch (Exception $e) {
        // Check if it's an "already exists" error (which is safe to ignore)
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "  [⚠] Skipped (already exists): {$e->getMessage()}\n";
            $skippedCount++;
            continue;
        }
        echo "[✗] Failed to execute statement:\n";
        echo "    {$e->getMessage()}\n";
        echo "    Statement: " . substr($statement, 0, 100) . "...\n";
        exit(1);
    }
}

echo "[✓] Schema migration completed\n";
echo "    Executed: $executedCount statements\n";
echo "    Skipped: $skippedCount statements\n\n";

// Verify tables were created
try {
    $tables = $pdo->query("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$dbName'")->fetch();
    $tableCount = $tables['count'];
    echo "✓ Verification: $tableCount tables created in database '$dbName'\n\n";
} catch (Exception $e) {
    echo "[✗] Failed to verify tables:\n";
    echo "    {$e->getMessage()}\n";
    exit(1);
}

// List all created tables
try {
    echo "Tables created:\n";
    $tables = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$dbName' ORDER BY TABLE_NAME");
    $tableList = $tables->fetchAll();
    
    foreach ($tableList as $i => $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$table['TABLE_NAME']}`")->fetch(PDO::FETCH_NUM)[0];
        printf("  %2d. %-30s (%d rows)\n", $i + 1, $table['TABLE_NAME'], $count);
    }
} catch (Exception $e) {
    echo "[⚠] Could not list tables\n";
}

echo "\n";

// Seed with sample data if --seed flag is set
if ($seedFlag) {
    echo "Seeding database with sample data...\n";
    seedDatabase($pdo);
    echo "[✓] Database seeded with sample data\n\n";
}

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║          ✓ Migration completed successfully!               ║\n";
echo "║                                                            ║\n";
echo "║  Next steps:                                               ║\n";
echo "║  1. Configure your .env file with database credentials    ║\n";
echo "║  2. Run: composer install                                 ║\n";
echo "║  3. Start your application server                         ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

exit(0);

// ============================================================================
// Helper function to seed database with sample data
// ============================================================================

function seedDatabase($pdo) {
    try {
        // Create a test user account
        $hashedPassword = password_hash('password123', PASSWORD_BCRYPT, ['cost' => 12]);
        
        $pdo->prepare("INSERT INTO users (email, password_hash, display_name, subscription_tier, email_verified_at) VALUES (?, ?, ?, ?, NOW())")->execute([
            'test@example.com',
            $hashedPassword,
            'Test User',
            'free'
        ]);
        
        echo "  [✓] Created test user: test@example.com (password: password123)\n";
        
        // Get the user ID
        $userId = $pdo->lastInsertId();
        
        // Create Instagram connection
        $pdo->prepare("INSERT INTO instagram_connections (user_id, instagram_user_id, instagram_username, followers_count, following_count) VALUES (?, ?, ?, ?, ?)")->execute([
            $userId,
            '12345678',
            'testuser_ig',
            150,
            200
        ]);
        
        echo "  [✓] Created Instagram connection for test user\n";
        
        // Create user_scoring_preferences
        $pdo->prepare("INSERT INTO user_scoring_preferences (user_id) VALUES (?)")->execute([$userId]);
        echo "  [✓] Created default scoring preferences for test user\n";
        
        // Create subscription
        $pdo->prepare("INSERT INTO subscriptions (user_id, stripe_customer_id, tier) VALUES (?, ?, ?)")->execute([
            $userId,
            'cus_test_' . uniqid(),
            'free'
        ]);
        
        echo "  [✓] Created free subscription for test user\n";
        
    } catch (Exception $e) {
        echo "  [⚠] Error during seeding: {$e->getMessage()}\n";
    }
}
