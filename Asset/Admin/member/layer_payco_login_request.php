<form id="layerForm" name="layerForm" action="" method="post">
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>쇼핑몰명</th>
            <td>
                <input type="text" name="serviceName" id="serviceName" value="" placeholder="예) 고도몰" class="form-control width-lg"/>
            </td>
        </tr>
        <tr>
            <th>쇼핑몰 URL</th>
            <td>
                <input type="text" name="serviceURL" id="serviceURL" value="" placeholder="예) godo.co.kr" class="form-control width-lg"/>
            </td>
        </tr>
        <tr>
            <th>상호(회사명)</th>
            <td>
                <input type="text" name="consumerName" id="consumerName" value="" placeholder="예) 엔에이치엔고도(주)" class="form-control width-lg"/>
            </td>
        </tr>
    </table>
    <div id="paycoAgreement" class="form-inline panel pd10">
        <?= $terms; ?>
    </div>
    <div class="form-inline center">
        <label class="checkbox-inline mgb10">
            <input type="checkbox" name="agreementFlag" value="y"/>
            위 내용에 모두 동의합니다.
        </label>
    </div>
    <div class="text-center">
        <button type="button" class="btn btn-lg btn-black" id="layerBtnConfirm">사용신청</button>
        <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $("#layerForm").validate({
            dialog: false,
            ignore: '.ignore',
            rules: {
                serviceName: {
                    required: true
                },
                serviceURL: {
                    required: true
                },
                consumerName: {
                    required: true
                },
                agreementFlag: {
                    required: true
                }
            },
            messages: {
                serviceName: {
                    required: "쇼핑몰명을 입력해주세요."
                },
                serviceURL: {
                    required: "쇼핑몰 URL을 입력해주세요."
                },
                consumerName: {
                    required: "상호(회사명)을 입력해주세요."
                },
                agreementFlag: {
                    required: "페이코 로그인 서비스를 사용하려면 이용정책에 동의해 주세요."
                }
            },
            submitHandler: function (form) {
                var params = $(form).serializeArray();
                params.push({name: "serviceCode", value: "GODO5"});
                var $ajax = $.ajax('payco_login_request_ps.php', {
                    data: params,
                    method: "POST"
                });
                $ajax.done(function (response) {
                    alert(response.message);
                    dialog_alert(response.message, '확인', {
                        callback: function () {
                            if (_.isUndefined(response.error)) {
                                var $form = $('#form');
                                $form.find('#clientId').val(response.clientId);
                                $form.find('#clientSecret').val(response.clientSecret);
                                $(form).find(':text').each(function () {
                                    $form.find(':hidden[name="' + this.name + '"]').val(this.value);
                                });
                                layer_close();
                            }
                        }
                    });
                });
            }
        });
        $('#layerBtnConfirm').click(function () {
            $('#layerForm').submit();
        });
    });
</script>
