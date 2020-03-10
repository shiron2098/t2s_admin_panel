<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/abstractFunctionAut.php';
require_once __DIR__ . '/../../config/t2s_users.php';

use \Firebase\JWT\JWT;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


class refresh extends abstractFunctionAut
{
    public function __construct()
    {
       $this->DbConnectAuthencation();
    }

    public function fieldToken($jsonObj)
    {
        if (!empty($jsonObj->refreshToken) && isset($jsonObj->refreshToken)) {
            $acess = $jsonObj->accessToken;
            $decodedacess = JWT::decode($acess, userglobalkey, array('HS256'));
            $key = $this->tokenKey($decodedacess->idstring);
            try {
                $decoded = JWT::decode($jsonObj->refreshToken, $key, array('HS256'));
                if ($decoded->ext > time()) {
                    $newAccess = $this->CreateTokenKeyAcess();
                    $newRefresh = $this->CreateTokenKeyRefresh($this->guid(),$decodedacess);
                    if (!empty($newRefresh)&&!empty($newAccess)) {
                        $output = array(
                            'accessToken' => $newAccess,
                            'refreshToken' => $newRefresh,
                            'userGlobalKey' => (string)$decodedacess->id,
                        );
                        echo json_encode($output);
                    }else {
                        log::logInsert('new-refresh or new-access null CHECK REFRESH',log_file_authentication,ERROR);
                    }
                }else {
                    $this->deletetoken($decodedacess->idstring);
                    log::logInsert('refresh time expired' . $decoded->id ,log_file_authentication,ERROR);
                    http_response_code(403);
                    $text = 'refresh time expired';
                    return $text;
                }
            } catch (Exception $e) {
                http_response_code(401);
                log::logInsert($e->getMessage() ,log_file_authentication,ERROR);
                echo json_encode(array(
                    "message" => "Access denied.",
                    "error" => $e->getMessage()
                ));
            }
        }
    }
        public function tokenKey($id)
        {
            try {
                $stmt = $this->pdo->prepare("SELECT token_key FROM refresh_tokens
                                      where id=?"
                );
                $stmt->execute(array($id));
                 $keymysql = $stmt->fetchColumn();

                return $keymysql;

            } catch (Exception $e) {
                echo $e->getMessage();
                log::logInsert($e->getMessage() . '  '. $id ,log_file_authentication,ERROR);
            }
        }
        private function deletetoken($id){
            try {
            $stmt = $this->pdo->prepare("delete FROM refresh_tokens
                                      where id=?"
            );
            $stmt->execute(array($id));
            } catch (Exception $e) {
                log::logInsert($e->getMessage() .'  ' . $id ,log_file_authentication,ERROR);
                echo $e->getMessage();
            }
        }
}

$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str);
$a= new refresh();
$a->fieldToken($json_obj);