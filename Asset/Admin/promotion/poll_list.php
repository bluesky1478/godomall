<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <input type="button" value="설문조사 등록" class="btn btn-red-line" id="btnRegister"/>
</div>
<form id="frmSearchBase" method="get" class="content-form js-search-form js-form-enter-submit">
    <input type="hidden" name="sort" value="<?= Request::get()->get('sort', '') ?>"/>
    <input type="hidden" name="pageNum" value="<?= Request::get()->get('pageNum', 10) ?>"/>
    <input type="hidden" name="deleteMode" id="deleteMode" value="<?= $deleteMode ?>"/>
    <div class="table-title">
        설문조사 검색
    </div>
    <div class="search-detail-box form-inline">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td>
                    <?= gd_select_box('key', 'key', $pollSearch, null, Request::get()->get('key', ''), null, null, 'form-control'); ?>
                    <input type="text" name="keyword" value="<?= Request::get()->get('keyword', '') ?>"
                           class="form-control"/>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td>
                    <?= gd_select_box('date', 'date', $pollDateSearch, null, Request::get()->get('date', ''), null, null, 'form-control'); ?>
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
            <tr>
                <th>진행상태</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="statusFl" value="" <?= gd_isset($checked['statusFl']['']); ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="statusFl" value="S" <?= gd_isset($checked['statusFl']['S']); ?>/>대기
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="statusFl" value="Y" <?= gd_isset($checked['statusFl']['Y']); ?>/>진행중
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="statusFl" value="E" <?= gd_isset($checked['statusFl']['E']); ?>/>종료
                    </label>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/>
    </div>
</form>
<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <input type="hidden" name="sno" value="">
    <input type="hidden" name="status" value="">
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
            <col class="width5p"/>
            <col class="width5p"/>
            <col/>
            <col class="width7p"/>
            <col class="width7p"/>
            <col class="width7p"/>
            <col class="width5p"/>
            <col class="width5p"/>
            <col class="width5p"/>
            <col class="width5p"/>
            <col class="width7p"/>
            <col class="width5p"/>
            <col class="width5p"/>
        </colgroup>
        <thead>
        <tr>
            <th>
                <input type="checkbox" id="chk_all" class="js-checkall" data-target-name="chk"/>
            </th>
            <th>번호</th>
            <th>설문제목</th>
            <th>등록일</th>
            <th>등록자</th>
            <th>진행기간</th>
            <th>진행범위</th>
            <th>참여대상</th>
            <th>진행상태</th>
            <th>참여현황</th>
            <th>치환코드<br/>복사</th>
            <th>미리보기</th>
            <th>결과보기</th>
            <th>수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            $listHtml = [];
            $today = gd_date_format('Y-m-d H:', 'now');
            /**
             * @var array $methodFl
             * @var array $arrManager
             * @var array $deviceFl
             * @var array $conditionFl
             * @var array $activeFl
             */
            foreach ($data as $val) {
                $pollDate = gd_date_format('Y-m-d', $val['pollStartDt']) . '~<br/>';
                if ($val['pollEndDtFl'] == 'Y') $pollDate .= '제한없음';
                else $pollDate .= gd_date_format('Y-m-d', $val['pollEndDt']);
                $clipboard = '{=pollViewBanner(' . $val['pollCode'] . ')}';
                $status = 'Y';
                if ($val['pollStatusFl'] == 'N') {
                    $status = 'N';
                }
                if (gd_date_format('Y-m-d H:i', $val['pollStartDt']) > $today) {
                    $status = 'S';
                } elseif ($val['pollEndDtFl'] == 'N' && gd_date_format('Y-m-d H:i', $val['pollEndDt']) < $today) {
                    $status = 'E';
                }
                $statusBtn = $previewBtn = $groupBtn = '';
                if ($status == 'Y') {
                    $statusBtn = '<br /><button type="button" class="btn btn-white btn-sm btn-status" data-status="N">일시중지</button>';
                } elseif ($status == 'N') {
                    $statusBtn = '<br /><button type="button" class="btn btn-white btn-sm btn-status" data-status="Y">재시작</button>';
                }
                if ($val['pollDeviceFl'] == 'all' || $val['pollDeviceFl'] == 'pc') {
                    $previewBtn .= '<div><button type="button" class="btn btn-white btn-sm btn-preview" data-device="pc">PC</button></div>';
                }
                if ($val['pollDeviceFl'] == 'all' || $val['pollDeviceFl'] == 'mobile') {
                    $previewBtn .= '<div><button type="button" class="btn btn-white btn-sm btn-preview" data-device="mobile">모바일</button></div>';
                }
                if ($val['pollGroupFl'] == 'select') {
                    $groupBtn = '<br /><button type="button" class="btn btn-white btn-sm btn-poll-group" data-group="' . $val['pollGroupSno'] . '">등급보기</button>';;
                }

                
                $listHtml[] = '<tr class="center" data-sno="' . $val['sno'] . '" data-code="' . $val['pollCode'] . '">';
                $listHtml[] = '<td><input type="checkbox" name="chk[]" value="' . $val['pollCode'] . '" /></td>';
                $listHtml[] = '<td>' . $page->idx-- . '</td>';
                $listHtml[] = '<td>' . $val['pollTitle'] . '</td>';
                $listHtml[] = '<td>' . $val['regDt'] . '</td>';
                $listHtml[] = '<td>' . $val['managerNm'] . '<br />(' . $val['managerId'] . ')</td>';
                $listHtml[] = '<td>' . $pollDate . '</td>';
                $listHtml[] = '<td>' . $deviceFl[$val['pollDeviceFl']] . '</td>';
                $listHtml[] = '<td>' . $groupFl[$val['pollGroupFl']] . $groupBtn . '</td>';
                $listHtml[] = '<td>' . $statusFl[$status] . $statusBtn . '</td>';
                $listHtml[] = '<td>' . number_format($val['joinCnt']) . '</td>';
                $listHtml[] = '<td><button type="button" title="' . $val['pollTitle'] . '" class="btn btn-gray btn-sm js-popover" data-original-title="치환코드" data-content="' . $clipboard . '" data-placement="bottom">코드보기</button> <button type="button" title="' . $val['pollTitle'] . '" class="btn btn-white btn-sm js-clipboard" data-clipboard-text="' . $clipboard . '">복사</button></td>';
                $listHtml[] = '<td>' . $previewBtn . '</td>';
                $listHtml[] = '<td><button type="button" class="btn btn-white btn-sm btn-result">결과보기</button></td>';
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

<script>
    $(document).ready(function () {
        $('#btnRegister').click(function () {
            window.location.href = '../promotion/poll_register.php';
        });

        $('.btn-modify').click(function () {
            var sno = $(this).closest('tr').data('sno');
            window.location.href = '../promotion/poll_register.php?sno=' + sno;
        });

        $('.btn-result').click(function () {
            var sno = $(this).closest('tr').data('sno');
            var code = $(this).closest('tr').data('code');
            window.location.href = '../promotion/poll_result.php?sno=' + sno + '&code=' + code;
        });

        $('.btn-status').click(function () {
            var status = $(this).data('status');
            var sno = $(this).closest('tr').data('sno');
            if (status == 'Y') {
                var msg = '설문조사를 재시작하시겠습니까?';
            } else {
                var msg = '설문조사를 일시중지하시겠습니까?';
            }
            dialog_confirm(msg, function(result){
                if (result) {
                    $('#frmList').find('input[name="mode"]').val('changeStatus');
                    $('#frmList').find('input[name="sno"]').val(sno);
                    $('#frmList').find('input[name="status"]').val(status);
                    $('#frmList').prop({
                        'method': 'post',
                        'action': '../promotion/poll_ps.php'
                    }).submit();
                }
            });
        });

        $('.btn-preview').click(function () {
            var device = $(this).data('device');
            var code = $(this).closest('tr').data('code');

            if (device == 'pc') {
                var url = '<?php echo URI_HOME . DS . 'service' . DS . 'poll_register.php?code='; ?>' + code;
            } else {
                var url = '<?php echo URI_MOBILE . DS . 'service' . DS . 'poll_register.php?code='; ?>' + code;
            }
            window.open(url, '_blank');
        });

        $('.btn-poll-group').click(function () {
            var group = $(this).data('group');

            var title = "참여대상 보기";
            $.get('../promotion/poll_group.php',{ group : group }, function(data){

                data = '<div id="viewInfoForm">'+data+'</div>';

                var layerForm = data;

                BootstrapDialog.show({
                    title:title,
                    size: get_layer_size('normal'),
                    message: $(layerForm),
                    closable: true
                });
            });
        });

        $('#btnDelete').click(function () {

            var chkCnt = $('input[name="chk[]"]:checked').length;

            if (chkCnt == 0) {
                alert('선택된 설문조사가 없습니다.');
                return;
            }

            dialog_confirm('설문조사를 삭제하시겠습니까? 삭제 시 설문 응답정보도 함께 삭제됩니다.', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('delete');
                    $('#frmList').prop({
                        'method': 'post',
                        'action': '../promotion/poll_ps.php'
                    }).submit();
                }
            });

        });
    });
</script>