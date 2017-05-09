<?php
namespace Statistics\Modules;

use Statistics\Config;

function phplogger($module, $interface, $date, $start_time, $offset, $count)
{

    $group = isset($_GET['group']) ? $_GET['group'] : '';
    $groups = getGroupByModule();

    if (!in_array($group, $groups)) {
        echo '错误的group';
        return;
    }

    $count = 40;
    
//    $module_str = '';
//    foreach (\Statistics\Lib\Cache::$phpModulesDataCache as $mod => $interfaces) {
//        if ($mod == 'WorkerMan') {
//            continue;
//        }
//        $module_str .= '<li><a href="/?fn=phpstat&module=' . $mod . '">' . $mod . '</a></li>';
//        if ($module == $mod) {
//            foreach ($interfaces as $if) {
//                $module_str .= '<li>&nbsp;&nbsp;<a href="/?fn=phpstat&module=' . $mod . '&interface=' . $if . '">' . $if . '</a></li>';
//            }
//        }
//    }

    $end_time = $_GET['end_time'];

    $log_data_arr = getPhpStasticLog($group, $module, $interface, $start_time, $end_time, $offset, $count);
    unset($_GET['fn'], $_GET['ip'], $_GET['offset']);
    $log_str = '';


    $log_str .= $log_data_arr['data'];
    $_GET['offset'] = $log_data_arr['offset'];

    $log_str = nl2br(str_replace("\n", "\n\n", $log_str));


    $next_page_url = http_build_query($_GET);
    $log_str .= "</br><center><a href='/?fn=phplogger&$next_page_url'>下一页</a></center>";

    $log_str = '';

    include ST_ROOT . '/Views/header.tpl.php';
    include ST_ROOT . '/Views/phplog.tpl.php';
    include ST_ROOT . '/Views/footer.tpl.php';
}