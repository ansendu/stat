<div class="container">
    <div class="row clearfix">
        <div class="col-md-12 column">
            <ul class="nav nav-tabs">


                <li class="active">
                    <a href="/">接口概述</a>
                </li>
                <li>
                    <a href="/?fn=phpstathome">接口列表</a>
                </li>

                <!--                <li>-->
                <!--                    <a href="/?fn=work">work接口概述</a>-->
                <!--                </li>-->
                <!--                <li>-->
                <!--                    <a href="/?fn=workstat">work接口列表</a>-->
                <!--                </li>-->


                <!--                <li>-->
                <!--                    <a href="/?fn=logger">日志</a>-->
                <!--                </li>-->
                <!--				<li class="disabled">-->
                <!--					<a href="#">告警</a>-->
                <!--				</li>-->
                <li class="dropdown pull-right">
                    <a href="#" data-toggle="dropdown" class="dropdown-toggle">其它<strong class="caret"></strong></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="/?fn=admin&act=detect_server">探测数据源</a>
                        </li>
                        <li>
                            <a href="/?fn=admin">数据源管理</a>
                        </li>
                        <li>
                            <a href="/?fn=setting">设置</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>

    <div class="row clearfix">
        <div class="col-md-12 column">
            <?php if ($err_msg) { ?>
                <div class="alert alert-dismissable alert-danger">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <strong><?php echo $err_msg; ?></strong>
                </div>
            <?php } elseif ($notice_msg) { ?>
                <div class="alert alert-dismissable alert-info">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <strong><?php echo $notice_msg; ?></strong>
                </div>
            <?php } ?>
            <div class="row clearfix">
                <div class="col-md-12 column text-center">
                    <span style='font-size:18px;font-weight:bold;'><?php echo $group; ?></span>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <span style='font-size:14px;'><a href="/?fn=phpsummary&group=<?php echo $group;?>&date=lastweek" target="_blank">上周概述</a> | <a href="/?fn=phpsummary&group=<?php echo $group;?>&date=lastmonth" target="_blank">上月概述</a></span>

                </div>
            </div>
            <div class="row clearfix">
                <div class="col-md-12 column text-center">
                    <?php echo $date_btn_str; ?>
                </div>
            </div>
            <div class="row clearfix" style="border-bottom:1px solid #000;margin-bottom:20px;">
                <div class="col-md-6 column height-400" id="suc-pie">
                </div>
                <div class="col-md-6 column height-400" id="level-pie">
                </div>
            </div>

            <div class="row clearfix">

                <!--                <div class="col-md-6 column height-400" id="code-pie">-->
                <!--                </div>-->

                <div class="col-md-6 column height-400" id="code_pie_notice" style="display:none;">
                </div>
                <div class="col-md-6 column height-400" id="code_pie_warn" style="display:none;">
                </div>
                <div class="col-md-6 column height-400" id="code_pie_error" style="display:none;">
                </div>
                <div class="col-md-6 column height-400" id="code_pie_timeout" style="display:none;">
                </div>
                <div class="col-md-6 column height-400" id="code_pie_unknown" style="display:none;">
                </div>

                <div class="col-md-6 column height-400" id="level_pie_notice" style="display:none;">
                </div>

                <div class="col-md-6 column height-400" id="level_pie_warn" style="display:none;">
                </div>

                <div class="col-md-6 column height-400" id="level_pie_error" style="display:none;">
                </div>

                <div class="col-md-6 column height-400" id="level_pie_timeout" style="display:none;">
                </div>

                <div class="col-md-6 column height-400" id="level_pie_unknown" style="display:none;">
                </div>
            </div>


            <div class="row clearfix">
                <div class="col-md-12 column height-400" id="req-container">
                </div>
            </div>
            <div class="row clearfix">
                <div class="col-md-12 column height-400" id="time-container">
                </div>
            </div>
            <script>

                //level api
                function pie_notice_api() {
                    $("#level_pie_notice").show();
                    $("#level_pie_warn").hide();
                    $("#level_pie_error").hide();
                    $("#level_pie_timeout").hide();
                    $("#level_pie_unknown").hide();

                    $('#level_pie_notice').highcharts({
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                        },
                        title: {
                            useHTML: true,
                            style: {"color": 'green'},
//                            text: '<?php //echo $date;?>// notice分布'
                            text: "<a style='color:green;' target='_blank' href='/?fn=phploggerext&group=<?php echo $group?>&level=notice&date=<?php echo $date;?>'>&raquo;<?php echo $date;?> notice api分布</a>"
                        },
                        tooltip: {
                            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    color: 'green',
                                    connectorColor: 'green',
                                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                                },
                                events: {
                                    click: function (e) {
                                        window.open('/?fn=phploggerext&group=<?php echo $group?>&level=notice&date=<?php echo $date;?>&api=' + e.point.api);
                                    }
                                },
                            }
                        },
                        credits: {
                            enabled: false,
                        },
                        series: [{
                            type: 'pie',
                            name: '分布',
                            data: <?php echo json_encode($apis_pie_data['notice']);?>
                        }]
                    });

                }
                function pie_warn_api() {
                    $("#level_pie_notice").hide();
                    $("#level_pie_warn").show();
                    $("#level_pie_error").hide();
                    $("#level_pie_timeout").hide();
                    $("#level_pie_unknown").hide();

                    $('#level_pie_warn').highcharts({
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                        },
                        title: {
                            useHTML: true,
                            style: {"color": 'blue'},
                            text: "<a style='color:blue;' target='_blank' href='/?fn=phploggerext&group=<?php echo $group?>&level=warn&date=<?php echo $date;?>'>&raquo;<?php echo $date;?> warn api分布</a>"
                        },
                        tooltip: {
                            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    color: 'blue',
                                    connectorColor: 'blue',
                                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                                },
                                events: {
                                    click: function (e) {
                                        window.open('/?fn=phploggerext&group=<?php echo $group?>&level=warn&date=<?php echo $date;?>&api=' + e.point.api);
                                    }
                                },
                            }
                        },
                        credits: {
                            enabled: false,
                        },
                        series: [{
                            type: 'pie',
                            name: '分布',
                            data: <?php echo json_encode($apis_pie_data['warn']);?>
                        }]
                    });
                }
                function pie_error_api() {
                    $("#level_pie_notice").hide();
                    $("#level_pie_warn").hide();
                    $("#level_pie_error").show();
                    $("#level_pie_timeout").hide();
                    $("#level_pie_unknown").hide();

                    $('#level_pie_error').highcharts({
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                        },
                        title: {
                            useHTML: true,
                            style: {"color": 'red'},
                            text: "<a style='color:red;' target='_blank' href='/?fn=phploggerext&group=<?php echo $group?>&level=error&date=<?php echo $date;?>'>&raquo;<?php echo $date;?> error api分布</a>"
                        },
                        tooltip: {
                            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    color: 'red',
                                    connectorColor: 'red',
                                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                                },
                                events: {
                                    click: function (e) {
                                        window.open('/?fn=phploggerext&group=<?php echo $group?>&level=error&date=<?php echo $date;?>&api=' + e.point.api);
                                    }
                                },
                            }
                        },
                        credits: {
                            enabled: false,
                        },
                        series: [{
                            type: 'pie',
                            name: '分布',
                            data: <?php echo json_encode($apis_pie_data['error']);?>
                        }]
                    });
                }
                function pie_timeout_api() {
                    $("#level_pie_notice").hide();
                    $("#level_pie_warn").hide();
                    $("#level_pie_error").hide();
                    $("#level_pie_timeout").show();
                    $("#level_pie_unknown").hide();

                    $('#level_pie_timeout').highcharts({
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                        },
                        title: {
                            useHTML: true,
                            style: {"color": 'brown'},
                            text: "<a style='color:brown;' target='_blank' href='/?fn=phploggerext&group=<?php echo $group?>&level=timeout&date=<?php echo $date;?>'>&raquo;<?php echo $date;?> timeout api分布</a>"
                        },
                        tooltip: {
                            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    color: 'brown',
                                    connectorColor: 'brown',
                                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                                },
                                events: {
                                    click: function (e) {
                                        window.open('/?fn=phploggerext&group=<?php echo $group?>&level=timeout&date=<?php echo $date;?>&api=' + e.point.api);
                                    }
                                },
                            }
                        },
                        credits: {
                            enabled: false,
                        },
                        series: [{
                            type: 'pie',
                            name: '分布',
                            data: <?php echo json_encode($apis_pie_data['timeout']);?>
                        }]
                    });
                }
                function pie_unknown_api() {
                    $("#level_pie_notice").hide();
                    $("#level_pie_warn").hide();
                    $("#level_pie_error").hide();
                    $("#level_pie_timeout").hide();
                    $("#level_pie_unknown").show();

                    $('#level_pie_unknown').highcharts({
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                        },
                        title: {
                            useHTML: true,
                            style: {"color": 'black'},
//                            text: '<?php //echo $date;?>// unknown分布'
                            text: "<a style='color:black;' target='_blank' href='/?fn=phploggerext&group=<?php echo $group?>&level=unknown&date=<?php echo $date;?>'>&raquo;<?php echo $date;?> unknown api分布</a>"
                        },
                        tooltip: {
                            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    color: '#000000',
                                    connectorColor: '#000000',
                                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                                },
                                events: {
                                    click: function (e) {
                                        window.open('/?fn=phploggerext&group=<?php echo $group?>&level=unknown&date=<?php echo $date;?>&api=' + e.point.api);
                                    }
                                },
                            }
                        },
                        credits: {
                            enabled: false,
                        },
                        series: [{
                            type: 'pie',
                            name: '分布',
                            data: <?php echo json_encode($apis_pie_data['unknown']);?>
                        }]
                    });
                }
                function pie_undefined_api() {
                    return;
                }

                //level code
                function pie_notice_code() {
                    $("#code_pie_notice").show();
                    $("#code_pie_warn").hide();
                    $("#code_pie_error").hide();
                    $("#code_pie_timeout").hide();
                    $("#code_pie_unknown").hide();

                    $('#code_pie_notice').highcharts({
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                        },
                        title: {
                            useHTML: true,
                            style: {"color": 'green'},
                            text: "<a style='color:green;' target='_blank' href='/?fn=phploggerext&group=<?php echo $group?>&level=notice&date=<?php echo $date;?>'>&raquo;<?php echo $date;?> notice code分布</a>"
                        },
                        tooltip: {
                            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    color: 'green',
                                    connectorColor: 'green',
                                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                                },
                                events: {
                                    click: function (e) {
                                        window.open('/?fn=phploggerext&group=<?php echo $group?>&level=notice&date=<?php echo $date;?>&code=' + e.point.code);
                                    }
                                },
                            }
                        },
                        credits: {
                            enabled: false,
                        },
                        series: [{
                            type: 'pie',
                            name: '分布',
                            data: <?php echo json_encode($codes_pie_data['notice']);?>
                        }]
                    });

                }
                function pie_warn_code() {
                    $("#code_pie_notice").hide();
                    $("#code_pie_warn").show();
                    $("#code_pie_error").hide();
                    $("#code_pie_timeout").hide();
                    $("#code_pie_unknown").hide();

                    $('#code_pie_warn').highcharts({
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                        },
                        title: {
                            useHTML: true,
                            style: {"color": 'blue'},
                            text: "<a style='color:blue;' target='_blank' href='/?fn=phploggerext&group=<?php echo $group?>&level=warn&date=<?php echo $date;?>'>&raquo;<?php echo $date;?> warn code分布</a>"
                        },
                        tooltip: {
                            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    color: 'blue',
                                    connectorColor: 'blue',
                                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                                },
                                events: {
                                    click: function (e) {
                                        window.open('/?fn=phploggerext&group=<?php echo $group?>&level=warn&date=<?php echo $date;?>&code=' + e.point.code);
                                    }
                                },
                            }
                        },
                        credits: {
                            enabled: false,
                        },
                        series: [{
                            type: 'pie',
                            name: '分布',
                            data: <?php echo json_encode($codes_pie_data['warn']);?>
                        }]
                    });

                }
                function pie_error_code() {
                    $("#code_pie_notice").hide();
                    $("#code_pie_warn").hide();
                    $("#code_pie_error").show();
                    $("#code_pie_timeout").hide();
                    $("#code_pie_unknown").hide();

                    $('#code_pie_error').highcharts({
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                        },
                        title: {
                            useHTML: true,
                            style: {"color": 'red'},
                            text: "<a style='color:red;' target='_blank' href='/?fn=phploggerext&group=<?php echo $group?>&level=error&date=<?php echo $date;?>'>&raquo;<?php echo $date;?> error code分布</a>"
                        },
                        tooltip: {
                            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    color: 'red',
                                    connectorColor: 'red',
                                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                                },
                                events: {
                                    click: function (e) {
                                        window.open('/?fn=phploggerext&group=<?php echo $group?>&level=error&date=<?php echo $date;?>&code=' + e.point.code);
                                    }
                                },
                            }
                        },
                        credits: {
                            enabled: false,
                        },
                        series: [{
                            type: 'pie',
                            name: '分布',
                            data: <?php echo json_encode($codes_pie_data['error']);?>
                        }]
                    });

                }
                function pie_timeout_code() {
                    $("#code_pie_notice").hide();
                    $("#code_pie_warn").hide();
                    $("#code_pie_error").hide();
                    $("#code_pie_timeout").show();
                    $("#code_pie_unknown").hide();

                    $('#code_pie_timeout').highcharts({
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                        },
                        title: {
                            useHTML: true,
                            style: {"color": 'brown'},
                            text: "<a style='color:brown;' target='_blank' href='/?fn=phploggerext&group=<?php echo $group?>&level=timeout&date=<?php echo $date;?>'>&raquo;<?php echo $date;?> timeout code分布</a>"
                        },
                        tooltip: {
                            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    color: 'brown',
                                    connectorColor: 'brown',
                                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                                },
                                events: {
                                    click: function (e) {
                                        window.open('/?fn=phploggerext&group=<?php echo $group?>&level=timeout&date=<?php echo $date;?>&code=' + e.point.code);
                                    }
                                },
                            }
                        },
                        credits: {
                            enabled: false,
                        },
                        series: [{
                            type: 'pie',
                            name: '分布',
                            data: <?php echo json_encode($codes_pie_data['timeout']);?>
                        }]
                    });

                }
                function pie_unknown_code() {
                    $("#code_pie_notice").hide();
                    $("#code_pie_warn").hide();
                    $("#code_pie_error").hide();
                    $("#code_pie_timeout").hide();
                    $("#code_pie_unknown").show();

                    $('#code_pie_unknown').highcharts({
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false,
                        },
                        title: {
                            useHTML: true,
                            style: {"color": 'black'},
                            text: "<a style='color:black;' target='_blank' href='/?fn=phploggerext&group=<?php echo $group?>&level=unknown&date=<?php echo $date;?>'>&raquo;<?php echo $date;?> unknown code分布</a>"
                        },
                        tooltip: {
                            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        },
                        plotOptions: {
                            pie: {
                                allowPointSelect: true,
                                cursor: 'pointer',
                                dataLabels: {
                                    enabled: true,
                                    color: 'black',
                                    connectorColor: 'black',
                                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                                },
                                events: {
                                    click: function (e) {
                                        window.open('/?fn=phploggerext&group=<?php echo $group?>&level=unknown&date=<?php echo $date;?>&code=' + e.point.code);
                                    }
                                },
                            }
                        },
                        credits: {
                            enabled: false,
                        },
                        series: [{
                            type: 'pie',
                            name: '分布',
                            data: <?php echo json_encode($codes_pie_data['unknown']);?>
                        }]
                    });

                }
                function pie_undefined_code() {
                    return;
                }


                Highcharts.setOptions({
                    global: {
                        useUTC: false
                    }
                });
                $('#suc-pie').highcharts({
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false
                    },
                    title: {
                        text: '<?php echo $date;?> 整体可用性'
                    },
                    tooltip: {
                        //pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        pointFormat: '<b>{point.percentage:.1f}%</b>'
                    },
                    plotOptions: {
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
                                count:<?php echo $total_count - $fail_count;?>,
                                y: <?php echo $global_rate;?>,
//                                sliced: true,
//                                selected: true,
                                color: '#2f7ed8'
                            },
                            {
                                name: '失败',
                                count:<?php echo $fail_count;?>,
                                y: <?php echo(100 - $global_rate);?>,
//                                sliced: true,
//                                selected: true,
                                color: '#910000'
                            }
                        ]
                    }]
                });
                $('#level-pie').highcharts({
                    chart: {
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false,
                    },
                    title: {
                        text: '<?php echo $date;?> 错误等级整体分布'
                    },
                    tooltip: {
                        useHTML: true,
                        formatter: function () {
                            return "<span style='color:" + this.point.color + "'>" + this.point.name + "<br>" + this.series.name + Highcharts.numberFormat(this.point.percentage, 1) + "  %</span>";
                        },

                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: false,
                            cursor: 'pointer',
                            dataLabels: {
                                useHTML: true,
                                enabled: true,
                                colorByPoint: true,
//                                color: '#000000',
//                                connectorColor: '#000000',
//                                format: "<b>{point.name}</b>: {point.percentage:.1f} %"
                                formatter: function () {
                                    pn = this.point.name;
                                    ppc = Highcharts.numberFormat(this.point.percentage, 1);
                                    return "<span style='color:" + this.point.color + "'><br>" + pn + "</b>:" + ppc + "  %</span>";
                                },
                            },
                            events: {
                                click: function (e) {
                                    eval('pie_' + e.point.level + '_api()');
                                    eval('pie_' + e.point.level + '_code()');
                                }
                            },
                        }
                    },
                    credits: {
                        enabled: false,
                    },
                    series: [{
                        type: 'pie',
                        name: '错误等级分布',
                        data: <?php echo json_encode($level_pie_data);?>
                    }]
                });

                //返回码整体分布
                //                $('#code-pie').highcharts({
                //                    chart: {
                //                        plotBackgroundColor: null,
                //                        plotBorderWidth: null,
                //                        plotShadow: false,
                //                    },
                //                    title: {
                //                        text: '<?php //echo $date;?>// 返回码分布'
                //                    },
                //                    tooltip: {
                //                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                //                    },
                //                    plotOptions: {
                //                        pie: {
                //                            allowPointSelect: true,
                //                            cursor: 'pointer',
                //                            dataLabels: {
                //                                enabled: true,
                //                                color: '#000000',
                //                                connectorColor: '#000000',
                //                                format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                //                            }
                //                        }
                //                    },
                //                    credits: {
                //                        enabled: false,
                //                    },
                //                    series: [{
                //                        type: 'pie',
                //                        name: '返回码分布',
                //                        data: [
                ////                            ["999:3", 12.5], ["1111:7个", 29.1667], ["6577:5个", 20.8333], ["10001:9个", 37.5]
                //                            <?php //echo $code_pie_data;?>
                //                        ]
                //                    }]
                //                });


                //                $('#level-pie').highcharts({
                //                    chart: {
                //                        type: 'pie',
                //                        options3d: {
                //                            enabled: true,
                //                            alpha: 45,
                //                            beta: 0
                //                        }
                //                    },
                //                    title: {
                //                        text: '<?php //echo $date;?>// 错误等级整体分布'
                //                    },
                //                    tooltip: {
                //                        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                //                    },
                //                    plotOptions: {
                //                        pie: {
                //                            allowPointSelect: true,
                //                            cursor: 'pointer',
                //                            depth: 35,
                //                            dataLabels: {
                //                                enabled: true,
                //                                format: '{point.name}'
                //                            },
                //                            events: {
                //                                click: function (e) {
                //                                    eval('pie_' + e.point.level + '()');
                //                                }
                //                            },
                //                        }
                //                    },
                //                    series: [{
                //                        type: 'pie',
                //                        name: '错误等级分布',
                //                        data: <?php //echo json_encode($level_pie_data);?>
                //                    }]
                //                });


                $('#req-container').highcharts({
                    chart: {
                        type: 'spline'
                    },
                    title: {
                        text: '<?php echo "$date $interface_name";?>  请求量曲线'
                    },
                    subtitle: {
                        text: ''
                    },
                    xAxis: {
                        type: 'datetime',
                        dateTimeLabelFormats: {
                            hour: '%H:%M'
                        }
                    },
                    yAxis: {
                        title: {
                            text: '请求量(次/5分钟)'
                        },
                        min: 0
                    },
                    tooltip: {
                        formatter: function () {
                            return '<p style="color:' + this.series.color + ';font-weight:bold;">'
                                + this.series.name +
                                '</p><br /><p style="color:' + this.series.color + ';font-weight:bold;">时间：' + Highcharts.dateFormat('%m月%d日 %H:%M', this.x) +
                                '</p><br /><p style="color:' + this.series.color + ';font-weight:bold;">数量：' + this.y + '</p>';
                        }
                    },
                    credits: {
                        enabled: false,
                    },
                    series: [{
                        name: '成功曲线',
                        data: [
                            <?php echo $success_series_data;?>
                        ],
                        lineWidth: 2,
                        marker: {
                            radius: 1
                        },

                        pointInterval: 300 * 1000
                    },
                        {
                            name: '失败曲线',
                            data: [
                                <?php echo $fail_series_data;?>
                            ],
                            lineWidth: 2,
                            marker: {
                                radius: 1
                            },
                            pointInterval: 300 * 1000,
                            color: '#9C0D0D'
                        }]
                });
                $('#time-container').highcharts({
                    chart: {
                        type: 'spline'
                    },
                    title: {
                        text: '<?php echo "$date $interface_name";?>  请求耗时曲线'
                    },
                    subtitle: {
                        text: ''
                    },
                    xAxis: {
                        type: 'datetime',
                        dateTimeLabelFormats: {
                            hour: '%H:%M'
                        }
                    },
                    yAxis: {
                        title: {
                            text: '平均耗时(单位：秒)'
                        },
                        min: 0
                    },
                    tooltip: {
                        formatter: function () {
                            return '<p style="color:' + this.series.color + ';font-weight:bold;">'
                                + this.series.name +
                                '</p><br /><p style="color:' + this.series.color + ';font-weight:bold;">时间：' + Highcharts.dateFormat('%m月%d日 %H:%M', this.x) +
                                '</p><br /><p style="color:' + this.series.color + ';font-weight:bold;">平均耗时：' + this.y + '</p>';
                        }
                    },
                    credits: {
                        enabled: false,
                    },
                    series: [{
                        name: '成功曲线',
                        data: [
                            <?php echo $success_time_series_data;?>
                        ],
                        lineWidth: 2,
                        marker: {
                            radius: 1
                        },
                        pointInterval: 300 * 1000
                    },
                        {
                            name: '失败曲线',
                            data: [
                                <?php echo $fail_time_series_data;?>
                            ],
                            lineWidth: 2,
                            marker: {
                                radius: 1
                            },
                            pointInterval: 300 * 1000,
                            color: '#9C0D0D'
                        }]
                });
            </script>
            <style>

                .fixed_headers thead tr {
                    display: block;
                    position: relative;
                }

                .fixed_headers tbody {
                    display: block;
                    overflow: auto;
                    height: 750px;
                }

                .fixed_headers td:nth-child(1),
                .fixed_headers th:nth-child(1) {
                    min-width: 150px;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    overflow: hidden;
                }

                .fixed_headers td:nth-child(2),
                .fixed_headers th:nth-child(2) {
                    min-width: 150px;
                }

                .fixed_headers td:nth-child(3),
                .fixed_headers th:nth-child(3) {
                    min-width: 150px;
                }

                .fixed_headers td:nth-child(4),
                .fixed_headers th:nth-child(4) {
                    min-width: 150px;
                }

                .fixed_headers td:nth-child(5),
                .fixed_headers th:nth-child(5) {
                    min-width: 150px;
                }

                .fixed_headers td:nth-child(6),
                .fixed_headers th:nth-child(6) {
                    min-width: 150px;
                }

                .fixed_headers td:nth-child(7),
                .fixed_headers th:nth-child(7) {
                    min-width: 150px;
                }

                .fixed_headers td:nth-child(8),
                .fixed_headers th:nth-child(8) {
                    min-width: 150px;
                }
            </style>
            <table class="table table-hover table-condensed table-bordered fixed_headers">
                <thead>
                <tr>
                    <th>时间</th>
                    <th>调用总数</th>
                    <th>平均耗时</th>
                    <th>成功调用总数</th>
                    <th>成功平均耗时</th>
                    <th>失败调用总数</th>
                    <th>失败平均耗时</th>
                    <th>成功率</th>
                </tr>
                </thead>
                <tbody>
                <?php echo $table_data; ?>
                </tbody>
            </table>
        </div>
    </div>


</div>
