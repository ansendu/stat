<?php

namespace Statistics\Modules;

use Statistics\Config;
use Workerman\Protocols\Http;

function test()
{

    $fileDir = explode('Web/',Config::$imgdataPath)[1];
    $fileName = "prefix_" . time() . ".jpg";
    $filePath = Config::$imgdataPath . $fileName;

    $url = "http://192.168.1.110:40002/?fn=phpstat&group=toc_bole_work&module=News&interface=delete";
    //$url = "http://www.cheng95.com/feedback";

    $content = zhtml2img($url);
    file_put_contents($filePath, $content);


    if (!isImgFile($filePath)) {
        echo 1;
        $content = zhtml2img($url);
        file_put_contents($filePath, $content);
    }



    if (!isImgFile($filePath)) {
        echo 2;
        $content = zhtml2img($url);
        file_put_contents($filePath, $content);
    }

    if (!isImgFile($filePath)) {
        echo 3;
        $content = zhtml2img($url);
        file_put_contents($filePath, $content);
    }


    include ST_ROOT . '/Views/test.tpl.php';

}

