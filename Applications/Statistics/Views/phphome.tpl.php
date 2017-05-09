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
                <div class="col-md-12 column text-left">
                    <?php echo $group_str?>
                </div>
            </div>
        </div>
    </div>


</div>
