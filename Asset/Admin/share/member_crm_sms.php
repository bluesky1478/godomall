<form id="formSearch" method="get">
    <input type="hidden" name="detailSearch" value="y"/>
    <input type="hidden" name="memNo" id="memNo" value="<?= $requestGetParams['memNo']; ?>"/>
    <input type="hidden" name="navTabs" id="navTabs" value="<?= $requestGetParams['navTabs']; ?>"/>
    <input type="hidden" name="sort" value="<?= $requestGetParams['sort']; ?>"/>
    <input type="hidden" name="pageNum" value="<?= $requestGetParams['pageNum']; ?>"/>

    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col class="width-2xl"/>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>발송자</th>
            <td class="form-inline">
                <input type="text" name="keyword" value="<?php echo $requestGetParams['keyword']; ?>" class="form-control width-md" placeholder="키워드를 입력해 주세요."/>
            </td>
            <th>발송 유형</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="smsType" value="" <?php echo gd_isset($checked['smsType']['']); ?> />
                    전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="smsType" value="auto" <?php echo gd_isset($checked['smsType']['auto']); ?> />
                    자동발송
                </label>
                <label class="radio-inline">
                    <input type="radio" name="smsType" value="user" <?php echo gd_isset($checked['smsType']['user']); ?> />
                    개별/전체발송
                </label>
            </td>
        </tr>
        <tr>
            <th>기간검색</th>
            <td colspan="3">
                <div class="form-inline">
                    <div class="input-group js-datepicker">
                        <input type="text" name="regDt[]" value="<?php echo $requestGetParams['regDt'][0]; ?>" class="form-control width-xs" placeholder="수기입력 가능"/>
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar"></span>
                        </span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" name="regDt[]" value="<?php echo $requestGetParams['regDt'][1]; ?>" class="form-control width-xs" placeholder="수기입력 가능"/>
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar"></span>
                        </span>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>발송 구분</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="sendFl" value="" <?php echo gd_isset($checked['sendFl']['']); ?> />
                    전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="sendFl" value="sms" <?php echo gd_isset($checked['sendFl']['sms']); ?> />
                    SMS
                </label>
                <label class="radio-inline">
                    <input type="radio" name="sendFl" value="lms" <?php echo gd_isset($checked['sendFl']['lms']); ?> />
                    LMS
                </label>
                <label class="radio-inline">
                    <input type="radio" name="sendFl" value="kakao" <?php echo gd_isset($checked['sendFl']['kakao']); ?> />
                    알림톡
                </label>
            </td>
            <th>발송 상태</th>
            <td class="form-inline">
                <?php echo gd_select_box(null, 'sendStatus', $smsSendStatus, '', $requestGetParams['sendStatus'], '=전체보기='); ?>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black"/>
    </div>

    <div class="table-header form-inline">
        <div class="pull-left">
            SMS/알림톡 발송내역 리스트 (검색결과
            <strong><?php echo number_format($page->recode['total']); ?></strong>
            개, 전체
            <strong><?php echo number_format($page->recode['amount']); ?></strong>
            개)
        </div>
        <div class="pull-right">
            <?php echo gd_select_box('sort', 'sort', $sorts, null, $requestGetParams['sort'], null); ?>
            <?php echo gd_select_box_by_page_view_count($requestGetParams['pageNum']); ?>
        </div>
    </div>
</form>

<table class="table table-rows table-fixed">
    <colgroup>
        <col class="width5p"/>
        <col class="width10p"/>
        <col class="width5p"/>
        <col class="width30p"/>
        <col class="width10p"/>
        <col class="width10p"/>
        <col class="width10p"/>
        <col class="width10p"/>
    </colgroup>
    <thead>
    <tr>
        <th>번호</th>
        <th>발송유형</th>
        <th>구분</th>
        <th>SMS/알림톡 내용</th>
        <th>발송자</th>
        <th>발송일시</th>
        <th>발송대상</th>
        <th>발송상태</th>
    </tr>
    </thead>
    <tbody class="sms-log-list">
    <?php
    if (empty($logData) === false) {
        $listHtml = [];
        foreach ($logData as $data) {
            $isAutoSend = $data['smsType'] != 'user';

            // 발송자 정보
            $sender = json_decode($data['sender']);

            // 예약 발송 여부
            $reserveFl = '';
            if ($data['reserveDt'] !== '0000-00-00 00:00:00') {
                $reserveFl = '<br/><span class="text-orange-red">[예약발송]</span>';
            }

            // SMS 결과 수신 처리
            $smsSendResult = 'n';
            if ($data['sendStatus'] === 'r' && $data['receiverCnt'] < 100) {
                $smsSendResult = 'y';
            }

            // 템플릿 버튼
            if (empty($data['templateButton']) === false) {
                $tmpBtnData = json_decode($data['templateButton'], true);
                foreach ($tmpBtnData as $bVal) {
                    $tmpData[] = '<span class="alrim-template-button">' . $bVal['name'] . '</span>';
                }
                $data['templateContent'] .= '<br/><div class="alrim-template-button-list">' . implode(' ', $tmpData) . '</div>';
                unset($tmpBtnData, $tmpData);
            }

            $sender = json_decode($data['sender']);
            $sSendFl = ($data['sendFl'] != 'kakao') ? strtoupper($data['sendFl']) : '알림톡';

            $listHtml[] = '<tr data-sno="' . $data['sno'] . '" data-api-yn="' . $smsSendResult . '">';
            $listHtml[] = '<td class="text-center number">' . number_format($page->idx--) . '</td>';
            $listHtml[] = '<td class="text-center">' . $smsSendType[$data['smsType']] . $reserveFl . '</td>';
            $listHtml[] = '<td class="text-center">' . $sSendFl . '</td>';
            $listHtml[] = '<td style="word-break:break-all;word-wrap:break-word;overflow:hidden;" class="multirows">';
            if ($data['sendFl'] != 'kakao') {
                $listHtml[] = '<div>' . $data['contents'] . '</div>';
            } else {
                $listHtml[] = '<div>' . nl2br($data['templateContent']) . '</div>';
            }
            if ($data['sendStatus'] == 'r' && $data['sendFl'] != 'kakao') {
                $listHtml[] = '<div><button type="button" class="btn btn-xs btn-gray btn-modify">수정하기</button></div>';
            }
            $listHtml[] = '</td>';
            $listHtml[] = '<td class="text-center">' . $sender[0];
            $listHtml[] = '<br/>(<span class="eng text-darkblue">' . $sender[1] . '</span>)<br/><span class="font-num">' . gd_number_to_phone($sender[2]) . '</span></td>';
            if ($data['reserveDt'] !== '0000-00-00 00:00:00') {
                $listHtml[] = '<td class="text-center number multirows">' . str_replace(' ', '<br />', $data['reserveDt']) . '</td>';
            } else {
                $listHtml[] = '<td class="text-center number multirows">' . str_replace(' ', '<br />', $data['sendDt']) . '</td>';
            };
            $listHtml[] = '<td class="text-center number">' . number_format($data['receiverCnt']) . '명</td>';
            $listHtml[] = '<td class="text-center number">';
            $listHtml[] = '<span class="sms-send-status-' . $data['sno'] . '">' . $smsSendStatus[$data['sendStatus']] . '</span>';
            $listHtml[] = '</td>';
            $listHtml[] = '</tr>';
        }
        echo implode('', $listHtml);
    } else {
        echo '<tr><td colspan="8" class="no-data">SMS/알림톡 발송내역이 없습니다.</td></tr>';
    }
    ?>
    </tbody>
</table>

<div class="center"><?php echo $page->getPage(); ?></div>
<script language="javascript" type="text/javascript">
    <!--
    $(document).ready(function () {
        $('select[name=\'sort\']').change({targetForm: '#formSearch'}, member.page_sort);
        $('select[name=\'pageNum\']').change({targetForm: '#formSearch'}, member.page_number);

        $('.btn-modify').click(function (e) {
            layer_close();
            layer_sms_contents($(e.target).closest('tr').data('sno'));
        });

        $('.btn-register').click(function () {
            member_sms($('#memNo').val());
        });

        $('.btn-send-list').click(function (e) {
            layer_close();
            layer_sms_send_list($(e.target).closest('tr').data('sno'));
        });

        $.each($('.sms-log-list > tr'), function () {
            var smsLogSno = $(this).data('sno');
            if ($(this).data('api-yn') == 'y') {
                $('.sms-send-status-' + smsLogSno).html('<img src="<?php echo PATH_ADMIN_GD_SHARE;?>img/icon_loading.gif" alt="로딩중" width="20" class="middle" />');
                $.post('<?php echo URI_ADMIN;?>share/sms_send_result.php', {'smsLogSno': smsLogSno}, function (data) {
                    $('.sms-send-status-' + smsLogSno).html(data);
                });
            }
        });
    });
    //-->
</script>
