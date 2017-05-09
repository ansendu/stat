<div class="container">
    <div class="row clearfix">
        <div class="col-md-12 column">
            <ul class="nav nav-tabs">
                <li class="<?php echo $tabFlag == 'lastweek' ? 'active' : ''; ?>">
                    <a href="/?fn=phpsummary&group=<?php echo $group ?>&date=lastweek">上周概述</a>
                </li>
                <li class="<?php echo $tabFlag == 'lastmonth' ? 'active' : ''; ?>">
                    <a href="/?fn=phpsummary&group=<?php echo $group ?>&date=lastmonth">上月概述</a>
                </li>
            </ul>
        </div>
    </div>

    <p class="row clearfix">
        <!--<div class="row clearfix">-->
        <!--    <div class="col-md-12 column text-left">-->
        <!--        <a onclick="javascript:window.history.go(-1);">&raquo;返回</a>-->
        <!--    </div>-->
        <!--</div>-->

        <!--<div class="col-md-12 column">-->
        <!--    <div class="row clearfix" style="">-->
        <!--        <div class="col-md-6 column height-400" id="suc-pie">-->
        <!--        </div>-->
        <!--    </div>-->
        <!---->
        <!--</div>-->
        <div class="col-md-12 column">
            <table class="">
            <tr>
                <td>
                    <p><span style=font-size:18px;><?php echo $date_str;?> <span style=font-size:18px;color:red;><?php echo $group?></span> 整体可用性
                            (成功:<?php echo $global_avail['total_count'] - $global_avail['fail_count'];?>个占<?php echo round($global_rate,2)?>%,<span style="color: red">失败:<?php echo $global_avail['fail_count'];?>个占<?php echo round(100-$global_rate,2)?>%</span>)以下为具体失败信息:</span>
                    </p>
                </td>
            </tr>
            </table>
        </div>


        <div class="col-md-12 column">
            <table class="table table-hover table-condensed table-bordered" style="width: 85%">
                <thead>
                <tr>
                    <th>work/模块名</th>
                    <th>接口名</th>
                    <th>失败次数</th>
                    <th>平均调用时间(秒)</th>
                    <th>超时次数</th>
                    <th>平均超时时间(秒)</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($statAll as $mod => $stat) {
                    echo "<tr><td colspan='6'>$mod</td></tr>";
                    echo "<tr>";
                    foreach ($stat as $interface => $levels) {
                        echo "<td></td><td>$interface</td>";
                        $stat = array('fail'=>array('count'=>0,'avg'=>0),'timeout_fail'=> array('count'=>0,'avg'=>0));
                        $costArr = array();
                        foreach ($levels as $key => $val){
                            if($key == 'timeout'){
                                $costArr = array_merge($costArr,$val['cost']);
                                $stat['timeout_fail']['count']+=$val['count'];
                                $stat['timeout_fail']['avg']+= round(array_sum($costArr)/count($costArr),4);
                            }
                            $costArr = array_merge($costArr,$val['cost']);
                            $stat['fail']['count']+=$val['count'];
                            $stat['fail']['avg']+= round(array_sum($costArr)/count($costArr),4);
                        }
                        if($stat['timeout_fail']['count'] == 0){
                            $stat['timeout_fail']['count'] = '';
                        }

                        if($stat['timeout_fail']['avg'] == 0){
                            $stat['timeout_fail']['avg'] = '';
                        }

                        echo "<td>{$stat['fail']['count']}</td><td>{$stat['fail']['avg']}</td>";
                        echo "<td>{$stat['timeout_fail']['count']}</td><td>{$stat['timeout_fail']['avg']}</td></tr>";
                    }
                }
                ?>

                </tbody>
            </table>
        </div>


    </div>


</div>

<script>
    Math.easeOutBounce = function (pos) {
        if ((pos) < (1 / 2.75)) {
            return (7.5625 * pos * pos);
        }
        if (pos < (2 / 2.75)) {
            return (7.5625 * (pos -= (1.5 / 2.75)) * pos + 0.75);
        }
        if (pos < (2.5 / 2.75)) {
            return (7.5625 * (pos -= (2.25 / 2.75)) * pos + 0.9375);
        }
        return (7.5625 * (pos -= (2.625 / 2.75)) * pos + 0.984375);
    };

    $('#suc-pie').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
        },
        title: {
            useHTML: true,
            text: '<span style=font-size:14px;><?php echo $date_str;?> <span style=font-size:14px;color:red;><?php echo $group?></span> 整体可用性</span>'
        },
        tooltip: {
            //pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            pointFormat: '<b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            series: {
                animation: {
                    duration: 0,
                    //easing: 'easeOutBounce'
                }
            },
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    format: '<b>{point.name}{point.count}个</b>: {point.percentage:.1f} %'
                }
            }
        },
        credits: {
            enabled: false,
        },
        series: [{
            type: 'pie',
            name: '可用性',
            data: [
                {
                    name: '成功',
                    count:<?php echo $global_avail['total_count'] - $global_avail['fail_count'];?>,
                    y: <?php echo $global_rate;?>,
//                                sliced: true,
//                                selected: true,
                    color: '#2f7ed8'
                },
                {
                    name: '失败',
                    count:<?php echo $global_avail['fail_count'];?>,
                    y: <?php echo(100 - $global_rate);?>,
//                                sliced: true,
//                                selected: true,
                    color: '#910000'
                }
            ]
        }]
    });
</script>



