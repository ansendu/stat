<?php

namespace Statistics\Modules;

use Statistics\Config;

function phpsummary($module, $interface, $date, $start_time, $offset)
{
    $group = isset($_GET['group']) ? $_GET['group'] : '';
    $groups = getGroupByModule();

    if (!in_array($group, $groups)) {
        echo '错误的group';

        return;
    }


    switch ($date) {
        case 'lastweek':
            //tab
            $tabFlag = 'lastweek';
            //get date string
            $startTime = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - date("w") + 1 - 7, date("Y")));
            $endTime = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - date("w") + 7 - 7, date("Y")));
            $date_str = $startTime . '～' . $endTime;

            //get date list
            $timeLists = array();
            for ($i = 1; $i <= 7; $i++) {
                $time = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - date("w") + $i - 7, date("Y")));
                $timeLists[] = $time;
            }
            //global availability
            $global_avail = $global_avail_time = array();
            foreach ($timeLists as $t) {
                $global_avail_time[$t] = availability($group, $t);
            }
            $global_avail = array('total_count' => array_sum_field($global_avail_time, 'total_count'), 'success_count' => array_sum_field($global_avail_time, 'success_count'), 'fail_count' => array_sum_field($global_avail_time, 'fail_count'),);
            // 总体成功率
            $global_rate = $global_avail['total_count'] ? round((($global_avail['total_count'] - $global_avail['fail_count']) / $global_avail['total_count']) * 100, 4) : 100;


            //整体分析
            $statDays = array();
            foreach ($timeLists as $t) {
                $statDays[$t] = monthsummary($group, $t);
            }
            $statAll = array();
            $statAll = statallByMonth($statDays);


            //echo "<pre>";
            //print_r($global_avail);

            break;


        case 'lastmonth':
            //tab
            $tabFlag = 'lastmonth';
            $date = getlastMonthDays(date('Y-m'));
            //$date = array('2017-04-01','2017-04-30');
            $startTime = $date[0];
            $endTime = $date[1];
            $date_str = $startTime . '～' . $endTime;

            //get date list
            $timeLists = array();
            $days = date('t', strtotime($startTime));

            $starTimeStr = strtotime($startTime);
            $endTimeStr = strtotime($endTime);

            while ($starTimeStr <= $endTimeStr) {
                $timeLists[] = date('Y-m-d', $starTimeStr);
                $starTimeStr += 86400;
            }
            //print_r($timeLists);

            //global availability
            $global_avail = $global_avail_time = array();
            foreach ($timeLists as $t) {
                $global_avail_time[$t] = availability($group, $t);
            }
            $global_avail = array('total_count' => array_sum_field($global_avail_time, 'total_count'), 'success_count' => array_sum_field($global_avail_time, 'success_count'), 'fail_count' => array_sum_field($global_avail_time, 'fail_count'),);
            // 总体成功率
            $global_rate = $global_avail['total_count'] ? round((($global_avail['total_count'] - $global_avail['fail_count']) / $global_avail['total_count']) * 100, 4) : 100;

            //整体分析
            $statDays = array();
            foreach ($timeLists as $t) {
                $statDays[$t] = monthsummary($group, $t);
            }
            $statAll = array();
            $statAll = statallByMonth($statDays);




            //print_r($statAll);

            break;
        default:

            break;

    }
    include ST_ROOT . '/Views/header.tpl.php';
    include ST_ROOT . '/Views/phpsummary.tpl.php';
    include ST_ROOT . '/Views/footer.tpl.php';
}


function getlastMonthDays($date)
{
    $timestamp = strtotime($date);
    $firstday = date('Y-m-01', strtotime(date('Y', $timestamp) . '-' . (date('m', $timestamp) - 1) . '-01'));
    $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));

    return array($firstday, $lastday);
}
