<form id="frmIfdo" name="frmIfdo" action="ifdo_ps.php" method="post">
    <input type="hidden" name="mode" value="config"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>

        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        IFDO 마케팅 자동화 사용 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>사용여부</th>
            <td class="form-inline">
                <label class="radio-inline">
                    <input type="radio" name="ifdoUseType" value="y" <?= $checked['ifdoUseType']['y']; ?> />사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="ifdoUseType" value="n" <?= $checked['ifdoUseType']['n']; ?> />사용안함
                </label>
                <p class="notice-info">서비스를 사용하시려면 IFDO 마케팅 자동화 신청 후 사용이 가능합니다.<a href="https://www.godo.co.kr/echost/power/add/marketing/ifdo-intro.gd" target="_blank" class="btn-link">신청하기</a></p>
            </td>
        </tr>
        <tr>
            <th>사이트 구분 코드</th>
            <td class="form-inline">
                <input type="text" name="ifdoServiceCode" value="<?= $data['ifdoServiceCode'] ?>" class="form-control width-sm"/>
                <p class="notice-info">IFDO 회원가입 시 발급 된 회원 구분키를 입력해주세요.</p>
            </td>
        </tr>
        </tbody>
    </table>
</form>
<div class="notice-info">
    IFDO 관리자 화면에서 실시간 방문자 정보 확인 및 고객 세분화, 타겟 메시지 발송 등을 하실 수 있습니다.
</div>
<div class="notice-info">
    사이트 구분 코드는 IFDO 관리자 > 분석 스크립트 메뉴에서 다시 확인하실 수 있습니다.
</div>
<div class="center">
    <a href="https://ifdo.co.kr/login/login_frm.apz" target="_blank"/><button type="button" class="btn btn-lg btn-black">IFDO 관리자 바로가기</button></a>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#frmIfdo').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                ifdoServiceCode: {
                    required: function (input) {
                        var required = false;
                        if ($('[name=ifdoUseType]:checked').val() == 'y') {
                            required = true;
                        }
                        return required;
                    },
                },
            },
            messages: {
                ifdoServiceCode: {
                    required: '회원 구분키를 입력하여주세요.',
                },
            }
        });
        $('input:radio[name="ifdoUseType"]').click(function () {
            ifdoServiceCodeChk();
        });
        ifdoServiceCodeChk();
    });
    function ifdoServiceCodeChk() {
        if ($('input:radio[name="ifdoUseType"]:checked').val() == 'y') {
            $('input:text[name="ifdoServiceCode"]').prop('readonly', false);
        } else {
            $('input:text[name="ifdoServiceCode"]').prop('readonly', true);
        }
    }
    //-->
</script>
