<form id="frmBank" name="frmBank" action="settle_ps.php" method="post">
    <input type="hidden" name="mode" value="<?php echo $data['mode']; ?>"/>
    <input type="hidden" name="sno" value="<?php echo gd_isset($data['sno']); ?>"/>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>은행명</th>
            <td>
                <input type="text" name="bankName" value="<?php echo $data['bankName']; ?>" class="form-control width-sm"/>
            </td>
        </tr>
        <tr>
            <th>계좌번호</th>
            <td>
                <input type="text" name="accountNumber" value="<?php echo $data['accountNumber']; ?>" class="form-control width-lg js-number js-maxlength " maxlength="20"/>
            </td>
        </tr>
        <tr>
            <th>예금주</th>
            <td>
                <input type="text" name="depositor" value="<?php echo $data['depositor']; ?>" class="form-control width-sm"/>
            </td>
        </tr>
        <tr>
            <th>사용상태</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="useFl" value="y" <?php echo gd_isset($checked['useFl']['y']); ?> <?php if($data['disabled'] =='y') { echo "disabled"; } ?>  />사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="useFl" value="n" <?php echo gd_isset($checked['useFl']['n']); ?> <?php if($data['disabled'] =='y') { echo "disabled"; } ?> />사용안함
                </label>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border-bottom:0px;border-right:0px;"><label class="checkbox-inline"><input type="checkbox"  name="defaultFl" value="y" <?php if($data['defaultFl'] =='y') { echo "disabled"; } ?> <?php echo gd_isset($checked['defaultFl']['y']); ?> >쇼핑몰에 기본으로 노출되도록 설정합니다.</label>
            <?php if($data['disabled'] =='y' || ($data['mode'] =='bank_modify' && $data['defaultFl'] =='y')) { ?>
                <input type="hidden" name="defaultFl" value="y" />
                <?php } ?>
    </td>
        </tr>
    </table>
    <div class="text-center">
        <button type="button" class="btn btn-lg btn-white js-layer-close">닫기</button>
        <input type="submit" value="저장" class="btn btn-lg btn-black" />
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 무통장입금 은행 등록
        $("#frmBank").validate({
            dialog: false,
            submitHandler: function (form) {
                var disabled = "<?=$data['disabled']?>";
                if($("#frmBank input[name='defaultFl']:checked").prop('checked') === true && $("#frmBank input[name='useFl']:checked").val() =='n') {
                    alert('쇼핑몰 기본 노출 설정 시 사용상태는 사용함으로 설정되어야 합니다');
                    return false;
                }
                if($("#frmBank input[name='defaultFl']:checked").prop('checked') === true && disabled !='y') {
                    dialog_confirm('무통장 입금은행 기본설정을 이 은행정보로 변경하시겠습니까?', function (result) {
                        if (!result) {
                            $("#frmBank input[name='defaultFl']:checked").prop("checked",false);
                        }
                        form.target = 'ifrmProcess';
                        form.submit();
                        setTimeout(function(){
                            location.reload();
                        }, 1000);
                    });
                } else {
                    form.target = 'ifrmProcess';
                    form.submit();
                }
            },
            rules: {
                bankName: "required",
                accountNumber: {
                    required: true
                },
                depositor: "required"
            },
            messages: {
                bankName: {
                    required: '은행명을 입력해 주세요.'
                },
                accountNumber: {
                    required: '계좌번호를 입력해 주세요.',
                },
                depositor: {
                    required: '예금주를 입력해 주세요.'
                }
            }
        });

        $('input[maxlength]').maxlength({
            showOnReady: true,
            alwaysShow: true
        });

        // maxlength의 경우 display none으로 되어있으면 정상작동 하지 않는다 따라서 페이지 로딩 후 maxlength가 적용된 후 display none으로 강제 처리 (임시방편 처리)
        setTimeout(function(){
            $('#frmBank').find('input[maxlength]').next('span.bootstrap-maxlength').css({top: '11px', left: '221px'});
        }, 1000);

    });
    //-->
</script>
