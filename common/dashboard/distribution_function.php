<?php
require_once __DIR__ . '/route_and_group_and_sort_function.php';

class distribution_function extends  route_and_group_and_sort_function
{
    const ok = 'ok';
    const alert = 'alert';


    public function __construct()
    {
        $this->connectT2S_dashboard();
    }

    public function daily_collection_and_avg($datetime, $datetimeavg,$routekey,$groupkey,$less50,$more50_less75,$more75_less100,$more100_less150,$more150)
    {
        $route = $this->CheckRouteAndGroup($datetime, $routekey, $groupkey,table_collection_distribution);
        $route ='"' . $route . '"';
        $sql_chart_distribution_avg_week_and_month = "CALL chart_distribution_collection_avg_week_and_month('$datetimeavg','$datetime','$less50','$more50_less75','$more75_less100','$more100_less150','$more150',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_chart_distribution_avg_week_and_month);
        if ($result !== false &&!empty($result)) {
            $dataAll = mysqli_fetch_assoc($result);
            if (!array_filter($dataAll)) {
                return null;
            }
            $this->connectT2S_dashboard();
            $sql_chart_distribution_avg_day = "CALL chart_distribution_collection_avg_day('$datetime','$less50','$more50_less75','$more75_less100','$more100_less150','$more150',$route)";
            $result2 = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_chart_distribution_avg_day);
        }
            if ($result2 !== false &&!empty($result2)) {
                $dataOne = mysqli_fetch_assoc($result2);
                if (!array_filter($dataOne)) {
                    return null;
                }
                $this->connectT2S_dashboard();
                $sql_chart_distribution_avg_sum = "CALL chart_distribution_collection_avg_sum('$datetime','$less50','$more50_less75','$more75_less100','$more100_less150','$more150',$route)";
                $result3 = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_chart_distribution_avg_sum);
            }
        if ($result !== false &&!empty($result) && $result2 !== false &&!empty($result2) && $result3 !== false &&!empty($result3)) {
            $dataSum = mysqli_fetch_assoc($result3);
            if (!array_filter($dataSum)) {
                return null;
            }
            foreach ($dataSum as $column => $value) {
                switch ($column) {
                    case 'less_50':
                        if ($dataAll['less50'] <= $dataOne['less_50']) {
                            $upandown[] = array(
                                'minTresholdSales' => '',
                                'maxTresholdSales' => '50',
                                'levelLessByThreshold' => static::ok,
                                'numberOfPos' => (string)$value,
                                'trend' => 'down');
                            break;
                        } else {
                            $upandown[] = array(
                                'minTresholdSales' => '',
                                'maxTresholdSales' => '50',
                                'levelLessByThreshold' => static::ok,
                                'numberOfPos' => (string)$value,
                                'trend' => 'up');
                            break;
                        }
                    case 'more_50_less_75':
                        if ($dataAll['more50less75'] <= $dataOne['more_50_less_75']) {
                            $upandown[] = array(
                                'minTresholdSales' => '50',
                                'maxTresholdSales' => '75',
                                'levelLessByThreshold' => static::ok,
                                'numberOfPos' => (string)$value,
                                'trend' => 'down');
                            break;
                        } else {
                            $upandown[] = array(
                                'minTresholdSales' => '50',
                                'maxTresholdSales' => '75',
                                'levelLessByThreshold' => static::ok,
                                'numberOfPos' => (string)$value,
                                'trend' => 'up');
                            break;
                        }
                    case 'more_75_less_100':
                        if ($dataAll['more75less100'] <= $dataOne['more_75_less_100']) {
                            $upandown[] = array(
                                'minTresholdSales' => '75',
                                'maxTresholdSales' => '100',
                                'levelLessByThreshold' => static::alert,
                                'numberOfPos' => (string)$value,
                                'trend' => 'down');
                            break;
                        } else {
                            $upandown[] = array(
                                'minTresholdSales' => '75',
                                'maxTresholdSales' => '100',
                                'levelLessByThreshold' => static::alert,
                                'numberOfPos' => (string)$value,
                                'trend' => 'up');
                            break;
                        }
                    case 'more_100_less_150':
                        if ($dataAll['more100less150'] <= $dataOne['more_100_less_150']) {
                            $upandown[] = array(
                                'minTresholdSales' => '100',
                                'maxTresholdSales' => '150',
                                'levelLessByThreshold' => static::alert,
                                'numberOfPos' => (string)$value,
                                'trend' => 'down');
                            break;
                        } else {
                            $upandown[] = array(
                                'minTresholdSales' => '100',
                                'maxTresholdSales' => '150',
                                'levelLessByThreshold' => static::alert,
                                'numberOfPos' => (string)$value,
                                'trend' => 'up');
                            break;
                        }
                    case 'more_150':
                        if ($dataAll['more150'] <= $dataOne['more_150']) {
                            $upandown[] = array(
                                'minTresholdSales' => '150',
                                'maxTresholdSales' => '',
                                'levelLessByThreshold' => static::alert,
                                'numberOfPos' => (string)$value,
                                'trend' => 'down');
                            break;
                        } else {
                            $upandown[] = array(
                                'minTresholdSales' => '150',
                                'maxTresholdSales' => '',
                                'levelLessByThreshold' => static::alert,
                                'numberOfPos' => (string)$value,
                                'trend' => 'up');
                            break;
                        }
                }
            }
            return $upandown;
        }else{

            log::logInsert('result null in daily_collection_distribution_and_avg date #' . $datetime,log_file_distribution_name_and_folder,ERROR);
        }
    }

    public function daily_distribution_CALENDAR($dateStart,$routekey,$groupkey,$distribution)
    {
        $route = $this->CheckRouteAndGroup($dateStart, $routekey, $groupkey,table_collection_distribution);
        $route ='"' . $route . '"';
        $sql_chart_distribution_calendar = "CALL chart_distribution_collection_calendar('$dateStart',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_chart_distribution_calendar);
        if ($result !== false && !empty($result)) {
            foreach ($result as $date)
                $array1= array(
                    'summaless_50'=> $date['less_50'],
                    'summaless_75'=> $date['more_50_less_75'],
                );
            $array2= array(
                'summaless_50'=> $date['less_50'],
                'summaless_75'=> $date['more_50_less_75'],
                'summaless_100'=> $date['more_75_less_100'],
                'summaless_150'=> $date['more_100_less_150'],
                'summamore_150'=> $date['more_150'],
            );
            $summ1 = array_sum($array1);
            $summ2 = array_sum($array2);
            $procentandsumm2 = $summ2 / $distribution;
            if($summ1 < $procentandsumm2) {
                $value['date']=$dateStart;
                $value['value']='ok';
                return $value;
            }elseif($summ1> $procentandsumm2){
                $value['date']=$dateStart;
                $value['value']='alert';
                return $value;
            }
        } else {
            log::logInsert('result null in daily_distribution_calendar date #' .$dateStart,log_file_distribution_name_and_folder,ERROR);
        }
    }

    public function daily_distribution_per_collection($date, $offset, $count, $columnsorting,$minsales,$maxsales,$routekey,$groupkey,$collection_value,$collection_50,$collection_150,$sort)
    {
        $route = $this->CheckRouteAndGroup($date, $routekey, $groupkey,table_collection_distribution);
        if ($maxsales == '50') {
            $collection = $collection_50 . $maxsales;
        } else if ($maxsales > '50'){
            $collection = $collection_150 . $minsales . ' ' . $collection_50 . $maxsales;
        } else if (isset($minsales) && !isset($maxsales) && empty($maxsales) || $minsales == '0') {
            $collection = $collection_150 . $minsales;
        }
        $route ='"' . $route . '"';
        $sql_chart_distribution_per_collection = "CALL chart_distribution_collection_per_collection('$date','$collection_value','$columnsorting','$sort','$offset','$count','$collection',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_chart_distribution_per_collection);
        if ($result !== false && !empty($result)) {
            foreach ($result as $data) {
                $stringAdress = $data['zip'] . ',' . $data['state'] . ',' . $data['city'] . ',' . $data['address_1'] . ',' . $data['address_2'];
                if (empty($data['zip']) && empty($data['state']) && empty($data['city']) && empty($data['address_1']) && empty($data['address_2'])) {
                    $stringAdress = null;
                }
                $dataPOS = array(
                    'routeCode' => $data['codeRoute'],
                    'routeDescription' => $data['routeDescription'],
                    'posCode' => $data['posCode'],
                    'posGlobalKey' => $data['posGlobalKey'],
                    'posDescription' => $data['posDescription'],
                    'customerCode' => $data['customerCode'],
                    'customerDescription' => $data['customerDescription'],
                    'collectionsValue' => $data['collection'],
                    'address' => $stringAdress,
                );
                $array[] = $dataPOS;
            }
            return $array;
        } else {
            log::logInsert('result null in daily_distribution_per_collection date #' .$date,log_file_distribution_name_and_folder,ERROR);
        }
    }

    public function daily_distribution_count($date, $minsales, $maxsales,$routekey,$groupkey,$collection_50,$collection_150)
    {
        $route = $this->CheckRouteAndGroup($date, $routekey, $groupkey,table_collection_distribution);
        if ($maxsales == '50') {
            $collection = $collection_50 . $maxsales;
        } else if ($maxsales > '50'){
            $collection = $collection_150 . $minsales . ' ' . $collection_50 . $maxsales;
        } else if (isset($minsales) && !isset($maxsales) && empty($maxsales) || $minsales == '0') {
            $collection = $collection_150 . $minsales;
        }
        $route ='"' . $route . '"';
        $sql_chart_distribution_per_collection_count = "CALL chart_distribution_collection_per_collection_count('$date','$collection',$route)";
        $result = mysqli_query(MYSQLConnect::$linkConnectT2S, $sql_chart_distribution_per_collection_count);
        if ($result !== false && !empty($result)) {
            foreach($result as $date) {
                $count = $date['count'];
            }
            return $count;
        } else {
            log::logInsert('result null in daily_distribution_count date #' . $date,log_file_distribution_name_and_folder,ERROR);
        }
    }

}