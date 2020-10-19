<form id="frm" name="frm" method="post" action="qr_code_ps.php" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="config">

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>
    <div class="table-title">
        QR코드 기본설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>상품 상세페이지<br/> QR코드 노출상태</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="useGoods" value="y" <?php echo ($data['useGoods'] == 'y') ? 'checked' : ''; ?>>
                    사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="useGoods" value="n" <?php echo ($data['useGoods'] == 'n') ? 'checked' : ''; ?>>
                    사용안함
                </label>
                <p class="notice-info">"사용함" 선택 후 상품>상품관리>상품등록 화면에서 QR코드 노출상태를 "노출함"으로 설정하면 상품 상세 화면에 상품 주소 정보를 담은 QR코드가 노출됩니다.</p>
            </td>
        </tr>
        <!--        <tr>
            <th>이벤트 QR 사용</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="useEvent" value="y" <?php /*echo ($data['useEvent'] == 'y') ? 'checked' : ''; */ ?>>
                    사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="useEvent" value="n" <?php /*echo ($data['useEvent'] == 'n') ? 'checked' : ''; */ ?>>
                    사용안함
                </label>
                <p class="notice-info">이벤트 페이지에 이벤트 주소 정보를 담은 qr코드가 삽입 됩니다.</p>
            </td>
        </tr>-->
        <tr>
            <th>QR노출 형태</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="qrStyle" value="image" <?php echo ($data['qrStyle'] == 'image') ? 'checked' : ''; ?>>
                    QR코드 이미지
                </label>
                <label class="radio-inline">
                    <input type="radio" name="qrStyle" value="btn" <?php echo ($data['qrStyle'] == 'btn') ? 'checked' : ''; ?>>
                    QR코드 이미지 + 저장버튼
                </label>
            </td>
        </tr>
    </table>
</form>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#frm').validate({
            submitHandler: function (form) {
                var params = $(form).serializeArray();
                post_with_reload('../promotion/qr_code_ps.php', params);
            }
        });
    });
    // -->
</script>
