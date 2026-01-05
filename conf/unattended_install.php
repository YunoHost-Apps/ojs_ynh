<?php
/**
 * Unattended OJS Installer for Yunohost (Xith Debug)
 */

require(dirname(__FILE__) . '/tools/bootstrap.php');

use PKP\cliTool\InstallTool;
use PKP\install\Installer;

// Enable full PHP error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

class UnattendedInstallTool extends InstallTool
{
    public function readParams()
    {
        // 1. General Settings
        $this->params['locale'] = getenv('OJS_LOCALE') ?: 'en_US';
        $this->params['clientCharset'] = 'utf-8';
        $this->params['baseUrl'] = getenv('OJS_BASE_URL');
        $this->params['timeZone'] = getenv('OJS_TIMEZONE') ?: 'UTC'; 

        // 2. File Settings
        $this->params['filesDir'] = getenv('OJS_FILES_DIR');

        // 3. Admin Account
        $this->params['adminUsername'] = getenv('OJS_ADMIN_USER');
        $this->params['adminPassword'] = getenv('OJS_ADMIN_PASS');
        $this->params['adminEmail'] = getenv('OJS_ADMIN_EMAIL');
        $this->params['encryption'] = 'sha1'; 

        // 4. Database Settings
        $this->params['databaseDriver'] = getenv('OJS_DB_DRIVER') ?: 'mysqli';
        $this->params['databaseHost'] = getenv('OJS_DB_HOST') ?: 'localhost';
        $this->params['databaseUsername'] = getenv('OJS_DB_USER');
        $this->params['databasePassword'] = getenv('OJS_DB_PASS');
        $this->params['databaseName'] = getenv('OJS_DB_NAME');
        $this->params['createDatabase'] = getenv('OJS_DB_CREATE') === 'true' ? 1 : 0;

        // 5. OAI Settings
        $this->params['oaiRepositoryId'] = getenv('OJS_OAI_ID');

        // 6. Install Flag
        $this->params['install'] = 1;
    }

    /**
     * Overriding execute to capture and print specific errors
     */
    public function execute()
    {
        // Manually instantiate the Installer so we can access its error logs
        $installer = new Installer($this->params);
        $installer->setLogger($this); // Allows installer to print progress to CLI

        echo "Attempting installation...\n";

        if ($installer->execute()) {
            // Success logic
            if (!$installer->writeConfig()) {
                echo "\n[WARNING]: Installer succeeded but could not write 'config.inc.php'.\n";
                echo "Check permissions on the OJS root directory.\n";
                return false;
            }
            return true;
        } else {
            // Print detailed errors
            echo "\n[ERROR] Installation Failed. Details:\n";
            echo "------------------------------------------------\n";
            echo "Error Type:   " . $installer->getErrorType() . "\n";
            echo "Error String: " . $installer->getErrorString() . "\n";
            
            // Check for DB specific errors
            if (isset($installer->dbErrorMsg) && !empty($installer->dbErrorMsg)) {
                echo "DB Message:   " . $installer->dbErrorMsg . "\n";
            }
            
            // Dump install notes if available
            if (method_exists($installer, 'getNotes')) {
                echo "Installer Notes: " . print_r($installer->getNotes(), true) . "\n";
            }
            echo "------------------------------------------------\n";
            
            return false;
        }
    }
}

$tool = new UnattendedInstallTool($argv ?? []);

if ($tool->execute()) {
    echo "OJS Installation completed successfully.\n";
    exit(0);
} else {
    echo "Exiting with error.\n";
    exit(1);
}
