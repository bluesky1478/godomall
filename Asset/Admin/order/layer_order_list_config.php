<table class="table table-cols no-title-line">
    <colgroup>
        <col class="width-sm"/>
        <col/>
    </colgroup>
    <tr>
        <th>기본 조회 기간</th>
        <td>
            <div class="form-inline">
                <input type="text" name="searchPeriod" value="<?= $data['searchPeriod']; ?>" class="form-control width-xs"/> 일
            </div>
        </td>

    </tr>
    <tr>
        <th>기본 상태 설정</th>
        <td>
            <?php
            foreach ($statusStandardNm as $key => $val) {
                echo '<label class="nobr"><input type="checkbox" name="searchStatus[]" value="' . $key . '" ' . gd_isset($checked['searchStatus'][$key]) . ' />' . $val . ' 단계' . str_repeat('&nbsp', 3) . '</label>';
            }
            ?>
        </td>
    </tr>
</table>

<div class="text-center">
    <input type="button" value="저장" onclick="list_config();" class="btn btn-lg btn-black" />
</div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('input[name=\'searchPeriod\']').number_only(4, 100, 100);
    });

    /**
     * 설정 값 저장
     */
    function list_config() {
        var searchPeriod = $('input[name=\'searchPeriod\']').val();
        var searchStatus = '';
        var searchStatusLen = $('input[name=\'searchStatus\[\]\']').length;

        for (var i = 0; i < searchStatusLen; i++) {
            var checkedStatus = $('input[name=\'searchStatus\[\]\']:checked').eq(i).val();
            if (typeof checkedStatus != 'undefined') {
                searchStatus += checkedStatus + '<?= STR_DIVISION;?>';
            }
        }

        var parameters = {
            'mode': 'config_order_list',
            'searchPeriod': searchPeriod,
            'searchStatus': searchStatus
        };

        // 저장
        $.post('order_ps.php', parameters, function (data) {
            // 페이지 reload
            location.reload();
        });
    }
    //-->
</script>
