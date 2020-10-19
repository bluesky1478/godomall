<form id="frmSet" name="frmSet" action="../base/schedule_ps.php" method="post">
    <input type="hidden" name="mode" value="setAlarm"/>
    <table class="table table-cols no-title-line">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>알람 사용</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="alarmUseFl" value="y" <?php echo gd_isset($checked['alarmUseFl']['y']); ?>/>사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="alarmUseFl" value="n" <?php echo gd_isset($checked['alarmUseFl']['n']); ?>/>사용안함
                </label>
            </td>
        </tr>
        <tr>
            <th>알람 방법</th>
            <td>
                <div class="form-inline">
                    <select name="dDayPopup" class="form-control input-sm width-sm">
                        <option value="1" <?php echo gd_isset($selected['dDayPopup']['1']); ?>>일정 당일</option>
                        <option value="2" <?php echo gd_isset($selected['dDayPopup']['2']); ?>>일정 1일전</option>
                        <option value="3" <?php echo gd_isset($selected['dDayPopup']['3']); ?>>일정 2일전</option>
                        <option value="4" <?php echo gd_isset($selected['dDayPopup']['4']); ?>>일정 3일전</option>
                    </select>
                    관리자페이지 로그인 시 팝업
                </div>
            </td>
        </tr>
    </table>
    <div class="text-center">
        <input type="submit" value="설정 저장" class="btn btn-red"/>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 알람 설정 저장하기
        $("#frmSet").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {},
            messages: {}
        });
    });
    //-->
</script>
