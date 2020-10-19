<script type="text/javascript">
$(document).ready(function(){


	$(".excel-submit").click(function () {

		if($("input[name='excel']").val() =='') {
			alert("엑셀 파일을 선택해주세요.");
			return false;
		} else {
            //상품수정일 변경 확인 팝업
            <?php
            $goodsConfig = (gd_policy('goods.display')); //상품  설정 config 불러오기
            $goodsConfig['goodsModDtTypeExcel'] = gd_isset($goodsConfig['goodsModDtTypeExcel'], 'y');
            $goodsConfig['goodsModDtFl'] = gd_isset($goodsConfig['goodsModDtFl'], 'n');
            if ($goodsConfig['goodsModDtTypeExcel'] == 'y' && $goodsConfig['goodsModDtFl'] == 'y') { ?>
            dialog_confirm("상품수정일을 현재시간으로 변경하시겠습니까?", function (result2) {
                if (result2) {
                    $('input[name="modDtUse"]').val('y');
                } else {
                    $('input[name="modDtUse"]').val('n');
                }
                $("#frmExcelGoods").submit();
            }, '상품수정일 변경', {cancelLabel:'유지', 'confirmLabel':'변경'});
            <?php } else { ?>
                //상품 수정일 변경 범위설정 체크
                <?php if ($goodsConfig['goodsModDtTypeExcel'] == 'y') { ?>
                    $('input[name="modDtUse"]').val('y');
                <?php } else { ?>
                    $('input[name="modDtUse"]').val('n');
                <?php } ?>
                $("#frmExcelGoods").submit();
            <?php } ?>
		}

	});

	$("input[name='scmFl']").click(function () {
		if ($(this).val() == 'n') {
			$("#scmLayer").html('');
		}
	});
});

/**
 * 카테고리 연결하기 Ajax layer
 */
function layer_register(typeStr, mode, isDisabled) {

	var addParam = {
		"mode": mode,
	};

	if (typeStr == 'scm') {
		$('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
	}

	if (!_.isUndefined(isDisabled) && isDisabled == true) {
		addParam.disabled = 'disabled';
	}
	layer_add_info(typeStr, addParam);

}
</script>
<style>
.information span { padding:0px;display:inline; }
.information .content ul li a.btn-link { color : #117ef9; }
.information .content ul li a.btn-link:hover { color: #004ab9;text-decoration: underline;background-color: transparent; }
</style>


<form id="frmExcelGoods" name="frmExcelGoods" action="excel_goods_ps.php" method="post" enctype="multipart/form-data">
	<div class="page-header js-affix">
		<h3>상품 엑셀 업로드</h3>
	</div>

	<div class="table-title gd-help-manual">
		<?=end($naviMenu->location); ?>
	</div>



	<input type="hidden" name="mode" value="excel_up" />
<input type="hidden" name="preFix" value="Goods_Result" />
    <input type="hidden" name="modDtUse" value="" />
<div>
<table class="table table-cols">
<colgroup><col class="width-sm" /><col/></colgroup>
	<tr>
		<th>엑셀파일 업로드</th>
		<td> <div class="form-inline">
				<input type="file" name="excel" value="" class="form-control" />
				<input type="button"  class="btn btn-white btn-sm excel-submit" value="엑셀업로드"></div>
		</td>
	</tr>
	<tr>
		<?php if (gd_use_provider() === true) { ?>
			<?php if (gd_is_provider() === false) { ?>
				<th>공급사 구분</th>
				<td>
					<label class="radio-inline"><input type="radio" name="scmFl"
													   value="n" checked />본사
					</label>
					<label class="radio-inline">
						<input type="radio" name="scmFl" value="y"
							   onclick="layer_register('scm','radio',true)"/>공급사
					</label>
					<label>
						<button type="button" class="btn btn-sm btn-gray" onclick="layer_register('scm','radio',true)">공급사 선택</button>
					</label>
					<div id="scmLayer" class="selected-btn-group <?= $data['scmNo'] ? 'active' : '' ?>">
					</div>
				</td>
			<?php } else { ?>
				<div class="sr-only">
					<input type="text" name="scmNo" value="<?= $data['scmNo'] ?>"/>
					<input type="radio" name="scmFl" value="y" checked="checked"/>
				</div>
			<?php } ?>
		<?php } else { ?>
			<div class="sr-only">
				<input type="hidden" name="scmNo" value="<?= DEFAULT_CODE_SCMNO ?>"/>
				<input type="radio" name="scmFl" value="n" checked="checked"/>
			</div>
		<?php } ?>
	</tr>
</table>
</div>
</form>


<div class="information">
	<div class="helper_left"><div class="helper_right"><div class="helper_bottom"><div class="helper_right_top"><div class="helper_right_bottom">

						<div class="content">
							<ul class="pdl0">
								<li>
									<h3>상품 엑셀 샘플 다운로드</h3>
									1. 아래 &quot;상품 엑셀 샘플 다운로드&quot; 버튼을 눌러 샘플을 참고하시기 바랍니다.<br />
									2. 엑셀 파일 저장은 반드시 &quot;Excel 97-2003 통합문서&quot;로 저장을 하셔야 합니다. 그외 csv 나 xlsx파일 등은 지원이 되지 않습니다.<br />
									<form id="frmExcelGoods" name="frmExcelGoods" action="excel_goods_ps.php" method="post">
										<input type="hidden" name="mode" value="excel_sample_down" />
										<input type="hidden" name="preFix" value="Goods_Sample" />
										<input type="submit" value="상품 엑셀 샘플 다운로드"  class="btn btn-white btn-icon-excel mgt10"/>
									</form>
								</li>
								<li>
									<h3>상품 업로드 방법</h3>
									1. 아래 항목 설명되어 있는 것을 기준으로 엑셀 파일을 작성을 합니다.<br />
									2. 상품 다운로드에서 받은 엑셀이나 &quot;상품 엑셀 샘플 다운로드&quot;에서 받은 엑셀을 기준으로 파일을 작성을 합니다.<br />
									3. 엑셀 파일 저장은 반드시 &quot;Excel 97-2003 통합문서&quot;로 저장을 하셔야 합니다. 절대 csv 파일이 아닌 진짜 엑셀 파일입니다.(xls)<br />
									4. 작성된 엑셀 파일을 업로드 합니다.<br />
								</li>
								<li>
									<h3>주의사항</h3>
									1.엑셀 파일 저장은 반드시 &quot;Excel 97-2003 통합문서&quot;만 가능하며, csv 파일은 업로드가 되지 않습니다.<br />
									2.엑셀 2003 사용자의 경우는 그냥 저장을 하시면 되고, 엑셀 2007 이나 엑셀 2010 인 경우는 새이름으로 저장을 선택해서 &quot;Excel 97-2003 통합문서&quot;로 저장을 하십시요.<br />
									3.엑셀의 내용이 너무 많은 경우 업로드가 불가능 할수 있으므로 100개나 200개 단위로 나누어 올리시기 바랍니다.<br />
									4.엑셀 파일이 작성이 완료 되었다면 하나의 상품만 테스트로 업로드하여 확인후 이상이 없으시면 나머지를 올리시기 바랍니다.<br />
									5.엑셀 내용 중 &quot;1번째 줄은 설명&quot;, &quot;2번째 줄은 excel DB 코드&quot;, &quot;3번째 줄은 설명&quot; 이며, &quot;4번째 줄부터&quot; 데이터 입니다.<br />
									6.엑셀 내용 중 2번째 줄 &quot;excel DB&quot; 코드부터 3번째 줄 &quot;설명&quot;은 필수 데이터 입니다. 그리고 <span class="text-danger">반드시 내용은 &quot;4번째 줄부터&quot; 작성</span> 하셔야 합니다.<br />
									7.<span class="text-danger">신규상품 등록 시 필수 데이터인 &quot;상품코드, 상품명&quot; 정보 외 등록을 원하지 않는 항목의 필드는 삭제 또는 공란으로 남겨두고 올리시기 바랍니다.<br />
									&nbsp;(삭제 또는 공란으로 남겨두고 업로드할 경우, 해당 항목의 정보는 기본값으로 등록됩니다.)</span><br />
									8.상품 수정은 [상품관리>상품 엑셀 관리>상품 다운로드]에서 다운로드하여 상품정보를 수정하여 올리시기 바랍니다.<br />
									9.<span class="text-danger">상품의 일부 내용만 수정할 경우, 수정을 원하지 않는 항목의 필드를 삭제하고 올리시기 바랍니다.<br />
									&nbsp;(필드를 삭제하지 않고 공란으로 남겨둘 경우, 입력항목의 정보는 공란으로 등록되며 선택항목은 시스템 기본값으로 등록됩니다.)</span><br />
                                    10.<span class="text-danger"><b>엑셀로 등록한 상품의 이미지는 "디자인>이미지 브라우저>이미지 브라우저 (Web FTP)" 메뉴의 "/data/goods/tmp/"폴더에 업로드 후 일괄처리해야 합니다.</b></span><br />
                                    &nbsp;(상품 이미지 일괄처리는 <a href="./goods_image_batch.php" target="_blank" class="btn-link">상품>상품 일괄 관리>상품 이미지 일괄 처리</a>에서 등록할 수 있습니다.)
								</li>
								<li>
									<h3>항목 설명</h3>
									1. 아래 설명된 내용을 확인후 작성을 해주세요.<br />
								</li>
							</ul>
							<table class="input">
								<colgroup><col class="width-sm" /><col/></colgroup>
								<tr>
									<th>항목 설명</th>
									<td>
										<table class="content_table">
											<colgroup>
												<col class="width-sm" /><col class="width-xs"/><col />
											</colgroup>
											<thead>
											<tr>
												<th>한글필드명</th>
												<th>영문필드명</th>
												<th>설명</th>
											</tr>
											</thead>
											<?php
											foreach ($excelField as $key => $val) {
												?>
												<tr>
													<th class="desc01"><?=$val['text'];?></td>
													<td class="desc02"><?=$val['excelKey'];?></td>
													<td><?=$val['desc'];?></td>
												</tr>
												<?php
											}
											?>
										</table>
									</td>
								</tr>
							</table>
						</div>
					</div></div></div></div></div>
</div>


