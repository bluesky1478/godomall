<div class="page-header js-affix">
    <h3><?=end($naviMenu->location);?></h3>
    <div class="btn-group">
        <input type="button" value="회원정보 이벤트 등록" class="btn btn-red-line js-register"/>
    </div>
</div>
<div class="table-title gd-help-manual">회원정보 이벤트 검색</div>
<form id="frmSearchEventList" method="get" class="content-form js-form-enter-submit">
    <input type="hidden" name="sort" value="<?=$search['sort'];?>"/>
    <input type="hidden" name="pageNum" value="<?=$search['pageNum'];?>"/>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tbody>
            <?php if ($gGlobal['isUse'] === true) { ?>
                <tr>
                    <th>상점</th>
                    <td colspan="3">
                        <label class="radio-inline">
                            <input type="radio" name="mallStatus" value="all" <?=$checked['mallFl']['all'];?> />전체
                        </label>
                        <?php
                        foreach ($gGlobal['useMallList'] as $val) {
                            ?>
                            <label class="radio-inline">
                                <input type="radio" name="mallStatus" value="<?= $val['sno'] ?>" <?=$checked['mallFl'][$val['sno']];?>/><span class="flag flag-16 flag-<?= $val['domainFl'] ?>"></span> <?= $val['mallName'] ?>
                            </label>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('searchKeyword', 'searchKeyword', $search['searchKeywordList'], null, $search['searchKeyword']); ?>
                        <input type="text" name="keyword" value="<?= $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>진행상태</th>
                <td><?=gd_radio_box('eventStatusFl', $search['searchStatus'], $search['eventStatusFl']);?></td>
                <th>이벤트 유형</th>
                <td><?=gd_radio_box('eventType', $search['searchEventType'], $search['eventType'], $checked['eventType']);?></td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('searchDateStatus', 'searchDateStatus', $search['searchDateStatusList'], null, $search['searchDateStatus']); ?>
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?=gd_isset($search['searchDate'][0], ' ');?>">
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar"></span>
                            </span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][1];?>">
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar"></span>
                            </span>
                        </div>
                        <?=gd_search_date($search['searchPeriod'], 'searchDate', false);?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>
    <div class="table-header">
        <div class="pull-left">
            검색 <strong><?= number_format($page->recode['total']); ?></strong>개 /
            전체 <strong><?= number_format($page->recode['amount']); ?></strong>개
        </div>
        <div class="pull-right form-inline">
            <?= gd_select_box('sortList', 'sortList', $search['sortList'], null, $search['sort'], null, null, 'form-control js-page-sort'); ?>
            <?= gd_select_box_by_page_view_count($search['pageNum']); ?>
        </div>
    </div>
</form>

<form id="frmMemberModifyEventList" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width5p"><input type="checkbox" class="js-checkall" data-target-name="eventListCheck[]"></th>
            <th class="width5p">번호</th>
            <?php if ($gGlobal['isUse'] === true) { ?>
                <th class="width5p">상점구분</th>
            <?php } ?>
            <th>이벤트명</th>
            <th>이벤트 유형</th>
            <th class="width7p">진행상태</th>
            <th class="width20p">진행기간</th>
            <th class="width7p">등록자</th>
            <th class="width10p">등록일</th>
            <th class="width7p">참여내역관리</th>
            <th class="width7p">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php if (gd_isset($eventList['data'])) {
            foreach ($eventList['data'] as $key => $val) { ?>
                <tr class="text-center">
                    <td><input type="checkbox" name="eventListCheck[]" value="<?=$val['sno'];?>" data-status="<?=$val['eventStatusFl'];?>" data-eventstatusfl="<?=$val['display']['eventStatusFl'];?>" /></td>
                    <td><?=$page->idx--;?></td>
                    <?php if ($gGlobal['isUse'] === true) { ?>
                        <td class="font-kor">
                            <span class="flag flag-16 flag-<?=$val['domainFl']?>"></span>
                            <?=$val['mallName']?>
                        </td>
                    <?php } ?>
                    <td><?=$val['eventNm'];?></td>
                    <td><?=$val['eventTypeName'];?></td>
                    <td><?=$val['display']['eventStatusFl'];?></td>
                    <td><?=$val['display']['eventStartDate'];?> <?=$val['display']['eventStartTime'];?> ~ <?=$val['display']['eventEndDate'];?> <?=$val['display']['eventEndTime'];?></td>
                    <td><?=$val['managerNm'];?><br/>(<?=$val['managerId'];?>)<?=$val['deleteText'];?></td>
                    <td><?=$val['display']['regDate'];?></td>
                    <td><?=$val['memberShipTotal']?><br /><a href="../member/member_modify_event_result.php?eventNo=<?=$val['sno'];?>" class="btn btn-sm btn-white">관리</a></td>
                    <td><a href="../member/member_modify_event_register.php?eventNo=<?=$val['sno'];?>" class="btn btn-sm btn-white">수정</a></td>
                </tr>
            <?php }
        } else { ?>
            <tr class="text-center">
                <td colspan="12" class="no-data">검색된 이벤트가 없습니다.</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white js-delete-event">선택삭제</button>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-white js-close-event">종료하기</button>
        </div>
    </div>
</form>
<div class="center"><?=$page->getPage();?></div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 회원정보 수정 이벤트 등록
        $('.js-register').click(function () {
            location.href = './member_modify_event_register.php';
        });

        // 이벤트 종료
        $('.js-close-event').click(function () {
            if ($('input[name="eventListCheck[]"]:checked').length === 0) {
                alert('종료할 이벤트를 선택해 주세요.');
                return false;
            }

            var closeEventFl = false;
            $('input[name="eventListCheck[]"]:checked').each(function () {
                var status = $(this).data('status');
                if (status === 'n') {
                    alert('종료된 이벤트입니다.');
                    closeEventFl = true;
                    return false;
                } else if (status === 'w') {
                    alert('진행중인 이벤트가 아닙니다.');
                    closeEventFl = true;
                    return false;
                }
            });

            if (closeEventFl) {
                return false;
            }

            $('#frmMemberModifyEventList input[name="mode"]').val('closeEvent');
            var params = $('#frmMemberModifyEventList').serializeArray();
            post_with_reload('../member/member_modify_event_ps.php', params);
        });

        // 이벤트 삭제
        $('.js-delete-event').click(function () {
            var chkEventList = $('input[name="eventListCheck[]"]:checked');
            if (chkEventList.length === 0) {
                alert('삭제할 이벤트를 선택해 주세요.');
                return false;
            }

            var deleteEventFl = false;
            chkEventList.each(function () {
                if ($(this).data('eventstatusfl') === 'y' && $(this).data('eventstatusfl') === '진행중') {
                    alert('진행 중인 이벤트는 삭제하실 수 없습니다. 이벤트 종료 후 삭제하여 주시기 바랍니다.');
                    deleteEventFl = true;
                    return false;
                }
            });

            if (deleteEventFl) {
                return false;
            }

            BootstrapDialog.confirm({
                title: '회원정보 수정 이벤트 삭제',
                message: '선택된 ' + chkEventList.length + '개의 이벤트를 정말로 삭제하시겠습니까?<br/> 삭제된 이벤트는 복구되지 않습니다.',
                btnCancelLabel: "취소",
                btnOKLabel: '확인',
                closable: true,
                callback: function(result){
                    if (result) {
                        $('#frmMemberModifyEventList input[name="mode"]').val('deleteEvent');
                        var params = $('#frmMemberModifyEventList').serializeArray();
                        post_with_reload('../member/member_modify_event_ps.php', params);
                    }
                }
            });
        });
    });

    //-->
</script>