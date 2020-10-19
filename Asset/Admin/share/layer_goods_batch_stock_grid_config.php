<style>
	.goods-grid-area { height: 480px; }
	.goods-grid-left-area,
	.goods-grid-right-area { width: 45%; float: left; }
	.goods-grid-center-area {
		width:9%;
		float: left;
		text-align:center;
		margin-top: 150px;
	}
	.goods-grid-act-top {
		width: 100%;
		text-align: right;
		margin-bottom: 5px;
		float: right;
	}
	.goods-grid-act-top select,
	.goods-grid-act-top .js-moverow { float: right; }
	.goods-grid-act-top span { float: left; line-height: 20px; }
	.goods-grie-bottom-info-area {
		float: left;
		text-align: left;
		width: 100%;
	}
	.goods-grie-bottom-info-area>div{ margin-bottom: 3px !important; }
	.goods-grie-bottom-info-area>div:first-child{ margin-top: 3px; }
	.goods-grie-bottom-area {
		width: 100%;
		float: left;
		text-align: center;
		margin-top: 10px;
	}
	.js-field-select-wapper {
		height:400px;
		overflow:scroll;
		overflow-x:hidden;
		border:1px solid #dddddd;
	}
	.js-field-default{ width: 100%; }
	.js-field-default td { border:1px solid #dddddd; }
	.select-item { color:#999; }
	.table-cols { margin-top:3px; margin-bottom:3px; border: 1px solid #dddddd;}
	.add-display-th { width:140px;white-space:nowrap !important;}
	.add-display-td { min-width:400px;white-space:nowrap !important;}
	.add-display-td input[type="checkbox"]{
		margin : 0 !important;
	}
</style>
<div class="goods-grid-area">
	<form name="goodsGridBatchStockForm" id="goodsGridBatchStockForm" action="./goods_ps.php" method="post" target="ifrmProcess">
		<input type="hidden" name="mode" value="get_goods_batch_stock_admin_grid_list" />
		<input type="hidden" name="goodsBatchStockGridMode" value="<?=$goodsGridBatchStockMode?>" />

		<!-- 좌측 -->
		<div class="goods-grid-left-area">
			<div class="goods-grid-act-top">
				<span>전체 조회항목</span>
				<select class="form-control js-goods-grid-sort" name="gridSort">
					<option value="">기본순서</option>
					<option value="desc">가나다순</option>
					<option value="asc">가나다역순</option>
				</select>
			</div>

			<div class="js-field-select-wapper">
				<table class="js-field-default">
					<tbody class="goods-grid-default-print">
					</tbody>
				</table>
			</div>
		</div>
		<!-- 좌측 -->

		<!-- 중간 -->
		<div class="goods-grid-center-area">
			<p><button type="button" class="btn btn-sm btn-white btn-icon-left js-add">추가</button></p>
			<p><button type="button" class="btn btn-sm btn-white btn-icon-right js-remove">삭제</button></p>
		</div>
		<!-- 중간 -->

		<!-- 우측 -->
		<div class="goods-grid-right-area">
			<div class="btn-group goods-grid-act-top">
				<span>노출 조회항목</span>

				<button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">
					맨아래
				</button>
				<button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down">
					아래
				</button>
				<button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up">
					위
				</button>

				<button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top">
					맨위
				</button>
			</div>
			<div class="js-field-select-wapper">
				<table class="js-field-select table table-rows">
					<tbody class="goods-grid-select-print">
					</tbody>
				</table>
			</div>
		</div>
		<!-- 우측 -->

		<div class="goods-grie-bottom-info-area">
			<div class="notice-info">Shift 버튼을 누른 상태에서 선택하면 여러 항목을 동시에 선택할 수 있습니다.</div>
		</div>

		<div class="goods-grie-bottom-area">
			<input type="button" value="설정" class="btn btn-gray js-save" />
			<input type="button" value="취소" class="btn btn-white js-close" />
		</div>
	</form>
</div>


<script type="text/javascript">
	<!--
	var iciRow = '';
	var preRow = '';

	$(document).ready(function () {
		//document 에 할당된 event 가 레이어 클로즈 후 다시 레이어 실행시 중복 이벤트 등록되어 초기화 해줌.
		$(document).off("click", ".js-field-default tbody tr");
		$(document).off("click", ".js-field-select tbody tr");
		$(document).off("keydown");


		$(".js-save").click(function(e){
			$("input[name='mode']").val('save_goods_batch_stock_admin_grid_list');
			$("#goodsGridBatchStockForm").submit();
		});

		//기본리스트 클릭시
		var lastDefaultRow;
		$(document).on('click', '.js-field-default tbody tr', function (event) {
			$(".js-field-select tbody tr").siblings().each(function () {
				$(this).removeClass('warning').css('background','#fff');
			});
			preRow = iciRow = '';

			if (event.shiftKey) {
				var ia = lastDefaultRow.index();
				var ib = $(this).index();

				var bot = Math.min(ia, ib);
				var top = Math.max(ia, ib);

				for (var i = bot; i <= top; i++) {
					$('.js-field-default tbody tr').eq(i).addClass('default_select');
					$('.js-field-default tbody tr').eq(i).css('background','#fcf8e3');
				}
			}
			else {
				if($(this).hasClass('default_select')) {
					$(this).removeClass('default_select');
					$(this).css('background','#ffffff');
				}
				else {
					$(this).addClass('default_select');
					$(this).css('background','#fcf8e3');
				}
			}

			lastDefaultRow = $(this);
		});

		//선택 리스트 클릭시
		var lastSelectedRow = "";
		$(document).on('click', '.js-field-select tbody tr', function (event) {
			if (iciRow) {
				preRow = iciRow;
			}
			iciRow = $(this);

			if (event.shiftKey) {
				var ia = lastSelectedRow.index();
				var ib = $(this).index();

				var bot = Math.min(ia, ib);
				var top = Math.max(ia, ib);

				for (var i = bot; i <= top; i++) {
					$('.js-field-select tbody tr').eq(i).addClass('warning');
					$('.js-field-select tbody tr').eq(i).css('background','#fcf8e3');
				}
			}
			else {
				if($(this).hasClass('warning')) {
					$(this).removeClass('warning');
					$(this).css('background','#ffffff');
				} else {
					$(this).addClass('warning');
					$(this).css('background','#fcf8e3');
				}
			}

			lastSelectedRow = $(this);

			if($(".js-field-select tr.warning").length == 0 ) {
				preRow = iciRow = '';
			}
		});

		//추가
		$(".js-add").click(function(e){
			if($(".js-field-default tr.default_select").length == 0 ) {
				alert("이동할 항목을 선택해주세요.");
				return false;
			}

			var checkCnt = 0;
			$(".js-field-default tr.default_select").each(function () {
				var key = $(this).data("field-key");
				var check = true;

				$('.js-field-select tbody tr').each(function () {
					if(key == $(this).data("field-key")) {
						checkCnt++;
						check = false;
					}
				});

				if(check == true){
					var newClone = $(this).clone();
					newClone.append("<input type='hidden' name='goodsBatchStockGridList[]' value='"+$(this).data("field-key")+"' />");
					newClone.removeClass('default_select');
					newClone.addClass('move-row');
					newClone.css('background','#ffffff');

					$(".js-field-select tbody").append(newClone);
				}

				$(this).removeClass('default_select');
				$(this).css('background', '#ffffff').addClass("select-item");
			});

			if(checkCnt > 0 ) {
				alert("중복된 항목은 추가 되지 않습니다.");
			}

			return false;
		});

		// 삭제
		$(".js-remove").click(function(e){
			if($(".js-field-select tr.warning").length == 0 ) {
				alert("삭제할 항목을 선택해주세요.");
				return false;
			}

			$(".js-field-select tbody tr.warning").each(function () {
				var key = $(this).data('field-key');
				$(this).remove();
				$(".js-field-default tr[data-field-key='" + key + "']").removeClass("select-item");
				$(".js-field-default tr[data-field-key='" + key + "']").removeClass("select-item");
			});

			$(".js-field-select").css("height","");
			return false;
		});

		// 위/아래이동 버튼 이벤트
		$('.js-moverow').click(function(e){
			if (iciRow) {
				switch ($(this).data('direction')) {
					case 'up':
						iciRow.moveRow(-1);
						break;
					case 'down':
						if(!$(".js-field-select tr").last().hasClass('warning')){
							iciRow.moveRow(1);
						}
						break;
					case 'top':
						iciRow.moveRow(-100);
						break;
					case 'bottom':
						if(!$(".js-field-select tr").last().hasClass('warning')){
							iciRow.moveRow(100);
						}
						break;
				}
			}
			else {
				alert('순서 변경을 원하시는 항목을 선택해주세요. 클릭해주세요.');
			}
		});

		$('.js-close').click(function(){
			$(document).off("keydown");

			layer_close();
		});

		$('div.bootstrap-dialog-close-button').click(function() {
			$(document).off("keydown");
		});

		// 리스트 키보드 이동
		$(document).keydown(function (event) {
			if (iciRow) {
				switch (event.keyCode) {
					case 38:
						iciRow.moveRow(-1);
						break;
					case 40:
						iciRow.moveRow(1);
						break;
				}
				return false;
			}
		});

		// 전체 조회항목 진열 순서 변경
		$(".js-goods-grid-sort").change(function(){
			$("input[name='mode']").val('get_grid_batch_stock_list_sort');
			$.post('goods_ps.php', $("#goodsGridBatchStockForm").serialize(), function (data) {
				var warningFlag = "<img src='/admin/gd_share/img/bl_required.png' style='padding-right: 5px'>";
				if(data){
					var goods_grid_data_all = $.parseJSON(data);
					// 전체리스트
					if(goods_grid_data_all){
						var goods_grid_data_select_values = $(".js-field-select>tbody>tr").map(function () {
							return $(this).attr("data-field-key");
						});

						var contentsHtml = '';
						$.each(goods_grid_data_all, function (key, val) {
							var addClass = '';
							if($.inArray(key, goods_grid_data_select_values) !== -1){
								addClass = "select-item";
							}
							if(key == 'option') val = warningFlag + val;
							contentsHtml+= "<tr class='"+addClass+"' data-field-key='"+key+"'><td style='padding: 10px;'>";
							contentsHtml+= val;
							contentsHtml+= "</td></tr>";
						});
						$(".js-field-default>tbody").html(contentsHtml);
					}
				}
			});
		});

		$.post('goods_ps.php', $("#goodsGridBatchStockForm").serialize(), function (data) {
			var warningFlag = "<img src='/admin/gd_share/img/bl_required.png' style='padding-right: 5px'>";
			if(data){
				var goods_grid_data = $.parseJSON(data);
				var goods_grid_data_all = goods_grid_data.all;
				var goods_grid_data_select = goods_grid_data.select;
				var goods_grid_data_intersect= $.map(goods_grid_data.intersect, function(value) {
					return [value];
				});
				var goods_grid_data_display = goods_grid_data.display;

				// 전체리스트
				if(goods_grid_data_all){
					var contentsHtml = '';
					$.each(goods_grid_data_all, function (key, val) {
						var addClass = '';
						if($.inArray(key, goods_grid_data_intersect) !== -1){
							addClass = "select-item";
						}
						if(key == 'option') val = warningFlag + val;
						contentsHtml+= "<tr class='"+addClass+"' data-field-key='"+key+"'><td style='padding: 10px;'>";
						contentsHtml+= val;
						contentsHtml+= "</td></tr>";
					});
					$(".js-field-default > tbody").html(contentsHtml);
				}
				// 선택리스트
				if(goods_grid_data_select){
					var contentsHtml = '';
					$.each(goods_grid_data_select, function (key, val) {
						if(key == 'option') val = warningFlag + val;
						contentsHtml+= "<tr class='move-row' data-field-key='"+key+"'><td style='padding: 10px;'>";
						contentsHtml+= val;
						contentsHtml+= "<input type='hidden' name='goodsBatchStockGridList[]' value='"+key+"' />";
						contentsHtml+= "</td></tr>";
					});
					$(".js-field-select > tbody").html(contentsHtml);
				}
				// 노출항목 리스트
				if(goods_grid_data_display) {
					$.each(goods_grid_data_display, function (key, val) {
						if(val == 'y') {
							$("input:checkbox[name='listDisplay[" + key + "]']").prop("checked", true);
						}
					});
				}
			}
		});
	});
	//-->
</script>
