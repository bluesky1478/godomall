<form id="frmGoodsImage" name="frmGoodsImage" action="./goods_ps.php" method="post" target="ifrmProcess">
<input type="hidden" name="mode" value="goods_image" />

	<div class="page-header js-affix">
		<h3><?=end($naviMenu->location);?> </h3>
		<div class="btn-group">
			<input type="submit" value="저장" class="btn btn-red">
		</div>
	</div>

	<div class="table-title gd-help-manual">
		이미지사이즈 설정 방법
	</div>
	<table class="table table-cols" style="margin-bottom:0" >
		<colgroup><col class="width-md" /><col/></colgroup>
		<tr>
			<th>설정 방법 선택</th>
			<td>
				<label for="imageTypeAuto" class="radio-inline"><input type="radio" name="imageType" id="imageTypeAuto" value="auto" <?=$imageType['auto']?> /> 가로 사이즈 기준 비율 조정
				<span class="notice-info-blue">(세로사이즈가 가로사이즈에 따라 자동 비율 조정됩니다.)</span></label><br />
				<label for="imageTypeFixed" class="radio-inline"><input type="radio" name="imageType" id="imageTypeFixed" value="fixed" <?=$imageType['fixed']?> /> 가로 세로 사이즈 고정
				<span class="notice-info-blue">(가로 세로 사이즈를 직접 등록합니다.)</span></label>
			</td>
		</tr>
	</table>
	<div class="notice-danger">
		상품 이미지 사이즈 설정은 PC쇼핑몰에만 적용되며, 모바일쇼핑몰은 브라우저 크기에 따라 자동 비율 조정되어 출력 됩니다.
		<div class="gray">
			가로 사이즈 기준 비율 조정 : 등록된 이미지의 가로 세로 비율이 일정하지 않은 경우 세로사이즈가 일정하지 않을 수 있습니다.<br />
			가로 세로 사이즈 고정 : 설정된 사이즈보다 크거나 작은 이미지를 등록하면 설정된 사이즈대로 확대/축소(리사이징) 됩니다.
		</div>
	</div>

	<div class="table-title gd-help-manual">
		기본 이미지 사이즈 설정
	</div>
	<table class="table table-cols" id="imageSizeDefault" >
		<colgroup><col class="width-md" /><col/></colgroup>
<?php
    foreach ($data['title_base'] as $tKey => $tVal) {
        $imageID    = $tVal['id'].$tVal['addNo'];
        $fieldID    = $data['fieldID'].$imageID;

        if (isset($data['config'][$imageID]) === true) {
?>
		<tr <?php if ($tVal['id'] == 'add') {?>id="imageSize<?=$tVal['addNo'];?>"<?php }?>>
		<th>
			<?php if ($tVal['id'] != 'add') {?>
				<?=$tVal['name'];?>
				<input type="hidden" name="image[<?=$imageID;?>][text]" value="<?=$tVal['name'];?>" />
			<?php } else {?>
				<input type="text" name="image[<?=$imageID;?>][text]" value="<?=$data['config'][$imageID]['text'];?>" class="form-control width70p" />
				<span class="button gray small"><input type="button" onclick="info_remove('imageSize<?=$tVal['addNo'];?>','y');" value="-" /></span>
			<?php }?>
			<input type="hidden" name="image[<?=$imageID;?>][addKey]" value="<?=$tVal['addKey'];?>" />
		</th>
		<td class="form-inline" id="<?=$fieldID;?>">
<?php
            $i = 0;
            foreach ($data['config'][$imageID] as $cKey => $cVal) {
                if ($cKey == 'text' || $cKey == 'addKey' || substr($cKey, 0, 5) == 'hsize') {
                    continue;
                }
                $i++;
?>
			<div class="form-inline mgb5">
			<div id="<?=$fieldID.$i;?>">
				가로 <input type="text" name="image[<?=$imageID;?>][size<?=$i;?>]" value="<?=$data['config'][$imageID]['size'.$i];?>" class="form-control width-2xs" /> 픽셀(pixel) /
				세로 <span class="fixed-div"><input type="text" name="image[<?=$imageID;?>][hsize<?=$i;?>]" value="<?=empty($data['config'][$imageID]['hsize'.$i]) ? $data['config'][$imageID]['size'.$i] : $data['config'][$imageID]['hsize'.$i] ;?>" class="form-control width-2xs" /> 픽셀(pixel)</span>
				<span class="auto-div">: 가로사이즈에 따라 자동 비율 조정</span>
				<span class="">
				<?php if ($tVal['addKey'] == 'y') {?>
					<?php if ($i == 1) {?>
						<button type="button" class="btn btn-white btn-icon-plus btn-sm" onclick="add_pixel('<?=$imageID;?>');">추가</button>
					<?php } else {?>
						<button type="button" class="btn btn-white btn-icon-minus btn-sm"  onclick="info_remove('<?=$fieldID.$i;?>','y');">삭제</button>
					<?php }?>
				<?php }?>
				</span>
			</div></div>
<?php
            }
?>
		</td>
		</tr>
<?php
        }
    }
?>
	</table>

	<div class="table-title gd-help-manual">
		리스트 이미지 사이즈 설정 / 추가
	</div>
	<table class="table table-cols" id="imageSize">
		<colgroup>
			<col class="width-md" />
			<col/>
		</colgroup>
		<tr >
			<th>
				리스트 이미지(기본)				<input type="hidden" name="image[main][text]" value="리스트이미지(기본)" />
				<input type="hidden" name="image[main][addKey]" value="n" />
			</th>
			<td class="input_area" id="imageAdd_main">
				<div class="form-inline">
					<div id="imageAdd_main1">
						가로 <input type="text" name="image[main][size1]" value="<?=$data['config']['main']['size1'];?>" class="form-control width-2xs" /> 픽셀(pixel) / 세로
						<span class="auto-div">:  가로사이즈에 따라 자동 비율 조정</span>
						<span class="fixed-div"><input type="text" name="image[main][hsize1]" value="<?=empty($data['config']['main']['hsize1']) ? $data['config']['main']['size1'] : $data['config']['main']['hsize1'];?>" class="form-control width-2xs" /> 픽셀(pixel)</span>
						<button type="button" class="btn btn-black btn-xs"  onclick="add_image('add');" >리스트 이미지 추가</button>
					</div>
					<div class="notice-danger" style="margin:0;">리스트 이미지는 최대 5개까지 추가할 수 있습니다.</div>
				</div>
			</td>
		</tr>
		<?php
		foreach ($data['config'] as $tKey => $tVal) {

				if (substr($tKey, 0, 3)  == 'add') {

					$addNo = str_replace('add','', $tKey);
					$fieldID =  $data['fieldID'].$tKey;
					?>
				<tr id="imageSize<?=$addNo;?>">
					<th><div class="form-inline">

						<input type="text" name="image[<?=$tKey;?>][text]" value="<?=$tVal['text'];?>" class="form-control width80p" />

						<input type="hidden" name="image[<?=$tKey;?>][addKey]" value="<?=$tVal['addKey'];?>" /></div>
					</th>
					<td class="form-inline" id="<?=$fieldID;?>">
						<?php
						$i = 0;
						foreach ($tVal as $cKey => $cVal) {
							if ($cKey == 'text' || $cKey == 'addKey' || substr($cKey, 0, 5) == 'hsize') {
								continue;
							}
							$i++;
							?>
							<div class="form-inline">
								<div id="<?=$fieldID.$i;?>">
									<?php if ($tVal['addKey'] == 'y') {?>
										<?php if ($i == 1) {?>
											<button type="button" class="btn btn-gray btn-sm" onclick="add_pixel('<?=$tKey;?>');">추가</button>
										<?php } else {?>
											<button type="button" class="btn btn-gray btn-sm"  onclick="info_remove('<?=$fieldID.$i;?>','y');">삭제</button>
										<?php }?>
									<?php }?>
									가로 <input type="text" name="image[<?=$tKey;?>][size<?=$i;?>]" value="<?=$tVal['size'.$i];?>" class="form-control width-2xs" /> 픽셀(pixel) / 세로
									<span class="auto-div">:  가로사이즈에 따라 자동 비율 조정</span>
									<span class="fixed-div"><input type="text" name="image[<?=$tKey;?>][hsize<?=$i;?>]" value="<?=empty($tVal['hsize'.$i]) ? $tVal['size'.$i] : $tVal['hsize'.$i];?>" class="form-control width-2xs" /> 픽셀(pixel)</span>
									<button type="button"  class="btn btn-black btn-xs" onclick="info_remove('imageSize<?=$addNo;?>','y');" >- 삭제</button>
								</div>
							</div>
							<?php
						}
						?>
					</td>
				</tr>
				<?php
			}
		}
		?>
	</table>
</form>
<style rel="stylesheet" type="text/css">
	.notice-info-blue { color:#117EF9; font-size:11px; }
	.gray { color:#999999; }
</style>
<script type="text/javascript">
	<!--
	$(document).ready(function(){

		$("#frmGoodsImage").validate({
			submitHandler: function (form) {

				var submitFl = true;
				$('#imageSizeDefault input[type="text"]').each(function (i) {
					if($(this).val() =='') {
						alert("기본 이미지 사이즈 설정을 확인해주세요.");
						submitFl = false;
						return false;
					}
				});

				$('#imageSize input[type="text"]').each(function (i) {
					if($(this).val() =='') {
						alert("리스트 이미지 사이즈 설정을 확인해주세요.");
						submitFl = false;
						return false;
					}
				});

				if(submitFl) {
					form.target='ifrmProcess';
					form.submit();
				} else {
					return false;
				}
			}
		});


		$('input[name*=\'size\']').number_only(4);
		$('input[name*=\'text\']').focus( function() { if(this.value == '<?=$data['idTitle'];?>') this.value = ''; } );
		change_image_type($("input:radio[name=imageType]:checked").val());

		$("input:radio[name=imageType]").click(function() {
			change_image_type($(this).val());
		});

	});

	/**
	 * 픽셀 정보 추가
	 *
	 * @param string divID 해당 div ID
	 */
	function add_pixel(divID) {
		var fieldID		= '<?=$data['fieldID'];?>'+divID;
		var fieldNo		= parseInt($('#'+fieldID).find('div:not(.form-inline):last').attr('id').replace(fieldID,'')) + 1;
		var addHtml		= '';
		addHtml	+= '<div class="form-inline mgt5"><div id="'+fieldID+fieldNo+'">가로 <input type="text" name="image['+divID+'][size'+fieldNo+']" class="form-control width-2xs" value="0" /> 픽셀(pixel) / 세로 ';
		addHtml += '<span class="auto-div">: 가로사이즈에 따라 자동 비율 조정 </span>';
		addHtml += '<span class="fixed-div"><input type="text" name="image['+divID+'][hsize'+fieldNo+']" class="form-control width-2xs" value="0"   /> 픽셀(pixel) </span>';
		addHtml += '<button type="button" class="btn btn-white btn-icon-minus btn-sm"  onclick="info_remove(\''+fieldID+fieldNo+'\',\'\');"  >삭제</button></div></div>';
		$('#'+fieldID).append(addHtml);
		change_image_type($("input:radio[name=imageType]:checked").val());
		$('input[name*=\'size\']').number_only(4);

	}


	/**
	 * 이미지 정보 추가
	 *
	 * @param string divAddID 해당 div ID
	 */
	function add_image(divAddID) {
		var fieldID		= 'imageSize';
		var fieldNoID	= $('#'+fieldID).find('tr:last').attr('id');
		if (typeof fieldNoID == 'undefined') {
			var fieldNoChk	= 0;
		} else {
			var fieldNoChk	= fieldNoID.replace(fieldID,'');
		}
		if (fieldNoChk == '') {
			var fieldNoChk	= 0;
		}
		var fieldNo		= parseInt(fieldNoChk) + 1;

		if ($('#'+fieldID).find('tr').length  > 4) {
			alert('추가용 이미지 설정은 5개 까지만 추가하실 수 있습니다.');
			return false;
		}
		var addID		= '<?=$data['fieldID'];?>'+divAddID+fieldNo;
		var addHtml		= '';
		addHtml	+= '<tr id="'+fieldID+fieldNo+'">';
		addHtml	+= '<th><div class="form-inline"><input type="text" name="image['+divAddID+fieldNo+'][text]" value="<?=$data['idTitle'];?>" class="form-control width80p" /></div></th>';
		addHtml	+= '<td>';
		addHtml	+= '<div class="form-inline"><input type="hidden" name="image['+divAddID+fieldNo+'][addKey]" value="n" />';
		addHtml	+= '<div id="'+addID+'">가로 <input type="text" name="image['+divAddID+fieldNo+'][size1]" class="form-control width-2xs" value="0" />  픽셀(pixel) / 세로 ';

		addHtml += '<span class="auto-div">: 가로사이즈에 따라 자동 비율 조정 </span>';
		addHtml += '<span class="fixed-div"><input type="text" name="image['+divAddID+fieldNo+'][hsize1]" class="form-control width-2xs" value="0" /> 픽셀(pixel) </span>';

		addHtml += '<button type="button"  class="btn btn-black btn-xs" onclick="info_remove(\''+fieldID+fieldNo+'\',\'\');">- 삭제</button></div>';
		addHtml	+= '</div></td>';
		addHtml	+= '</tr>';
		$('#'+fieldID).append(addHtml);
		change_image_type($("input:radio[name=imageType]:checked").val());
		var inputName	= $('input[name*=\'text\']:last').attr('name');
		$('input[name=\''+inputName+'\']').focus( function() { if(this.value == '<?=$data['idTitle'];?>') this.value = ''; } );

		$('input[name="image['+divAddID+fieldNo+'][size1]"]').number_only();
		$('input[name="image['+divAddID+fieldNo+'][hsize1"]]').number_only();

	}

	/**
	 * 정보 삭제
	 *
	 * @param string thisID 해당 ID
	 * @param boolean setYN 처리 여부
	 */
	function info_remove(thisID,setYN) {
		if (setYN == 'y'){
			if (confirm('설정되어 있는 값을 삭제하는 경우 아래 내용을 유의 하십시요.\n\n1.기존 등록된 상품의 이미지가 나오지 않습니다.\n2.해당 이미지가 아닌 메인용 이미지로 대체 됩니다.\n\n※ 삭제 하시겠습니까?') == true){
				$('#'+thisID).remove();
			}
		} else {
			$('#'+thisID).remove();
		}
	}

	function change_image_type(type) {
		if(type == 'auto') {
			$(".fixed-div").hide();
			$(".auto-div").show();
		} else if(type == 'fixed') {
			$(".fixed-div").show();
			$(".auto-div").hide();
		}
	}
	//-->
</script>
