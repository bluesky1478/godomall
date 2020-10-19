<div>
    <form name="receiverForm" id="receiverForm" method="post" action="./order_change_ps.php" target="ifrmProcess">
        <input type="hidden" name="mode" value="update_receiver_info" />
        <input type="hidden" name="info[sno]" value="<?=$data['infoSno']?>" />
        <input type="hidden" name="orderNo" value="<?=$orderNo?>" />


        <?php include $layoutOrderViewReceiverInfoModify; ?>


        <div class="pull-left notice-danger width100p mgb20" style="margin-top: -15px;">변경된 수령자 정보는 묶음배송처리 후 묶음배송해제시 수정전 정보로 변경되지 않습니다.</div>

        <div class="center">
            <input type="button" value="취소" class="btn btn-white js-close" />
            <input type="submit" value="저장" class="btn btn-gray" />
        </div>
    </form>
</div>

<script type="text/javascript">
    <!--
    $(document).ready(function(){
        //취소
        $('.js-close').click(function(){
            layer_close();
        });

        // 폼 체크 후 전송
        $('#receiverForm').validate({
            dialog: false,
            submitHandler: function(form) {
                if ($.trim($('[name="info[receiverName]"]').val()).length < 1) {
                    alert('수령자명을 입력해주세요');
                    $('[name="info[receiverName]"]').focus();
                    return false;
                }
                if ($.trim($('[name="info[receiverCellPhone]"]').val()).length < 1) {
                    alert('휴대폰 번호를 입력해주세요');
                    $('[name="info[receiverCellPhone]"]').focus();
                    return false;
                }
                if ($.trim($('[name="info[receiverZonecode]"]').val()).length < 1) {
                    alert('우편번호를 입력해주세요');
                    $('[name="info[receiverZonecode]"]').focus();
                    return false;
                }
                if ($.trim($('[name="info[receiverAddress]"]').val()).length < 1) {
                    alert('주소를 입력해주세요');
                    $('[name="info[receiverAddress]"]').focus();
                    return false;
                }
                if ($.trim($('[name="info[receiverAddressSub]"]').val()).length < 1) {
                    alert('주소를 입력해주세요');
                    $('[name="info[receiverAddressSub]"]').focus();
                    return false;
                }

                form.submit();
            }
        });
    });
    //-->
</script>
