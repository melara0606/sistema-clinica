<?php
  use \Psr\Http\Message\ResponseInterface as Response;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  
  include_once 'myphp-backup.php';

  define("DB_USER", 'root');
  define("DB_PASSWORD", '');
  define("DB_NAME", 'clinica-db');
  define("DB_HOST", 'localhost');
  define("BACKUP_DIR", __DIR__."/archivos/");
  define("TABLES", '*'); 
  define("CHARSET", 'utf8');
  define("GZIP_BACKUP_FILE", true);

  $app->get('/backup/db', function(Request $request, Response $response){
    error_reporting(E_ALL);
    set_time_limit(900);

    if (php_sapi_name() != "cli") {
      echo '<div style="font-family: monospace;">';
    }
    $backupDatabase = new Backup_Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $result = $backupDatabase->backupTables(TABLES, BACKUP_DIR) ? 'OK' : 'KO';
    $backupDatabase->obfPrint('Backup result: ' . $result, 1);
    if (php_sapi_name() != "cli") {
      echo '</div>';
    }
  });