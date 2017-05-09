<?php
namespace Statistics\Modules;

use Statistics\Config;

function phploggerext()
{
    $count = 40;
    $group = isset($_GET['group']) ? $_GET['group'] : '';
    $level = isset($_GET['level']) ? $_GET['level'] : '';
    $date = isset($_GET['date']) ? $_GET['date'] : '';
    $api = isset($_GET['api']) ? $_GET['api'] : '';
    $code = isset($_GET['code']) ? $_GET['code'] : 0;
    $page = isset($_GET['page']) && !empty($_GET['page']) ? intval($_GET['page']) : 1;

    $log_str = '';
    $log_data_arr = getPhpExtStasticLog($group, $level, $date, $api, $code, $page, $count);
    $log_str .= $log_data_arr['data'];
    $_GET['page'] = $log_data_arr['page'];


    $next_page_url = http_build_query($_GET);

    $log_str .= "</br><center><a href='/?fn=phploggerext&$next_page_url'>下一页</a></center>";


    include ST_ROOT . '/Views/header.tpl.php';
    include ST_ROOT . '/Views/phplogext.tpl.php';
    include ST_ROOT . '/Views/footer.tpl.php';
}