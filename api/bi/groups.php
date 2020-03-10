<?php
header('Content-type: application/json');
require_once __DIR__ . '/../../common/dashboard/MYSQL_t2s_bi_Collection.php';


class groups extends MYSQL_t2s_bi_Collection
{
    public function AUT()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        $this->selectkey($authHeader);
        if (!empty($authHeader)) {
            if ($_SESSION['AUT'] === true) {
                $json_str = file_get_contents('php://input');
                $json_obj = json_decode($json_str);
                $this->connectT2S_dashboard();
                if(!empty($json_str)){
                    $this->startFilter($json_obj);
                }else{
                    log::logInsert('post data null  check request',log_file_group_name_and_folder,ERROR);
                    http_response_code(400);
                }
            } else {
                log::logInsert('access_token time*s up',log_file_group_name_and_folder,ERROR);
                http_response_code(403);
            }
        } else {
            log::logInsert('not found HTTP_AUTHORIZATION server',log_file_group_name_and_folder,ERROR);
            http_response_code(401);
        }
    }
    private function startFilter($json)
    {
        if(isset($json->globalKeyCollection)&&!empty($json->globalKeyCollection)) {
            $this->groupFilter($json->globalKeyCollection);
        }else{
            $this->groupAll();
        }

    }

}
$start = new groups();
$start->AUT();