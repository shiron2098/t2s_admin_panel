<?php
require_once __DIR__ . '/route_and_group_and_sort_function.php';
require_once __DIR__ . '/../../common/log/log.php';

class stops_function extends  route_and_group_and_sort_function
{
    public function __construct()
    {
        $this->connectT2S_dashboard();
    }

    public function daily_missed_stops($datenum,$routekey,$groupkey)
    {
        $route = $this->CheckRouteAndGroup($datenum, $routekey, $groupkey,table_daily_stops);
        $route ='"' . $route . '"';
        $sql_stops = "CALL chart_stops('$datenum',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_stops);
        if (!empty($result)) {
            foreach ($result as $date)
                return $date;
        } else {
            return null;
        }
    }
    public function daily_stopsAVG($datetime, $datetimeavg,$routekey,$groupkey){
        $route = $this->CheckRouteAndGroup($datetime, $routekey, $groupkey,table_daily_stops);
        $route ='"' . $route . '"';
        $sql_stops_avg_week_and_month = "CALL chart_stops_avg_week_and_month('$datetimeavg','$datetime',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_stops_avg_week_and_month);
        if ($result !== false) {
            $row = mysqli_fetch_assoc($result);
            $this->connectT2S_dashboard();
            $sql_stops_avg_day = "CALL chart_stops_avg_day('$datetime',$route)";
            $result2 = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_stops_avg_day);
            $row2 = mysqli_fetch_assoc($result2);
        }
        if ($result !== false && $result2 !== false) {
            foreach ($row2 as $column => $value) {
                switch ($column) {
                    case 'missed_stops':
                        if ($row['missedstops'] <= $row2['missed_stops']) {
                            $upandown[] = 'down';
                            break;
                        } else {
                            $upandown[] = 'up';
                            break;
                        }
                    case 'out_of_schedule_stops':
                        if ($row['outstops'] <= $row2['out_of_schedule_stops']) {
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
            log::logInsert('result null in daily_stopsAVG date #' . $datetime,log_file_stops_name_and_folder,ERROR);
        }
    }
    public function daily_missed_stops_CALENDAR($datetime,$routekey,$groupkey,$missed){
        $route = $this->CheckRouteAndGroup($datetime, $routekey, $groupkey,table_daily_stops);
        $route ='"' . $route . '"';
        $sql_stops_calendar = "CALL chart_stops_avg_calendar('$datetime',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_stops_calendar);
        if (!empty($result)) {
            foreach ($result as $date) {
                if (is_null($date['missed_stops'])) {
                    return null;
                }
                if ($date['missed_stops'] <= $missed) {
                    $value['date'] = $datetime;
                    $value['value'] = 'ok';
                    return $value;
                } else {
                    $value['date'] = $datetime;
                    $value['value'] = 'alert';
                    return $value;
                }
            }
        } else {
            log::logInsert('result null in daily_missed_stops_CALENDAR date #' . $datetime,log_file_stops_name_and_folder,ERROR);
        }
    }
    public function daily_array_stops_collection($date,$offset,$count,$routekey, $groupkey, $columnsorting,$sort){
        $route = $this->CheckRouteAndGroup($date, $routekey, $groupkey,table_daily_stops);
        $route ='"' . $route . '"';
        $sql_stops_per_collection = "CALL chart_stops_per_collection('$date','$columnsorting','$sort',$offset,$count,$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_stops_per_collection);
        if (!empty($result)) {
            foreach ($result as $data) {
                $stringAdress = $data['zip'] . ',' . $data['state'] . ',' . $data['city'] . ',' . $data['address_1'] . ',' . $data['address_2'];
                if(empty($data['zip'])&&empty($data['state'])&&empty($data['city'])&&empty($data['address_1'])&&empty($data['address_2'])){
                    $stringAdress = null;
                }
                $dataPOS = array(
                    'routeCode' => $data['codeRoute'],
                    'routeDescription' => $data['routeDescription'],
                    'posGlobalKey' => $data['posGlobalKey'],
                    'customerCode' => $data['customerCode'],
                    'customerDescription' => $data['customerDescription'],
                    'posCode' => $data['posCode'],
                    'posDescription' => $data['posDescription'],
                    'address' => $stringAdress,
                );
                $array[] = $dataPOS;
            }
            return $array;
        } else {
            log::logInsert('result null in daily_array_stops_per_collection date #' . $date,log_file_stops_name_and_folder,ERROR);
        }
    }
    public function daily_pos_count($date,$routekey,$groupkey){
        $route = $this->CheckRouteAndGroup($date, $routekey, $groupkey,table_daily_stops);
        $route ='"' . $route . '"';
        $sql_pos_count = "CALL chart_stops_per_collection_count('$date',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_pos_count);
        if (!empty($result)) {
            foreach ($result as $date) {
                foreach ($date as $item) {
                    $array = $item;
                }
            }
            return $array;
        } else {
            log::logInsert('result null in daily_pos_count date #' . $date,log_file_stops_name_and_folder,ERROR);
        }
    }
    public function __destruct()
    {
        mysqli_close(MYSQLConnect::$linkConnectT2S);
    }

}