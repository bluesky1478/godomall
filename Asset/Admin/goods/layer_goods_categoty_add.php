<div>
	<div class="phead_wrap mgt10"><div class="phead">
		<h2>카테고리 연결</h2>
		<div class="scroll_save"><span class="button blue"><input type="button" value="선택" onclick="select_code();" /></span></div>
	</div></div>

	<div>
		<table class="table table-cols">
		<colgroup><col class="width-md" /><col /></colgroup>
		<tr>
			<th>카테고리 선택</th>
			<td class="input_area" id="addCateGoods">
				<?=$cate->getMultiCategoryBox(null, null, 'size="13" class="input_select" style="width:155px;"'); ?>
			</td>
		</tr>
		</table>
	</div>
</div>

<script type="text/javascript">
	<!--
	$(document).ready(function(){
	});

	/**
	 * 카테고리 적용
	 */
	function select_code()
	{
		var selectField		= $('#addCateGoods').find('select');
		var selectCateNm	= '';
		var selectCateCd	= '';
		var strCateNm		= '';
		var strCateCd		= '';
		var strCateCnt		= 0;

		for (var i = 0; i < selectField.length; i++) {
			var selectFieldID	= selectField.eq(i).get(0).id;

			selectCateNm	= $('#'+selectFieldID+' option:selected').text();
			selectCateCd	= $('#'+selectFieldID).val();

			if (selectCateCd != '' && selectCateCd != null) {
				if (i == 0) {
					strCateCd	= selectCateCd;
					strCateNm	= selectCateNm;
					strCateCnt++;
				} else {
					strCateCd	= strCateCd + '|' + selectCateCd;
					strCateNm	= strCateNm + ' &gt; ' + selectCateNm;
					strCateCnt++;
				}
			}
		}

		if (strCateCnt > 0) {
			goods_categoty_add(strCateNm,strCateCd,strCateCnt);
		}
	}

	/**
	 * 카테고리 적용을 위한 카테고리 정보
	 *
	 * @param string cateNm 카테고리 명
	 * @param string cateCd 카테고리 코드
	 * @param integer cateCnt 서브카테고리 갯
	 */
	function goods_categoty_add(cateNm,cateCd,cateCnt) {
		var fieldID		= 'cateGoodsInfo';
		var fieldNoChk	= $('#'+fieldID).find('tr:last').get(0).id.replace(fieldID,'');
		if (fieldNoChk == '') {
			var fieldNoChk	= 0;
		}
		var fieldNo		= parseInt(fieldNoChk) + 1;
		if (cateCnt == 1) {
			var tmp		= new Array();
			tmp[0]		= cateCd;
		} else{
			var tmp		= cateCd.split('|');
		}

		var addHtml		= '';
		addHtml	+= '<tr id="'+fieldID+fieldNo+'">';
		addHtml	+= '<td class="center">';
		for(var i = 0; i < tmp.length; i++) {
			addHtml	+= '<input type="hidden" name="link[cateCd][]" value="'+tmp[i]+'" />';
			if(i == (tmp.length - 1)){
				addHtml	+= '<input type="hidden" name="link[cateLinkFl][]" value="y" />';
				var selectCd = tmp[i];
			}else{
				addHtml	+= '<input type="hidden" name="link[cateLinkFl][]" value="n" />';
			}
		}

		addHtml	+= '<input type="radio" name="cateCd" value="'+selectCd+'" />';
		addHtml	+= '</td>';
		addHtml	+= '<td>'+cateNm+'</td>';
		addHtml	+= '<td class="center">'+selectCd+'</td>';
		addHtml	+= '<td class="center"><span class="button gray small"><input type="button" onclick="field_remove(\''+fieldID+fieldNo+'\');" value="삭제" /></span></td>';
		addHtml	+= '</tr>';
		$('#'+fieldID).append(addHtml);

		$.unblockUI();
	}
	//-->
</script>
