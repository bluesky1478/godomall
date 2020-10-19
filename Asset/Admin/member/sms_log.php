<form id="frmSearch" method="get" class="js-form-enter-submit">
    <input type="hidden" name="detailSearch" value="y"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
        </h3>
    </div>

    <div class="table-title gd-help-manual">
        SMS 발송 내역 검색
    </div>

    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col class="width-2xl"/>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td class="form-inline">
                    <?php echo gd_select_box(null, 'key', ['all' => '=통합검색=', 'sender' => '발송자', 'contents' => '발송내용', ], '', gd_isset($search['key'])); ?>
                    <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control width-md" placeholder="키워드를 입력해 주세요."/>
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
                        <?php echo gd_select_box(
                            'treatDateFl', 'treatDateFl', [
                            'regDt'     => '발송일',
                            'reserveDt' => '예약일',
                        ], '', $search['treatDateFl']
                        ); ?>
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[start]" value="<?php echo $search['treatDate']['start']; ?>" class="form-control width-xs" placeholder="수기입력 가능"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[end]" value="<?php echo $search['treatDate']['end']; ?>" class="form-control width-xs" placeholder="수기입력 가능"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        <?= gd_search_date($search['searchPeriod'], 'treatDate', false) ?>
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
                </td>
                <th>발송 상태</th>
                <td class="form-inline">
                    <?php echo gd_select_box(null, 'sendStatus', $smsSendStatus, '', $search['sendStatus'], '=전체보기='); ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black"/>
    </div>

    <div class="table-header">
        <div class="pull-left">
            <?= gd_display_only_search_result($page->recode['total'], '개'); ?>
        </div>
        <div class="pull-right">
            <ul>
                <li>
                    <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
                </li>
                <li>
                    <?php echo gd_select_box_by_page_view_count($search['pageNum']); ?>
                </li>
            </ul>
        </div>
    </div>
</form>

<table class="table table-rows table-fixed member-sms-log">
    <colgroup>
        <col class="width5p"/>
        <col class="width10p"/>
        <col class="width5p"/>
        <col class="width10p"/>
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
        <th>차감포인트</th>
        <th>SMS 내용</th>
        <th>발송자</th>
        <th>발송(예약)일시</th>
        <th>발송건수<br/>(발송성공/발송실패)</th>
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
            if ($data['sendStatus'] === 'r' && $data['receiverCnt'] < 100 && $data['reserveFl'] != 'y') {
                $smsSendResult = 'y';
            }

            // 실패경우에 일부 성공일때 추가 상태 표기
            if ($data['sendStatus'] == 'n' && $data['receiverCnt'] != $data['sendFailCnt']) {
                $sSendStatusAdd = '(일부)';
            } else {
                $sSendStatusAdd = '';
            }

            $listHtml[] = '<tr data-sno="' . $data['sno'] . '" data-api-yn="' . $smsSendResult . '">';
            $listHtml[] = '<td class="text-center number">' . number_format($page->idx--) . '</td>';
            $listHtml[] = '<td class="text-center">' . $smsSendType[$data['smsType']] . $reserveFl . '</td>';
            $listHtml[] = '<td class="text-center">' . strtoupper($data['sendFl']) . '</td>';
            if ($data['sendFl'] === 'sms') {
                $listHtml[] = '<td class="text-center number">' . $data['receiverCnt'] . '</td>';
            } else {
                $listHtml[] = '<td class="text-center number">' . ($data['receiverCnt'] * $lmsPoint) . '</td>';
            }
            $listHtml[] = '<td style="word-break:break-all;word-wrap:break-word;overflow:hidden;" class="multirows">';
            $listHtml[] = '<div class="div-contents">' . $data['contents'] . '</div>';
            if ($data['sendStatus'] === 'r' && $data['smsType'] === 'user') {
                $listHtml[] = '<div><button type="button" class="btn btn-xs btn-gray btn-modify">수정하기</button></div>';
            }
            $listHtml[] = '</td>';
            if ($isAutoSend) {
                $listHtml[] = '<td class="text-center">(<span class="eng text-blue">SYSTEM</span>)<br/><span class="font-num">' . gd_number_to_phone($sender[2]) . '</span></td>';
            } else {
                $listHtml[] = '<td class="text-center">' . $sender[0] . '<br/>(<span class="eng text-blue">' . $sender[1] . '</span>)<br/><span class="font-num">' . gd_number_to_phone($sender[2]) . '</span></td>';
            }
            $listHtml[] = '<td class="text-center number multirows">' . str_replace(' ', '<br />', $data['sendDt']) . '</td>';
            $listHtml[] = '<td class="text-center number">';
            $listHtml[] = number_format($data['receiverCnt']);
            if ($data['sendStatus'] === 'y' || $data['sendStatus'] === 'n') {
                $listHtml[] = '<br />(' . number_format($data['sendSuccessCnt']) . ' / '. number_format($data['sendFailCnt']) . ')';
            }
            $listHtml[] = '</td>';
            $listHtml[] = '<td class="text-center number">';
            $listHtml[] = '<span class="sms-send-status-' . $data['sno'] . '">' . $smsSendStatus[$data['sendStatus']] . $sSendStatusAdd . '</span>';
            $listHtml[] = '<div><button type="button" class="btn btn-xs btn-gray btn-send-list">상세보기</button></div>';
            $listHtml[] = '</td>';
            $listHtml[] = '</tr>';
        }
        echo implode('', $listHtml);
    } else {
        echo '<tr><td colspan="9" class="no-data">SMS 발송내역이 없습니다.</td></tr>';
    }
    ?>
    </tbody>
</table>

<div class="center"><?php echo $page->getPage(); ?></div>

<script language="javascript" type="text/javascript">
    <!--
    $(document).ready(function () {
        $('.btn-modify').click(function (e) {
            layer_close();
            layer_sms_contents($(e.target).closest('tr').data('sno'));
        });

        $('.btn-send-list').click(function (e) {
            layer_close();
            layer_sms_send_list($(e.target).closest('tr').data('sno'));
        });

        // SMS 결과 수신 처리
        $.each($('.sms-log-list > tr'), function (key, val) {
            var smsLogSno = $(this).data('sno');
            if ($(this).data('api-yn') == 'y') {
                $('.sms-send-status-' + smsLogSno).html('<img src="<?php echo PATH_ADMIN_GD_SHARE;?>img/icon_loading.gif" alt="로딩중" width="20" class="middle" />');
                $.post('<?php echo URI_ADMIN;?>share/sms_send_result.php', {'smsLogSno': smsLogSno}, function (data) {
                    $('.sms-send-status-' + smsLogSno).html(data);
                });
            }
        });

        $('select[name=\'sort\']').change({targetForm: '#frmSearch'}, member.page_sort);
        $('select[name=\'pageNum\']').change({targetForm: '#frmSearch'}, member.page_number);
    });
    //-->
</script>


