#!/bin/bash

#=================================================
# COMMON VARIABLES AND CUSTOM HELPERS
#=================================================

fetch_dynamic_ids()  {

curl -s index.php/index/install/install > form.html

adminUsername_id=$(grep -oP 'id="adminUsername-\K[^"]+' form.html)
adminPassword_id=$(grep -oP 'id="adminPassword-\K[^"]+' form.html)
adminPassword2_id=$(grep -oP 'id="adminPassword2-\K[^"]+' form.html)
adminEmail_id=$(grep -oP 'id="adminEmail-\K[^"]+' form.html)
filesDir_id=$(grep -oP 'id="filesDir-\K[^"]+' form.html)
databaseHost_id=$(grep -oP 'id="databaseHost-\K[^"]+' form.html)
databaseUsername_id=$(grep -oP 'id="databaseUsername-\K[^"]+' form.html)
databasePassword_id=$(grep -oP 'id="databasePassword-\K[^"]+' form.html)
databaseName_id=$(grep -oP 'id="databaseName-\K[^"]+' form.html)
oaiRepositoryId_id=$(grep -oP 'id="oaiRepositoryId-\K[^"]+' form.html)

}
