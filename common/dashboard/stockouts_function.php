<?php
require_once __DIR__ . '/route_and_group_and_sort_function.php';

class stockouts_function extends  route_and_group_and_sort_function
{
    public function __construct()
    {
        $this->connectT2S_dashboard();
    }

    public function daily_after_stockouts($datenum,$routekey,$groupkey)
    {
        $route = $this->CheckRouteAndGroup($datenum, $routekey, $groupkey,table_items_and_stockouts);
        $route ='"' . $route . '"';
        $sql_stockouts_after = "CALL chart_stockouts_after('$datenum',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_stockouts_after);
        if (!empty($result) && $result !== false) {
            foreach ($result as $date)
                return $date;
        } else {
            log::logInsert('result null in daily_after_stockouts date #' . $datenum,log_file_stockouts_name_and_folder,ERROR);
        }
    }

    public function daily_before_stockouts($datenum,$routekey,$groupkey)
    {
        $route = $this->CheckRouteAndGroup($datenum, $routekey, $groupkey,table_items_and_stockouts);
        $route ='"' . $route . '"';
        $sql_stockouts_before = "CALL chart_stockouts_before('$datenum',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_stockouts_before);
        if (!empty($result)) {
            foreach ($result as $date)
                return $date;
        } else {
            log::logInsert('result null in daily_before_stockouts date #' . $datenum, log_file_stockouts_name_and_folder,ERROR);
        }
    }

    public function daily_stockoutsAVG($datetime, $datetimeavg,$routekey,$groupkey)
    {
        $route = $this->CheckRouteAndGroup($datetime, $routekey, $groupkey,table_items_and_stockouts);
        $route ='"' . $route . '"';
        $sql_stockouts_avg_week_and_month = "CALL chart_stockouts_avg_week_and_month('$datetimeavg','$datetime',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_stockouts_avg_week_and_month);
        if ($result !== false) {
            $row = mysqli_fetch_assoc($result);
            $this->connectT2S_dashboard();
            $sql_stockouts_avg_week_and_month = "CALL chart_stockouts_avg_day('$datetime',$route)";
            $result2 = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_stockouts_avg_week_and_month);
            $row2 = mysqli_fetch_assoc($result2);
        }
        if ($result !== false && $result2 !== false && !empty($result)&&!empty($result2)) {
            foreach ($row2 as $column => $value) {
                switch ($column) {
                    case 'before_stockouts':
                        if ($row['beforestockouts'] <= $row2['before_stockouts']) {
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
            log::logInsert('result null in daily_stockoutsAVG date' . $datetime,log_file_stockouts_name_and_folder,ERROR);
        }
    }

    public function daily_stockouts_CALENDAR($dateStart,$routekey,$groupkey,$stockouts)
    {
        $route = $this->CheckRouteAndGroup($dateStart, $routekey, $groupkey,table_items_and_stockouts);
        $route ='"' . $route . '"';
        $sql_stockouts_calendar = "CALL chart_stockouts_calendar('$dateStart',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_stockouts_calendar);
        if (!empty($result)) {
            foreach ($result as $date)
                if($date['before_percentage'] <= $stockouts){
                    $value['date']=$dateStart;
                    $value['value']='ok';
                    return $value;
                }
                else{
                    $value['date']=$dateStart;
                    $value['value']='alert';
                    return $value;
                }
        } else {
            log::logInsert('result null in daily_stockouts_CALENDAR date #' .$dateStart,log_file_stockouts_name_and_folder,ERROR);
        }
    }

    public function daily_stockouts_per_collection($date, $offset, $count, $routekey, $groupkey, $columnsorting,$sort)
    {
        $route = $this->CheckRouteAndGroup($date, $routekey, $groupkey,table_items_and_stockouts);
        $route ='"' . $route . '"';
        $sql_stockouts_per_collection = "CALL chart_stockouts_per_collection('$date','$columnsorting','$sort',$offset,$count,$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_stockouts_per_collection);
        if (!empty($result) && $result !== false) {
            foreach ($result as $data) {
                $dataPOS = array(
                    'routeCode' => $data['codeRoute'],
                    'routeDescription' => $data['routeDescription'],
                    'productCode' => $data['productCode'],
                    'productDescription' => $data['productDescription'],
                    'posCode' => $data['posCode'],
                    'posDescription' => $data['posDescription'],
                    'customerCode' => $data['customerCode'],
                    'customerDescription' => $data['customerDescription'],
                );
                $array[] = $dataPOS;
            }
            return $array;
        } else {
            log::logInsert('result null in daily_stockouts_per_collection date #' . $date,log_file_stockouts_name_and_folder,ERROR);
        }
    }

    public function daily_stockouts_COUNT($date,$routekey,$groupkey)
    {
        $route = $this->CheckRouteAndGroup($date, $routekey, $groupkey,table_items_and_stockouts);
        $route ='"' . $route . '"';
        $sql_stockouts_per_collection_count = "CALL chart_stockouts_per_collection_count('$date',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_stockouts_per_collection_count);
        if (!empty($result)) {
            foreach($result as $date) {
                $count = $date['countPosid'];
            }
            return $count;
        } else {
            log::logInsert('result null in daily_stockouts_count date #' . $date,log_file_stockouts_name_and_folder,ERROR);
        }
    }
    public function __destruct()
    {
        $this->connectT2S_dashboard();
    }
}