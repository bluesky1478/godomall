<?php
/**
 * array $selected
 * array $checked
 * array $arrManager
 */
?>
<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <input type="button" value="출석체크 등록" class="btn btn-red-line" id="btnRegister"/>
</div>
<form id="frmSearchBase" method="get" class="content-form js-search-form js-form-enter-submit">
    <input type="hidden" name="sort" value="<?= Request::get()->get('sort', '') ?>"/>
    <input type="hidden" name="pageNum" value="<?= Request::get()->get('pageNum', 10) ?>"/>
    <input type="hidden" name="deleteMode" id="deleteMode" value="<?= $deleteMode ?>"/>
    <div class="table-title">
        출석체크 검색
    </div>
    <div class="search-detail-box form-inline">
        <input type="hidden" name="detailSearch" value="<?= Request::get()->get('detailSearch', '') ?>"/>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <?= gd_select_box('key', 'key', $combineSearch, null, Request::get()->get('key', ''), null, null, 'form-control'); ?>
                    <input type="text" name="keyword" value="<?= Request::get()->get('keyword', '') ?>"
                           class="form-control"/>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="input-group js-datepicker">
                        <input type="text" name="regDt[]" class="form-control width-xs" placeholder=""
                               value="<?= Request::get()->get('regDt')[0]; ?>">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" name="regDt[]" class="form-control width-xs" placeholder=""
                               value="<?= Request::get()->get('regDt')[1]; ?>">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    <?= gd_search_date($checked['regDtPeriod'], 'regDt', false) ?>
                </td>
            </tr>
            </tbody>
            <tbody class="js-search-detail">
            <tr>
                <th>진행범위</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="deviceFl" value="" <?= gd_isset($checked['deviceFl']['']); ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="deviceFl" value="pc" <?= gd_isset($checked['deviceFl']['pc']); ?>/>PC쇼핑몰
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="deviceFl" value="mobile" <?= gd_isset($checked['deviceFl']['mobile']); ?>/>모바일쇼핑몰
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="deviceFl" value="all" <?= gd_isset($checked['deviceFl']['all']); ?>/>PC + 모바일
                    </label>
                </td>
                <th>진행상태</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="activeFl" value="" <?= gd_isset($checked['activeFl']['']); ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="activeFl" value="y" <?= gd_isset($checked['activeFl']['y']); ?>/>진행중
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="activeFl" value="n" <?= gd_isset($checked['activeFl']['n']); ?>/>종료
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="activeFl" value="w" <?= gd_isset($checked['activeFl']['w']); ?>/>대기
                    </label>
                </td>
            </tr>
            <tr>
                <th>출석체크 형태</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="conditionFl" value="" <?= gd_isset($checked['conditionFl']['']); ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="conditionFl" value="sum" <?= gd_isset($checked['conditionFl']['sum']); ?>/>누적
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="conditionFl" value="continue" <?= gd_isset($checked['conditionFl']['continue']); ?>/>연속
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="conditionFl" value="each" <?= gd_isset($checked['conditionFl']['each']); ?>/>출석할 때 마다
                    </label>
                </td>
                <th>출석방법</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="methodFl" value="" <?= gd_isset($checked['methodFl']['']); ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="methodFl" value="stamp" <?= gd_isset($checked['methodFl']['stamp']); ?>/>스탬프형
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="methodFl" value="login" <?= gd_isset($checked['methodFl']['login']); ?>/>로그인형
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="methodFl" value="reply" <?= gd_isset($checked['methodFl']['reply']); ?>/>댓글형
                    </label>
                </td>
            </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-link js-search-toggle bold">상세검색
            <span>펼침</span>
        </button>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/>
    </div>
</form>
<form id="frmList" action="" method="get" target="ifrmProcess">
    <div class="table-header form-inline">
        <div class="pull-left">
            <?= gd_display_search_result($page->recode['total'], $page->recode['amount'], '건'); ?>
        </div>
        <div class="pull-right">
            <div>
                <?= gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10)); ?>
            </div>
        </div>
    </div>
    <table class="table table-rows">
        <colgroup>
            <col class="width-xs"/>
            <col class="width-xs"/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
        </colgroup>
        <thead>
        <tr>
            <th>
                <input type="checkbox" id="chk_all" class="js-checkall" data-target-name="chk"/>
            </th>
            <th>번호</th>
            <th>출석체크 이벤트명</th>
            <th>등록일</th>
            <th>등록자</th>
            <th>진행기간</th>
            <th>진행범위</th>
            <th>출석체크<br/>형태</th>
            <th>출석방법</th>
            <th>혜택지급<br/>방법</th>
            <th>진행상태</th>
            <th>출석현황<br/>(달성/전체참여)</th>
            <th>링크복사</th>
            <th>미리보기</th>
            <th>수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            $listHtml = [];
            $today = gd_date_format('Y-m-d H:i:s', 'today');
            /**
             * @var array $methodFl
             * @var array $arrManager
             * @var array $deviceFl
             * @var array $conditionFl
             * @var array $activeFl
             */
            foreach ($data as $val) {
                $activeDisabled = 'disabled="disabled"';
                $isWait = false;
                $activeValue = 'y';
                if ($val['endDt'] < $today) {
                    $activeValue = 'n';
                    $activeDisabled = '';
                } else if ($val['startDt'] > $today) {
                    $isWait = true;
                    $activeValue = 'w';
                    $activeDisabled = '';
                }
                $clipUrl = URI_HOME . 'event/attend_' . $val['methodFl'] . '.php?sno=' . $val['sno'];
                $clipUrlMobile = URI_MOBILE . 'event/attend_' . $val['methodFl'] . '.php?sno=' . $val['sno'];
                $listHtml[] = '<tr class="center" data-sno="' . $val['sno'] . '" data-method-fl="' . $val['methodFl'] . '">';
                $listHtml[] = '<td><input type="checkbox" name="chk[]" value="' . $val['sno'] . '" ' . $activeDisabled . '/></td>';
                $listHtml[] = '<td>' . $page->idx-- . '</td>';
                $listHtml[] = '<td>' . $val['title'] . '</td>';
                $listHtml[] = '<td>' . gd_date_format('Y-m-d', $val['regDt']) . '</td>';
                $listHtml[] = '<td>' . $arrManager[$val['managerNo']] . $val['deleteText'].'</td>';
                $listHtml[] = '<td>' . $val['startDt'] . '~<br/>' . $val['endDt'] . '</td>';
                $listHtml[] = '<td>' . $deviceFl[$val['deviceFl']] . '</td>';
                $listHtml[] = '<td>' . (($val['conditionFl'] == 'each') ? $conditionFl[$val['conditionFl']] : ($conditionFl[$val['conditionFl']] . $val['conditionCount'] . '회')) . '</td>';
                $listHtml[] = '<td>' . $methodFl[$val['methodFl']] . '</td>';
                $listHtml[] = '<td>' . (($val['benefitGiveFl'] == 'auto') ? '자동' : '수동') . '</td>';
                if ($val['endDt'] < $today) {
                    $listHtml[] = '<td>' . $activeFl['n'] . '</td>';
                } else if ($val['startDt'] > $today) {
                    $isWait = true;
                    $listHtml[] = '<td>' . $activeFl['w'] . '</td>';
                } else {
                    $listHtml[] = '<td>' . $activeFl['y'] . '</td>';
                }
                $listHtml[] = '<td>';
                if (!$isWait) {
                    $listHtml[] = '<span>' . $val['completeAttendanceCount'] . '/' . $val['totalAttendanceCount'] . '</span><br/><button type="button" class="btn btn-white btn-sm btn-detail-view">상세보기</button>';
                }
                $listHtml[] = '</td>';
                if($val['methodFl'] != 'login') {
                    if ($val['deviceFl'] == 'pc') {
                        $listHtml[] = '<td><button type="button" class="btn btn-white btn-sm btn-copy" data-clipboard-text="' . $clipUrl . '">PC</button></td>';
                        $listHtml[] = '<td><button type="button" class="btn btn-white btn-sm btn-preview">PC</button>';
                    } elseif ($val['deviceFl'] == 'mobile') {
                        $listHtml[] = '<td><button type="button" class="btn btn-white btn-sm btn-copy" data-clipboard-text="' . $clipUrlMobile . '">모바일</button></td>';
                        $listHtml[] = '<td><button type="button" class="btn btn-white btn-sm btn-preview-mobile">모바일</button></td>';
                    } else {
                        $listHtml[] = '<td><button type="button" class="btn btn-white btn-sm btn-copy" data-clipboard-text="' . $clipUrl . '">PC</button> <button type="button" class="btn btn-white btn-sm btn-copy" data-clipboard-text="' . $clipUrlMobile . '">모바일</button></td>';
                        $listHtml[] = '<td><button type="button" class="btn btn-white btn-sm btn-preview">PC</button> <button type="button" class="btn btn-white btn-sm btn-preview-mobile">모바일</button></td>';
                    }
                } else {
                    $listHtml[] = '<td></td><td></td>';
                }
                $listHtml[] = '<td><button type="button" class="btn btn-white btn-sm btn-modify">수정</button></td>';
                $listHtml[] = '</tr>';
            }
            echo join('', $listHtml);
            unset($listHtml, $data);
        } else {
            ?>
            <tr>
                <td class="center" colspan="15">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <div class="table-action clearfix">
        <div class="pull-left">
            <button type="button" class="btn btn-white" id="btnDelete">선택 삭제</button>
        </div>
    </div>
    <div class="center"><?= $page->getPage(); ?></div>
</form>
<script type="text/javascript">
    var attendance_list = (function ($, window, document, undefined) {
        var dialog;
        var frontUrl = "<?=URI_HOME?>";
        var mobileUrl = "<?=URI_MOBILE?>";
        var detail_view = function () {
            var $tr = $(this).closest('tr');
            var value = $tr.data('sno');

            var loadChk = $('div#formAttendanceDetail').length;
            $.get('../share/layer_attendance_detail.php?sno=' + value, {}, function (data) {
                if (loadChk === 0) {
                    data = '<div id="#formAttendanceDetail">' + data + '</div>';
                }

                dialog = BootstrapDialog.show({
                    name: "layer_attendance_detail",
                    title: "출석체크 상세보기",
                    size: BootstrapDialog.SIZE_WIDE,
                    message: $(data),
                    closable: true
                });
            });
        };
        var preview = function () {
            var $tr = $(this).closest('tr');
            var value = $tr.data('sno');
            var methodFl = $tr.data('method-fl');

            switch (methodFl) {
                case 'stamp':
                    window.open(frontUrl + 'event/attend_stamp.php?sno=' + value + '&preview=true');
                    break;
                case 'reply':
                    window.open(frontUrl + 'event/attend_reply.php?sno=' + value + '&preview=true');
                    break;
                default:
                    alert('미리보기를 지원하지 않는 출석체크 입니다.');
                    break;
            }
        };
        var previewMobile = function () {
            var $tr = $(this).closest('tr');
            var value = $tr.data('sno');
            var methodFl = $tr.data('method-fl');

            switch (methodFl) {
                case 'stamp':
                    window.open(mobileUrl + 'event/attend_stamp.php?sno=' + value + '&preview=true');
                    break;
                case 'reply':
                    alert('모바일을 지원하지 않는 출석체크 입니다.');
                    break;
                default:
                    alert('미리보기를 지원하지 않는 출석체크 입니다.');
                    break;
            }
        };
        var modify = function () {
            var value = $(this).closest('tr').data('sno');
            window.location.href = '../promotion/attendance_modify.php?sno=' + value;
        };

        var init = function () {
            $('#btnRegister').click(function () {
                window.location.href = '../promotion/attendance_register.php'
            });

            $('.btn-detail-view').click(detail_view);
            if ($('.btn-copy').length) {
                // https://clipboardjs.com
                var clipboard = new Clipboard('.btn-copy');
                clipboard.on('success', function (e){
                    if (e.text.indexOf('login') > 0) {
                        alert('링크복사를 지원하지 않는 출석체크 입니다.');
                    } else {
                        alert('[출석체크] 링크를 클립보드에 복사했습니다.\n<code>Ctrl+V</code>를 이용해서 사용하세요.');
                    }
                    e.clearSelection();
                });
                clipboard.on('error', function (e) {
                    console.error('Action:', e.action);
                    console.error('Trigger:', e.trigger);
                });
            }
            $('.btn-preview').click(preview);
            $('.btn-preview-mobile').click(previewMobile);
            $('.btn-modify').click(modify);
            $('#btnDelete').click(function () {
                if ($(':checkbox:checked').length === 0) {
                    alert('선택된 항목이 없습니다.');
                    return;
                }
                var params = $('#frmList').serializeArray();
                params.push({name: "mode", value: $('#deleteMode').val()});
                post_with_reload('../promotion/attendance_ps.php', params);
            });
        };

        $(document).ready(init);

        return {
            get_dialog: function () {
                return dialog;
            }
        }
    })($, window, document);
</script>
