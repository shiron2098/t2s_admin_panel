<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../common/MYSQLConnect.php';
require_once __DIR__ . '/../../config/t2s_users.php';
require_once __DIR__ . '/../../config/token_and_refresh.php';

header("Access-Control-Allow-Origin: * ");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use \Firebase\JWT\JWT;

abstract class abstractFunctionAut extends MYSQLConnect
{

    private $password;
    private $guild;


    public function check($obj)
    {
        if (!empty($obj) || !empty($obj->email) && !empty($obj->password)) {
            try{
            $stmt = $this->pdo->prepare("SELECT userGlobalKey FROM users WHERE email=?");
            $stmt->execute(array($obj->email));
                if( !$stmt ){
                    throw new Exception('Query Failed');
                }
            }
            catch(Exception $e){
                log::logInsert('not get users with this email',log_file_authentication,ERROR);
                echo $e->getMessage();
            }
            $userid = $stmt->fetchColumn();
            if (!empty($userid)) {
                $_SESSION['USERID']=$userid;
                $this->CheckEmailPassword($obj);
            } else {
                $_SESSION['USERID'] = rand(1, 10000);
                $this->CheckEmailPassword($obj);
            }
        } else {
            log::logInsert('not go data email and password CHECK REQUEST',log_file_authentication,ERROR);
            http_response_code(401);
        }
    }

    public function CheckEmailPassword($json_obj)
    {
        if (isset($json_obj->email) && !empty($json_obj->email) && isset($json_obj->password) && !empty($json_obj->password)) {
            try{
            $stmt = $this->pdo->prepare("SELECT email,password_hash,userGlobalKey,requireToChangePassword FROM users u
                                      left join refresh_tokens r on r.id = u.userGlobalKey WHERE email=?");
            $stmt->execute(array($json_obj->email));
                if( !$stmt ){
                    throw new Exception('ERROR result null invalid email');
                }
            }
            catch(Exception $e){
                echo $e->getMessage();
                log::logInsert($e->getMessage(),log_file_authentication,ERROR);
            }
            $DATE = $stmt->fetchAll(PDO::FETCH_OBJ);
            if (!empty($DATE)) {
                foreach ($DATE as $emailandpass) {
                    $password = password_verify($json_obj->password, $emailandpass->password_hash);
                    if ($json_obj->email === $emailandpass->email && $password === true) {
                        $this->guild = $this->guid();
                        switch ($emailandpass->requireToChangePassword) {
                            case 0:
                                $changes = 'false';
                                break;
                            case 1:
                                $changes = 'true';
                                break;
                        }
                        $jwtRefresh = $this->CreateTokenKeyRefresh($this->guild, $json_obj);
                        $jwtAccess = $this->CreateTokenKeyAcess();
                        if (!empty($jwtRefresh)) {
                            $output = array(
                                'accessToken' => $jwtAccess,
                                'refreshToken' => $jwtRefresh,
                                'userGlobalKey' => (string)$_SESSION['USERID'],
                                'requireToChangePassword' => $changes,
                            );
                            echo json_encode($output);
                        } else {
                            log::logInsert('refresh create func(CreateTokenKeyRefresh)',log_file_authentication,ERROR);
                            header('http/1.0 401 Unauthorized');
                        }
                    }else {
                        log::logInsert('invalid password ',log_file_authentication,ERROR);
                      header('http/1.0 401 Forbidden');
                    }
                }
            }else{
                log::logInsert('user DATA empty!',log_file_authentication,ERROR);
                header('http/1.0 403 Forbidden');
            }
        }
    }

    protected function CreateTokenKeyRefresh($guild, $json_obj)
    {
        $keymysql = null;
        try {
            if (isset($json_obj->email)) {
                if (empty($keymysql)) {
                        $sql = "INSERT INTO refresh_tokens (user_token_id,token_key) VALUES (?,?)";
                        $stmt = $this->pdo->prepare($sql);
                        $stmt->execute([$_SESSION['USERID'], $guild]);
                        $keymysql = $guild;
                        $_SESSION['ID'] = $this->pdo->lastInsertId();
                        if (!$stmt) {
                            throw new Exception('ERROR insert global key from refresh_token');
                        }
                }
            } else if (isset($json_obj->idstring)) {
                    $sql = "update refresh_tokens set token_key=? where id =?";
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute(array($guild, $json_obj->idstring));
                    if (!$stmt) {
                        throw new Exception('ERROR update global key from refresh_token');
                    }
            }
            if (!empty($keymysql)) {
                return $this->refreshToken($keymysql);
            } else {
                return $this->refreshToken($guild);
            }
        } catch (Exception $e) {
            log::logInsert($e->getMessage(),log_file_authentication,ERROR);
            echo $e->getMessage();
        }
    }
     protected function CreateTokenKeyAcess()
     {
         return $this->token(userglobalkey);
     }

    protected function guid()
    {
        if (function_exists('com_create_guid') === true)
            return trim(com_create_guid(), '{}');

        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    protected function token($key)
    {
        if(!empty($key)) {
            $time = strtotime(tokenaccess, time());
            $token = array(
                "iss" => $_SERVER['SERVER_NAME'],
                "ext" => $time,
                "idstring" => $_SESSION['ID'],
                "id" => $_SESSION['USERID'],
            );
            JWT::$leeway = 3600; // $leeway in seconds
            $jwt = JWT::encode($token, $key);
            return $jwt;
        }else{
            header('http/1.0 403 Forbidden');
        }
    }
    protected function refreshToken($key){
        if(!empty($key)) {
            $time = strtotime(tokenrefresh, time());
            $token = array(
                "iss" => $_SERVER['SERVER_NAME'],
                "ext" => $time,
                "id " => $_SESSION['USERID'],
            );
            JWT::$leeway = 3600; // $leeway in seconds
            $jwt = JWT::encode($token, $key);
            return $jwt;
        }else{
            header('http/1.0 403 Forbidden');
        }
    }

}



