<div class="super-admin-memo-title mgb20">
    <div class="pdl30">
        <p>주문번호 : <?=$orderNo?></p>
        <p>주문일시 : <?=$regDt?></p>
    </div>
</div>

<form method="post" name="frmSuperAdminMemo" id="frmSuperAdminMemo" action="../order/order_ps.php" target="ifrmProcess">
    <input type="hidden" name="mode" value="super_admin_memo">
    <input type="hidden" name="orderNo" value="<?=$orderNo?>">
    <textarea name="adminMemo" class="form-control" rows="6"><?=$data['adminMemo']?></textarea>

    <div class="text-center mgt10">
        <button type="button" class="btn btn-lg btn-white js-layer-close">닫기</button>
        <?php if(!$isProvider){ ?>
        <button type="submit" class="btn btn-lg btn-black">저장</button>
        <?php } ?>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function(){
        // 폼 체크 후 전송
        $('#frmSuperAdminMemo').validate({
            dialog: false,
            rules: {
                adminMemo: 'required'
            },
            messages: {
                adminMemo: '관리자메모를 입력해주세요.'
            },
            submitHandler: function(form) {
                // 현재 리스트에 있는 값 업데이트
                $.each($('.js-super-admin-memo').closest('td'), function(key, val) {
                    if ($(val).data('order-no') === <?=$orderNo?>) {
                        $(val).find('.js-super-admin-memo').removeClass('btn-white').addClass('btn-gray').popover({
                            trigger: 'hover',
                            container: '#content',
                            html: true
                        });
                        var popover = $(val).find('.js-super-admin-memo').attr('data-content', $('textarea[name=adminMemo]').val().replace(/\n/g,'<br>'));
                        // content redraw
                        popover.data('bs.popover').setContent();

                        return false; // break
                    }
                });

                form.target = 'ifrmProcess';
                form.submit();
            }
        });
    });
    //-->
</script>
