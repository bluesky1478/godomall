<div class="super-admin-memo-title mgb20" style="height:40px;">
	<div class="">
		<span style="font-weight:bold;margin-right:10px;">상품명</span>
		:<span style="margin-left:10px;"><?=$goodsAdminMemoData['goodsNm'];?></span></p>
	</div>
</div>
<form method="post" name="layerGoodsListMemoFrm" id="layerGoodsListMemoFrm" action="../goods/goods_ps.php" target="ifrmProcess">
	<input type="hidden" name="mode" value="goods_list_memo">
	<input type="hidden" name="goodsNo" value="<?=$goodsNo;?>">

	<div>
		<textarea name="adminMemo" rows="6" class="form-control" style="margin-bottom:15px;"><?=str_replace(['\r\n', '\n'], chr(10), gd_htmlspecialchars_stripslashes($goodsAdminMemoData['memo']));?></textarea>
	</div>
	<div class="text-center">
		<input type="button" value="닫기" class="btn btn-white js-layer-close" />
		<input type="submit" value="저장" class="btn btn-black js-check-save" />
	</div>
</form>

<script type="text/javascript">
	<!--
		$(document).ready(function(){
			// 폼 체크 후 전송
			$('#layerGoodsListMemoFrm').validate({
				dialog: false,
				rules: {
					adminMemo: 'required'
				},
				messages: {
					adminMemo: '관리자메모를 입력해주세요.'
				},
				submitHandler: function(form) {
					// 현재 리스트에 있는 값 업데이트
					$.each($('.js-layer-goods-memo').closest('td'), function(key, val) {
						if ($(val).data('data-goods-memo') === <?=$goodsNo?>) {
							$(val).find('.js-layer-goods-memo').removeClass('btn-white').addClass('btn-gray').popover({
								trigger: 'hover',
								container: '#content',
								html: true
							});
							var popover = $(val).find('.js-layer-goods-memo').attr('data-content', $('textarea[name=adminMemo]').val().replace(/\n/g,'<br>'));
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
