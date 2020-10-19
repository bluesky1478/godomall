<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
$depositReasons = gd_code('01006');
$requestGetParams = Request::get()->all();
?>
<div>
    <form class="formLayer" id="formLayerDeposit">
        <div class="form-inline">
            <table class="table table-cols no-title-line">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tbody>
                <tr>
                    <th>지급/차감여부</th>
                    <td colspan="3">
                        <label class="radio-inline">
                            <input type="radio" name="depositCheckFl" value="add" checked="checked" class=""/>
                            지급(+)
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="depositCheckFl" value="remove" class=""/>
                            차감(-)
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>금액설정</th>
                    <td>
                        <span>(+)</span>
                        <label>
                            <input type="text" name="depositValue" value="" class="js-number form-control" maxlength="8"/>
                            원
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>지급/차감사유</th>
                    <td>
                        <?= gd_select_box('reasonCd', 'reasonCd', $depositReasons, null, $requestGetParams['reasonCd'], '전체'); ?>
                        <div>
                            <input type="hidden" name="contents" class="form-control"
                                   value="<?= $requestGetParams['contents']; ?>"/>
                        </div>
                    </td>
                </tr>
                <tr id="trDepositCheckFlag" class="display-none">
                    <th>예치금<br/>부족 시 차감방법</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="removeMethodFl" value="minus" checked="checked" class=""/>
                            남은 예치금만 차감
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="removeMethodFl" value="exclude" class=""/>
                            예치금 부족시 차감대상 제외
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>회원안내</th>
                    <td>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="guideSend[]" value="sms" class=""/>
                            SMS발송 <a href="#member" class="js-link-sms-auto">상세설정 ></a>
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="guideSend[]" value="email" class=""/>
                            이메일발송 <a href="#point" class="js-link-mail-auto">상세설정 ></a>
                        </label>

                        <div>* SMS는 잔여포인트가 있어야 발송됩니다. (잔여포인트 :
                            <strong
                                class="font-num text-red"><?php echo gd_get_sms_point() ?></strong>
                            )
                            <button type="button" class="btn btn-gray btn-sm"
                                    onclick="show_popup('../member/sms_charge.php?popupMode=yes')">SMS 포인트 충전하기
                            </button>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-red" id="btnDeposit">처리</button>
            <button type="button" class="btn btn-gray" id="btnCancel">취소</button>
        </div>
    </form>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#formLayerDeposit').validate({
            ignore: [],
            rules: {
                reasonCd: "required",
                depositValue: "required"
            }, messages: {
                reasonCd: "예치금를 지급/차감한 사유를 입력해주세요.",
                depositValue: "지급/차감할 예치금 금액을 입력해주세요."
            }, submitHandler: function (form) {
                var $form = $(form);
                var data = $form.serializeArray();

                var depositCheckFl = $(':radio[name=depositCheckFl]:checked', $form).val();

                data.push({name: "depositCheckFl", value: depositCheckFl});

                try {
                    data.push({name: "chk[]", value: $('input[name=memNo]').val()});

                    if (depositCheckFl == 'add') {
                        data.push({name: "mode", value: "add_deposit"});
                    } else if (depositCheckFl == 'remove') {
                        data.push({name: "mode", value: "remove_deposit"});
                    } else {
                        alert("지급/차감 조건을 선택해주세요.");
                    }
                } catch (error) {
                    alert("예치금 지급/차감 중 오류가 발생하였습니다.");
                }

                ajax_with_layer('../member/member_batch_ps.php', data, function (data) {
                    var code = data.code;
                    var message = data.message;
                    if (_.isUndefined(code) && _.isUndefined(message)) {
                        layer_close();
                        BootstrapDialog.show({
                            title: '결과',
                            message: '지급/차감이 완료되었습니다.',
                            buttons: [{
                                label: '확인',
                                cssClass: 'btn-black',
                                hotkey: 32,
                                size: BootstrapDialog.SIZE_LARGE,
                                action: function (dialog) {
                                    dialog.close();
                                    top.location.reload(true);
                                }
                            }]
                        });
                    } else {
                        alert('예치금 지급/차감 처리 중 오류가 발생하였습니다.');
                    }
                });
            }
        });
        $('#btnCancel').click(function () {
            layer_close();
        });
        $('select[name=reasonCd]').on('change', function (e) {
            var $target = $(e.target);
            var $option = $target.find(':selected');
            var $contents = $('input[name=contents]');

            if ('01006006' == $option.val()) {
                $contents.attr('type', 'text').focus();
                $contents.val('');
            } else {
                $contents.attr('type', 'hidden');
                $contents.val($option.text());
            }
        });

        $(':radio[name=depositCheckFl]').on('click', function () {
            var $trDepositCheckFlag = $('#trDepositCheckFlag');
            if (this.value == 'remove') {
                $('input[name=depositValue]').parents('label').prev('span').html('(-)');
                $trDepositCheckFlag.removeClass('display-none');
            } else {
                $('input[name=depositValue]').parents('label').prev('span').html('(+)');
                $trDepositCheckFlag.addClass('display-none');
            }
        });

        sms_auto_popup();

        mail_config_auto_popup();
    });
    -->
</script>
