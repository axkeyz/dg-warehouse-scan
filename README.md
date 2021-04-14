# DG Warehouse Scan

An app using a custom-created microframework for the sole purpose of scanning things more easily.

Has been kinda broken into pieces to make simple apps more easily in the future as well (HINT: The Query class is pretty useful).

## Installation guide

1. Run `git clone https://github.com/axkeyz/dg-warehouse-scan`
2. Check the .htaccess works for you. It should be writing the /public folder to your / root url. You may need to change the second line to whatever folder you currently hold your database in. `RewriteBase /your-project-folder-here`
3. Add a config.php file to the route of your project. It should look like this:
```php
<?php

# Setup configuration files
define('DB_HOST', 'Your_database_IP_address,port_if_applicable'); # Your_database_IP_address will work by itself if a default port is used
define('DB_DATABASE', 'Your_database_table_name_here');
define('DB_USERNAME', 'Your_database_username');
define('DB_PASSWORD', 'Your_database_password');

define('APP_DEBUG', FALSE); # Set to TRUE if you wish to see PHP errors. You will still see SQLSRV issues from bad queries.
define('APP_FOLDER', 'your-project-folder-here'); # If your project does not live at the root of your webserver files, put its folder name here.
```