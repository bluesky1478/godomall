<div>
    <div class="mgt10"></div>
    <div class="table-title gd-help-manual">알림톡 발송 내역 상세 리스트</div>
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
            </tr>
            <tr>
                <th>발송결과</th>
                <td>
                    <div class="form-inline">
                        <?php echo gd_select_box(null, 'smsSendStatus', ['y' => '발송성공', 'n' => '발송실패'], '', $search['smsSendStatus'], '전체보기'); ?>
                    </div>
                </td>
                <th>실패사유</th>
                <td>
                    <div class="form-inline">
                        <?php echo gd_select_box(null, 'smsErrorCode', [
                            'K101' => '메시지를 전송할 수 없음',
                            'K102' => '전화번호 오류',
                            'K103' => '메시지 길이제한',
                            'K999' => '시스템 오류',
                            'etc' => '기타사유',
                        ], '', $search['smsErrorCode'], '= 실패사유 ='); ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="text-center"><input type="button" value="검색" class="btn btn-gray" onclick="layer_list_search();"/></div>
<div class="text-center">&nbsp;</div>

<form id="frmList" name="frmList" action="" method="post">
    <input type="hidden" name="mode"/>
    <input type="hidden" name="smsLogSno" value="<?= $search['smsLogSno'] ?>"/>
    <table class="table table-rows table-fixed">
        <colgroup>
            <col class="width5p"/>
            <col class="width15p"/>
            <col class="width10p"/>
            <col class="width15p"/>
            <col class="width30p"/>
            <col class="width10p"/>
            <col class="width15p"/>
        </colgroup>
        <thead>
        <tr>
            <th>번호</th>
            <th>알림톡 수신일시</th>
            <th>이름</th>
            <th>수신번호</th>
            <th>내용</th>
            <th>발송결과</th>
            <th>실패사유</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $key => $val) {
                // 템플릿 버튼
                if (empty($val['templateButton']) === false) {
                    $tmpBtnData = json_decode($val['templateButton'], true);
                    foreach ($tmpBtnData as $bVal) {
                        $tmpData[] = '<span class="alrim-template-button">' . $bVal['name'] . '</span>';
                    }
                    $val['templateContent'] .= '<br/><div class="alrim-template-button-list">' . implode(' ', $tmpData) . '</div>';
                    unset($tmpBtnData, $tmpData);
                }
                ?>
                <tr class="text-center">
                    <td class="font-num"><?php echo number_format($page->idx--); ?></td>
                    <td class="font-date"><?php echo $val['receiverDt']; ?></td>
                    <td class="font-date"><?php echo $val['receiverName']; ?></td>
                    <td class="font-date"><?php echo gd_number_to_phone($val['receiverCellPhone']); ?></td>
                    <td class="text-left font-date"><?php echo nl2br($val['templateContent']); ?></td>
                    <td>
                        <?php
                            echo $smsSendStatus[$val['sendCheckFl']];
                            if ($val['receiverReplaceCode'] != 'null') {
                                $aSno = json_decode($val['receiverReplaceCode'], true);
                                echo '<a href="#" onclick="layer_sms_send_list(' . $aSno['sno'] . ');">(SMS 발송내역 확인)</a>';
                            }
                        ?>
                    </td>
                    <td>
                        <?php if ($val['failCode'] != '') { ?>
                        <?php echo $oKakao->getStringResultCode($val['failCode']); ?>
                        <?php } ?>
                    </td>
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

<div class="notice-info">발송실패된 건은 SMS/LMS로 재발송됩니다. SMS 발송내역에서 확인해주세요.</div>

<div class="text-center">
    <input type="button" value="닫기" class="btn btn-white" onclick="layer_close();"/>
</div>
<div class="text-center"></div>

<script type="text/javascript">
    $(document).ready(function () {
    });

    // 페이지 출력
    function layer_list_search(pagelink) {
        var smsLogSno = $('input[name=\'keyword\']').val();
        var smsKey = $('select[name=\'smsKey\']').val();
        var smsKeyword = $('input[name=\'smsKeyword\']').val();
        var smsSendStatus = $('select[name=\'smsSendStatus\']').val();
        var smsErrorCode = $('select[name=\'smsErrorCode\']').val();

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
            'page': pagelink
        };

        $.get('<?php echo $pageUrl?>', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }
</script>
