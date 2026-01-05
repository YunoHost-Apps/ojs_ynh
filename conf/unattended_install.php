<?php
/**
 * Unattended OJS Installer for Yunohost
 * * Replaces the interactive tools/install.php and the broken python script.
 * Usage: Set environment variables and run: php unattended_install.php
 */

// Adjust path if this script is not in the root OJS directory
require(dirname(__FILE__) . '/tools/bootstrap.php');

use PKP\cliTool\InstallTool;

class UnattendedInstallTool extends InstallTool
{
    /**
     * Override readParams to get values from Env Vars instead of STDIN
     */
    public function readParams()
    {
        // 1. General Settings
        $this->params['locale'] = getenv('OJS_LOCALE') ?: 'en_US';
        $this->params['clientCharset'] = 'utf-8';
        $this->params['baseUrl'] = getenv('OJS_BASE_URL'); // Maps to --url
        
        // Maps to --time-zone. Must be a valid PHP timezone (e.g., 'Europe/Paris')
        $this->params['timeZone'] = getenv('OJS_TIMEZONE') ?: 'UTC'; 

        // 2. File Settings
        $this->params['filesDir'] = getenv('OJS_FILES_DIR'); // Maps to --files-dir

        // 3. Admin Account
        $this->params['adminUsername'] = getenv('OJS_ADMIN_USER');
        $this->params['adminPassword'] = getenv('OJS_ADMIN_PASS');
        $this->params['adminEmail'] = getenv('OJS_ADMIN_EMAIL');
        $this->params['encryption'] = 'sha1'; // Required legacy param

        // 4. Database Settings
        $this->params['databaseDriver'] = getenv('OJS_DB_DRIVER') ?: 'mysqli';
        $this->params['databaseHost'] = getenv('OJS_DB_HOST') ?: 'localhost';
        $this->params['databaseUsername'] = getenv('OJS_DB_USER');
        $this->params['databasePassword'] = getenv('OJS_DB_PASS');
        $this->params['databaseName'] = getenv('OJS_DB_NAME');
        $this->params['createDatabase'] = getenv('OJS_DB_CREATE') === 'true' ? 1 : 0;

        // 5. OAI Settings
        $this->params['oaiRepositoryId'] = getenv('OJS_OAI_ID'); // Maps to --oai-repository-id

        // 6. Signal that we want to proceed with installation
        $this->params['install'] = 1;
    }
}

// Instantiate and execute
$tool = new UnattendedInstallTool($argv ?? []);

// The execute method runs the installation
if ($tool->execute()) {
    echo "OJS Installation completed successfully.\n";
    exit(0);
} else {
    echo "OJS Installation failed.\n";
    exit(1);
}
