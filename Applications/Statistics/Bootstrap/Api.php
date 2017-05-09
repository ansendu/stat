<?php

namespace Bootstrap;

use Workerman\Lib\Timer;
use Statistics\Config;

class Api
{

    /**
     *  最大日志buffer，大于这个值就写磁盘
     * @var integer
     */
    const MAX_LOG_BUFFER_SZIE = 1024000;

    /**
     * 多长时间写一次数据到磁盘
     * @var integer
     */
    const WRITE_PERIOD_LENGTH = 60;

    /**
     * 多长时间清理一次老的磁盘数据
     * @var integer
     */
    const CLEAR_PERIOD_LENGTH = 86400;

    /**
     * 数据多长时间过期
     * @var integer
     */
    //    const EXPIRED_TIME = 62208000;

    const EXPIRED_TIME = 12960000;

    /**
     * 统计数据
     * ip=>modid=>interface=>['code'=>[xx=>count,xx=>count],'suc_cost_time'=>xx,'fail_cost_time'=>xx, 'suc_count'=>xx, 'fail_count'=>xx]
     * @var array
     */
    public static $statisticData = array();

    /**
     * 日志的buffer
     * @var string
     */
    //    public static $logBuffer = '';
    public static $logBuffer = array();


    //默认项目组
    public static $groupDir = 'default';

    /**
     * 放统计数据的目录
     * @var string
     */
    public static $statisticDir = 'statistic/statistic/';
    public static $_statisticDir = 'statistic/statistic/';

    /**
     * 存放统计日志的目录
     * @var string
     */
    public static $logDir = 'statistic/log/';
    public static $_logDir = 'statistic/log/';

    /**
     * 提供统计查询的socket
     * @var resource
     */
    public static $providerSocket = null;


    private static function setGroupDir()
    {
        self::$statisticDir = self::$groupDir . '/' . self::$statisticDir;
        self::$logDir = self::$groupDir . '/' . self::$logDir;
    }

    public static function onMessage($connection, $data)
    {

        var_dump($data);
        // 解码
        $module = $data['module'];
        $interface = $data['interface'];
        $cost_time = $data['cost_time'];
        $success = $data['success'];
        $time = $data['time'];
        $code = $data['code'];
        $msg = str_replace("\n", "<br>", $data['msg']);
        $extra = $data['extra'];
        $ip = $connection->getRemoteIp();

        $group = $extra['group'];
        if (!empty($group)) {
            self::$groupDir = $group;
        }

        self::setGroupDir();


        $_extra = json_encode($extra, true);

        //        模块接口统计
        self::collectStatistics($module, $interface, $cost_time, $success, $ip, $code, $msg);
        //         全局统计
        self::collectStatistics('WorkerMan', 'Statistics', $cost_time, $success, $ip, $code, $msg);


        //         失败记录日志
        if (!$success) {
            $level = $extra['level'] ? $extra['level'] : 'info';

            if (!isset(self::$logBuffer[self::$groupDir])) {
                self::$logBuffer[self::$groupDir] = '';
            }

            self::$logBuffer[self::$groupDir] .= trim($level) . "\t" . date('Y-m-d H:i:s', $time) . "\t$ip\t$module::$interface\tcode:$code\tmsg:$msg\textra:$_extra\t$cost_time\n";
            if (strlen(self::$logBuffer[self::$groupDir]) >= self::MAX_LOG_BUFFER_SZIE) {
                self::writeLogToDisk(self::$groupDir);
            }
        }


    }

    public static function collectStatistics($module, $interface, $cost_time, $success, $ip, $code)
    {
        // 统计相关信息
        if (!isset(self::$statisticData[$ip])) {
            self::$statisticData[$ip] = array();
        }
        if (!isset(self::$statisticData[$ip][self::$groupDir])) {
            self::$statisticData[$ip][self::$groupDir] = array();
        }

        if (!isset(self::$statisticData[$ip][self::$groupDir][$module])) {
            self::$statisticData[$ip][self::$groupDir][$module] = array();
        }
        if (!isset(self::$statisticData[$ip][self::$groupDir][$module][$interface])) {
            self::$statisticData[$ip][self::$groupDir][$module][$interface] = array('code' => array(), 'suc_cost_time' => 0, 'fail_cost_time' => 0, 'suc_count' => 0, 'fail_count' => 0,);
        }
        if (!isset(self::$statisticData[$ip][self::$groupDir][$module][$interface]['code'][$code])) {
            self::$statisticData[$ip][self::$groupDir][$module][$interface]['code'][$code] = 0;
        }

        self::$statisticData[$ip][self::$groupDir][$module][$interface]['code'][$code]++;
        if ($success) {
            self::$statisticData[$ip][self::$groupDir][$module][$interface]['suc_cost_time'] += $cost_time;
            self::$statisticData[$ip][self::$groupDir][$module][$interface]['suc_count']++;
        } else {
            self::$statisticData[$ip][self::$groupDir][$module][$interface]['fail_cost_time'] += $cost_time;
            self::$statisticData[$ip][self::$groupDir][$module][$interface]['fail_count']++;
        }
    }


    /**
     * 将日志数据写入磁盘
     * @return void
     */
    public static function writeLogToDisk($gr = null)
    {
        if (!empty($gr)) {
            if (!isset(self::$logBuffer[$gr]) || empty(self::$logBuffer[$gr])) {
                return;
            }
            $log_dir = Config::$apidataPath . $gr . '/' . self::$_logDir;
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0777, true);
            }

            // 写入磁盘
            file_put_contents($log_dir . date('Y-m-d'), self::$logBuffer[$gr], FILE_APPEND | LOCK_EX);
            unset(self::$logBuffer[$gr]);

            return;
        }


        // 没有统计数据则返回
        if (empty(self::$logBuffer)) {
            return;
        }
        $groupKeys = array_keys(self::$logBuffer);
        foreach ($groupKeys as $g) {
            $log_dir = Config::$apidataPath . $g . '/' . self::$_logDir;
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0777, true);
            }

            $statistic_dir = Config::$apidataPath . $g . '/' . self::$_statisticDir;
            if (!is_dir($statistic_dir)) {
                mkdir($statistic_dir, 0777, true);
            }
        }
        foreach (self::$logBuffer as $group => $val) {
            $logDir = $group . '/' . self::$_logDir;
            file_put_contents(Config::$apidataPath . $logDir . date('Y-m-d'), self::$logBuffer[$group], FILE_APPEND | LOCK_EX);
            unset(self::$logBuffer[$group]);
        }
        self::$logBuffer = array();
    }

    /**
     * 将统计数据写入磁盘
     * @return void
     */
    public static function writeStatisticsToDisk()
    {
        $time = time();
        // 循环将每个ip的group的统计数据写入磁盘
        foreach (self::$statisticData as $ip => $groups) {
            foreach ($groups as $g => $mod_if_data) {
                foreach ($mod_if_data as $module => $items) {
                    // 文件夹不存在则创建一个
                    $file_dir = Config::$apidataPath . $g . '/' . self::$_statisticDir . $module;
                    if (!is_dir($file_dir)) {
                        umask(0);
                        mkdir($file_dir, 0777, true);
                    }
                    // 依次写入磁盘
                    foreach ($items as $interface => $data) {
                        $interface = str_replace('\\', '-', str_replace('/', '-', $interface));
                        file_put_contents($file_dir . "/{$interface}." . date('Y-m-d'), "$ip\t$time\t{$data['suc_count']}\t{$data['suc_cost_time']}\t{$data['fail_count']}\t{$data['fail_cost_time']}\t" . json_encode($data['code']) . "\n", FILE_APPEND | LOCK_EX);
                    }
                }
            }
        }
        self::$statisticData = array();
    }


    /**
     * 清除磁盘数据
     * @param string $file
     * @param int $exp_time
     */
    public static function clearDisk($file = null, $exp_time = 86400)
    {
        $time_now = time();
        if (is_file($file)) {
            $mtime = filemtime($file);
            if (!$mtime) {
                StatisticWorker::log("filemtime $file fail");

                //                $this->notice("filemtime $file fail");
                return;
            }
            if ($time_now - $mtime > $exp_time) {
                unlink($file);
            }

            return;
        }
        foreach (glob($file . "/*") as $file_name) {
            self::clearDisk($file_name, $exp_time);
        }
    }


    public static function onStart()
    {

        // 初始化目录
        umask(0);

        self::setGroupDir();

        $img_dir = Config::$imgdataPath;
        if (!is_dir($img_dir)) {
            mkdir($img_dir, 0777, true);
        }
        $statistic_dir = Config::$apidataPath . self::$statisticDir;
        if (!is_dir($statistic_dir)) {
            mkdir($statistic_dir, 0777, true);
        }
        $log_dir = Config::$apidataPath . self::$logDir;
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }

        Timer::add(self::WRITE_PERIOD_LENGTH, array(__CLASS__, "writeStatisticsToDisk"));
        Timer::add(self::WRITE_PERIOD_LENGTH, array(__CLASS__, "writeLogToDisk"));

        Timer::add(self::CLEAR_PERIOD_LENGTH, array(__CLASS__, 'clearDisk'), array(Config::$apidataPath . self::$_statisticDir, self::EXPIRED_TIME));
        Timer::add(self::CLEAR_PERIOD_LENGTH, array(__CLASS__, 'clearDisk'), array(Config::$apidataPath . self::$_logDir, self::EXPIRED_TIME));
    }

    public static function onStop()
    {
        self::writeLogToDisk();
        self::writeStatisticsToDisk();
    }


}


?>