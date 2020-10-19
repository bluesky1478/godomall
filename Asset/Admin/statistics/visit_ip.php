<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<div class="table-title gd-help-manual">방문자 IP 검색 <?php if ($noticeFl) { ?><span class="notice-danger">PC/모바일 구분 데이터가 2017년 2월23일부터 제공됨에 따라 2017년 2월23일 이전 데이터는 PC/모바일 구분 데이터가 제공되지 않습니다.</span><?php } ?></div>
<form id="frmSearchStatistics" method="get">
    <input type="hidden" name="searchDevice" value="<?=$searchDevice?>">
    <input type="hidden" name="mode">
    <input type="hidden" name="dataTypes">
    <input type="hidden" name="searchOS">
    <input type="hidden" name="searchBrowser">
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>기간검색</th>
            <td>
                <div class="form-inline">
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control width-xs start-date" name="searchDate[]" value="<?= $searchDate[0]; ?>"/>
                        <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control width-xs end-date" name="searchDate[]" value="<?php echo $searchDate[1]; ?>"/>
                        <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                    </div>

                    <div class="btn-group js-dateperiod" data-toggle="buttons" data-target-name="searchDate[]">
                        <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['0']; ?>">
                            <input type="radio" name="searchPeriod" value="0" <?= $checked['searchPeriod']['0']; ?> >오늘
                        </label>
                        <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['6']; ?>">
                            <input type="radio" name="searchPeriod" value="6" <?= $checked['searchPeriod']['6']; ?> >7일
                        </label>
                        <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['14']; ?>">
                            <input type="radio" name="searchPeriod" value="14" <?= $checked['searchPeriod']['14']; ?> >15일
                        </label>
                        <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['29']; ?>">
                            <input type="radio" name="searchPeriod" value="29" <?= $checked['searchPeriod']['29']; ?> >1개월
                        </label>
                        <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['89']; ?>">
                            <input type="radio" name="searchPeriod" value="89" <?= $checked['searchPeriod']['89']; ?> >3개월
                        </label>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>IP 검색</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="searchIP" value="<?=$searchIP?>" class="form-control" />
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="table-btn">
        <button type="submit" class="btn btn-lg btn-black btn-search">검색</button>
    </div>

    <div class="table-action mgt30 mgb0">
        <div class="pull-left pdt5">
            데이터 노출형태 :
            <select name="deviceFl">
                <option value="all" <?= $checked['searchDevice']['all']; ?> >통합</option>
                <option value="pc" <?= $checked['searchDevice']['pc']; ?> >PC쇼핑몰</option>
                <option value="mobile" <?= $checked['searchDevice']['mobile']; ?> >모바일쇼핑몰</option>
            </select>
        </div>
        <div class="pull-right">
            <?= gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([20,30,40,50,60,70,80,90,100,200,300,500]), '개 보기', $page->page['list'], '', '', 'form-control bottom display-inline-block'); ?>
            <button type="button" class="btn btn-white btn-icon-excel btn-excel">엑셀 다운로드</button>
        </div>
    </div>
</form>

<div class="code-html js-excel-data mgb20">
    <div id="grid"></div>
</div>
<div class="center"><?=$page->getPage();?></div>

<script type="text/html" id="progressExcel">
    <div class="js-progress-excel progress-excel" style="position:absolute;width:100%;height:100%;top:0px;left:0px;background:#000000;z-index:1041;opacity:0.5;"></div>
    <div class="js-progress-excel progress-excel" id="js-progress-excel" style="width:300px;background:#fff;margin:0 auto;position:absolute;z-index:1042;padding:20px;left:50%;transform: translate(-50%, 0);text-align:center;">다운로드할 엑셀파일을 생성 중입니다.<br/> 잠시만 기다려주세요.
        <p style="font-size:22px;" id="progressView">0%</p>
        <div style="widtht:260px;height:18px;background:#ccc;margin-bottom:10px;">
            <div id="progressViewBg" style="height:100%">&nbsp;</div>
        </div>
        <span class="pregress-msg-btn"><input type="button" class="downloadCancel btn btn-lg btn-black" value="요청취소" /></span>
    </div>
</script>


<script>
    <!--
    $(document).ready(function () {
        $('[name="deviceFl"]').change(function (e) {
            $('[name="searchDevice"]').val($('[name="deviceFl"]').val());
            $('#frmSearchStatistics').submit();
        });

        $('.btn-excel').click(function(){
            if (confirm('엑셀양식을 생성해야 합니다.\r\n로그가 많으면 다소 오래 걸릴 수 있습니다. 생성하시겠습니까?')) {
                $("input[name=dataTypes]").val('list');
                var compiled = _.template($('#progressExcel').html());
                $('body').append(compiled);
                $('.js-progress-excel:eq(0)').height($('body').prop('scrollHeight'));
                $('.js-progress-excel:eq(1)').css('top', $('html').scrollTop() + 150);

                $('#frmSearchStatistics').prop({
                    'action': './visit_ip_excel.php',
                    'method': 'post',
                    'target': 'ifrmProcess'
                }).submit();
            }
        });

        $(document).on("click", ".downloadCancel", function() {
            cancelProgressExcel();
        });

        $("input[name=searchIP]").keyup(function(e) {
            $(this).val($(this).val().replace(/[^(0-9.)]/gi,''));
            if(e.keyCode == 13) {
                $(".btn-search").trigger("click");
            }
        });

        $('select[name=\'pageNum\']').change(function () {
            $('input[name=\'pageNum\']').val($(this).val());
            $('#frmSearchStatistics').submit();
        });

        $(document).on("click", ".detail-link", function() {
        //$(".detail-link").click(function() {
            var sendData = {
                'searchDate[0]':$(".start-date").val(),
                'searchDate[1]':$(".end-date").val(),
                'searchIP': $(this).attr("searchIP"),
                'searchOS': $(this).attr("searchOS"),
                'searchBrowser': $(this).attr("searchBrowser"),
                'deviceFl':$('[name="deviceFl"]').val()
            };
            $.ajax({
                url: '../statistics/layer_visit_ip_detail.php',
                data: sendData,
                dataType: 'text',
                type: 'get',
                async: false,
                success: function (data) {
                    data = '<div id="layerVisitDetail">' + data + '</div>';
                    BootstrapDialog.show({
                        title: '방문자 IP분석 상세보기',
                        size: BootstrapDialog.SIZE_WIDE_LARGE,
                        message: $(data),
                        closable: true
                    });
                },
                error: function (e) {
                    console.log(e);
                    alert('데이터를 불러오는데 실패했습니다. 다시 시도해주세요.');
                }
            });
        });
    });

    function progressExcel(size) {

        if($.isNumeric(size) == false ){
            size = "100";
        }
        $("#progressView").text(size+"%");
        $("#progressViewBg").css({
            "background-color": "#fa2828",
            "width": size+"%"
        });
    }

    function completeExcel(filename) {
        getExcel(filename, $("input[name=dataTypes]").val());
    }

    function getExcel(filename, dataTypes) {
        ifrmProcess.location.href = "./visit_ip_excel.php?mode=download&dataTypes="+dataTypes+"&filename=" + filename;
        setTimeout(function(){
            $('#frmSearchStatistics').prop({
                'action': '',
                'method': '',
                'target': ''
            });
            $(".progress-excel").remove();
            ifrmProcess.location.href = "/blank.php";
        }, 1000);
    }

    function cancelProgressExcel() {

        dialog_confirm('요청 취소 시 생성 중인 엑셀 다운로드 파일이 모두 삭제됩니다. 진행중인 내용을 취소하고 페이지를 이동하시겠습니까?', function (result) {
            if (result) {
                if ($.browser.msie) {
                    document.execCommand("Stop");
                } else {
                    window.stop(); //works in all browsers but IE
                }

                setTimeout(function(){
                    $('#frmSearchStatistics').prop({
                        'action': '',
                        'method': '',
                        'target': ''
                    });
                    $(".js-progress-excel").remove();
                }, 10);

            } else {
                return false;
            }

        },'확인',{"cancelLabel":'취소',"confirmLabel":'확인'});

    }

    var grid = new tui.Grid({
        el: $('#grid'),
        autoNumbering: false,
        columnFixCount: 1,
        headerHeight: 50,
        displayRowCount: <?= $rowDisplay; ?>,
        minimumColumnWidth: 20,
        columnModelList : [
            {
                "title" : "<b>IP</b>",
                "columnName" : "visitIP",
                "align" : "center",
                "width" : 300,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>운영체제</b>",
                "columnName" : "visitOS",
                "align" : "center",
                "width" : 200,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>브라우저</b>",
                "columnName" : "visitBrowser",
                "align" : "center",
                "width" : 200,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>전체 페이지뷰</b>",
                "columnName" : "visitPageView",
                "align" : "center",
                "width" : 200,
                editOption: {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>상세보기</b>",
                "columnName" : "visitDetailLink",
                "align" : "center",
                "width" : 200,
                editOption: {
                    type: 'normal'
                }
            }
        ]
    });
    grid.setRowList(<?= $rowList; ?>);
    //-->
</script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/statistics.js"></script>
