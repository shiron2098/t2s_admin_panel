<?php
require_once __DIR__ . '/../../common/MYSQLConnect.php';

class route_and_group_and_sort_function extends  MYSQLConnect
{



    protected $groupkey;
    protected $routekey;
    protected $noroute;


    protected function route($datenum,$table)
    {
        $result = mysqli_query(
            MYSQLConnect::$linkConnectT2S,
            "SELECT  r.rte_id as rte_id FROM $table dac
                                  JOIN routes r on r.rte_id = dac.route_id
                  WHERE date_num = $datenum"
        );
        if (!empty($result)) {
            foreach ($result as $date) {
                foreach ($date as $id) {
                    $array[] = $id;
                }
            }
        } else {
            log::logInsert('result null route_chart_per_collection in table for route #' . $datenum,log_file_route_name_and_folder,ERROR);
        }
        if (!empty($array['0'])) {
            $stringkey = "'" . implode("','", $array) . "'";
            return $stringkey;
        }else{
            log::logInsert('array null route_chart_per_collection in table for route #' . $datenum,log_file_route_name_and_folder,ERROR);
        }
    }
    protected function groupKey($groupkey){
        if(!empty($groupkey)&&isset($groupkey)) {
            $stringkey = "'" . implode("','", $groupkey) . "'";
            $result = mysqli_query(
                MYSQLConnect::$linkConnectT2S,
                "SELECT  rte_list FROM groups
                  WHERE groups.rte_grp_id IN ($stringkey)"
            );
            if(!empty($result)) {
                foreach ($result as $data) {
                    foreach ($data as $data2) {
                        $array[] = $data2;
                    }
                }
                $stringkey = "'" . implode("','", $array) . "'";
                return $stringkey;
            }else{
                log::logInsert('result null group_key_per_collection in table for group #' . $groupkey,log_file_group_name_and_folder,ERROR);
            }
        }else {
            log::logInsert('group_key_per_collection null check request' . $groupkey,log_file_group_name_and_folder,ERROR);
        }
    }
    protected function routeImplode($routekey){
        $stringkey = "'" . implode("','", $routekey) . "'";
        return $stringkey;
    }
    protected function CheckRouteAndGroup($datenum, $route,$group,$table)
    {
            if (!empty($route) && !empty($group)) {
                $this->groupkey = $this->groupkey($group);
                $this->routekey = $this->routeImplode($route);
                return $this->routekey . ',' . $this->groupkey;
                 }
            if (!empty($route)) {
                 return $this->routekey = $this->routeImplode($route);
            } else {
                $this->routekey = [];
            }
            if (!empty($group)) {
               return $this->groupkey = $this->groupkey($group);
            } else {
                $this->groupkey = [];
            }
            if (empty($route) && empty($group)) {

                return $this->route($datenum, $table);
            }
    }
    public function columnsort($arraysort){
        foreach($arraysort as $datasorting) {
            switch ($datasorting->field) {
                case 'posCode':
                    $columnsorting = poscode;
                    break;
                case 'posDescription':
                    $columnsorting = posdescription;
                    break;
                case 'customerCode':
                    $columnsorting = customerCode;
                    break;
                case 'customerDescription':
                    $columnsorting = customerDescription;
                    break;
                case 'collectionsValue':
                    $columnsorting = distcollection;
                    break;
                case 'address':
                    $columnsorting = address;
                    break;
                case 'codeRoute':
                    $columnsorting = route_code;
                    break;
                case 'codeDescription':
                    $columnsorting = route_description;
                    break;
                case 'routerCode':
                    $columnsorting = route_code;
                    break;
                case 'routeDescription':
                    $columnsorting = route_description;
                    break;
                case 'posGlobalKey':
                    $columnsorting = posGlobalKey;
                    break;
                case 'productGlobalKey':
                    $columnsorting = productGlobalKey;
                    break;
                case 'productCode':
                    $columnsorting = productCode;
                    break;
                case 'productDescription':
                    $columnsorting = productDescription;
                    break;
                case 'quantity':
                    $columnsorting = quantity;
                    break;

            }
        }
        return $columnsorting;
    }
}