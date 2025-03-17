#!/bin/bash

#=================================================
# COMMON VARIABLES AND CUSTOM HELPERS
#=================================================

fetch_dynamic_ids()  {

curl -s https://$domain$path/index.php/index/install/install > /tmp/form.html

adminUsername_id=$(grep -oP 'id="adminUsername-\K[^"]+' /tmp/form.html)
adminPassword_id=$(grep -oP 'id="adminPassword-\K[^"]+' /tmp/form.html)
adminPassword2_id=$(grep -oP 'id="adminPassword2-\K[^"]+' /tmp/form.html)
adminEmail_id=$(grep -oP 'id="adminEmail-\K[^"]+' /tmp/form.html)
filesDir_id=$(grep -oP 'id="filesDir-\K[^"]+' /tmp/form.html)
databaseHost_id=$(grep -oP 'id="databaseHost-\K[^"]+' /tmp/form.html)
databaseUsername_id=$(grep -oP 'id="databaseUsername-\K[^"]+' /tmp/form.html)
databasePassword_id=$(grep -oP 'id="databasePassword-\K[^"]+' /tmp/form.html)
databaseName_id=$(grep -oP 'id="databaseName-\K[^"]+' /tmp/form.html)
oaiRepositoryId_id=$(grep -oP 'id="oaiRepositoryId-\K[^"]+' /tmp/form.html)

}
