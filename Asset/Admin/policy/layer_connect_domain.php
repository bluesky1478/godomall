<form name="frmLayer" id="frmLayer" method="post" action="">
    <input type="hidden" id="sno" name="sno" value="<?= $sno; ?>"/>
    <div id="">
        <div class="form-inline">
            <table class="table table-cols">
                <tr>
                    <th>도메인 입력</th>
                    <td>
                        <input type="text" class="width-xl" id="connectDomain" name="connectDomain"/>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="mgb30">
        <p class="notice-info">도메인 연결을 신청하시기 전에<br/>반드시 "소유 도메인"의 네임서버정보가 고도의 네임서버 정보로 되어있는지 확인하세요.!!</p>
        <p class="notice-danger">'마이고도 > 도메인 연결'에서 해당 솔루션에 연결한 도메인만 연결신청이 가능합니다.</p>
        <p class="notice-danger">아래 2가지 도메인은 해외몰 도메인 연결 신청이 불가합니다.<br/>
            ① ‘기본설정 > 기본 정보 설정 > 쇼핑몰 기본 정보’에 입력한 ‘쇼핑몰 도메인’<br/>
            ② 현재 관리자에 접속한 도메인 주소</p>
    </div>
    <div class="table-btn mgb0">
        <button type="button" class="btn btn-lg btn-gray" id="btnConnectDomain">연결 신청하기</button>
        <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
    </div>
</form>
<script type="text/javascript">
    var add_domain_function, check_domain_function;
    $(document).ready(function () {
        $('#frmLayer').validate({
            rules: {
                connectDomain: {
                    required: true
                }
            },
            messages: {
                connectDomain: {
                    required: "도메인을 입력해주시기 바랍니다."
                }
            },
            submitHandler: function (form) {
                $.ajax('../policy/mall_config_ps.php', {
                    method: "post",
                    data: {
                        mode: "validateConnectDomain",
                        connectDomain: $('#connectDomain').val()
                    },
                    success: function () {
                        var response = arguments[0];
                        if (response.result) {
                            add_domain_function($('#connectDomain').val());
                            layer_close();
                        } else {
                            close_validate_process_dialog();
                            dialog_alert(response.message, '경고', {callback: close_validate_process_dialog});
                        }
                    }
                });
            }
        });
        $('#btnConnectDomain').click(function () {
            if (check_domain_function($('#connectDomain').val())) {
                alert('이미 추가된 도메인 입니다.');
            } else {
                $('#frmLayer').submit();
            }
        });
    });
</script>
