<div>
    <div class="mgt10"></div>
    <div class="table-title gd-help-manual">SMS 발송 내역 상세 리스트</div>
    <div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col class="width-lg"/>
                <col class="width-lg"/>
                <col/>
                <col class="width-3xs"/>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?php echo gd_select_box(
                            null, 'smsKey', [
                            'all'               => '=통합검색=',
                            'receiverName'      => '이름',
                            'receiverCellPhone' => '수신번호',
                        ], '', $search['smsKey']
                        ); ?>
                        <input type="text" name="smsKeyword" value="<?php echo $search['smsKeyword']; ?>" class="form-control width-xl" placeholder="키워드를 입력해 주세요."/>
                    </div>
                </td>
                <td rowspan="3">
                    <input type="button" value="검색" class="btn btn-gray" onclick="layer_list_search();"/>
                </td>
            </tr>
            <tr>
                <th>발송결과</th>
                <td>
                    <div class="form-inline">
                        <?php echo gd_select_box(null, 'smsSendStatus', $smsSendStatus, '', $search['smsSendStatus'], '= 발송결과 ='); ?>
                    </div>
                </td>
                <th>실패사유</th>
                <td>
                    <div class="form-inline">
                        <?php //echo gd_select_box(null, 'smsErrorCode', $smsErrorCode, '', $search['smsErrorCode'], '= 실패사유 ='); ?>
                        <select class="form-control" name="smsErrorCode" >
                            <option value="">= 실패사유 =</option>
                            <?php foreach($smsErrorCode as $key => $val) { ?>
                                <option value="<?=$key?>" <?=(string)$search['smsErrorCode'] == (string)$key ? 'selected=selected':''?>><?=$val?></option>
                            <?php } ?>
                        </select>
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
    <input type="hidden" name="mode"/>
    <input type="hidden" name="smsLogSno" value="<?= $search['smsLogSno'] ?>"/>
    <table class="table table-rows table-fixed">
        <colgroup>
            <col class="width3p"/>
            <col class="width5p"/>
            <col class="width20p"/>
            <col class="width10p"/>
            <col class="width20p"/>
            <col class="width15p"/>
            <col class="width30p"/>
        </colgroup>
        <thead>
        <tr>
            <th>
                <input class="js-checkall" type="checkbox" data-target-name="sno">
            </th>
            <th>번호</th>
            <th>SMS 수신일시</th>
            <th>이름</th>
            <th>수신번호</th>
            <th>발송결과</th>
            <th>실패사유</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $key => $val) {
                ?>
                <tr class="text-center">
                    <td>
                        <input name="sno[]" type="checkbox" value="<?php echo $val['sno']; ?>"/>
                    </td>
                    <td class="font-num"><?php echo number_format($page->idx--); ?></td>
                    <td class="font-date"><?php echo $val['receiverDt']; ?></td>
                    <td class="font-date"><?php echo $val['receiverName']; ?></td>
                    <td class="font-date"><?php echo gd_number_to_phone($val['receiverCellPhone']); ?></td>
                    <td><?php echo $smsSendStatus[$val['sendCheckFl']]; ?></td>
                    <td><?php echo $val['failReason']; ?></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center no-data" colspan="7">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</form>

<div class="text-center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')'); ?></div>

<div class="text-center">
    <input type="button" value="검색내역 전체 재발송" class="btn btn-gray all-member-resend"/>
    <input type="button" value="선택내역 전체 재발송" class="btn btn-gray select-member-resend"/>
    <input type="button" value="닫기" class="btn btn-white" onclick="layer_close();"/>
</div>
<div class="text-center"></div>

<script type="text/javascript">
    var disable_resend = false;
    <?= (gd_isset($disableResend, false) === true) ? 'disable_resend = true;' : '' ?>
    $(document).ready(function () {
        $('#layerSmsSendList #pageNum').change(function () {
            layer_list_search();
        });

        $('.all-member-resend').click(function () {
            if (disable_resend) {
                dialog_alert('인증번호 안내 SMS 내역은 재발송되지 않습니다.<br/>인증번호 재발송이 필요한 경우, 인증화면에서 재전송 기능을 이용하시기 바랍니다.');
                return false;
            }
            if ($('td.no-data').length > 0) {
                BootstrapDialog.show({
                    title: '검색내역 전체 재발송',
                    type: BootstrapDialog.TYPE_WARNING,
                    message: '검색된 정보가 없습니다.'
                });
                return false;
            } else {
                var $frm_list = $("#frmList");
                var sms_key = $('select[name=\'smsKey\']').val();
                var sms_keyword = $('input[name=\'smsKeyword\']').val();
                var sms_send_status = $('select[name=\'smsSendStatus\']').val();
                var sms_error_code = $('select[name=\'smsErrorCode\']').val();
                $frm_list.find(':hidden[name="smsKey"]').remove();
                $frm_list.find(':hidden[name="smsKeyword"]').remove();
                $frm_list.find(':hidden[name="smsSendStatus"]').remove();
                $frm_list.find(':hidden[name="smsErrorCode"]').remove();
                $frm_list.append('<input type="hidden" name="smsKey" value="' + sms_key + '"/>');
                $frm_list.append('<input type="hidden" name="smsKeyword" value="' + sms_keyword + '"/>');
                $frm_list.append('<input type="hidden" name="smsSendStatus" value="' + sms_send_status + '"/>');
                $frm_list.append('<input type="hidden" name="smsErrorCode" value="' + sms_error_code + '"/>');
                $frm_list.attr('target', '_blank');
                $frm_list.attr('action', './sms_send.php');
                $("[name='mode']").val('resend_all_member');
                $frm_list.submit();
            }
        });

        $('.select-member-resend').click(function () {
            if (disable_resend) {
                dialog_alert('인증번호 안내 SMS 내역은 재발송되지 않습니다.<br/>인증번호 재발송이 필요한 경우, 인증화면에서 재전송 기능을 이용하시기 바랍니다.');
                return false;
            }
            if ($('input:checkbox[name="sno[]"]:checked').length) {
                var $frm_list = $("#frmList");
                $frm_list.attr('target', '_blank');
                $frm_list.attr('action', './sms_send.php');
                $("[name='mode']").val('resend_select_member');
                $frm_list.submit();
            } else {
                BootstrapDialog.show({
                    title: '선택내역 전체 재발송',
                    type: BootstrapDialog.TYPE_WARNING,
                    message: '재발송할 내역을 선택해 주세요.'
                });
                return;
            }
        });
    });

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
