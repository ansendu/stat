<?php
error_reporting(E_ALL);

require 'sClient.php';

// error code
$errors = array(
    1001,
    1002,
    1003,
    1004,
    1005,
    1006,
    1007,
    1008,
    1009,
    1010,
);

//
$levels = array(
    'notice',
    'warn',
    'error',
    'timeout',
    'unknown'
);


$apis = array(
    'Resume'     => array(
        'add',
        'save',
        'view',
        'upload',
        'update',
    ),
    'User'       => array(
        'login',
        'reg',
        'add',
        'search',
    ),
    'Position'   => array(
        'view',
        'add',
        'new'
    ),
    'News'       => array(
        'add',
        'update',
        'delete'
    ),
    'icdc_basic' => array(
        'resumes/logic_resume::detail_by_id',
        'resumes/logic_resume::get_multi_all',
        'resumes/logic_resume::xxxxxxxxxx',
        'resumes/logic_resume::aaaaaaa',
    ),


);


//按项目,按组,按业务区分的namespace
$groups = array(
    'toc_cheng95',
    'toc_cheng95_work',
    'toc_bole',
    'toc_bole_work',
);


function test()
{
    usleep(100000);
    $rand = mt_rand(1, 100000);

    if ($rand % 3 == 0) {
        return true;
    }
    return false;
}


for ($i = 0; $i < rand(1, 20); $i++) {

    $api_key = array_rand($apis, 1);
    $api_name_key = array_rand($apis[$api_key], 1);

    $error_key = array_rand($errors, 1);
    $level_key = array_rand($levels, 1);
    $group_key = array_rand($groups, 1);


    //begin
    sClient::tick($api_key, $apis[$api_key][$api_name_key]);

    $success = true;
    $code = 0;
    $msg = '';
    $extra = array('group' => $groups[$group_key]); //成功请求， group为必填字段

    $ret = test();

    if (!$ret) {
        $success = false;
        $code = $errors[$error_key]; // error code
        $msg = "调用 [$api_key] [{$apis[$api_key][$api_name_key]}] 失败."; //error msg
        $extra = array(
            'level' => $levels[$level_key], //必填字段
            'group' => $groups[$group_key]  //必填字段
        );
    }


    $serverLists = array(
//        'udp://192.168.1.110:40001',
        'udp://192.168.10.9:40001',
    );

    $address = $serverLists[array_rand($serverLists, 1)];
    sClient::reportApi($api_key, $apis[$api_key][$api_name_key], $success, $code, $msg, $extra, $address);

    usleep(500000);
}

echo "finished" . time();


