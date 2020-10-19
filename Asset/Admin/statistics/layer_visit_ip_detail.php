<div id="layer-visit-ip-detail">
    <div>
        <table class="table table-cols no-title-line">
            <colgroup><col class="width-md" /><col /><col class="width-md" /><col /><col class="width-md" /><col /></colgroup>
            <tr>
                <th>IP</th>
                <td>
                    <?=$searchIP?>
                </td>
                <th>운영체제</th>
                <td>
                    <?=$searchOS?>
                </td>
                <th>브라우저</th>
                <td>
                    <?=$searchBrowser?>
                </td>
            </tr>
        </table>
    </div>
    <form id="frmSearchDetailStatistics" method="get">
        <div class="table-action mgt30 mgb0">
            <div class="pull-right">
                <?= gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([20,30,40,50,60,70,80,90,100,200,300,500]), '개 보기', $page->page['list'], '', 'onchange="layer_list_search(\'\', this.value);"', 'form-control bottom display-inline-block'); ?>
                <button type="button" class="btn btn-white btn-icon-excel btn-excel-detail" onclick="excel_detail()">엑셀 다운로드</button>
            </div>
        </div>
    </form>
    <div class="code-html js-excel-data mgb20">
        <div id="grid_detail"></div>
    </div>
    <div class="center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')');?></div>
</div>
<script type="text/javascript">
    var grid_detail = new tui.Grid({
        el: $('#grid_detail'),
        autoNumbering: false,
        columnFixCount: 1,
        headerHeight: 50,
        displayRowCount: <?= $rowDisplay; ?>,
        minimumColumnWidth: 20,
        columnModelList : [
            {
                "title" : "<b>접속시간</b>",
                "columnName" : "regDt",
                "align" : "center",
                "width" : 200,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>페이지뷰</b>",
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
                "title" : "<b>방문경로</b>",
                "columnName" : "visitReferer",
                "align" : "left",
                "width" : 800,
                editOption: {
                    type: 'normal'
                }
            }
        ]
    });
    grid_detail.setRowList(<?= $rowList; ?>);
</script>

<?php if ($reloadDisplay != true) { ?>
    <script type="text/html" id="progressExcelDetail">
        <div class="js-progress-excel-detail progress-excel" style="position:absolute;width:100%;height:100%;top:0px;left:0px;background:#000000;z-index:1051;opacity:0.5;"></div>
        <div class="js-progress-excel-detail progress-excel" id="js-progress-excel-detail" style="width:300px;background:#fff;margin:0 auto;position:fixed;z-index:1052;padding:20px;left:50%;transform: translate(-50%, 0);text-align:center;">다운로드할 엑셀파일을 생성 중입니다.<br/> 잠시만 기다려주세요.
            <p style="font-size:22px;" id="progressView">0%</p>
            <div style="widtht:260px;height:18px;background:#ccc;margin-bottom:10px;">
                <div id="progressViewBg" style="height:100%">&nbsp;</div>
            </div>
            <span class="pregress-msg-btn"><input type="button" class="downloadCancelDetail btn btn-lg btn-black" onclick="cancelProgressExcelDetail()" value="요청취소" /></span>
        </div>
    </script>

    <script type="text/javascript">
        <!--
        function excel_detail() {
            if (confirm('엑셀양식을 생성해야 합니다.\r\n로그가 많으면 다소 오래 걸릴 수 있습니다. 생성하시겠습니까?')) {
                var searchIP = $("input[name=searchIP]").val();
                $("input[name=dataTypes]").val('detail');
                $("input[name=searchIP]").val("<?php echo $searchIP ?>");
                $("input[name=searchOS]").val("<?php echo $searchOS ?>");
                $("input[name=searchBrowser]").val("<?php echo $searchBrowser ?>");
                var compiled = _.template($('#progressExcelDetail').html());
                $('body').append(compiled);
                $('.js-progress-excel-detail:eq(0)').height($('body').prop('scrollHeight'));
                $('.js-progress-excel-detail:eq(1)').css('top', 150);

                $('#frmSearchStatistics').prop({
                    'action': './visit_ip_excel.php',
                    'method': 'post',
                    'target': 'ifrmProcess'
                }).submit();
                $("input[name=searchIP]").val(searchIP);
            }
        }

        function cancelProgressExcelDetail() {

            dialog_confirm('요청 취소 시 생성 중인 엑셀 다운로드 파일이 모두 삭제됩니다. 진행중인 내용을 취소하고 페이지를 이동하시겠습니까?', function (result) {
                if (result) {
                    if ($.browser.msie) {
                        document.execCommand("Stop");
                    } else {
                        window.stop(); //works in all browsers but IE
                    }

                    setTimeout(function(){
                        $('#frmSearchDetailStatistics').prop({
                            'action': '',
                            'method': '',
                            'target': ''
                        });
                        $(".progress-excel").remove();
                    }, 10);

                } else {
                    return false;
                }

            },'확인',{"cancelLabel":'취소',"confirmLabel":'확인'});

        }

        // 페이지 출력
        function layer_list_search(pagelink, pageNum) {
            if (typeof pagelink == 'undefined') {
                pagelink = '';
            }
            if(typeof pageNum == 'undefined') {
                pageNum = $("#frmSearchDetailStatistics #pageNum").val();
            }
            var parameters = {
                'layerFormID': 'layerVisitDetail',
                'searchIP': '<?php echo $searchIP?>',
                'searchDate[0]': '<?php echo $searchDate[0]?>',
                'searchDate[1]': '<?php echo $searchDate[1]?>',
                'searchOS': '<?php echo $searchOS?>',
                'searchBrowser': '<?php echo $searchBrowser?>',
                'pageNum': pageNum,
                'pagelink': pagelink,
                'reloadDisplay': 1
            };

            $.get('<?php echo URI_ADMIN;?>statistics/layer_visit_ip_detail.php', parameters, function (data) {
                $('#layer-visit-ip-detail').html(data);
            });
        }
        //-->
    </script>
    <style>
        .modal {display:block !important;}
    </style>
<?php } ?>
