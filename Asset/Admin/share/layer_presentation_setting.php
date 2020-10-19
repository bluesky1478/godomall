<form id="frmMainSetting" name="frmPresentation" action="" method="post">
    <table class="table table-cols no-title-line">
        <colgroup>
            <col class="width-xs"/>
            <col/>
        </colgroup>
        <tr>
            <th>조회기간</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="period" value="1" <?= $checked['period'][1]; ?>>
                    오늘
                </label>
                <label class="radio-inline">
                    <input type="radio" name="period" value="7" <?= $checked['period'][7]; ?>>
                    7일
                </label>
                <label class="radio-inline">
                    <input type="radio" name="period" value="15" <?= $checked['period'][15]; ?>>
                    15일
                </label>
                <label class="radio-inline">
                    <input type="radio" name="period" value="30" <?= $checked['period'][30]; ?>>
                    30일
                </label>
            </td>
        </tr>
    </table>
    <div class="text-center">
        <button type="button" class="btn btn-lg btn-white js-layer-close">닫기</button>
        <button type="button" class="btn btn-lg btn-black js-layer-save">저장</button>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $('.js-layer-save').click(function () {
            var params = {mode: "presentation", period: $(':radio[name=period]:checked').val()};
            $.ajax('../base/main_setting_ps.php', {
                method: "post",
                data: params,
                success: function () {
                    var response = arguments[0];
                    if (response.success == 'OK') {
                        location.reload();
                    } else {
                        alert(response.fail);
                        console.log(response.fail);
                    }
                }
            });
        });
    });
</script>
