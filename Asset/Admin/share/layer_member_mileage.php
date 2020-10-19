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
$mileageReasons = gd_code('01005');
$requestGetParams = Request::get()->all();
?>
<div>
    <form class="formLayer" id="formLayerMileage">
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
                            <input type="radio" name="mileageCheckFl" value="add" checked="checked" class=""/>
                            지급(+)
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="mileageCheckFl" value="remove" class=""/>
                            차감(-)
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>금액설정</th>
                    <td>
                        <span>(+)</span>
                        <label>
                            <input type="text" name="mileageValue" value="" class="js-number form-control" maxlength="8"/>
                            원
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>지급/차감사유</th>
                    <td>
                        <?= gd_select_box('reasonCd', 'reasonCd', $mileageReasons, null, $requestGetParams['reasonCd'], '전체'); ?>
                        <div>
                            <input type="hidden" name="contents" class="form-control"
                                   value="<?= $requestGetParams['contents']; ?>"/>
                        </div>
                    </td>
                </tr>
                <tr id="trMileageCheckFlag" class="display-none">
                    <th>마일리지<br/>부족 시 차감방법</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="removeMethodFl" value="minus" checked="checked" class=""/>
                            마일리지 마이너스 처리 (예: -2000)
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="removeMethodFl" value="exclude" class=""/>
                            마일리지 부족시 차감대상 제외
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
            <button type="submit" class="btn btn-red" id="btnMileage">처리</button>
            <button type="button" class="btn btn-gray" id="btnCancel">취소</button>
        </div>
    </form>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#formLayerMileage').validate({
            ignore: [],
            rules: {
                reasonCd: "required",
                mileageValue: "required"
            }, messages: {
                reasonCd: "마일리지를 지급/차감한 사유를 입력해주세요.",
                mileageValue: "지급/차감할 마일리지 금액을 입력해주세요."
            }, submitHandler: function (form) {
                var $form = $(form);
                var data = $form.serializeArray();

                var mileageCheckFl = $(':radio[name=mileageCheckFl]:checked', $form).val();

                data.push({name: "mileageCheckFl", value: mileageCheckFl});

                try {
                    data.push({name: "chk[]", value: $('input[name=memNo]').val()});

                    if (mileageCheckFl == 'add') {
                        data.push({name: "mode", value: "add_mileage"});
                    } else if (mileageCheckFl == 'remove') {
                        data.push({name: "mode", value: "remove_mileage"});
                    } else {
                        alert("지급/차감 조건을 선택해주세요.");
                    }
                } catch (error) {
                    alert("마일리지 지급/차감 중 오류가 발생하였습니다.");
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
                        alert('마일리지 지급/차감 처리 중 오류가 발생하였습니다.');
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

            if ('01005011' == $option.val()) {
                $contents.attr('type', 'text').focus();
                $contents.val('');
            } else {
                $contents.attr('type', 'hidden');
                $contents.val($option.text());
            }
        });

        $(':radio[name=mileageCheckFl]').on('change', function (e) {
            var $target = $(e.target);
            var $trMileageCheckFlag = $('#trMileageCheckFlag');
            if ($target.val() == 'remove') {
                $('input[name=mileageValue]').parents('label').prev('span').html('(-)');
                $trMileageCheckFlag.removeClass('display-none');
            } else {
                $('input[name=mileageValue]').parents('label').prev('span').html('(+)');
                $trMileageCheckFlag.addClass('display-none');
            }
        });

        sms_auto_popup();

        mail_config_auto_popup();
    });
    -->
</script>
