<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/log/log.php';
require_once __DIR__ . '/log/logbasic.php';

class MYSQLConnect
{
    const NoConnect = 'incorrect password and username for MYSQL';
    protected $linkConnect;
    public static $linkConnectT2S;
    protected $pdo;
    protected $dsn;

    public function connectT2S_dashboard(){
        try {
            include_once __DIR__ . '/../config/t2s_bi_dashboard.php';
            if (defined('host') && defined('user') && defined('password') && defined('database_t2s_bi_dashboard')) {
                $link = mysqli_connect(
                    host,
                    user,
                    password,
                    database_t2s_bi_dashboard
                ) or die (static::NoConnect);
                return static::$linkConnectT2S = $link;
            }
        }catch(SQLiteException $e){
            log::logInsert('connect to database t2s_jobs',log_file_authentication,ERROR);
            $e->getMessage();
        }

    }
    protected function DbConnectAuthencation()
    {
        try {
            include_once __DIR__ . '/../config/t2s_users.php';
            if (defined('host') && defined('user') && defined('password') && defined('database_t2s_users')) {
                $this->dsn = "mysql:host=" . host . ";dbname=" . database_t2s_users . ";";
                $opt = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                $this->pdo = new PDO($this->dsn, user, password, $opt);
                return $this->pdo;
            } else {
                trigger_error('constant not found');
            }
        } catch (PDOException $e) {
            log::logInsert($e->getMessage(),log_file_authentication, ERROR);
            $e->getMessage();
        }
    }

}