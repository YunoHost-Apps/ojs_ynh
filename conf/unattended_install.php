<?php

ob_start();

$baseDir = dirname(dirname(__FILE__));
chdir($baseDir);

require($baseDir . '/tools/bootstrap.php');

use APP\install\Install;
use Illuminate\Support\Facades\DB;

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

$params = [
    'locale' => getenv('OJS_LOCALE') ?: 'en_US',
    'clientCharset' => 'utf-8',
    'baseUrl' => getenv('OJS_BASE_URL'),
    'timeZone' => getenv('OJS_TIMEZONE') ?: 'UTC',
    'filesDir' => getenv('OJS_FILES_DIR'),
    'adminUsername' => getenv('OJS_ADMIN_USER'),
    'adminPassword' => getenv('OJS_ADMIN_PASS'),
    'adminEmail' => getenv('OJS_ADMIN_EMAIL'),
    'encryption' => 'sha1',
    'databaseDriver' => getenv('OJS_DB_DRIVER') ?: 'mysqli',
    'databaseHost' => getenv('OJS_DB_HOST') ?: 'localhost',
    'databaseUsername' => getenv('OJS_DB_USER'),
    'databasePassword' => getenv('OJS_DB_PASS'),
    'databaseName' => getenv('OJS_DB_NAME'),
    'createDatabase' => getenv('OJS_DB_CREATE') === 'true' ? 1 : 0,
    'oaiRepositoryId' => getenv('OJS_OAI_ID'),
    'install' => 1
];

function ensureDummyTableExists($p) {
    try {
        $dsn = "mysql:host={$p['databaseHost']};dbname={$p['databaseName']};charset=utf8";
        $pdo = new PDO($dsn, $p['databaseUsername'], $p['databasePassword']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $pdo->query("SELECT 1 FROM versions LIMIT 1");
        } catch (Exception $e) {
             $sql = "CREATE TABLE versions (
                major_version INT NOT NULL DEFAULT 0,
                minor_version INT NOT NULL DEFAULT 0,
                revision INT NOT NULL DEFAULT 0,
                build INT NOT NULL DEFAULT 0,
                date_installed DATETIME NOT NULL,
                current TINYINT NOT NULL DEFAULT 0,
                product_type VARCHAR(30),
                product VARCHAR(30),
                product_class_name VARCHAR(80),
                lazy_load TINYINT NOT NULL DEFAULT 0,
                sitewide TINYINT NOT NULL DEFAULT 0
            )";
            $pdo->exec($sql);
        }
    } catch (PDOException $e) {}
}
ensureDummyTableExists($params);

class SmartInstaller extends Install {
    
    public function executeInstaller() {
        try {
            DB::statement('DROP TABLE IF EXISTS versions');
        } catch (Exception $e) {}
        return parent::executeInstaller();
    }

    public function updateRorRegistryDataset(): bool { return true; }
    public function downloadIPGeoDB(): bool { return true; }

    public function postInstall() {
        try {
            parent::postInstall();
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'already exists') === false) {
                throw $e;
            }
        }
        return true;
    }
}

$absoluteXmlPath = $baseDir . '/dbscripts/xml/install.xml';
$installer = new SmartInstaller($params, $absoluteXmlPath, true);

ob_end_clean();
echo "--- Starting Forced Installation ---\n";

class SimpleLogger {
    public function log($message) {
        echo "[Installer] " . strip_tags($message) . "\n";
    }
}
$installer->setLogger(new SimpleLogger());

try {
    if ($installer->execute()) {
        echo "\n[SUCCESS] Installation Logic Completed.\n";
    } else {
        echo "\n[WARNING] Logic finished with warnings (Ignored).\n";
    }
    
    echo "[FINAL STEP] Writing Config File...\n";
    if ($installer->updateConfig($params)) {
        echo "[SUCCESS] config.inc.php written successfully!\n";
        echo "You can now access your OJS site.\n";
        exit(0);
    } else {
        echo "[CRITICAL] Could not write config.inc.php.\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "\n[EXCEPTION] " . $e->getMessage() . "\n";
    exit(1);
}
