<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearch').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearch').submit();
        });

        $(".js-btn-write").bind('click', function () {
            $.ajax({
                url: 'template_write.php',
                success: function (data) {
                    var layerForm = data;
                    layer_popup($(layerForm), '게시글 양식 등록', 'wide')
                }
            });
        });

        $('.js-btn-modify').click(function () {
            var sno = $(this).data('sno');
            $.ajax({
                url: 'template_write.php',
                data: 'sno=' + sno,
                type: 'get',
                success: function (data) {
                    var layerForm = data;
                    layer_popup($(layerForm), '게시글 양식 등록', 'wide')
                }
            });
        });

        $("#frmList").validate({
            dialog: false,
            submitHandler: function (form) {
                var chkCnt = $('input[name*="sno"]:checked').length;
                dialog_confirm('선택된 게시글 양식 '+chkCnt+'개를 정말로 삭제하시겠습니까? \n삭제된 게시글 양식은 복구 되지 않습니다. ', function (result) {
                    if (result) {
                        form.target = 'ifrmProcess';
                        form.submit();
                    }
                });
            },
            rules: {
                "sno[]": 'required'
            },
            messages: {
                "sno[]": '선택된 게시글 양식이 없습니다.'
            }
        });

        // 삭제
        $('.js-btn-delete').click(function () {
            $(this).closest('tr').find('input[name="sno[]"]').prop('checked', true);
            $('.table-action button[type=submit]').trigger('click');
        });
    });

    //-->
</script>

<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?>
        <small></small>
    </h3>
    <input type="button" value="등록" class="btn btn-red js-btn-write"/>
</div>

<form name="frmSearch" id="frmSearch" action="template_list.php" class="frmSearch js-form-enter-submit">

    <div class="table-title gd-help-manual">게시글 양식 검색</div>

    <form name="frmSearch" id="frmSearch" action="article_list.php" class="frmSearch">
        <div class="search-detail-box">
            <table class="table table-cols">
                <tr>
                    <th class="width-xs">검색어</th>
                    <td>
                        <div class="form-inline">
                            <select class="form-control" name="searchField">
                                <option value="all" <?php if ($req['searchField'] == 'all') echo 'selected' ?>>
                                    =통합검색=
                                </option>
                                <option value="subject" <?php if ($req['searchField'] == 'subject') echo 'selected' ?>>
                                    제목
                                </option>
                                <option value="contents" <?php if ($req['searchField'] == 'contents') echo 'selected' ?>>내용
                                </option>
                            </select>

                            <input name="searchWord" value="<?= gd_isset($req['searchWord']) ?>"
                                   class="form-control form-control">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="width-xs">분류</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="templateType" value="" <?php if ($req['templateType'] == '') echo 'checked' ?>>전체
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="templateType" value="front" <?php if ($req['templateType'] == 'front') echo 'checked' ?>>쇼핑몰 게시글 양식
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="templateType" value="admin" <?php if ($req['templateType'] == 'admin') echo 'checked' ?>>관리자 게시글 양식
                        </label>

                    </td>
                </tr>
            </table>
            <div class="table-btn">
                <input type="submit" value="검색" class="btn btn-lg btn-black">
            </div>
    </form>


    <div class="table-header">
        <div class="pull-left">
            검색&nbsp;<strong><?= number_format($data['totalCnt']) ?></strong>개/
            전체&nbsp;<strong><?= number_format($data['amountCnt']) ?></strong>개
        </div>
        <div class="pull-right">
            <div class="form-inline">
                <?= gd_select_box('sort', 'sort', $data['sort'], null, $req['sort']); ?>
                <?= gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10)); ?>
            </div>
        </div>
    </div>
</form>

<form id="frmList" action="template_ps.php" method="post">
    <input type="hidden" name="mode" value="delete"/>

    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width-2xs">
                <input type="checkbox" class="js-checkall" data-target-name="sno">
            </th>
            <th class="width-2xs">번호</th>
            <th class="width-lg">분류</th>
            <th>제목</th>
            <th class="width-lg">등록일 / 수정일</th>
            <th class="width-xs">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_array_is_empty($data['data']) === false) {
            foreach ($data['data'] as $row) {
                ?>
                <tr class="text-center">
                    <td><input type="checkbox" name="sno[]" value="<?= $row['sno'] ?>"/></td>
                    <td><?= number_format($pager->idx--) ?></td>
                    <td><?= $row['templateTypeText'] ?></td>
                    <td align="left">
                        <a class="js-btn-modify hand" data-sno="<?= $row['sno'] ?>"><?= $row['subject'] ?></a>
                    </td>
                    <td><?=$row['regDtDate']?><br><?=$row['modDtDate']?></td>
                    <td><a class="js-btn-modify btn btn-white btn-sm" data-sno="<?= $row['sno'] ?>">수정</a>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr align="center">
                <td colspan="5" class="no-data">템플릿이 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left form-inline">
            <button type="submit" class="btn btn-white"/>
            선택 삭제</button>
        </div>
    </div>

    <div class="center"><?= $pager->getPage(); ?></div>
</form>

