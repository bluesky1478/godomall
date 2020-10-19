<form id="frmSync" method="post">
    <input type="hidden" name="detailSearch" value="y"/>
    <input type="hidden" name="policy[status]" value="<?= $policy['status'] ?>"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="submit" value="수동 동기화" class="btn btn-red"/>
        </div>
    </div>
</form>
<form id="frmSearch" method="get" class="js-form-enter-submit content-form">
    <div class="table-title gd-help-manual">
        수신거부 번호 입력
    </div>

    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col class=""/>
            </colgroup>
            <tbody>
            <tr>
                <th>수신거부 번호</th>
                <td class="form-inline">
                    <label>
                        <input type="text" name="keyword" value="<?= $search['keyword']; ?>" class="form-control width-md"/>
                    </label>
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
            <?= gd_display_search_result($page->recode['total'], $page->recode['amount'], '개'); ?>
        </div>
        <div class="pull-right">
            <ul>
                <li>
                    <?php echo gd_select_box_by_page_view_count($search['pageNum']); ?>
                </li>
            </ul>
        </div>
    </div>
</form>

<table class="table table-rows table-fixed">
    <colgroup>
        <col class="width10p"/>
        <col class=""/>
        <col class="width20p"/>
    </colgroup>
    <thead>
    <tr>
        <th>번호</th>
        <th>수신거부 번호</th>
        <th>등록일</th>
    </tr>
    </thead>
    <tbody class="">
    <?php if (is_array($list) && count($list) > 0) {
        foreach ($list as $key => $value) {
            ?>
            <tr class="center">
                <td><?= $page->idx--; ?></td>
                <td><?= gd_number_to_cell_phone($value['rejectCellPhone']); ?></td>
                <td><?= $value['rejectDt']; ?></td>
            </tr>
        <?php }
    } else { ?>
        <tr>
            <td class="no-data" colspan="3">검색결과가 없습니다.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<div class="center"><?php echo $page->getPage(); ?></div>
<script type="text/javascript">
    $(document).ready(function () {
        $('#frmSync').validate({
            dialog: false,
            rules: {
                "policy[status]": {
                    required: function (element) {
                        return element.value !== 'O';
                    }
                }
            },
            messages: {
                "policy[status]": {
                    required: "개통상태가 아닙니다."
                }
            },
            submitHandler: function () {
                dialog = BootstrapDialog.show({
                    buttons: [{
                        label: '확인',
                        cssClass: 'btn-primary',
                        autospin: true,
                        action: function (dialogRef) {
                            dialogRef.enableButtons(false);
                            dialogRef.setClosable(false);
                        }
                    }],
                    message: '080 수신거부 리스트를 동기화 중입니다.',
                    onshown: function (dialogRef) {
                        $(dialogRef.$modalFooter).find('button').trigger('click');
                        $.ajax('../member/sms080_ps.php', {
                            data: {mode: 'manualSync'},
                            success: function () {
                                console.log(arguments);
                                BootstrapDialog.closeAll();
                                dialog_alert(arguments[0], '확인', {isReload: true});
                            },
                            error: function () {
                                console.log(arguments);
                                BootstrapDialog.closeAll();
                                alert(arguments[0]);
                            }
                        });
                    }
                });
            }
        });
    });
</script>

