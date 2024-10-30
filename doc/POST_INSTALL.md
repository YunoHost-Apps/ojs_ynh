**Database**
Username = __DB_USER__
Password = "__DB_PWD__"
Name = __DB_NAME__

**Data directory**
__DATA_DIR__

**SMTP settings**
SMTP_LOGIN=__APP__
SMTP_PASSWORD=__MAIL_PWD__
SMTP_FROM_ADDRESS= <__APP__@__DOMAIN__>

curl -X POST -d "installLanguage=$language" -d "adminUsername=$admin" -d "adminPassword=$password" -d "adminPassword2=$password" -d "adminEmail=$admin_mail" -d "locale=$locale" -d "timeZone=$timezone" -d "filesDir=$data_dir" -d "databaseDriver=mysqli" -d "databaseHost=localhost" -d "databaseUsername=$db_user" -d "databasePassword=$db_pwd" -d "databaseName=$db_name" -d "oaiRepositoryId=ojs.local" -d "submitFormButton=1" -i $domain$path/index.php/index/install/install  -v  -o response.html
