<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>
<h5 class="table-title gd-help-manual">이벤트 기본 정보</h5>
<table class="table table-cols">
    <colgroup>
        <col class="width-sm"/>
        <col/>
    </colgroup>
    <tbody>
        <tr>
            <th>이벤트 유형</th>
            <td><?=$eventInfo['eventTypeName'];?></td>
        </tr>
        <tr>
            <th>이벤트명</th>
            <td><?=$eventInfo['eventNm'];?></td>
        </tr>
        <tr>
            <th>설명</th>
            <td><?=$eventInfo['eventDescription'];?></td>
        </tr>
        <tr>
            <th>진행기간</th>
            <td><?=$eventInfo['eventStartDt'];?> ~ <?=$eventInfo['eventEndDt'];?></td>
        </tr>
        <?php if ($eventInfo['exceptJoinType'] != 'unlimit') { ?>
        <tr>
            <th>이벤트 대상 제외<br/>가입기간</th>
            <td><?=$eventInfo['display']['exceptJoin']?></td>
        </tr>
        <?php } ?>
        <tr class="<?=$eventInfo['eventType'] === 'modify' ? '' : 'display-none'; ?>">
            <th>혜택지급 조건</th>
            <td><?=$eventInfo['display']['benefitCondition']?></td>
        </tr>
        <tr>
            <th>지급혜택</th>
            <td><?=$eventInfo['display']['benefit']?></td>
        </tr>
    </tbody>
</table>
<h5 class="table-title gd-help-manual">참여 내역 검색</h5>
<form id="frmSearchEventList" method="get" class="content-form js-form-enter-submit">
    <input type="hidden" name="eventNo" value="<?=$eventInfo['sno'];?>"/>
    <input type="hidden" name="pageNum" value="<?=$search['pageNum'];?>"/>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm">
                <col>
                <col class="width-sm">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('searchKeyword', 'searchKeyword', $search['searchKeywordResult'], null, $search['searchKeyword']); ?>
                        <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>참여일자 검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][0];?>">
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
                        <?=gd_search_date($search['searchPeriod'], 'searchDate');?>
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
        <div class="pull-right">
            <div class="form-inline">
                <?= gd_select_box_by_page_view_count($search['pageNum']); ?>
            </div>
        </div>
    </div>
</form>
<form id="frmMemberModifyEventResult" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <table class="table table-rows">
        <colgroup>
            <col class="width-sm">
            <col class="width-sm">
            <col>
            <col>
            <col>
            <col class="width-lg">
        </colgroup>
        <thead>
            <tr>
                <!--<th><input type="checkbox" class="js-checkall" data-target-name="eventResultCheck[]"></th>-->
                <th>번호</th>
                <th>아이디</th>
                <th>이름</th>
                <th>등급</th>
                <th>참여일시</th>
            </tr>
        </thead>
        <tbody>
        <?php if (gd_isset($eventResult['data'])) {
            foreach ($eventResult['data'] as $key => $val) { ?>
            <tr class="text-center">
                <!--<td><input type="checkbox" name="eventResultCheck[]" value="<?/*=$val['sno'];*/?>" /></td>-->
                <td><?=$page->idx--;?></td>
                <td><span class="js-layer-crm hand" data-member-no="<?=$val['memNo'];?>"><?=$val['memId'];?></span></td>
                <td><?=$val['memNm'];?></td>
                <td><?=$val['groupNm'];?></td>
                <td><?=$val['regDt'];?></td>
            </tr>
            <?php }
        } else { ?>
            <tr class="text-center">
                <td colspan="12" class="no-data">검색된 정보가 없습니다.</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <div class="table-action">
        <!--<div class="pull-left">
            <button type="button" class="btn btn-white js-delete-result">선택 삭제</button>
        </div>-->
        <div class="pull-right">
            <button type="button" class="btn btn-white btn-icon-excel btn-excel">엑셀다운로드</button>
        </div>
    </div>
</form>
<div class="center"><?= $page->getPage(); ?></div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 이벤트 삭제
        $('.js-delete-result').click(function () {
            var chkEventResult = $('input[name="eventResultCheck[]"]:checked');
            if (chkEventResult.length === 0) {
                alert('선택 내역이 없습니다.');
                return false;
            }

            BootstrapDialog.confirm({
                title: '회원정보 수정 이벤트 참여내역 삭제',
                message: '선택한 참여내역을 삭제하시겠습니까?<br/>참여내역이 삭제된 회원은 회원정보 수정 이벤트에 다시 참여할 수 있습니다.',
                btnCancelLabel: "취소",
                btnOKLabel: '확인',
                closable: true,
                callback: function(result){
                    if (result) {
                        $('#frmMemberModifyEventResult input[name="mode"]').val('deleteResult');
                        var params = $('#frmMemberModifyEventResult').serializeArray();
                        post_with_reload('../member/member_modify_event_ps.php', params);
                    }
                }
            });
        });

        // 엑셀 다운로드
        $('.btn-excel').click(function () {
            var $form = $('<form></form>');
            $form.attr('action', './excel_member_ps.php');
            $form.attr('method', 'post');
            $form.attr('target', 'ifrmProcess');
            $form.appendTo('body');

            var mode = $('<input type="hidden" name="mode" value="excel_modify_event_result_down">');
            var eventNo = $('<input type="hidden" name="eventNo" value="<?=$eventInfo['sno'];?>">');
            var searchKeyword = $('<input type="hidden" name="searchKeyword" value="<?=$search['searchKeyword'];?>">');
            var keyword = $('<input type="hidden" name="keyword" value="<?=$search['keyword'];?>">');
            var searchStartDate = $('<input type="hidden" name="searchDate[]" value="<?=$search['searchDate'][0];?>">');
            var searchEndDate = $('<input type="hidden" name="searchDate[]" value="<?=$search['searchDate'][1];?>">');
            var searchPeriod = $('<input type="hidden" name="searchPeriod" value="<?=$search['searchPeriod'];?>">');

            $form.append(mode).append(eventNo).append(keyword).append(searchStartDate).append(searchEndDate).append(searchKeyword).append(searchPeriod);
            $form.submit();
        });
    });

    //-->
</script>
