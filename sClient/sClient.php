<?php

class sClient
{
    protected static $timeMap = array();

    public static function tick($module = '', $interface = '')
    {
        return self::$timeMap[$module][$interface] = microtime(true);
    }

    //后端api统一上报入口
    public static function reportApi($module, $interface, $success, $code, $msg, $extra, $report_address = '')
    {
        if (!is_array($extra)) {
            $extra = array(
                '_desc' => $extra,
                'group' => 'default'
            );
        }
        $extra['_module'] = 'api';
        $extra = json_encode($extra);

        $report_address = $report_address ? $report_address : 'udp://127.0.0.1:40001';
        if (isset(self::$timeMap[$module][$interface]) && self::$timeMap[$module][$interface] > 0) {
            $time_start = self::$timeMap[$module][$interface];
            self::$timeMap[$module][$interface] = 0;
        } else if (isset(self::$timeMap['']['']) && self::$timeMap[''][''] > 0) {
            $time_start = self::$timeMap[''][''];
            self::$timeMap[''][''] = 0;
        } else {
            $time_start = microtime(true);
        }

        $cost_time = microtime(true) - $time_start;
        $bin_data = StatisticProtocol::encode($module, $interface, $cost_time, $success, $code, $msg, $extra);
        return self::sendData($report_address, $bin_data);
    }

    public static function sendData($address, $buffer)
    {
        $socket = stream_socket_client($address);
        if (!$socket) {
            return false;
        }
        return stream_socket_sendto($socket, $buffer) == strlen($buffer);
    }

}

class StatisticProtocol
{

    const PACKAGE_FIXED_LENGTH = 19;

    const MAX_UDP_PACKGE_SIZE = 65507;

    const MAX_CHAR_VALUE = 255;

    const MAX_UNSIGNED_SHORT_VALUE = 65535;

    public static function encode($module, $interface, $cost_time, $success, $code = 0, $msg = '', $extra = '')
    {
        if (strlen($module) > self::MAX_CHAR_VALUE) {
            $module = substr($module, 0, self::MAX_CHAR_VALUE);
        }

        if (strlen($interface) > self::MAX_CHAR_VALUE) {
            $interface = substr($interface, 0, self::MAX_CHAR_VALUE);
        }

        $module_name_length = strlen($module);
        $interface_name_length = strlen($interface);
        $avalible_size = self::MAX_UDP_PACKGE_SIZE - self::PACKAGE_FIXED_LENGTH - $module_name_length - $interface_name_length;
        if (strlen($msg) > $avalible_size) {
            $msg = substr($msg, 0, $avalible_size);
        }
        return pack('CCfCNnnN', $module_name_length, $interface_name_length, $cost_time, $success ? 1 : 0, $code, strlen($msg), strlen($extra), time()) . $module . $interface . $msg . $extra;
    }

    public static function decode($bin_data)
    {
        $data = unpack("Cmodule_name_len/Cinterface_name_len/fcost_time/Csuccess/Ncode/nmsg_len/Ntime", $bin_data);
        $module = substr($bin_data, self::PACKAGE_FIXED_LENGTH, $data['module_name_len']);
        $interface = substr($bin_data, self::PACKAGE_FIXED_LENGTH + $data['module_name_len'], $data['interface_name_len']);
        $msg = substr($bin_data, self::PACKAGE_FIXED_LENGTH + $data['module_name_len'] + $data['interface_name_len']);
        return array(
            'module'    => $module,
            'interface' => $interface,
            'cost_time' => $data['cost_time'],
            'success'   => $data['success'],
            'time'      => $data['time'],
            'code'      => $data['code'],
            'msg'       => $msg,
        );
    }

}
