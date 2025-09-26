#!/bin/bash

#=================================================
# COMMON VARIABLES AND CUSTOM HELPERS
#=================================================

fetch_dynamic_ids()  {

curl -s -k https://$domain$path/index.php/index/install/install > /tmp/form.html

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

ojs_install()  {
python -m venv ojs_install
source ojs_install/bin/activate
pip install playwright
playwright install-deps
playwright install chromium

cp ../conf/ojs.py ojs.py

python3 ojs.py \
    --install-language "$language" \
    --admin-username "$admin" \
    --admin-password "$password" \
    --admin-email "$admin_mail" \
    --locale "$locale" \
    --time-zone "$timezone" \
    --files-dir "$data_dir" \
    --database-driver "mysqli" \
    --database-host "localhost" \
    --database-username "$db_user" \
    --database-password "$db_pwd" \
    --database-name "$db_name" \
    --oai-repository-id "$domain" \
    --url "$domain$path"

deactivate
ynh_safe_rm ojs_install

}

format_timezone() {
    local timezone="$1"
    if [[ -z "$timezone" ]]; then
        echo "UTC"
        return
    fi

    offset=$(TZ="$timezone" date +%z)
    if [[ -z "$offset" ]]; then
        echo "UTC"
        return
    fi

    offset_formatted="${offset:0:3}:${offset:3:2}"

    city=$(echo "$timezone" | awk -F/ '{print $NF}')

    echo "${city} (${offset_formatted})"
}
