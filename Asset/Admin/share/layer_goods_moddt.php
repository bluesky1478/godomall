<style>
    #layerGoodsModdtFrm .content{border-bottom: 1px solid #dedede; padding-top:10px; padding-bottom: 30px;}
    #layerGoodsModdtFrm .text-center{padding-top: 20px;}
</style>
<form name="layerGoodsModdtFrm" id="layerGoodsModdtFrm">
    <input type="hidden" name="mode" value="goods_moddt">
    <input type="hidden" name="goodsNo" value="">
    <div class="content">
    선택된 상품의 수정일을 오늘로 변경하시겠습니까?
    </div>
    <div class="text-center">
        <button class="btn btn-white js-check-close">아니요</button>
        <button class="btn btn-black js-check-save">예</button>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $('.js-check-save').click(function(){
            var goodsNo = $('input[name*="goodsNo"]:checked').map(function(){
                return this.value;
            }).get().join(',');

            var parameters = {
                mode :'goods_moddt',
                goodsNo : goodsNo
            };

            $.ajax({
                method: "POST",
                data: parameters,
                url: "../goods/goods_ps.php",
                success: function (data) {
                    $(this).closest('.modal-dialog').find('div.bootstrap-dialog-close-button').click();
                    self.location.reload();
                },
                error: function (data) {
                }
            });
        });

        $('button.js-check-close').click(function(){
            $(this).closest('.modal-dialog').find('div.bootstrap-dialog-close-button').click();
            return false;
        });
    });
</script>
