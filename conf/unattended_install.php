<?php

$baseDir = dirname(dirname(__FILE__));
chdir($baseDir);

$configFile = $baseDir . '/config.inc.php';
$templateFile = $baseDir . '/config.TEMPLATE.inc.php';
if (!file_exists($configFile) && file_exists($templateFile)) {
    copy($templateFile, $configFile);
}
if (file_exists($configFile)) {
    $cfg = file_get_contents($configFile);
    $cfg = preg_replace('/^\s*installed\s*=\s*On/m', 'installed = Off', $cfg);
    file_put_contents($configFile, $cfg);
}

require($baseDir . '/tools/bootstrap.php');

use APP\install\Install;

$params = [
    'locale'           => getenv('OJS_LOCALE') ?: 'en_US',
    'clientCharset'    => 'utf-8',
    'baseUrl'          => getenv('OJS_BASE_URL'),
    'timeZone'         => getenv('OJS_TIMEZONE') ?: 'UTC',
    'filesDir'         => getenv('OJS_FILES_DIR'),
    'adminUsername'    => getenv('OJS_ADMIN_USER'),
    'adminPassword'    => getenv('OJS_ADMIN_PASS'),
    'adminEmail'       => getenv('OJS_ADMIN_EMAIL'),
    'encryption'       => 'sha1',
    'databaseDriver'   => getenv('OJS_DB_DRIVER') ?: 'mysqli',
    'databaseHost'     => getenv('OJS_DB_HOST') ?: 'localhost',
    'databaseUsername' => getenv('OJS_DB_USER'),
    'databasePassword' => getenv('OJS_DB_PASS'),
    'databaseName'     => getenv('OJS_DB_NAME'),
    'createDatabase'   => getenv('OJS_DB_CREATE') === 'true' ? 1 : 0,
    'oaiRepositoryId'  => getenv('OJS_OAI_ID'),
    'install'          => 1,
];

class UnattendedInstaller extends Install {
    public function updateRorRegistryDataset(): bool { return true; }
    public function downloadIPGeoDB(): bool { return true; }
}

class CliLogger {
    public function log($message): void {
        echo "[Installer] " . trim(strip_tags((string) $message)) . "\n";
    }
}

$installer = new UnattendedInstaller($params, $baseDir . '/dbscripts/xml/install.xml', true);
$installer->setLogger(new CliLogger());

echo "--- Starting Unattended OJS Installation ---\n";

try {
    if (!$installer->execute()) {
        $errorType = method_exists($installer, 'getErrorType') ? $installer->getErrorType() : 'unknown';
        $errorMsg  = method_exists($installer, 'getErrorString') ? $installer->getErrorString() : 'No error string returned';
        fwrite(STDERR, "[FAILED] Installer aborted (type: {$errorType}): {$errorMsg}\n");
        exit(1);
    }

    echo "[OK] Database schema, data, and version record installed.\n";

    if (!$installer->updateConfig($params)) {
        fwrite(STDERR, "[CRITICAL] Installation succeeded in DB, but failed to write config.inc.php.\n");
        exit(1);
    }

    echo "[SUCCESS] config.inc.php updated (installed = On).\n";
    exit(0);

} catch (Throwable $e) {
    fwrite(STDERR, "[EXCEPTION] " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n");
    exit(1);
}
