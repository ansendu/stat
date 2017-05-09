<?php
namespace Statistics\Modules;

use Statistics\Config;

function phphome($module, $interface, $date, $start_time, $offset)
{

    $group_str = '';
    $groups = getGroupByModule();
    if(!empty($groups)){
        foreach ($groups as $group){
            $group_str .=  "<a href='/?fn=php&group=".$group."'>&raquo;".$group."</a><br>";
        }
    }else{
        $group_str = '暂无数据!';
    }


    $err_msg = '';
    if (\Statistics\Lib\Cache::$lastFailedIpArray) {
        $err_msg = '<strong>无法从以下数据源获取数据:</strong>';
        foreach (\Statistics\Lib\Cache::$lastFailedIpArray as $ip) {
            $err_msg .= $ip . '::' . \Statistics\Config::$ProviderPort . '&nbsp;';
        }
    }

    $notice_msg = '';
    if (empty(\Statistics\Lib\Cache::$ServerIpList)) {
        $notice_msg = <<<EOT
<h4>数据源为空</h4>
您可以 <a href="/?fn=admin&act=detect_server" class="btn" type="button"><strong>探测数据源</strong></a>或者<a href="/?fn=admin" class="btn" type="button"><strong>添加数据源</strong></a>
EOT;
    }

    include ST_ROOT . '/Views/header.tpl.php';
    include ST_ROOT . '/Views/phphome.tpl.php';
    include ST_ROOT . '/Views/footer.tpl.php';
}



