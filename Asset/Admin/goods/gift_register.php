<script type="text/javascript">
<!--
$(document).ready(function(){
	$("#frmGift").validate({
		submitHandler: function (form) {
			oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
			form.target='ifrmProcess';
			form.submit();
		},
		// onclick: false, // <-- add this option
		rules: {
			giftNm: 'required'
		},
		messages: {
			giftNm: {
				required: '사은품명을 입력하세요.'
			}
		}
	});

	<?php if(($data['mode'] =='register' &&  Request::get()->get('scmFl')) ||  $data['mode'] =='modify') { ?>
	$('input:radio[name=scmFl]').prop("disabled", true);
	$('button.scmBtn').attr("disabled", true);
	<?php }?>

	$('#stockCnt').number_only(5,32767,32767);
	image_storage_selector('<?=$data['imageStorage'];?>');
});

/**
 * 이미지 저장소에 따른 상품 이미지 종류
 *
 * @param string modeType 이미지 저장소 종류
 */
function image_storage_selector(modeType) {
	$('span[id*=\'imageStorageMode_\']').removeClass();
	$('span[id*=\'imageStorageMode_\']').addClass('display-none');
	if (modeType == 'url') {
		$('#giftImageImg').attr('class','display-none');
		$('#giftImageUrl').attr('class','display-block');
		$('#imageStorageMode_url').removeClass();
		$('#imageStorageMode_url').addClass('display-block');
	} else if (modeType == 'local') {
		$('#giftImageImg').attr('class','display-block');
		$('#giftImageUrl').attr('class','display-none');
		$('#imageStorageMode_local').removeClass();
		$('#imageStorageMode_local').addClass('display-block');
	} else if (modeType == '') {
		$('#giftImageImg').attr('class','display-block');
		$('#giftImageUrl').attr('class','display-none');
		$('#imageStorageMode_local').removeClass();
		$('#imageStorageMode_local').addClass('display-block');
		$('#imageStorageMode_none').removeClass();
		$('#imageStorageMode_none').addClass('display-block');
	} else {
		$('#giftImageImg').attr('class','display-block');
		$('#giftImageUrl').attr('class','display-none');
		$('#imageStorageMode_etc').removeClass();
		$('#imageStorageMode_etc').addClass('display-block');
		$('#imageStorageModeNm').html(modeType);
	}
	if (modeType != 'url') {
		$('#imagePreView').removeClass();
		if (modeType == '<?=$data['imageStorage'];?>') {
			$('#imagePreView').addClass('display-block');
		} else {
			$('#imagePreView').addClass('display-none');
		}
	}
}

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

	layer_add_info(typeStr,addParam);
}
//-->
</script>

<form id="frmGift" name="frmGift" action="gift_ps.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="mode" value="<?=$data['mode'];?>" />
<input type="hidden" name="giftNo" value="<?=$data['giftNo'];?>" />
<input type="hidden" name="imageNmTemp" value="<?=$data['imageNm'];?>" />

	<div class="page-header js-affix">
		<h3><?=end($naviMenu->location); ?></h3>
		<div class="btn-group">
			<input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./gift_list.php');" />
			<input type="submit" value="저장" class="btn btn-red" />
		</div>
	</div>

	<div class="table-title ">
		기본정보
	</div>
	<div>
		<table class="table table-cols">
		<colgroup><col class="width-md" /><col class="width-2xl"/><col class="width-md" /><col /></colgroup>
            <?php if(gd_use_provider()) { ?>
			<?php if(gd_is_provider()) { ?>
				<input type="hidden" name="scmNo" value="<?=$data['scmNo']?>">
			<?php }  else { ?>
				<tr>
				<th class="r_space ">공급사 구분</th>
				<td colspan="3">
					<label class="radio-inline"><input type="radio" name="scmFl"
								  value="n" <?=gd_isset($checked['scmFl']['n']); ?>    onclick="$('#scmLayer').html('')";/>본사</label>
					<label class="radio-inline"><input type="radio" name="scmFl" value="y" <?=gd_isset($checked['scmFl']['y']); ?>
								  onclick="layer_register('scm','radio',true)"/>공급사</label>
					<label> <button type="button" class="btn btn-sm btn-gray scmBtn" onclick="layer_register('scm','radio',true)">공급사 선택</button></label>
					<div id="scmLayer" class="selected-btn-group <?=$data['scmNoNm'] && $data['scmNo'] != DEFAULT_CODE_SCMNO ? 'active' : ''?>">
						<?php if ($data['scmNo']) { ?>
							<h5>선택된 공급사 : </h5>
							<span id="info_scm_<?= $data['scmNo'] ?>" class="btn-group btn-group-xs">
							<input type="hidden" name="scmNo" value="<?= $data['scmNo'] ?>"/>
								<?php if($data['scmNo'] != DEFAULT_CODE_SCMNO) { ?>
								<span class="btn"> <?= $data['scmNoNm'] ?></span>
  <?php if($data['mode'] =='register' ) { ?>
										<button type="button" class="btn btn-danger" data-toggle="delete" data-target="#info_scm_<?= $data['scmNo'] ?>">삭제</button> <?php } ?>
								<?php }?>
					        </span>
						<?php } ?>
					</div>

				</td>
			</tr>
			<?php } ?>
            <?php } ?>
		<tr>
			<th  nowrap="nowrap">사은품코드</th>
			<td><?php if ($data['mode'] == "register") { echo "사은품 등록 저장 시 자동 생성됩니다."; } else { echo $data['giftNo']; }?></td>
			<th nowrap="nowrap">자체 사은품코드</th>
			<td><input type="text" name="giftCd" value="<?=$data['giftCd'];?>" class="form-control width-md js-maxlength" maxlength="30" /></td>
		</tr>
		<tr>
			<th class="require">사은품명</th>
			<td class="input_area" colspan="3"><input type="text" name="giftNm" value="<?=$data['giftNm'];?>" class="form-control width90p  js-maxlength" maxlength="250" /></td>
		</tr>
		<tr>
			<th>브랜드</th>
			<td><div class="form-inline">
				<label><input type="text" name="brandCdNm" value="<?=$data['brandCdNm']; ?>"
							  class="form-control width-sm"  readonly onclick="layer_register('brand', 'radio')"/> </label>
					<label><input type="button" value="브랜드 검색" onclick="layer_register('brand', 'radio')" class="btn btn-gray btn-sm"/></label>
					<?php if (gd_is_provider() === false) { ?><a href="./category_tree.php?cateType=brand" target="_blank" class="btn btn-gray btn-sm" >브랜드 추가</a><?php } ?>
                    <label id="brandCdDel" style="display:<?= $data['brandCdNm'] ? '':'none'; ?>"><input type="checkbox" name="brandCdDel" value="y"> <span class="text-red">체크시 삭제</span></label>
				<div id="brandLayer" class="width100p">
					<?php if ($data['brandCd']) { ?>
						<span id="idbrand<?= $data['brandCd'] ?>" class="pull-left">
                        <input type="hidden" name="brandCd" value="<?= $data['brandCd'] ?>"/>
                        </span>
					<?php } ?>
				</div></div>
			</td>
			<th >제조사</th>
			<td><input type="text" name="makerNm" value="<?=$data['makerNm'];?>" class="form-control width-md  js-maxlength"  maxlength="30"  /></td>
		</tr>
		<tr>
			<th >재고상태</th>
			<td class="input_area" colspan="3">
				<div class="form-inline">
					<label class="radio-inline"><input type="radio" name="stockFl" value="n" <?=gd_isset($checked['stockFl']['n']);?> /> 제한없음</label>
					<label class="radio-inline"><input type="radio" name="stockFl" value="y" <?=gd_isset($checked['stockFl']['y']);?> /> <input type="text" id="stockCnt" name="stockCnt" class="form-control width-xs" value="<?=$data['stockCnt'];?>" class="input_int" />개</label>
				</div>
			</td>
		</tr>
		<tr>
			<th >사은품 설명</th>
			<td class="input_area" colspan="3">
				<textarea name="giftDescription" rows="3" style="width:100%; height:300px;" id="editor" class="form-control width90p"><?=$data['giftDescription'];?></textarea>
			</td>
		</tr>
		</table>
		<div class="desc_box">
			<div class="notice-info">사은품 상품의 재고 차감은 <?php if (gd_is_provider() === false) { ?><a href="../policy/order_status.php" target="_blank" >주문 상태 설정</a><?php } else { ?>주문 상태 설정<?php } ?>에 설정된 재고 차감시에 차감이 됩니다. 그리고 사은품 상품은 재고가 복구되지 않습니다.(차감만 됨).</div>
		</div>
	</div>

	<div class="table-title ">
		이미지 설정
	</div>
	<div>
		<table class="table table-cols">
		<colgroup><col class="width-md" /><col /></colgroup>
		<tr>
			<th>저장소 선택</th>
			<td>
				<div class="pull-left">
					<?=gd_select_box('imageStorage','imageStorage',$conf['storage'],null,$data['imageStorage'],'=저장소 선택=','onchange="image_storage_selector(this.value);"');?>
				</div>
				<div class="pull-left" style="padding:5px 0px 0px 5px">
					<span id="imageStorageMode_none" class="display-none"> 저장소 선택을 하지 않으면 &quot;기본경로&quot; 설정을 사용을 합니다.</span>
				</div>
			</td>
		</tr>
		<tr>
			<th>저장 경로</th>
			<td>
<?php
    if (empty($data['imagePath'])) {
        echo '<span id="imageStorageMode_url" class="display-none">&quot;URL 직접입력&quot;은 따로 저장 경로가 없이 아래 작성한 URL로 대체 됩니다.</span>';
        echo '<span id="imageStorageMode_local" class="display-none">'.UserFilePath::data('gift')->www().'/코드/사은품코드/</span>';
        echo '<span id="imageStorageMode_etc" class="display-none"><span id="imageStorageModeNm">'.$data['imageStorage'].'</span>'.DIR_GIFT_IMAGE_FTP.'코드/사은품코드/</span>';
    } else {
        echo '<span id="imageStorageMode_url" class="display-none">&quot;URL 직접입력&quot;은 따로 저장 경로가 없이 아래 작성한 URL로 대체 됩니다.</span>';
        echo '<span id="imageStorageMode_local" class="display-none">'.UserFilePath::data('gift', $data['imagePath'])->www().'</span>';
        echo '<span id="imageStorageMode_etc" class="display-none"><span id="imageStorageModeNm">'.$data['imageStorage'].'</span>'.DIR_GIFT_IMAGE_FTP.$data['imagePath'].'</span>';
    }
?>
				<input type="hidden" name="imagePath" value="<?=$data['imagePath'];?>" class="form-control" />
			</td>
		</tr>
		<tr>
			<th>사은품 이미지</th>
			<td>
<?php
    if (empty($data['imageNm'])) {
        $preViewImg    = '';
    } else {
        $preViewImg    = gd_html_gift_image($data['imageNm'], $data['imagePath'], $data['imageStorage'], 25, $data['giftNm']) . gd_htmlspecialchars_slashes($data['imageNm'],'add');
    }
?>

				<div id="giftImageImg" class="display-none">
					<div class="pull-left width60p"><input type="file" name="imageNm" value="" class="form-control" /></div> <span id="imagePreView">&nbsp;<?=$preViewImg;?></span>
				</div>
				<div id="giftImageUrl" class="display-none">
					<input type="input" name="imageNm" value="<?=$data['imageNm'];?>" class="form-control width60p" /> <?=$preViewImg;?>
				</div>
				<div class="notice-info" style="clear:both;">이미지 사이즈는 가로 <?=GIFT_IMAGE_SIZE;?> 픽셀(pixel) 입니다.</div>
			</td>
		</tr>
		</table>
	</div>

</form>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js" charset="utf-8"></script>
