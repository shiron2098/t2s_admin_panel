<?php
require_once __DIR__ . '/route_and_group_and_sort_function.php';

class items_function extends  route_and_group_and_sort_function
{
    public function __construct()
    {
        $this->connectT2S_dashboard();
    }

    public function daily_items($datenum,$routekey,$groupkey){
        $route = $this->CheckRouteAndGroup($datenum, $routekey, $groupkey,table_items_and_stockouts);
        $route ='"' . $route . '"';
        $sql_daily_items = "CALL chart_items('$datenum',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_daily_items);
        if (!empty($result)&& $result !== false) {
            foreach ($result as $date) {
                return $date;
            }
        } else {
            log::logInsert('result null in daily_items date #' . $datenum,log_file_items_name_and_folder,ERROR);
        }
    }
    public function daily_itemsAVG($datetime,$datetimeavg,$routekey,$groupkey){
        $route = $this->CheckRouteAndGroup($datetime, $routekey, $groupkey,table_items_and_stockouts);
        $route ='"' . $route . '"';
        $sql_items_avg_week_and_month = "CALL chart_items_avg_week_and_month('$datetimeavg','$datetime',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_items_avg_week_and_month);
        if ($result !== false && !empty($result)) {
            $row = mysqli_fetch_assoc($result);
            $this->connectT2S_dashboard();
            if(!array_filter($row)) {
                return null;
            }
            $sql_items_avg_day = "CALL chart_items_avg_day('$datetime',$route)";
            $result2 = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_items_avg_day);
            $row2 = mysqli_fetch_assoc($result2);
        }
        if ($result !== false && $result2 !== false && !empty($result) && !empty($result2)) {
            foreach ($row2 as $column => $value) {
                switch ($column) {
                    case 'not_picked':
                        if ($row['notpicked'] <= $row2['not_picked']) {
                            $upandown[] = 'down';
                            break;
                        } else {
                            $upandown[] = 'up';
                            break;
                        }
                }
            }
            return $upandown;
        }else{
            log::logInsert('result null in daily_before_stockouts date #' . $datetime,log_file_items_name_and_folder,ERROR);
        }
    }
    public function daily_items_calendar($dateStart,$routekey,$groupkey,$item){
        $route = $this->CheckRouteAndGroup($dateStart, $routekey, $groupkey,table_items_and_stockouts);
        $route ='"' . $route . '"';
        $sql_items_calendar = "CALL chart_items_calendar('$dateStart',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_items_calendar);
        if (!empty($result)) {
            foreach ($result as $date) {
                if(is_null($date['total_picked'])){
                    return null;
                }
                if ($date['total_picked'] <= $item) {
                    $value['date'] = $dateStart;
                    $value['value'] = 'ok';
                    return $value;
                } else {
                    $value['date'] = $dateStart;
                    $value['value'] = 'alert';
                    return $value;
                }
            }
        } else {
            log::logInsert('result null in daily_items_calendar date #' . $dateStart,log_file_items_name_and_folder,ERROR);
        }
    }
    public function daily_items_per_collection($date, $offset, $count, $routekey, $groupkey, $columnsorting, $sort){
        $route = $this->CheckRouteAndGroup($date, $routekey, $groupkey,table_items_and_stockouts);
        $route ='"' . $route . '"';
        $sql_items_per_collection = "CALL chart_items_per_collection('$date','$columnsorting','$sort',$offset,$count,$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_items_per_collection);
        if (!empty($result)) {
            foreach ($result as $data) {
                $dataPOS = array(
                    'routeCode' => $data['codeRoute'],
                    'routeDescription' => $data['routeDescription'],
                    'posCode' => $data['posCode'],
                    'posDescription' => $data['posDescription'],
                    'productGlobalKey' => $data['productGlobalKey'],
                    'productCode' => $data['productCode'],
                    'productDescription' => $data['productDescription'],
                    'quantity' => $data['quantity'],
                );
                $array[] = $dataPOS;
            }
            return $array;
        } else {
            log::logInsert('result null in daily_items_per_collection date #' . $date,log_file_items_name_and_folder,ERROR);
        }
    }
    public function daily_count_items($date,$routekey,$groupkey){
        $route = $this->CheckRouteAndGroup($date, $routekey, $groupkey,table_items_and_stockouts);
        $route ='"' . $route . '"';
        $sql_items_per_collection_count = "CALL chart_items_per_collection_count('$date',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_items_per_collection_count);
        if (!empty($result && $result !== false)) {
            foreach($result as $date) {
                $count = $date['count'];
            }
            return $count;
        } else {
            log::logInsert('result null in daily_count_items date #' . $date,log_file_items_name_and_folder,ERROR);
        }
    }
    public function __destruct()
    {
        mysqli_close(MYSQLConnect::$linkConnectT2S);
    }

}