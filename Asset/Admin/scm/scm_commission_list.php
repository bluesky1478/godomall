<style media="screen">
    /* 공급사 전체보기 */
    .table-wrap{position: relative;}
    .table-wrap .dim{display: none;position: absolute;left: 0;top:0;width:100%;height:100%;background-color: #000000;opacity: 0.2;filter:alpha(opacity=20);z-index:55;}
    .table-wrap .list-wrap{display: none;position: absolute;right: 1px;top: 0;width: 300px;height: 100%;background-color: #ffffff;border: 1px solid #E6E6E6;overflow: hidden;z-index:80;}
    .table-wrap .list-wrap h4{background-color: #444444;color: #ffffff;margin: 0;padding: 10px 10px 12px 10px;}
    .table-wrap .list-wrap h4 .pull-right{position: relative;top: -5px;}
    .table_scroll{overflow-y: auto;max-height: 720px;width: 100%;}
    .table-wrap .list-wrap table.table-rows{width: 100%;margin: 0;}
    .table-wrap .list-wrap table thead tr th{color: #333333;background-color: #F6F6F6!important;text-align: center;padding: 10px 0 10px 0;}
    .table-wrap .list-wrap table tbody tr th{text-align: left;padding: 10px 0 10px 10px;background-color: #ffffff!important;}
    .table-wrap .list-wrap table tbody tr th a{color: #627DCE;}
    .table-wrap .list-wrap table tbody tr td{height:60px;text-align: center;padding: 10px 0 10px 0;}

    /* 달력 선택 */
    table.cal tr th{height:40px;text-align:center;}
    .cal-wrap{height: 50px;padding-top: 11px;background-color: #F6F6F6;}
    .cal-wrap a{text-decoration: none;}
    .cal-wrap a:hover{color: #333333;}
    .cal-wrap a:visited{color: #333333;}
    .cal-wrap .form-inline{position: absolute;left: 50%;width: 200px;display: inline-block;margin-left: -100px}
    .cal-wrap .form-inline h4{font-size:18px;color:#333333;font-weight: bold;margin: 0;padding: 4px 0 7px 0;text-align: center;}
    .cal-wrap .form-inline h4 .cal{margin: 0 10px 0 10px;}
    .cal-wrap .form-inline h4 .cal img{position: relative;top:2px;left: 5px;}
    .cal-wrap .form-inline h4 .arrow img{position: relative;top:-2px;}
    .cal-wrap .form-inline .sel_wrap{display: none;overflow: hidden;position: absolute;left: 50%;top: 30px;width: 202px;height: 170px;margin-left: -101px;background-color: #ffffff;border: 1px solid #dbdbdb;z-index: 3;}
    .cal-wrap .form-inline .sel_wrap h5{position: relative;font-size:14px;color:#333333;text-align: left;background-color: #dbdbdb;margin: 0;padding: 5px 5px 5px 5px;}
    .cal-wrap .form-inline .sel_wrap h5 a{position: absolute;right: 5px;top: 3px;}
    .cal-wrap .form-inline .ul-wrap{float: left;overflow-y: scroll;width: 100px;height: 145px;}
    .cal-wrap .form-inline ul li{font-size: 14px;font-weight: normal;}
    .cal-wrap .form-inline ul li a{display: block;padding: 3px 5px 5px 5px;}
    .cal-wrap .form-inline ul li a.on{background-color: #dbdbdb;}
    /* 공급사별 달력(캘린더) */
    table.cal tr td{height:146px;font-size:12px;vertical-align:top;padding: 0;}
    table.cal tr td .top{overflow: hidden;padding:10px 10px 10px 10px;}
    table.cal tr td .top.more{background-color:#dbdbdb;cursor:pointer;}
    table.cal tr td .top span{float: right;font-size:12px;margin:0;background-color:#dbdbdb;}
    table.cal tr td .cont{padding:5px;padding-top: 0;}
    table.cal tr td .cont ul{display: block;}
    table.cal tr td .cont ul li{position: relative;margin-top:3px;width: 100%;}
    table.cal tr td .cont ul li .layer{position: absolute;left: 10px; top: 15px;display: none;padding: 7px 7px 7px 7px;background-color: #ffffff;border: 1px solid #dbdbdb;z-index: 1;white-space: nowrap;}
    table.cal tr td .cont ul li a{text-decoration: none;background-color: #D4F4FA ;}
    table.cal tr td .cont ul li a:hover{color: #333333;}
    table.cal tr td .cont ul li a:visited{color: #333333;}
    table.cal tr.week{text-align:center;font-weight:bold;}
    table.cal tr.week td{height:30px;vertical-align:middle;}
    table.cal tr.week td.sun{color:#fa2828;}
    table.cal tr.week td.sat{color:#dbdbdb;}
    table.cal tr td.before{color:#dbdbdb;}
    table.cal tr td.after{color:#dbdbdb;}
    table.cal tr td .top strong{font-size:14px;}
    table.cal tr td.sun .top strong, table.cal tr td.holi .top strong{color:#fa2828;}
    table.cal tr td.sat .top strong{color:#dbdbdb;}
    /* 공급사별 스케쥴 */
    table.schedule tr td{min-width: 22px;height: 50px;text-align: center;}
    table.schedule.table-rows tr td:first-child:after{background: none;}
    table.schedule tr td span{display: inline-block;width: 19px}
    table.schedule tr.week td{height: 30px;}
    table.schedule tr.day td{height: 30px;}
    table.schedule tr td.start{position: relative;cursor:pointer;}
    table.schedule tr td.start div{position: absolute;left: 0;top: 9px;background-color:#D4F4FA ;height:30px;padding-top: 6px;color: #333333;}
    table.schedule tr td.start div a{text-decoration: none;background-color: #D4F4FA ;}
    table.schedule tr td.start div a:hover{color: #333333;}
    table.schedule tr td.start div a:visited{color: #333333;}
    table.schedule tr td.start div ul.cont{display: block;}
    table.schedule tr td.start div ul.cont li{position: relative;margin-top:3px;width: 100%;}
    table.schedule tr td.start div ul.cont li .layer{position: absolute;left: 10px; top: 15px;display: none;padding: 7px 7px 7px 7px;background-color: #ffffff;border: 1px solid #dbdbdb;z-index: 1;white-space: pre-wrap;width:100%;!important;}
</style>

<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
    <?php if(!$isProvider) { ?>
        <div class="btn-group">
            <input type="button" class="btn btn-red-line js-scmCommission-register" value="일정 등록">
        </div>
    <?php } ?>
</div>

<form id="frmSearchScmCommissionList" method="get" class="js-form-enter-submit">
    <input type="hidden" name="mode" value="<?=$search['mode'];?>">
    <input type="hidden" name="toYear" value="<?=$search['toYear'];?>">
    <input type="hidden" name="toMonth" value="<?=$search['toMonth'];?>">
    <div>
        <?php if(!$isProvider) { ?>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>공급사</th>
                <td class="form-inline">
                    <input type="text" name="scmNmSearch" class="form-control width-lg" value="<?=$search['scmNmSearch'];?>" placeholder="공급사 명을 입력해주세요.">
                    <input type="button" value="공급사 선택" class="btn btn-sm btn-gray js-layer-register" data-type="scm" data-mode="search" data-scm-commission-set="p"/>
                    <div id="scmLayer" class="selected-btn-group <?= $search['scmFl'] == '1' && !empty($search['scmNo']) ? 'active' : '' ?>">
                        <h5>선택된 공급사 : </h5>
                        <?php if ($search['scmFl'] == '1') { ?>
                            <?php foreach ($search['scmNo'] as $k => $v) { ?>
                                <div id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
                                    <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                                    <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                                    <span class="btn"><?= $search['scmNoNm'][$k] ?></span>
                                    <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $v ?>" data-none="#scmNoneFlText">삭제</button>
                                </div>
                            <?php } ?>
                        <?php } ?>
                        <input type="button" value="전체 삭제" class="btn btn-sm btn-gray js-scm-all-delete" data-toggle="delete" data-target="#scmLayer div" data-none="#scmNoneFlText"/>
                    </div>
                </td>
                <td colspan="3" style="text-align:right;">
                    <input type="submit" value="검색" class="btn btn-lg btn-black">
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php } ?>
    <div class="cal-wrap">
        <div class="form-inline">
            <h4>
                <a href="javascript:void(0);" class="arrow" onclick="changeCalendarData('<?=$calendarData['calendar']['prevYear']?>', '<?=$calendarData['calendar']['prevMonth']?>')"><img src="/admin/gd_share/img/btn/gv-thumbnail-prev.png"></a>
                <a href="javascript:void(0);" class="cal"><?=$calendarData['calendar']['setYear'];?>.<?=$calendarData['calendar']['setMonth'];?><img src="/admin/gd_share/img/icon_arrow_down_off.png"></a>
                <a href="javascript:void(0);" class="arrow" onclick="changeCalendarData('<?=$calendarData['calendar']['nextYear']?>', '<?=$calendarData['calendar']['nextMonth']?>')" ><img src="/admin/gd_share/img/btn/gv-thumbnail-next.png"></a>
            </h4>
            <div class="sel_wrap">
                <h5>날짜선택<a href="javascript:void(0);"><img src="/admin/gd_share/img/development/icon_del_on.png"></a></h5>
                <div class="ul-wrap year">
                    <ul>
                        <?php for($i = $calendarData['calendar']['startYear']; $i < $calendarData['calendar']['endYear']; $i++ ) {
                            if($calendarData['calendar']['setYear'] == $i) {
                                $selectDateClass = "class='on'";
                            } else {
                                $selectDateClass = "";
                            }
                            ?>
                            <li><a href="javascript:void(0);" <?=$selectDateClass;?> onclick="changeCalendarData('<?=$i;?>', '<?=$calendarData['calendar']['setMonth'];?>')"><?=$i?>년</a></li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="ul-wrap month">
                    <ul>
                        <?php for($i = 1; $i <= 12; $i++) {
                            $convertI = sprintf("%02d", $i);
                            if($calendarData['calendar']['setMonth'] == $convertI) {
                                $selectDateClass = "class='on'";
                            } else {
                                $selectDateClass = "";
                            }
                            ?>
                            <li><a href="javascript:void(0);" <?=$selectDateClass;?> onclick="changeCalendarData('<?=$calendarData['calendar']['setYear']?>', '<?=$convertI;?>')"><?=$i?>월</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php if(!$isPovider ) { ?>
            <div class="pull-right">

                <?php
                if(!$isProvider) {
                    if($search['mode'] == 'schedule') { ?>
                        <input type="button" value="일자별" class="btn btn-white btn-m btn-unstoring  js-commission-calendar">
                    <?php } else if($search['mode'] == 'calendar') { ?>
                        <input type="button" value="공급사별" class="btn btn-white btn-m btn-unstoring  js-commission-schedule">
                    <?php } ?>
                    <input type="button" value="공급사 수수료 설정" class="btn btn-black btn-m btn-unstoring js-scmCommission-set">
                <?php } ?>
            </div>
        <?php } ?>
    </div>
    <?php
    $checkToday = false;
    if($calendarData['calendar']['setYear'] == $calendarData['calendar']['checkToday']['year']&& $calendarData['calendar']['setMonth'] == $calendarData['calendar']['checkToday']['month']) {
        $checkToday = true;
    }
    if(!$isProvider && $search['mode'] == 'calendar') {
        include $layoutScmCommissionCalendar;
    }
    if($search['mode'] == 'schedule') {
        include $layoutScmCommissionSchedule;
    }
    ?>
    <div class="notice-info">공급사 일정 등록 시 판매수수료와 배송비수수료를 개별로 설정한 경우 판매수수료와 배송비수수료가 “/(슬래시)”로 구분되어 표기됩니다. (10.00 / 10.00)<br/>
        배송비수수료가 “판매수수료 동일 적용“으로 설정된 경우 판매수수료 기준으로 한번만 표기됩니다. (10.00)<br/>
        공급사 수수료 일정이 종료되면 상품에 등록된 수수료가 적용되며, 추가상품은 수수료 일정과 상관없이 상품에 등록된 수수료가 적용됩니다.
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 공급사 전체 삭제 클릭 시 scm선택 Layer 비활성화
        $('.js-scm-all-delete').click(function() {
            $('#scmLayer').removeClass('active');
        });

        $(".cal-wrap .form-inline h4 .cal").on("click",function(){
            if($(".sel_wrap").css('display') == 'block') {
                $(".sel_wrap").hide();
            } else {
                $(".sel_wrap").show();
                $('.sel_wrap .ul-wrap .on')[0].scrollIntoView(false);
                $('.sel_wrap .ul-wrap .on')[1].scrollIntoView(false);
            }
        });
        $(".cal-wrap .form-inline .sel_wrap h5 a, .cal-wrap .form-inline .month ul li").on("click",function(){
            $(".sel_wrap").hide();
        });
        $(".cal-wrap .form-inline ul li a").on("click",function() {
            $(".cal-wrap .form-inline ul li a").removeClass("on");
            $(".cal-wrap .form-inline ul li a").removeAttr("id");
            $(this).addClass("on");
        });

        // 5건 이상의 스케쥴이 있을 경우 전체보기 버튼 활성화
        $(".top.more").on("click",function() {
            $(".dim").show();
            $(this).prev(".list-wrap").show();
        });
        // 5건 이상의 스케쥴 공급사별 전체보기 버튼 닫기
        $(".btn-commission-layer-close").click(function() {
            $('.list-wrap').hide();
            $('.dim').hide();
        });
        // 공급사명 마우스오버 layer
        $(".cont li a").hover(function(){
            $(this).siblings(".layer").show();
        },function(){
            $(this).siblings(".layer").hide();
        });

        // 공급사 일정등록 팝업
        $('.js-scmCommission-register').click(function() {
            <?php if($isProvider) { ?>
            url = '/provider/policy/popup_scm_commission_register.php?popupMode=yes';
            <?php } else { ?>
            url = '/scm/popup_scm_commission_register.php?popupMode=yes';
            <?php } ?>
            win = popup({
                url: url,
                width: 1000,
                height: 800,
                resizable: 'yes',
                scrollbars: 'yes'
            });
        });
        <?php if(!$isPovider) { ?>
        // 공급사수수료일정(스케쥴)이동
        $('.js-commission-schedule').click(function(e) {
            $('input[name="mode"]').val('schedule');
            $('#frmSearchScmCommissionList').submit();
        });
        // 공급사수수료달력(캘린더)이동
        $('.js-commission-calendar').click(function(e) {
            $('input[name="mode"]').val('calendar');
            $('#frmSearchScmCommissionList').submit();
        });

        //공급사 수수료 설정 팝업
        $('.js-scmCommission-set').click(function() {
            url = '/scm/popup_scm_commission_set.php?popupMode=yes';
            win = popup({
                url: url,
                width: 1000,
                height: 700,
                resizable: 'yes'
            });
        });
        <?php } ?>
    });

    <?php if(!$isPovider) { ?>
    // 날짜 변경 함수
    function changeCalendarData(changeYear, changeMonth) {
        if(changeYear) {
            var urlParam =  'toYear=' + changeYear;
        }
        if(changeMonth) {
            if(urlParam) {
                var urlParam = urlParam + '&toMonth=' + changeMonth;
            } else {
                var urlParam = 'toMonth=' + changeMonth;
            }
        }
        <?php if($search['mode'] == 'schedule') { ?>
        var urlParam = urlParam + '&mode='+'<?=$search['mode'];?>';
        <?php } ?>
        <?php if($isProvider) { ?>
        var url = '/provider/policy/scm_commission_list.php?' + urlParam;
        <?php } else { ?>
        var url = '/scm/scm_commission_list.php?' + urlParam;
        <?php } ?>
        location.href = url;
    }

    // 공급사 스케쥴 수정 팝업
    function scmScheduleModify(scmNo, scmScheduleSno) {
        <?php if($isProvider) { ?>
        url = '/provider/policy/popup_scm_commission_register.php?popupMode=yes&scmNo=' + scmNo +'&scmScheduleSno=' + scmScheduleSno;
        <?php } else { ?>
        url = '/scm/popup_scm_commission_register.php?popupMode=yes&scmNo=' + scmNo +'&scmScheduleSno=' + scmScheduleSno;
        <?php } ?>
        win = popup({
            url: url,
            width: 1000,
            height: 800,
            resizable: 'yes',
            scrollbars: 'yes'
        });
    }
    <?php } ?>
    //-->
</script>
