<?php

namespace Statistics\Modules;

use Statistics\Config;
use Bootstrap\Api;

/**
 * 日志二分查找法
 * @param int $start_point
 * @param int $end_point
 * @param int $time
 * @param fd $fd
 * @return int
 */
function binarySearch($start_point, $end_point, $time, $fd)
{
    if ($end_point - $start_point < 65535) {
        return $start_point;
    }

    // 计算中点
    $mid_point = (int)(($end_point + $start_point) / 2);

    // 定位文件指针在中点
    fseek($fd, $mid_point - 1);

    // 读第一行
    $line = fgets($fd);
    if (feof($fd) || false === $line) {
        return $start_point;
    }

    // 第一行可能数据不全，再读一行
    $line = fgets($fd);
    if (feof($fd) || false === $line || trim($line) == '') {
        return $start_point;
    }

    // 判断是否越界
    $current_point = ftell($fd);
    if ($current_point >= $end_point) {
        return $start_point;
    }

    // 获得时间
    $tmp = explode("\t", $line);
    $tmp_time = strtotime($tmp[0]);

    // 判断时间，返回指针位置
    if ($tmp_time > $time) {
        return binarySearch($start_point, $current_point, $time, $fd);
    } elseif ($tmp_time < $time) {
        return binarySearch($current_point, $end_point, $time, $fd);
    } else {
        return $current_point;
    }
}

function formatSt($str, $date, &$code_map)
{
    // time:[suc_count:xx,suc_cost_time:xx,fail_count:xx,fail_cost_time:xx]
    $st_data = $code_map = array();
    $st_explode = explode("\n", $str);
    // 汇总计算

    foreach ($st_explode as $line) {
        // line = IP time suc_count suc_cost_time fail_count fail_cost_time code_json
        $line_data = explode("\t", $line);
        if (!isset($line_data[5])) {
            continue;
        }
        $time_line = $line_data[1];
        $time_line = ceil($time_line / 300) * 300;
        $suc_count = $line_data[2];
        $suc_cost_time = $line_data[3];
        $fail_count = $line_data[4];
        $fail_cost_time = $line_data[5];
        $tmp_code_map = json_decode($line_data[6], true);
        if (!isset($st_data[$time_line])) {
            $st_data[$time_line] = array('suc_count' => 0, 'suc_cost_time' => 0, 'fail_count' => 0, 'fail_cost_time' => 0);
        }
        $st_data[$time_line]['suc_count'] += $suc_count;
        $st_data[$time_line]['suc_cost_time'] += $suc_cost_time;
        $st_data[$time_line]['fail_count'] += $fail_count;
        $st_data[$time_line]['fail_cost_time'] += $fail_cost_time;

        if (is_array($tmp_code_map)) {
            foreach ($tmp_code_map as $code => $count) {
                if (!isset($code_map[$code])) {
                    $code_map[$code] = 0;
                }
                $code_map[$code] += $count;
            }
        }
    }
    // 按照时间排序
    ksort($st_data);
    // time => [total_count:xx,suc_count:xx,suc_avg_time:xx,fail_count:xx,fail_avg_time:xx,percent:xx]
    $data = array();
    // 计算成功率 耗时
    foreach ($st_data as $time_line => $item) {
        $data[$time_line] = array('time' => date('Y-m-d H:i:s', $time_line), 'total_count' => $item['suc_count'] + $item['fail_count'], 'total_avg_time' => $item['suc_count'] + $item['fail_count'] == 0 ? 0 : number_format(($item['suc_cost_time'] + $item['fail_cost_time']) / ($item['suc_count'] + $item['fail_count']), 6), 'suc_count' => $item['suc_count'], 'suc_avg_time' => $item['suc_count'] == 0 ? $item['suc_count'] : number_format($item['suc_cost_time'] / $item['suc_count'], 6), 'fail_count' => $item['fail_count'], 'fail_avg_time' => $item['fail_count'] == 0 ? 0 : number_format($item['fail_cost_time'] / $item['fail_count'], 6), 'precent' => $item['suc_count'] + $item['fail_count'] == 0 ? 0 : number_format(($item['suc_count'] * 100 / ($item['suc_count'] + $item['fail_count'])), 4),);
    }
    $time_point = strtotime($date);
    for ($i = 0; $i < 288; $i++) {
        $data[$time_point] = isset($data[$time_point]) ? $data[$time_point] : array('time' => date('Y-m-d H:i:s', $time_point), 'total_count' => 0, 'total_avg_time' => 0, 'suc_count' => 0, 'suc_avg_time' => 0, 'fail_count' => 0, 'fail_avg_time' => 0, 'precent' => 100,);
        $time_point += 300;
    }
    ksort($data);

    return $data;
}

/**
 * 获取模块
 * @return array
 */
function getModules($current_module = '', $dPath, $stDir)
{
    //    $st_dir = Config::$workdataPath . Work::$statisticDir;
    $st_dir = $dPath . $stDir;

    $modules_name_array = array();
    foreach (glob($st_dir . "/*", GLOB_ONLYDIR) as $module_file) {
        $tmp = explode("/", $module_file);
        $module = end($tmp);
        $modules_name_array[$module] = array();
        if ($current_module == $module) {
            $st_dir = $st_dir . $current_module . '/';
            $all_interface = array();
            foreach (glob($st_dir . "*") as $file) {
                if (is_dir($file)) {
                    continue;
                }
                list($interface, $date) = explode(".", basename($file));
                $all_interface[$interface] = $interface;
            }
            $modules_name_array[$module] = $all_interface;
        }
    }

    return $modules_name_array;
}

/**
 * 获得统计数据
 * @param string $module
 * @param string $interface
 * @param int $date
 * @return bool/string
 */
function getStatistic($date, $module, $interface, $dPath, $stDir)
{
    if (empty($module) || empty($interface)) {
        return '';
    }
    // log文件
    //    $log_file = Config::$workdataPath . Work::$statisticDir . "{$module}/{$interface}.{$date}";
    $log_file = $dPath . $stDir . "{$module}/{$interface}.{$date}";

    $handle = @fopen($log_file, 'r');
    if (!$handle) {
        return '';
    }

    // 预处理统计数据，每5分钟一行
    // [time=>[ip=>['suc_count'=>xx, 'suc_cost_time'=>xx, 'fail_count'=>xx, 'fail_cost_time'=>xx, 'code_map'=>[code=>count, ..], ..], ..]
    $statistics_data = array();
    while (!feof($handle)) {
        $line = fgets($handle, 4096);
        if ($line) {
            $explode = explode("\t", $line);
            if (count($explode) < 7) {
                continue;
            }
            list($ip, $time, $suc_count, $suc_cost_time, $fail_count, $fail_cost_time, $code_map) = $explode;
            $time = ceil($time / 300) * 300;
            if (!isset($statistics_data[$time])) {
                $statistics_data[$time] = array();
            }
            if (!isset($statistics_data[$time][$ip])) {
                $statistics_data[$time][$ip] = array('suc_count' => 0, 'suc_cost_time' => 0, 'fail_count' => 0, 'fail_cost_time' => 0, 'code_map' => array(),);
            }
            $statistics_data[$time][$ip]['suc_count'] += $suc_count;
            $statistics_data[$time][$ip]['suc_cost_time'] += round($suc_cost_time, 5);
            $statistics_data[$time][$ip]['fail_count'] += $fail_count;
            $statistics_data[$time][$ip]['fail_cost_time'] += round($fail_cost_time, 5);

            $code_map = json_decode(trim($code_map), true);
            if ($code_map && is_array($code_map)) {
                foreach ($code_map as $code => $count) {
                    if (!isset($statistics_data[$time][$ip]['code_map'][$code])) {
                        $statistics_data[$time][$ip]['code_map'][$code] = 0;
                    }
                    $statistics_data[$time][$ip]['code_map'][$code] += $count;
                }
            }
        } // end if
    } // end while

    fclose($handle);
    ksort($statistics_data);

    // 整理数据
    $statistics_str = '';
    foreach ($statistics_data as $time => $items) {
        foreach ($items as $ip => $item) {
            $statistics_str .= "$ip\t$time\t{$item['suc_count']}\t{$item['suc_cost_time']}\t{$item['fail_count']}\t{$item['fail_cost_time']}\t" . json_encode($item['code_map']) . "\n";
        }
    }

    return $statistics_str;
}

//根据模块获取所有group
function getGroupByModule($_module = 'php')
{
    $path = array();
    if ($_module == 'php') {
        $path = Config::$apidataPath;
    }

    $dir_array = array();
    foreach (glob($path . "/*") as $dir) {
        $tmp = explode('/', $dir);
        if (end($tmp) == 'default') {
            continue;
        }
        $dir_array[] = end($tmp);
    }

    return $dir_array;
}

//获取work模块这里 未分布式
function getWorkStAndModules($module, $interface, $date)
{
    $read_buffer_array = array('modules' => getModules($module, Config::$workdataPath, Work::$statisticDir), 'statistic' => getStatistic($date, $module, $interface, Config::$workdataPath, Work::$statisticDir));

    $modules_data = isset($read_buffer_array['modules']) ? $read_buffer_array['modules'] : array();
    // 整理modules
    foreach ($modules_data as $mod => $interfaces) {
        if (!isset(\Statistics\Lib\Cache::$workModulesDataCache[$mod])) {
            \Statistics\Lib\Cache::$workModulesDataCache[$mod] = array();
        }
        foreach ($interfaces as $if) {
            \Statistics\Lib\Cache::$workModulesDataCache[$mod][$if] = $if;
        }
    }

    return $read_buffer_array['statistic'];
}

//获取php模块这里 未分布式
function getPhpStAndModules($group, $module, $interface, $date)
{
    $read_buffer_array = array('modules' => getModules($module, Config::$apidataPath . $group . '/', Api::$_statisticDir), 'statistic' => getStatistic($date, $module, $interface, Config::$apidataPath . $group . '/', Api::$_statisticDir));

    $modules_data = isset($read_buffer_array['modules']) ? $read_buffer_array['modules'] : array();
    // 整理modules
    foreach ($modules_data as $mod => $interfaces) {

        if (!isset(\Statistics\Lib\Cache::$phpModulesDataCache[$group])) {
            \Statistics\Lib\Cache::$phpModulesDataCache[$group] = array();
        }

        if (!isset(\Statistics\Lib\Cache::$phpModulesDataCache[$group][$mod])) {
            \Statistics\Lib\Cache::$phpModulesDataCache[$group][$mod] = array();
        }
        foreach ($interfaces as $if) {
            \Statistics\Lib\Cache::$phpModulesDataCache[$group][$mod][$if] = $if;
        }
    }

    return $read_buffer_array['statistic'];
}

//获取php模块 level分布数据
function getPhpLevelRate($group, $date)
{

    $outPut = array();


    //    $outPut = "[\"0:1个\", 100]";

    // log文件
    $log_file = Config::$apidataPath . $group . '/' . Api::$_logDir . (empty($date) ? date('Y-m-d') : $date);

    if (!is_file($log_file)) {
        $outPut[] = array('name' => "0:1个", 'y' => 100);

        return $outPut;
    }

    $all_line_num = explode(" ", trim(exec("wc -l " . $log_file)))[0];

    if (empty($all_line_num)) {
        return $outPut;
    }

    $noticePatten = "grep \"notice\t\" $log_file | wc -l";
    $warnPatten = "grep \"warn\t\" $log_file | wc -l";
    $errorPatten = "grep \"error\t\" $log_file | wc -l";
    $timeoutPatten = "grep \"timeout\t\" $log_file | wc -l";
    $unknownPatten = "grep \"unknown\t\" $log_file | wc -l";

    $notice_line_num = explode(" ", trim(exec($noticePatten)))[0];
    $warn_line_num = explode(" ", trim(exec($warnPatten)))[0];
    $error_line_num = explode(" ", trim(exec($errorPatten)))[0];
    $timeout_line_num = explode(" ", trim(exec($timeoutPatten)))[0];
    $unknown_line_num = explode(" ", trim(exec($unknownPatten)))[0];

    $level_map = array('notice' => $notice_line_num, 'warn' => $warn_line_num, 'error' => $error_line_num, 'timeout' => $timeout_line_num, 'unknown' => $unknown_line_num,);

    //    $total_item_count = array_sum($level_map);
    //    foreach ($level_map as $level => $count) {
    //        if (empty($count)) {
    //            continue;
    //        }
    //        $level_pie_array[] = "[\"$level:{$count}个\", " . round($count * 100 / $total_item_count, 4) . "]";
    //    }
    //    $outPut = implode(',', $level_pie_array);

    $total_item_count = array_sum($level_map);
    foreach ($level_map as $level => $count) {
        if (empty($count)) {
            continue;
        }
        $color = '';
        if ($level == 'error') {
            $color = 'red';
        } elseif ($level == 'warn') {
            $color = 'blue';
        } elseif ($level == 'notice') {
            $color = 'green';
        } elseif ($level == 'timeout') {
            $color = 'brown';
        } elseif ($level == 'unknown') {
            $color = 'black';
        } else {
            $color = '';
        }

        $api_pie_array[] = array('name' => "$level:{$count}个", 'y' => round($count * 100 / $total_item_count, 4), 'level' => $level, 'color' => $color,);
    }
    $outPut = $api_pie_array;
    $api_pie_array = array();

    return $outPut;
}

//php版本小于5.5.0
function _getPhpLevelApiRateUnder50($log_file)
{
    $outPut = array();

    $noticePatten = "grep -i \"notice\t\" $log_file";
    $warnPatten = "grep -i \"warn\t\" $log_file";
    $errorPatten = "grep -i \"error\t\" $log_file";
    $timeoutPatten = "grep -i \"timeout\t\" $log_file";
    $unknownPatten = "grep -i \"unknown\t\" $log_file";

    $api_map = array('notice' => array(), 'warn' => array(), 'error' => array(), 'timeout' => array(), 'unknown' => array(),);

    exec($noticePatten, $notice_lines);
    foreach ($notice_lines as &$il) {
        $line = explode("\t", $il);

        if (!isset($api_map['notice'][$line[3]])) {
            $api_map['notice'][$line[3]] = 1;
        } else {
            $api_map['notice'][$line[3]]++;
        }
        unset($il);
    }
    unset($notice_lines);


    exec($warnPatten, $warn_lines);
    foreach ($warn_lines as &$wl) {
        $line = explode("\t", $wl);

        if (!isset($api_map['warn'][$line[3]])) {
            $api_map['warn'][$line[3]] = 1;
        } else {
            $api_map['warn'][$line[3]]++;
        }
        unset($wl);
    }
    unset($warn_lines);

    exec($errorPatten, $error_lines);
    foreach ($error_lines as &$el) {
        $line = explode("\t", $el);

        if (!isset($api_map['error'][$line[3]])) {
            $api_map['error'][$line[3]] = 1;
        } else {
            $api_map['error'][$line[3]]++;
        }
        unset($el);
    }
    unset($error_lines);

    exec($timeoutPatten, $timeout_lines);
    foreach ($timeout_lines as &$tl) {
        $line = explode("\t", $tl);

        if (!isset($api_map['timeout'][$line[3]])) {
            $api_map['timeout'][$line[3]] = 1;
        } else {
            $api_map['timeout'][$line[3]]++;
        }
        unset($tl);
    }
    unset($timeout_lines);

    exec($unknownPatten, $unknown_lines);
    foreach ($unknown_lines as &$ul) {
        $line = explode("\t", $ul);

        if (!isset($api_map['unknown'][$line[3]])) {
            $api_map['unknown'][$line[3]] = 1;
        } else {
            $api_map['unknown'][$line[3]]++;
        }
        unset($ul);
    }
    unset($unknown_lines);

    //    print_r($api_map);

    foreach ($api_map as $level => $amp) {
        if (empty($amp)) {
            //            $outPut[$level] = "[\"0:1个\", 100]";
            $outPut[$level][] = array('name' => "0:1个", 'y' => 100);
            continue;
        }

        //        $total_item_count = array_sum($amp);
        //        foreach ($amp as $api => $count) {
        //
        //            $api_pie_array[] = "[\"$api:{$count}个\", " . round($count * 100 / $total_item_count, 4) . "]";
        //        }
        //        $outPut[$level] = implode(',', $api_pie_array);
        $api_pie_array = array();
        $total_item_count = array_sum($amp);
        foreach ($amp as $api => $count) {

            $api_pie_array[] = array('name' => "$api:{$count}个", 'y' => round($count * 100 / $total_item_count, 4), 'api' => $api,);
        }
        $outPut[$level] = $api_pie_array;
        //        $api_pie_array = array();

    }

    return $outPut;
}

function _getPhpLevelCodeRateUnder50($log_file)
{
    $outPut = array();

    $noticePatten = "grep -i \"notice\t\" $log_file";
    $warnPatten = "grep -i \"warn\t\" $log_file";
    $errorPatten = "grep -i \"error\t\" $log_file";
    $timeoutPatten = "grep -i \"timeout\t\" $log_file";
    $unknownPatten = "grep -i \"unknown\t\" $log_file";

    $api_map = array('notice' => array(), 'warn' => array(), 'error' => array(), 'unknown' => array(),);

    exec($noticePatten, $notice_lines);

    foreach ($notice_lines as &$il) {
        $line = explode("\t", $il);

        if (!isset($api_map['notice'][$line[4]])) {
            $api_map['notice'][$line[4]] = 1;
        } else {
            $api_map['notice'][$line[4]]++;
        }
        unset($il);
    }
    unset($notice_lines);


    exec($warnPatten, $warn_lines);
    foreach ($warn_lines as &$wl) {
        $line = explode("\t", $wl);

        if (!isset($api_map['warn'][$line[4]])) {
            $api_map['warn'][$line[4]] = 1;
        } else {
            $api_map['warn'][$line[4]]++;
        }
        unset($wl);
    }
    unset($warn_lines);

    exec($errorPatten, $error_lines);
    foreach ($error_lines as &$el) {
        $line = explode("\t", $el);

        if (!isset($api_map['error'][$line[4]])) {
            $api_map['error'][$line[4]] = 1;
        } else {
            $api_map['error'][$line[4]]++;
        }
        unset($el);
    }
    unset($error_lines);

    exec($timeoutPatten, $timeout_lines);
    foreach ($timeout_lines as &$tl) {
        $line = explode("\t", $tl);

        if (!isset($api_map['timeout'][$line[4]])) {
            $api_map['timeout'][$line[4]] = 1;
        } else {
            $api_map['timeout'][$line[4]]++;
        }
        unset($el);
    }
    unset($timeout_lines);

    exec($unknownPatten, $unknown_lines);
    foreach ($unknown_lines as &$ul) {
        $line = explode("\t", $ul);

        if (!isset($api_map['unknown'][$line[4]])) {
            $api_map['unknown'][$line[4]] = 1;
        } else {
            $api_map['unknown'][$line[4]]++;
        }
        unset($ul);
    }
    unset($unknown_lines);

    //    print_r($api_map);

    foreach ($api_map as $level => $amp) {
        if (empty($amp)) {
            //            $outPut[$level] = "[\"0:1个\", 100]";
            $outPut[$level][] = array('name' => "0:1个", 'y' => 100);
            continue;
        }

        $code_pie_array = array();
        $total_item_count = array_sum($amp);
        foreach ($amp as $code => $count) {
            $_code = explode(':', $code)[1];
            $code_pie_array[] = array('name' => "$_code:{$count}个", 'y' => round($count * 100 / $total_item_count, 4), 'code' => $_code,);
        }
        $outPut[$level] = $code_pie_array;

    }

    return $outPut;
}

//php版本大于5.5.0 no test! todo
function _getPhpLevelApiRateUp50($log_file)
{
    $outPut = array();
    $noticePatten = "grep -i \"notice\t\" $log_file";
    $warnPatten = "grep -i \"warn\t\" $log_file";
    $errorPatten = "grep -i \"error\t\" $log_file";
    $timeoutPatten = "grep -i \"timeout\t\" $log_file";
    $unknownPatten = "grep -i \"unknown\t\" $log_file";

    $range = function ($arr) {
        foreach ($arr as $val) {
            yield $val;
        }
    };

    $api_map = array('notice' => array(), 'warn' => array(), 'error' => array(), 'timeout' => array(), 'unknown' => array(),);

    exec($noticePatten, $notice_lines);
    foreach ($range($notice_lines) as $il) {
        $line = explode("\t", $il);
        if (!isset($api_map['notice'][$line[3]])) {
            $api_map['notice'][$line[3]] = 1;
        } else {
            $api_map['notice'][$line[3]]++;
        }
    }
    unset($notice_lines);

    exec($warnPatten, $warn_lines);
    foreach ($range($warn_lines) as $wl) {
        $line = explode("\t", $wl);

        if (!isset($api_map['warn'][$line[3]])) {
            $api_map['warn'][$line[3]] = 1;
        } else {
            $api_map['warn'][$line[3]]++;
        }
    }
    unset($warn_lines);

    exec($errorPatten, $error_lines);
    foreach ($range($error_lines) as $el) {
        $line = explode("\t", $el);

        if (!isset($api_map['error'][$line[3]])) {
            $api_map['error'][$line[3]] = 1;
        } else {
            $api_map['error'][$line[3]]++;
        }
    }
    unset($error_lines);

    exec($timeoutPatten, $timeout_lines);
    foreach ($range($timeout_lines) as $tl) {
        $line = explode("\t", $tl);

        if (!isset($api_map['timeout'][$line[3]])) {
            $api_map['timeout'][$line[3]] = 1;
        } else {
            $api_map['timeout'][$line[3]]++;
        }
    }
    unset($timeout_lines);

    exec($unknownPatten, $unknown_lines);
    foreach ($range($unknown_lines) as $ul) {
        $line = explode("\t", $ul);

        if (!isset($api_map['unknown'][$line[3]])) {
            $api_map['unknown'][$line[3]] = 1;
        } else {
            $api_map['unknown'][$line[3]]++;
        }
    }
    unset($unknown_lines);

    foreach ($api_map as $level => $amp) {
        if (empty($amp)) {
            $outPut[$level][] = array('name' => "0:1个", 'y' => 100);
            continue;
        }

        $total_item_count = array_sum($amp);
        foreach ($amp as $api => $count) {

            $api_pie_array[] = array('name' => "$api:{$count}个", 'y' => round($count * 100 / $total_item_count, 4), 'api' => $api);
        }
        $outPut[$level] = $api_pie_array;
        $api_pie_array = array();

    }

    return $outPut;
}

//no test
function _getPhpLevelCodeRateUp50($log_file)
{
    $outPut = array();
    $noticePatten = "grep -i \"notice\t\" $log_file";
    $warnPatten = "grep -i \"warn\t\" $log_file";
    $errorPatten = "grep -i \"error\t\" $log_file";
    $timeoutPatten = "grep -i \"timeout\t\" $log_file";
    $unknownPatten = "grep -i \"unknown\t\" $log_file";

    $range = function ($arr) {
        foreach ($arr as $val) {
            yield $val;
        }
    };

    $api_map = array('notice' => array(), 'warn' => array(), 'error' => array(), 'timeout' => array(), 'unknown' => array());

    exec($noticePatten, $notice_lines);
    foreach ($range($notice_lines) as $il) {
        $line = explode("\t", $il);
        if (!isset($api_map['notice'][$line[4]])) {
            $api_map['notice'][$line[4]] = 1;
        } else {
            $api_map['notice'][$line[4]]++;
        }
    }
    unset($notice_lines);

    exec($warnPatten, $warn_lines);
    foreach ($range($warn_lines) as $wl) {
        $line = explode("\t", $wl);

        if (!isset($api_map['warn'][$line[4]])) {
            $api_map['warn'][$line[4]] = 1;
        } else {
            $api_map['warn'][$line[4]]++;
        }
    }
    unset($warn_lines);

    exec($errorPatten, $error_lines);
    foreach ($range($error_lines) as $el) {
        $line = explode("\t", $el);

        if (!isset($api_map['error'][$line[4]])) {
            $api_map['error'][$line[4]] = 1;
        } else {
            $api_map['error'][$line[4]]++;
        }
    }
    unset($error_lines);

    exec($timeoutPatten, $timeout_lines);
    foreach ($range($timeout_lines) as $tl) {
        $line = explode("\t", $tl);

        if (!isset($api_map['timeout'][$line[4]])) {
            $api_map['timeout'][$line[4]] = 1;
        } else {
            $api_map['timeout'][$line[4]]++;
        }
    }
    unset($timeout_lines);

    exec($unknownPatten, $unknown_lines);
    foreach ($range($unknown_lines) as $ul) {
        $line = explode("\t", $ul);

        if (!isset($api_map['unknown'][$line[4]])) {
            $api_map['unknown'][$line[4]] = 1;
        } else {
            $api_map['unknown'][$line[4]]++;
        }
    }
    unset($unknown_lines);


    foreach ($api_map as $level => $amp) {
        if (empty($amp)) {
            $outPut[$level][] = array('name' => "0:1个", 'y' => 100);
            continue;
        }

        $code_pie_array = array();
        $total_item_count = array_sum($amp);
        foreach ($amp as $code => $count) {
            $_code = explode(':', $code)[1];
            $code_pie_array[] = array('name' => "$_code:{$count}个", 'y' => round($count * 100 / $total_item_count, 4), 'code' => $_code,);
        }
        $outPut[$level] = $code_pie_array;
    }

    return $outPut;
}

//获取php api对于的等级接口分布
function getPhpLevelApiRate($group, $date)
{
    $outPut = array();

    //    $outPut = "[\"0:1个\", 100]";
    // log文件
    $log_file = Config::$apidataPath . $group . '/' . Api::$_logDir . (empty($date) ? date('Y-m-d') : $date);

    if (!is_file($log_file)) {
        $outPut = array(
            'notice' => array(0 => array('name' => "0:1个", 'y' => 100)),

            'warn' => array(0 => array('name' => "0:1个", 'y' => 100)),

            'error' => array(0 => array('name' => "0:1个", 'y' => 100)),

            'timeout' => array(0 => array('name' => "0:1个", 'y' => 100)),

            'unknown' => array(0 => array('name' => "0:1个", 'y' => 100)),
        );

        return $outPut;
    }

    if (version_compare(phpversion(), '5.5.0', '>=')) {
        return _getPhpLevelApiRateUp50($log_file);
    } else {
        return _getPhpLevelApiRateUnder50($log_file);
    }

}

function getPhpLevelCodeRate($group, $date)
{
    $outPut = array();

    //    $outPut = "[\"0:1个\", 100]";
    // log文件
    $log_file = Config::$apidataPath . $group . '/' . Api::$_logDir . (empty($date) ? date('Y-m-d') : $date);

    if (!is_file($log_file)) {
        $outPut = array(
            'notice' => array(0 => array('name' => "0:1个", 'y' => 100)),

            'warn' => array(0 => array('name' => "0:1个", 'y' => 100)),

            'error' => array(0 => array('name' => "0:1个", 'y' => 100)),

            'timeout' => array(0 => array('name' => "0:1个", 'y' => 100)),

            'unknown' => array(0 => array('name' => "0:1个", 'y' => 100)),
        );

        return $outPut;
    }

    if (version_compare(phpversion(), '5.5.0', '>=')) {
        return _getPhpLevelCodeRateUp50($log_file);
    } else {
        return _getPhpLevelCodeRateUnder50($log_file);
    }

}


/**
 * 获取work模块指定日志
 *
 */
function getWorkStasticLog($module, $interface, $start_time = '', $end_time = '', $offset = '', $count = 20)
{

    $offset = !empty($offset) ? $offset : 0;
    // log文件
    $log_file = Config::$workdataPath . Work::$logDir . (empty($start_time) ? date('Y-m-d') : date('Y-m-d', $start_time));


    if (!is_readable($log_file)) {
        return array('offset' => 0, 'data' => '');
    }
    // 读文件
    $h = fopen($log_file, 'r');

    // 如果有时间，则进行二分查找，加速查询
    if ($start_time && $offset == 0 && ($file_size = filesize($log_file)) > 1024000) {
        $offset = binarySearch(0, $file_size, $start_time - 1, $h);
        $offset = $offset < 100000 ? 0 : $offset - 100000;
    }

    // 指定偏移位置
    if ($offset > 0) {
        //        fseek($h, (int)$offset - 1);
        fseek($h, (int)$offset);
    }


    // 查找符合条件的数据
    $now_count = 0;
    $log_buffer = '';

    while (1) {
        if (feof($h)) {
            break;
        }
        // 读1行
        $line = fgets($h);

        $row = explode("\t", $line);


        if ($module && $interface) {
            $mod = strstr($row[2], '::', true);
            $if = substr(strstr($row[2], '::'), 2);
            if ($mod != $module || $if != $interface) {
                continue;
            }
        }

        // 判断时间是否符合要求
        $time = strtotime($row[0]);
        if ($start_time) {
            if ($time < $start_time) {
                continue;
            }
        }

        if ($end_time) {
            if ($time > $end_time) {
                break;
            }
        }
        // 收集符合条件的log
        $log_buffer .= $line;
        if (++$now_count >= $count) {

            break;
        }
    }
    // 记录偏移位置
    $offset = ftell($h);

    return array('offset' => $offset, 'data' => $log_buffer);
}


/**
 * 获取php模块指定日志
 *
 */
function getPhpStasticLog($group, $module, $interface, $start_time = '', $end_time = '', $offset = '', $count = 20)
{

    $offset = !empty($offset) ? $offset : 0;
    // log文件
    $log_file = Config::$apidataPath . $group . '/' . Api::$_logDir . (empty($start_time) ? date('Y-m-d') : date('Y-m-d', $start_time));


    if (!is_readable($log_file)) {
        return array('offset' => 0, 'data' => '');
    }
    // 读文件
    $h = fopen($log_file, 'r');

    // 如果有时间，则进行二分查找，加速查询
    if ($start_time && $offset == 0 && ($file_size = filesize($log_file)) > 1024000) {
        $offset = binarySearch(0, $file_size, $start_time - 1, $h);
        $offset = $offset < 100000 ? 0 : $offset - 100000;
    }

    // 指定偏移位置
    if ($offset > 0) {
        //        fseek($h, (int)$offset - 1);
        fseek($h, (int)$offset);
    }


    // 查找符合条件的数据
    $now_count = 0;
    $log_buffer = '';

    while (1) {
        if (feof($h)) {
            break;
        }
        // 读1行
        $line = fgets($h);

        $row = explode("\t", $line);

        //        print_r($row);


        if (empty($row[0])) {
            continue;
        }

        if ($module && $interface) {
            $mod_if = explode('::', $row[3]);
            if (count($mod_if) > 2) {
                $tmp = array_slice($mod_if, 1, count($mod_if));
                $mod_if[1] = implode('::', $tmp);
            }
            if ($mod_if[0] != $module || $mod_if[1] != $interface) {
                continue;
            }
        }

        // 判断时间是否符合要求
        $time = strtotime($row[1]);
        if ($start_time) {
            if ($time < $start_time) {
                continue;
            }
        }

        if ($end_time) {
            if ($time > $end_time) {
                break;
            }
        }


        // 收集符合条件的log
        //notice','warn','error','unknown'
        $level = '';
        if ($row[0] == 'notice') {
            $level = "<b><font color='green'>[$row[0]]</font></b>";
        } elseif ($row[0] == 'warn') {
            $level = "<b><font color='blue'>[$row[0]]</font></b>";
        } elseif ($row[0] == 'error') {
            $level = "<b><font color='red'>[$row[0]]</font></b>";
        } elseif ($row[0] == 'unknown') {
            $level = "<b><font color='black'>[$row[0]]</font></b>";
        } else {
            $level = "<b><font color='yellow'>[$row[0]]</font></b>";
        }

        $new_line = array($level, $row[1], $row[3], $row[4], $row[5], $row[6],);


        //        $log_buffer .= $line;
        $log_buffer .= implode("&emsp;", $new_line);
        if (++$now_count >= $count) {

            break;
        }
    }
    // 记录偏移位置
    $offset = ftell($h);

    return array('offset' => $offset, 'data' => $log_buffer);
}

//oOps!
function getPhpExtStasticLog($group, $level = '', $date = '', $api = '', $code = 0, $page = 1, $count = 20)
{
    $offset = ($page - 1) * $count;

    $log_file = Config::$apidataPath . $group . '/' . Api::$_logDir . (empty($date) ? date('Y-m-d') : $date);

    if (!is_readable($log_file) || empty($level) || !in_array($level, array('notice', 'warn', 'error', 'timeout', 'unknown'))) {
        return array('page' => 0, 'data' => '');
    }


    $patten = "grep -i \"$level\t";
    if ($api) {
        $patten .= ".*$api\t";
    }

    if ($code) {
        $patten .= ".*code:$code\t";
    }

    $patten .= "\" $log_file";

    exec($patten, $_lines);

    $lines = array_slice($_lines, $offset, $count);
    unset($_lines);

    if (empty($lines)) {
        return array('page' => $page, 'data' => '');
    }


    $log_buffer = '';
    foreach ($lines as $line) {
        $row = explode("\t", $line);

        // 收集符合条件的log
        //notice','warn','error','unknown'
        $level = '';
        if ($row[0] == 'notice') {
            $level = "<b><font color='green'>[$row[0]]</font></b>";
        } elseif ($row[0] == 'warn') {
            $level = "<b><font color='blue'>[$row[0]]</font></b>";
        } elseif ($row[0] == 'error') {
            $level = "<b><font color='red'>[$row[0]]</font></b>";
        } elseif ($row[0] == 'timeout') {
            $level = "<b><font color='brown'>[$row[0]]</font></b>";
        } elseif ($row[0] == 'unknown') {
            $level = "<b><font color='black'>[$row[0]]</font></b>";
        } else {
            $level = "<b><font color='yellow'>[$row[0]]</font></b>";
        }

        $new_line = array($level, $row[1], $row[3], $row[4], $row[5], $row[6],);

        $log_buffer .= "<p>" . implode("&emsp;", $new_line) . "</p>";

    }

    $page = $page + 1;

    return array('page' => $page, 'data' => $log_buffer);
}

function isImgFile($file)
{
    $types = '.gif|.jpeg|.png|.bmp';//定义检查的图片类型
    if (file_exists($file)) {
        $info = getimagesize($file);
        $ext = image_type_to_extension($info['2']);

        return stripos($types, $ext);
    } else {
        return false;
    }
}

/**
 * 将一个二维数组按照键名进行求和
 *
 * @param array $arr
 * @param string $keyField
 */
function array_sum_field(& $arr, $keyField)
{
    $value = 0;
    if (empty($arr)) {
        return $value;
    }
    foreach ($arr as $row) {
        $value += $row[$keyField];
    }

    return $value;
}


//summary===========整体可用性
function availability($g = 'default', $date)
{
    $output = array('total_count' => 0, 'success_count' => 0, 'fail_count' => 0,);
    $module = 'WorkerMan';
    $interface = 'Statistics';
    $statistic = getStatistic($date, $module, $interface, Config::$apidataPath . $g . '/', Api::$_statisticDir);
    $all_st_str = '';
    $all_st_str .= $statistic;

    $code_map = array();
    $data = formatSt($all_st_str, $date, $code_map);
    unset($code_map);

    $total_count = $fail_count = 0;
    foreach ($data as $time_point => $item) {
        if ($item['total_count']) {
            $total_count += $item['total_count'];
        }
        $fail_count += $item['fail_count'];
    }

    $output = array('total_count' => $total_count, 'success_count' => $total_count - $fail_count, 'fail_count' => $fail_count,);

    return $output;
}

function monthsummary($g = 'default', $date)
{
    $log_file = Config::$apidataPath . $g . '/' . Api::$_logDir . $date;
    if (!is_file($log_file)) {
        return array();
    }

    $handle = @fopen($log_file, 'r');
    if (!$handle) {
        return array();
    }

    $output = array();
    while (!feof($handle)) {
        $line = fgets($handle, 4096);
        if ($line) {
            $explode = explode("\t", $line);

            list($level, $time, $ip, $api, $code, $msg, $extra, $cost_time) = $explode;

            $tmp = explode('::', $api);
            $module = $tmp[0];
            $interface = substr($api, strlen($tmp[0]) + 2);

            if (!isset($output[$module])) {
                $output[$module] = array();

            }

            if (!isset($output[$module][$interface])) {
                $output[$module][$interface] = array();
            }

            if (!isset($output[$module][$interface][$level])) {
                $output[$module][$interface][$level] = array('count' => 0, 'cost' => array());
            }
            $output[$module][$interface][$level]['count'] += 1;
            $output[$module][$interface][$level]['cost'][] = $cost_time;


        } // end if
    } // end while

    fclose($handle);

    return $output;
}

function statallByMonth(& $statDays)
{
    $modules = array();
    foreach ($statDays as $day) {
        if (empty($day)) {
            continue;
        }
        foreach ($day as $mod => $inter) {
            if (!isset($modules[$mod])) {
                $modules[$mod] = array();
            }
            foreach ($inter as $interKey => $levels) {
                if (!isset($modules[$mod][$interKey])) {
                    $modules[$mod][$interKey] = array();
                }
                foreach ($levels as $lkey => $level) {
                    if (!isset($modules[$mod][$interKey][$lkey])) {
                        $modules[$mod][$interKey][$lkey] = array('count' => 0, 'cost' => array());
                    }
                    $modules[$mod][$interKey][$lkey]['count'] += $level['count'];
                    $modules[$mod][$interKey][$lkey]['cost'] = array_merge($modules[$mod][$interKey][$lkey]['cost'],$level['cost']);
                }

            }
        }
        unset($day);
    }

    return $modules;
}
