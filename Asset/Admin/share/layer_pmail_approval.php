<div class="formPmailApproval">
<div class="table-title text-orange-red">
    대량메일 사용을 위해서는 개인정보 제3자 제공 동의가 필요합니다.
</div>
<div class="form-inline">
    <div class="toggle-item">
        <div>
            <label class="checkbox-inline">
                <input type="checkbox" name="approvalFl" value="y"> (필수) 개인정보 제3자 제공에 동의합니다.
            </label>
        </div>
        <div class="pMail-area">
            대량메일 이용자들의 개인정보를 아래 이용 목적에서 고지한 범위 내에서 사용하며, 이용자의 사전 동의 없이는 동 범위를 초과하여 이용하거나 원칙적으로 이용자의 개인정보를 외부에 공개하지 않습니다.</br>
            1. 제공받는자 : ㈜휴머스온</br>
            2. 이용 목적 : 대량메일 서비스 이용을 위한 고객 응대 및 지원</br>
            3. 제공 항목 : 이름, 이메일, 전화번호, 휴대폰번호</br>
            4. 보유 및 이용기간 : 서비스 해지 후 파기
        </div>
    </div>
</div>
<div class="text-center">
    <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
    <input type="submit" value="확인" class="btn btn-lg btn-black js-submit"/>
</div>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('.js-submit').click(function(){
            if($("input:checkbox[name='approvalFl']").is(":checked") == false) {
                alert("개인정보 제3자 제공에 동의해주세요.");
                return false;
            }
            $.ajax({
                url: '../member/mail_ps.php',
                type: 'post',
                data: {
                    mode: 'configPmail',
                    userNm: '<?= $configPmail['userNm'] ?>',
                    email: '<?= $configPmail['email'] ?>',
                    'tel[]': '<?= $configPmail['tel'] ?>',
                    'mobile[]': '<?= $configPmail['mobile'] ?>',
                    approvalFl: 'y'
                },
                success: function(ret) {
                    if(ret.code) {
                        alert(ret.message);
                    } else {
                        layer_close();
                        $('#btnSendPowerMail').trigger('click');
                    }
                }
            });
        })
    });
    //-->
</script>

