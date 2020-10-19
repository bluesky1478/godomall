<!-- //@formatter:off -->
<form id="formSearch" method="get" class="content-form js-search-form">
    <input type="hidden" name="indicate" value="search"/>
    <input type="hidden" name="memNo" id="memNo" value="<?= $requestGetParams['memNo']; ?>"/>
    <input type="hidden" name="navTabs" id="navTabs" value="<?= $requestGetParams['navTabs']; ?>"/>
    <input type="hidden" name="detailSearch" value="<?= gd_isset($requestGetParams['detailSearch']); ?>"/>
    <input type="hidden" name="sort" value="<?= $requestGetParams['sort']; ?>"/>
    <input type="hidden" name="pageNum" value="<?= $requestGetParams['pageNum']; ?>"/>
    <div class="form-inline search-detail-box">
        <table class="table table-cols">
            <colgroup><col class="width-sm"/><col/><col class="width-sm"/><col/></colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td>
                    <?= gd_select_box('key', 'key', $keys, null, gd_isset($requestGetParams['key']), null); ?>
                    <input type="text" name="keyword" value="<?= gd_isset($requestGetParams['keyword']); ?>" class="form-control"/>
                </td>
                <th>발송유형</th>
                <td>
                    <label><input type="radio" name="sendType" value="" <?= $checked['sendType']['']; ?>/>전체</label>
                    <label><input type="radio" name="sendType" value="auto" <?= $checked['sendType']['auto']; ?>/>자동메일</label>
                    <label><input type="radio" name="sendType" value="manual" <?= $checked['sendType']['manual']; ?>/>개별/전체메일</label>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control" placeholder="" name="regDt[]" value="<?= $requestGetParams['regDt'][0]; ?>"/><span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control" placeholder="" name="regDt[]" value="<?= $requestGetParams['regDt'][1]; ?>"/><span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn"><input type="submit" value="검색" class="btn btn-lg btn-black" id="btn_search"></div>
</form>
<form id="frmList" action="" method="get" target="ifrmProcess">
    <div class="table-header form-inline">
        <div class="pull-left">메일 리스트 (검색결과<strong><?= $page->recode['total']; ?></strong>건)</div>
        <div class="pull-right">
            <div><?= gd_select_box('sort', 'sort', $sorts, '', $requestGetParams['sort']); ?> <?= gd_select_box_by_page_view_count($requestGetParams['pageNum']); ?></div>
        </div>
    </div>

    <table class="table table-rows">
        <colgroup><col class="width-xs"/><col class="width-xs"/><col><col><col><col><col></colgroup>
        <thead><tr><th>번호</th><th>발송유형</th><th>메일제목</th><th>발송자</th><th>발송일시</th><th>내용보기</th></tr></thead>
        <tbody>
        <?php
        if (count($list) > 0) {
            foreach ($list as $val) {
                ?>
                <tr class="center" data-sno="<?= $val['sno']; ?>">
                    <td class="font-num"><?= $page->idx--; ?></td><td><?= $val['sendType'] == 'auto' ? '자동메일' : '개별/전체메일' ?></td>
                    <td><?= $val['subject']; ?></td><td><?= $val['sender']; ?></td><td><?= $val['regDt']; ?></td>
                    <td><button type="button" class="btn btn-sm btn-white btn-view">내용보기</button></td>
                </tr>
            <?php }
        } else {
            echo '<tr> <td colspan="8" class="no-data">메일발송내역이 없습니다.</td> </tr>';
        }
        ?>
        </tbody>
    </table>
    <div class="center"><?= $page->getPage(); ?></div>
</form>
<!-- //@formatter:on -->
<script type="text/javascript">
    $(document).ready(function () {
        $('#frmList').on('click', 'button.btn-view', function (e) {
            e.preventDefault();
            var $tr = $(e.target).closest('tr');
            var params = [{name: "chk", value: $tr.data('sno')}, {name: "mode", value: "viewMailLog"}];

            ajax_with_layer('../member/mail_ps.php', params, function (data, textStatus, jqXHR) {
                console.log(data);
                _.templateSettings.variable = 'log';
                var _template = _.template($('script#mailLogTemplate').html());
                var _templateData = {
                    contents: data
                };

                layer_close();
                top.BootstrapDialog.show({
                    title: "메일 내용보기",
                    message: _template(_templateData),
                    size: BootstrapDialog.SIZE_WIDE_LARGE
                });
            });
        });


        $('.btn-register').click(function () {
            member_mail($('#memNo').val());
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
