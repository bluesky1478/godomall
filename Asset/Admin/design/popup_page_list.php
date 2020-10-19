<?php if ($getValue['reloadDisplay'] != true) { ?>
<div id="popup-page-regist-area">
    <div class="page-header">
        <div class="page-header-title"><h3>노출위치 관리</h3></div>
        <div class="page-header-regist"><input type="button" value="노출위치 등록" class="btn btn-red-line btn-popup-page-regist"></div>
    </div>
</div>
<?php } ?>

<div id="popup-page-list-area">
    <form name="frmPopupPageList" id="frmPopupPageList" method="post" action="./popup_ps.php">
        <input type="hidden" name="mode" value="popupPageDelete">
        <div>
            <div class="pull-left table-title">노출위치 리스트</div>
            <div class="pull-right">
                <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50]), '개 보기', \Request::get()->get('pageNum'), null); ?>
            </div>
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-4xs"/>
                <col class="width-3xs"/>
                <col class="width-xs"/>
                <col/>
                <col class="width-sm"/>
                <col class="width-2xs"/>
            </colgroup>
            <thead>
            <tr>
                <th><input type="checkbox" class="popup-all-check"></th>
                <th>번호</th>
                <th>구분</th>
                <th>페이지명/URL</th>
                <th>등록일/수정일</th>
                <th>수정</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (empty($data) === false) {
                foreach ($data as $value) {
                    ?>
                    <tr>
                        <td><input type="checkbox" name="del[]" value="<?php echo $value['sno']; ?>"></td>
                        <td align="center"><?php echo $page->idx--; ?></td>
                        <td>
                            <?php
                                if ($value['pcDisplayFl'] == 'y' && $value['mobileDisplayFl'] == 'y') {
                                    echo '공통';
                                } elseif ($value['pcDisplayFl'] == 'y') {
                                    echo 'PC쇼핑몰';
                                } elseif ($value['mobileDisplayFl'] == 'y') {
                                    echo '모바일쇼핑몰';
                                }
                            ?>
                        </td>
                        <td>
                            <?php
                                echo $value['pageName'] . '<br />' . $value['pageUrl'];
                            ?>
                        </td>
                        <td align="center">
                            <?php
                            echo gd_date_format('Y-m-d', $value['regDt']) . '<br />' . gd_date_format('Y-m-d', $value['modDt']) ?? '-';
                            ?>
                        </td>
                        <td align="center"><input type="button" data-sno="<?php echo $value['sno']; ?>" value="수정" class="btn btn-gray btn-sm btn-popup-page-regist"></td>
                    </tr>
                    <?php
                }
            } else { ?>
            <td colspan="5" align="center">등록된 정보가 없습니다.</td>
            <?php } ?>
            </tbody>
            <tfoot>
                <th colspan="5">
                    <input type="button" value="선택 삭제" class="btn btn-gray btn-sm btn-popup-page-delete">
                </th>
            </tfoot>
        </table>
    </form>
    <div class="center"><?php echo $page->getPage('layer_list(\'PAGELINK\')'); ?></div>
</div>

<?php if ($getValue['reloadDisplay'] != true) { ?>
<style>
    .page-header {position:relative; margin:-15px 0 15px;}
    .page-header .page-header-regist {position:absolute; top:0; right:1px;}
</style>

<script type="text/javascript">
    $(function(){
        $('#popupPageForm').on('click', '.btn-popup-page-regist', function(){
            var sno = $(this).data('sno');
            var param = {
                'layerFormID': 'popupPageForm',
                'mode': 'simple',
                'sno': sno
            };
            moveUrl('./popup_page_regist.php', {'layerFormID': 'popupPageForm', 'mode': 'simple', 'sno': sno}, '#popup-page-regist-area');
        });

        $('select[name="pageNum"]').change(function(){
            moveUrl('./popup_page_list.php', {'layerFormID': 'popupPageForm', 'mode': 'simple', 'reloadDisplay': 1, 'pageNum': $(this).val()}, '#popup-page-list-area');
        });

        $('#popupPageForm').on('click', '.popup-all-check', function(){
            if ($(this).prop('checked') === true) {
                $('input[name="del[]"]').prop('checked', true);
            } else {
                $('input[name="del[]"]').prop('checked', false);
            }
        });

        $('#popupPageForm').on('click', '.btn-popup-page-delete', function(){
            var len = $('input[name="del[]"]:checked').length;
            if (!len) {
                alert('삭제할 리스트를 선택해주세요.');
                return false;
            }

            if (confirm('삭제하시겠습니까')) {
                var param = $('#frmPopupPageList').serialize();
                $.ajax({
                    method: 'post',
                    cache: false,
                    url: './popup_ps.php',
                    data: param,
                    success: function(data){
                        data = $.parseJSON(data);
                        for (var i in data) {
                            $('select[name="popupPageUrl"]').find('option[data-sno="' + data[i] + '"]').remove();
                            $('select[name="mobilePopupPageUrl"]').find('option[data-sno="' + data[i] + '"]').remove();
                        }
                        moveUrl('./popup_page_list.php', {'layerFormID': 'popupPageForm', 'mode': 'simple', 'reloadDisplay': 1, 'pageNum': $('select[name="pageNum"]').val()}, '#popup-page-list-area');
                    }
                });
            }
        });
    });

    function layer_list(pagelink) {
        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        moveUrl('./popup_page_list.php', {'layerFormID': 'popupPageForm', 'mode': 'simple', 'reloadDisplay': 1, 'pageNum': $('select[name="pageNum"]').val(), 'pagelink': pagelink}, '#popup-page-list-area');
    }

    function moveUrl(url, param, target)
    {
        $.get(url, param, function(data){
            $(target).html(data);
        });
    }
</script>
<?php } ?>
