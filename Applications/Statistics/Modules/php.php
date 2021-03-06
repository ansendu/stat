<?php
namespace Statistics\Modules;

use Statistics\Config;

function php($module, $interface, $date, $start_time, $offset)
{
    $group = isset($_GET['group']) ? $_GET['group'] : '';
    $groups = getGroupByModule();

    if (!in_array($group, $groups)) {
        echo '错误的group';
        return;
    }

    $err_msg = $notice_msg = '';
    $module = 'WorkerMan';
    $interface = 'Statistics';
    $today = date('Y-m-d');
    $time_now = time();


    //level
    $level_pie_data = getPhpLevelRate($group, $date);

    //lever api
    $apis_pie_data = getPhpLevelApiRate($group, $date);

    //lever code
    $codes_pie_data = getPhpLevelCodeRate($group, $date);

    $read_buffer_array = getPhpStAndModules($group, $module, $interface, $date);

    $all_st_str = '';
    $all_st_str .= $read_buffer_array;


    $code_map = array();
    $data = formatSt($all_st_str, $date, $code_map);


    $interface_name = '整体';
    $success_series_data = $fail_series_data = $success_time_series_data = $fail_time_series_data = array();
    $total_count = $fail_count = 0;


    foreach ($data as $time_point => $item) {
        if ($item['total_count']) {
            $success_series_data[] = "[" . ($time_point * 1000) . ",{$item['total_count']}]";
            $total_count += $item['total_count'];
        }
        $fail_series_data[] = "[" . ($time_point * 1000) . ",{$item['fail_count']}]";
        $fail_count += $item['fail_count'];
        if ($item['total_avg_time']) {
            $success_time_series_data[] = "[" . ($time_point * 1000) . ",{$item['total_avg_time']}]";
        }
        $fail_time_series_data[] = "[" . ($time_point * 1000) . ",{$item['fail_avg_time']}]";
    }
    $success_series_data = implode(',', $success_series_data);
    $fail_series_data = implode(',', $fail_series_data);
    $success_time_series_data = implode(',', $success_time_series_data);
    $fail_time_series_data = implode(',', $fail_time_series_data);

    // 总体成功率
    $global_rate = $total_count ? round((($total_count - $fail_count) / $total_count) * 100, 4) : 100;


    // 返回码分布
//    $code_pie_data = '';
//    $code_pie_array = array();
//    unset($code_map[0]);
//    if (empty($code_map)) {
//        $code_map[0] = $total_count > 0 ? $total_count : 1;
//    }
//
//    if (is_array($code_map)) {
//        $total_item_count = array_sum($code_map);
//        foreach ($code_map as $code => $count) {
//            $code_pie_array[] = "[\"$code:{$count}个\", " . round($count * 100 / $total_item_count, 4) . "]";
//        }
//        $code_pie_data = implode(',', $code_pie_array);
//    }


    unset($_GET['start_time'], $_GET['end_time'], $_GET['date'], $_GET['fn']);
    $query = http_build_query($_GET);

    // 删除末尾0的记录
    if ($today == $date) {
        while (!empty($data) && ($item = end($data)) && $item['total_count'] == 0 && ($key = key($data)) && $time_now < $key) {
            unset($data[$key]);
        }
    }

    $table_data = '';
    if ($data) {
        $first_line = true;
        foreach ($data as $item) {
            if ($first_line) {
                $first_line = false;
                if ($item['total_count'] == 0) {
                    continue;
                }
            }
            $html_class = 'class="danger"';
            if ($item['total_count'] == 0) {
                $html_class = '';
            } elseif ($item['precent'] >= 99.99) {
                $html_class = 'class="success"';
            } elseif ($item['precent'] >= 99) {
                $html_class = '';
            } elseif ($item['precent'] >= 98) {
                $html_class = 'class="warning"';
            }
            $table_data .= "\n<tr $html_class>
                       <td>{$item['time']}</td>
                       <td>{$item['total_count']}</td>
                        <td> {$item['total_avg_time']}</td>
                        <td>{$item['suc_count']}</td>
                        <td>{$item['suc_avg_time']}</td>
                        <td>" . ($item['fail_count'] > 0 ? ("<a target='_blank' href='/?fn=phplogger&$query&start_time=" . (strtotime($item['time']) - 300) . "&end_time=" . (strtotime($item['time'])) . "'>{$item['fail_count']}</a>") : $item['fail_count']) . "</td>
                        <td>{$item['fail_avg_time']}</td>
                        <td>{$item['precent']}%</td>
                    </tr>
            ";
        }
    }

    // date btn
    $date_btn_str = '';
    for ($i = 13; $i >= 1; $i--) {
        $the_time = strtotime("-$i day");
        $the_date = date('Y-m-d', $the_time);
        $html_the_date = $date == $the_date ? "<b>$the_date</b>" : $the_date;
        $date_btn_str .= '<a href="/?fn=php&date=' . "$the_date&$query" . '" class="btn ' . $html_class . '" type="button">' . $html_the_date . '</a>';
        if ($i == 7) {
            $date_btn_str .= '</br>';
        }
    }
    $the_date = date('Y-m-d');
    $html_the_date = $date == $the_date ? "<b>$the_date</b>" : $the_date;
    $date_btn_str .= '<a href="/?fn=php&date=' . "$the_date&$query" . '" class="btn" type="button">' . $html_the_date . '</a>';

    if (\Statistics\Lib\Cache::$lastFailedIpArray) {
        $err_msg = '<strong>无法从以下数据源获取数据:</strong>';
        foreach (\Statistics\Lib\Cache::$lastFailedIpArray as $ip) {
            $err_msg .= $ip . '::' . \Statistics\Config::$ProviderPort . '&nbsp;';
        }
    }

    if (empty(\Statistics\Lib\Cache::$ServerIpList)) {
        $notice_msg = <<<EOT
<h4>数据源为空</h4>
您可以 <a href="/?fn=admin&act=detect_server" class="btn" type="button"><strong>探测数据源</strong></a>或者<a href="/?fn=admin" class="btn" type="button"><strong>添加数据源</strong></a>
EOT;
    }

    include ST_ROOT . '/Views/header.tpl.php';
    include ST_ROOT . '/Views/php.tpl.php';
    include ST_ROOT . '/Views/footer.tpl.php';
}



