<?php
//@formatter:off
$sortArray=['regDt DESC'=>'발송일&darr;','regDt ASC'=>'발송일&uarr;','subject DESC'=>'메일제목&darr;','subject ASC'=>'메일제목&uarr;',];
$keyArray=['all'=>'=통합검색=','sender'=>'발송자','receiver'=>'발송대상','subject'=>'메일제목',];
//@formatter:on
$sortSelect = gd_select_box('sort', 'sort', $sortArray, '', Request::get()->get('sort'));
$keySelect = gd_select_box('key', 'key', $keyArray, null, Request::get()->get('key'));
?>
<!-- //@formatter:off -->
<form id="formSearch" method="get" class="content-form js-search-form js-form-enter-submit">
    <div class="page-header js-affix"><h3><?= end($naviMenu->location); ?></h3></div>
    <input type="hidden" name="sort" value="<?= $requestGetParams['sort'] ?>"/>
    <input type="hidden" name="pageNum" value="<?= $requestGetParams['pageNum'] ?>"/>
    <div class="table-title gd-help-manual">메일 발송 내역 보기 검색</div>
    <div class="search-detail-box form-inline">
        <table class="table table-cols">
            <colgroup><col class="width-sm"><col></colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td><?= $keySelect ?><input type="text" name="keyword" value="<?= $requestGetParams['keyword']; ?>" class="form-control"/></td>
                <th>발송유형</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="sendType" value="" <?= $checked['sendType']['']; ?>/>전체</label>
                    <label class="radio-inline"><input type="radio" name="sendType" value="auto" <?= $checked['sendType']['auto']; ?>/>자동메일</label>
                    <label class="radio-inline"><input type="radio" name="sendType" value="manual" <?= $checked['sendType']['manual']; ?>/>개별/전체메일</label>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="input-group js-datepicker"><input type="text" class="form-control" placeholder="" name="regdt[]" value="<?= $requestGetParams['regdt'][0]; ?>"/><span class="input-group-addon"><span class="btn-icon-calendar"></span></span></div>
                    ~
                    <div class="input-group js-datepicker"><input type="text" class="form-control" placeholder="" name="regdt[]" value="<?= $requestGetParams['regdt'][1]; ?>"/><span class="input-group-addon"><span class="btn-icon-calendar"></span></span></div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn"><input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/></div>
</form>
<form id="formList" action="" method="get" target="ifrmProcess">
    <div class="table-header form-inline">
        <div class="pull-left"><?= gd_display_only_search_result($page->recode['total'], '개'); ?></div>
        <div class="pull-right"><div><?= $sortSelect ?> <?= gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10)); ?></div></div>
    </div>
    <table class="table table-rows">
        <colgroup><col class="width-xs"/><col class="width-xs"/><col class="width-xs"/><col class="width30p"/><col><col><col><col></colgroup>
        <thead><tr><th><input type="checkbox" id="chk_all" class="js-checkall" data-target-name="chk"/></th><th>번호</th><th>발송유형</th><th>메일제목</th><th>발송자</th><th>발송일시</th><th>발송대상</th><th>내용보기</th></tr></thead>
        <tbody>
        <?php
        if (count($data) > 0) {
            $listHtml = [];
            foreach ($data as $val) {
                $isAuto = $val['sendType'] == 'auto';
                $sno = $val['sno'];
                $listHtml[] = '<tr class="center" data-member-no="' . $sno . '">';
                $listHtml[] = '<td><input type="checkbox" name="chk[]" value="' . $sno . '"/></td>';
                $listHtml[] = '<td class="font-num ">' . $page->idx-- . '</td>';
                if ($isAuto) {
                    $listHtml[] = '<td class="font-kor">자동메일</td>';
                } else {
                    $listHtml[] = '<td class="font-kor">개별/전체메일</td>';
                }
                $listHtml[] = '<td class="">' . $val['subject'] . '</td>';
                if ($isAuto) {
                    $listHtml[] = '<td class="">-</td>';
                } else {
                    $listHtml[] = '<td class="">' . $val['sender'] .$val['deleteText']. '</td>';
                }
                $listHtml[] = '<td class="font-date">' . $val['regDt'] . '</td>';
                if ($isAuto) {
                    $listHtml[] = '<td class="font-eng">' . gd_htmlspecialchars($val['receiver']) . '</td>';
                } else {
                    $listHtml[] = '<td class="font-num">' . $val['receiverCnt'] . '명</td>';
                }
                $listHtml[] = '<td><button type="button" class="btn btn-gray btn-sm btn-view" data-sno="' . $sno . '">내용보기</button></td>';
                $listHtml[] = '</tr>';
            }
            echo join('', $listHtml);
        } else {
            ?>
            <tr><td class="center" colspan="8">검색된 정보가 없습니다.</td></tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left"><button type="submit" class="btn btn-white" id="btnDelete">선택 삭제</button></div>
    </div>
    <div class="center"><?= $page->getPage(); ?></div>
</form>
<!-- //@formatter:on -->
<script type="text/javascript">
    $(document).ready(function () {
        var $formList = $('#formList');

        $formList.validate({
            rules: {
                "chk[]": "required"
            }, messages: {
                "chk[]": "선택된 내역이 없습니다."
            },
            submitHandler: function (form) {
                var data = $(form).serializeArray();
                data.push({name: "mode", value: "deleteMailLog"});

                post_with_reload('../member/mail_ps.php', data);
            }
        });

        $('#formSearch').validate({
            submitHandler: function (form) {
                $('input[name=sort]', form).val($('select[name=\'sort\']', $formList).val());
                $('input[name=pageNum]', form).val($('select[name=\'pageNum\']', $formList).val());

                form.submit();
            }
        });


        $('.btn-view', $formList).click(function (e) {
            e.preventDefault();
            var params = [{name: "sno", value: this.dataset.sno}, {name: "mode", value: "viewMailLog"}];
            var loadChk = $('#mailLogDetail').length;
            $.get('../member/layer_mail_log_detail.php', params, function (data) {
                if (loadChk == 0) {
                    data = '<div id="mailLogDetail">' + data + '</div>';
                    BootstrapDialog.show({
                        name: "layer_mail_log_detail",
                        title: "메일 발송 내역",
                        size: BootstrapDialog.SIZE_WIDE_LARGE,
                        message: $(data),
                        closable: true
                    });
                }
            });
        });
        $('select[name=\'sort\']').change({targetForm: '#formSearch'}, member.page_sort);
        $('select[name=\'pageNum\']').change({targetForm: '#formSearch'}, member.page_number);
    });
</script>
<script type="text/template" id="mailLogTemplate">
    <div class="panel panel-default">
        <div class="panel-body"><%= log.contents %></div>
    </div>
    <div class="text-center">
        <button class="btn btn-black js-layer-close">닫기</button>
    </div>
</script>
