<div>
    <div class="mgt10"></div>
    <div class="table-title gd-help-manual">SMS 발송 내역 상세 리스트</div>
    <div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col class="width-lg"/>
                <col class="width-sm"/>
                <col/>
                <col class="width-3xs"/>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?php echo gd_select_box(null, 'smsKey', ['all' => '= 통합검색 =', 'receiverName' => '이름', 'receiverCellPhone' => '수신번호'], '', $search['smsKey']); ?>
                        <input type="text" name="smsKeyword" value="<?= $search['smsKeyword']; ?>" class="form-control width-xl" placeholder="키워드를 입력해 주세요." />
                    </div>
                </td>
                <td rowspan="3"><input type="button" value="검색" class="btn btn-gray" onclick="layer_list_search();" /></td>
            </tr>
            <tr>
                <th>예약상태</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?php echo gd_select_box(null, 'smsSendStatus', $smsSendStatus, '', $search['smsSendStatus'], '= 전체 ='); ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="table-header">
    <div class="pull-right">
        <ul>
            <li>
                <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100]), '개 보기', $search['pageNum'], null); ?>
            </li>
        </ul>
    </div>
</div>
<form id="frmList" name="frmList" action="" method="post" style="max-height:400px; overflow-y:auto;">
    <input type="hidden" name="mode" />
    <input type="hidden" name="smsLogSno" value="<?=$search['smsLogSno']?>" />
    <table class="table table-rows table-fixed">
        <colgroup>
            <col class="width3p" />
            <col class="width5p" />
            <col class="width20p" />
            <col class="width10p" />
            <col class="width20p" />
            <col class="width15p" />
        </colgroup>
        <thead>
        <tr>
            <th><input class="js-checkall" type="checkbox" data-target-name="sno"></th>
            <th>번호</th>
            <th>SMS발송에정일시</th>
            <th>이름</th>
            <th>수신번호</th>
            <th>예약상태</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (is_array($data) === true) {
            foreach ($data as $key => $val) {
                ?>
                <tr class="text-center">
                    <td><input name="sno[]" type="checkbox" value="<?php echo $val['sno']; ?>" <?php if($val['sendCheckFl'] == 'c') { ?>disabled<?php } ?> /></td>
                    <td class="font-num"><?php echo number_format($page->idx--); ?></td>
                    <td class="font-date"><?php echo $reserveDt; ?></td>
                    <td class="font-date"><?php echo $val['receiverName']; ?></td>
                    <td class="font-date"><?php echo gd_number_to_phone($val['receiverCellPhone']); ?></td>
                    <td><?php echo $smsSendStatus[$val['sendCheckFl']] ?></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="7">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</form>

<div class="text-center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')');?></div>

<div class="text-center">
    <td rowspan="3"><input type="button" value="전체 예약취소" class="btn btn-black all-member-cancle" /></td>
    <td rowspan="3"><input type="button" value="선택회원 예약취소" class="btn btn-gray select-member-cancel" /></td>
    <td rowspan="3"><input type="button" value="닫기" class="btn btn-white" onclick="layer_close();" /></td>
</div>
<div class="text-center"></div>

<script type="text/javascript">
    $(document).ready(function () {
        var complied = _.template($('#progressExcel').html());
        $('.js-progress-excel').remove();
        $(".modal-content").append(complied());

        $('.all-member-cancle').click(function () {
            dialog_confirm("SMS 발송예약을 <span class='text-orange-red'>전체 취소</span> 하시겠습니까?", function (result) {
                if (result) {
                    $("#frmList").attr('target', 'ifrmProcess');
                    $("#frmList").attr('action', './sms_ps.php');
                    var sms_key = $('select[name=\'smsKey\']').val();
                    var sms_keyword = $('input[name=\'smsKeyword\']').val();
                    var sms_send_status = $('select[name=\'smsSendStatus\']').val();
                    var page = $("#layerSmsSendList .pagination li.active span").text();
                    $("#frmList").append('<input type="hidden" name="smsKey" value="' + sms_key + '"/>');
                    $("#frmList").append('<input type="hidden" name="smsKeyword" value="' + sms_keyword + '"/>');
                    $("#frmList").append('<input type="hidden" name="smsSendStatus" value="' + sms_send_status + '"/>');
                    $("#frmList").append('<input type="hidden" name="page" value="page=' + page + '"/>');
                    $("[name='mode']").val('cancel_all_member');
                    $("#frmList").submit();
                    $(".js-progress-excel").show();
                }
            }, null, {cancelLabel:'예약취소하지 않음', 'confirmLabel':'예약취소'});
        });

        $('.select-member-cancel').click(function () {
            var chkLen = $('input:checkbox[name="sno[]"]:checked').length;
            if(chkLen > 100) {
                alert('선택회원 예약 취소는 최대 100개까지 선택 가능합니다.');
                return false;
            }
            if(chkLen) {
                dialog_confirm("SMS 발송예약을 <span class='text-orange-red'>선택 취소</span> 하시겠습니까?", function (result) {
                    if (result) {
                        $("#frmList").attr('target', 'ifrmProcess');

                        $("#frmList").attr('action', './sms_ps.php');
                        var page = $("#layerSmsSendList .pagination li.active span").text();
                        $("#frmList").append('<input type="hidden" name="page" value="page=' + page + '"/>');
                        $("[name='mode']").val('cancel_select_member');
                        $("#frmList").submit();
                        $(".js-progress-excel").show();
                    }
                }, null, {cancelLabel:'예약취소하지 않음', 'confirmLabel':'예약취소'});
            } else {
                BootstrapDialog.show({
                    title: '선택회원 재발송',
                    type: BootstrapDialog.TYPE_WARNING,
                    message: '발송예약을 취소할 회원을 선택해주세요.',
                });
                return;
            }
        });
    });

    $('#layerSmsSendList #pageNum').change(function () {
        layer_list_search();
    });

    function hide_process() {
        $(".js-progress-excel").hide();
    }

    function progressExcel(size) {

        if ($.isNumeric(size) == false) {
            size = "100";
        }
        $("#progressView").text(size + "%");
        console.log(size, $("#progressView").text())
        $("#progressViewBg").css({
            "background-color": "#fa2828",
            "width": size + "%"
        });
    }

    // 페이지 출력
    function layer_list_search(pagelink) {
        var smsLogSno = $('input[name=\'keyword\']').val();
        var smsKey = $('select[name=\'smsKey\']').val();
        var smsKeyword = $('input[name=\'smsKeyword\']').val();
        var smsSendStatus = $('select[name=\'smsSendStatus\']').val();
        var smsErrorCode = $('select[name=\'smsErrorCode\']').val();
        var pageNum = $('#layerSmsSendList select[name=\'pageNum\']').val();

        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            'layerFormID': '<?php echo $layerFormID?>',
            'smsLogSno': '<?php echo $search['smsLogSno']?>',
            'smsKey': smsKey,
            'smsKeyword': smsKeyword,
            'smsSendStatus': smsSendStatus,
            'smsErrorCode': smsErrorCode,
            'page': pagelink,
            'pageNum': pageNum
        };

        $.get('<?php echo $pageUrl?>', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }
</script>
<script type="text/html" id="progressExcel">
    <div class="js-progress-excel" style="position:absolute;width:100%;height:100%;top:0px;left:-1px;background:#000000;z-index:1060;opacity:0.5;display:none;"></div>
    <div class="js-progress-excel" style="width:300px;background:#fff;margin:0 auto;position:absolute;z-index:1070;top:250px;padding:20px;left:270px;text-align:center;display:none;">요청하신 sms 예약 발송을 취소중입니다.<br/> 잠시만 기다려주세요.
        <p style="font-size:22px;" id="progressView">0%</p>
        <div style="widtht:260px;height:18px;background:#ccc;margin-bottom:10px;">
            <div id="progressViewBg" style="height:100%">&nbsp;</div>
        </div>
    </div>
</script>