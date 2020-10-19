<div class="page-header js-affix">
	<h3><?php echo end($naviMenu->location);?></h3>
	<div class="btn-group">
		<?php if ($isUsableMall === true && gd_is_provider() === false) { ?>
			<a href="./goods_detail_info_global_register.php"><input type="button" value="이용안내 등록 (해외몰 적용)" class="btn btn-red-line mgr5" /></a>
		<?php } ?>
		<a href="./goods_detail_info_register.php"><input type="button" value="이용안내 등록" class="btn btn-red-line" /></a>
	</div>
</div>

<?php
    $arrInfo    = array('002'=>'배송안내','003'=>'AS안내','004'=>'환불안내','005'=>'교환안내');
?>
<form id="frmSearchGoods" name="frmSearchGoods" method="get" class="js-form-enter-submit">
<input type="hidden" name="sort" value="<?=$search['sort']?>">

	<div class="table-title gd-help-manual">
		이용안내 검색
	</div>
	<table class="table table-cols">
		<colgroup><col class="width-md" /><col /></colgroup>
        <?php if(gd_use_provider() === true) { ?>
		<tr>
			<th>공급사 구분</th>
			<td>
				<label class="radio-inline">
					<input type="radio" name="scmFl" value="all" <?php echo gd_isset($checked['scmFl']['all']); ?>  />전체</label>
				<label class="radio-inline">
					<input type="radio" name="scmFl" value="n" <?php echo gd_isset($checked['scmFl']['n']); ?> />본사</label>
				<label class="radio-inline">
					<input type="radio" name="scmFl" value="y" <?php echo gd_isset($checked['scmFl']['y']); ?> />공급사
				</label>
				<?php if(gd_is_provider() === false) { ?>
				<label>
					<button type="button" class="btn btn-sm btn-gray" onclick="layer_register('scm','checkbox')">공급사 선택</button>
				</label>
				<?php } ?>
				<div id="scmLayer" class="selected-btn-group <?=$search['scmFl'] == 'y' && !empty($search['scmNo']) ? 'active' : ''?>">
					<h5>선택된 공급사 : </h5>
					<?php if(gd_is_provider() === false) {
						if ($search['scmFl'] == 'y') {
							foreach ($search['scmNo'] as $k => $v) { ?>
								<div id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
									<input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
									<input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
									<span class="btn"><?= $search['scmNoNm'][$k] ?></span>
									<button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $v ?>">삭제</button>
								</div>
							<?php }
						}
					} else {  ?>
						<?=$search['scmNoNm']?>
					<?php } ?>
				</div>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<th>검색어</th>
			<td>
				<div class="form-inline">
					<?php echo gd_select_box('key','key',array('informNm'=>'이용안내 제목'),'',$search['key'],'=통합검색=');?>
					<input type="text" name="keyword" value="<?php echo $search['keyword'];?>" class="form-control" />
				</div>
			</td>
		</tr>
		<tr>
			<th>이용안내 종류</th>
			<td>
				<?php
				foreach ($arrInfo as $key => $val) {
					echo '	<label class="radio-inline"><input type="radio" name="groupCd" value="'.$key.'" '.gd_isset($checked['groupCd'][$key]).' />'.$val.'</label>';
				}
				?>
			</td>
		</tr>
	</table>

	<div class="table-btn">
		<input type="submit" value="검색" class="btn btn-lg btn-black">
	</div>

	<div class="table-header">
		<div class="pull-left">
			검색 <strong><?php echo number_format($page->recode['total']);?></strong>개 /
			전체 <strong><?php echo number_format($page->recode['amount']);?></strong>개
		</div>
		<div class="pull-right">
			<div class="form-inline">
				<?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
				<?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
			</div>
		</div>
	</div>
</form>

<form id="frmList" action="" method="get" target="ifrmProcess">
	<input type="hidden" name="mode" value="">
	<table class="table table-rows">
	<thead>
	<tr>
		<th class="width3p"><input type="checkbox" id="allCheck" value="y"
					onclick="check_toggle(this.id,'informCd');"/></th>
		<th class="width5p center">번호</th>
		<th class="width5p center">이용안내 코드</th>
		<th class="width10p center">이용안내 종류</th>
		<th>이용안내 제목</th>
		<th class="width10p center">공급사 구분</th>
		<th class="width10p center">등록일</th>
		<th class="width5p center">수정</th>
	</tr>
	</thead>
	<tbody>
<?php
    if (is_array(gd_isset($data))) {
        foreach ($data as $key => $val) {

			if(gd_is_provider() === false) {
				if ($val['modeFl'] == 'y') {
					$strDisplay    = '(기본설정)';
				} else {
					$strDisplay    = '';
				}
			} else {

				if(($val['scmNo'] ==DEFAULT_CODE_SCMNO && $val['scmModeFl'] =='y') || ($val['scmNo'] ==Session::get('manager.scmNo') && $val['modeFl'] =='y')  ) {
					$strDisplay    = '(기본설정)';
				} else {
					$strDisplay    = '';
				}
			}

			if($val['modeFl'] =='y' || $val['goodsNo'] !='' || (gd_is_provider() === true && $val['scmNo'] ==DEFAULT_CODE_SCMNO)) $deleteFl = "n";
			else $deleteFl = "y";

			if($val['scmNo'] ==DEFAULT_CODE_SCMNO) $scmFl = "n";
			else $scmFl = "y";

?>
	<tr>
		<td class="center"><input type="checkbox" name="informCd[<?php echo $val['informCd']; ?>]" value="<?php echo $val['informCd']; ?>" data-delete-fl = '<?=$deleteFl?>' data-scm-fl = '<?=$scmFl?>' /></td>
		<td class="center"><?php echo number_format($page->idx--); ?></td>
		<td class="center"><?php echo $val['informCd'];?></td>
		<td class="center">[<?php echo $arrInfo[$val['groupCd']];?>]</td>
		<td>
			<a href="./goods_detail_info_register.php?informCd=<?php echo $val['informCd'];?>"><?php echo $val['informNm'];?></a>
			<p class="mgb0"><?php echo $strDisplay;?></p>
		</td>
		<td class="center"><?=$val['scmNm']?></td>
		<td class="center"><?php echo gd_date_format('Y-m-d', $val['regDt']);?></td>
		<td class="center">
			<a href="./goods_detail_info_register.php?informCd=<?php echo $val['informCd'];?>" class="btn btn-gray btn-sm">수정</a>
		</td>

	</tr>
<?php
        }
    } else {
?>
	<tr>
		<td class="center" colspan="10">검색된 정보가 없습니다.</td>
	</tr>
<?php
    }
?>
	</tbody>
	</table>
	<div class="table-action">
		<div class="pull-left">
			<button type="button" class="btn btn-white checkCopy">선택 이용안내 복사</button>
			<button type="button" class="btn btn-white checkDelete">선택 이용안내 삭제</button>
		</div>
		<!--
        <div class="pull-right">
            <button type="button" class="btn btn-white btn-icon-excel">엑셀다운로드</button>
        </div> -->
	</div>
</form>
	<div class="center"><?= $page->getPage(); ?></div>

<script type="text/javascript">
	<!--
	$(document).ready(function(){


		$('input[name="scmFl"]').click(function () {

			<?php if(gd_is_provider() === false) { ?>
			if($(this).val() =='n' || $(this).val() =='all') {
				$('#scmLayer').html('');
			} else {
				layer_register('scm','checkbox');
			}
			<?php } else { ?>
			if($(this).val() =='n' || $(this).val() =='all') {
				$('#scmLayer').hide();
			} else {
				$('#scmLayer').show();
			}
			<?php } ?>


		});


		$('select[name=\'pageNum\']').change(function () {
			$('input[name=\'pageNum\']').val($(this).val());
			$('#frmSearchGoods').submit();
		});

		$('select[name=\'sort\']').change(function () {
			$('input[name=\'sort\']').val($(this).val());
			$('#frmSearchGoods').submit();
		});


		// 삭제
		$('button.checkDelete').click(function () {

			var except = new Array();
			var exceptScm = new Array();
			$('input[name*="informCd"]:checked').each(function () {
				if($(this).data('delete-fl') =='n') {
					$(this).prop('checked', false);

					<?php if(gd_is_provider() === true) { ?>
					if($(this).data('scm-fl') =='n') {
						exceptScm.push($(this).val());
					} else {
						except.push($(this).val());
					}
					<?php } else { ?>
					except.push($(this).val());
					<?php } ?>
				}
			});

			var chkCnt = $('input[name*="informCd"]:checked').length;
			var msg = "";
			if(except.length > 0 || exceptScm.length > 0) {

				if(except.length > 0) msg += "이용안내 코드 ["+except.join(",")+"] 는 현재 사용중으로 삭제가 불가능합니다.<br/> ";
				<?php if(gd_is_provider() === true) { ?>
				if(exceptScm.length > 0)  msg += "이용안내 코드 ["+exceptScm.join(",")+"] 는 본사 정보로  삭제가 불가능합니다.<br/>";
				<?php } ?>
				if (chkCnt == 0) {
					alert(msg);
					return false;
				}
			}

			if (chkCnt == 0) {
				alert('선택된 이용안내가 없습니다.');
				return;
			}
			dialog_confirm(msg+'선택한 ' + chkCnt + '개 이용안내를  정말로 삭제하시겠습니까?<br/>삭제시 정보는 복구 되지 않습니다.', function (result) {
				if (result) {
					$('#frmList input[name=\'mode\']').val('goods_info_delete');
					$('#frmList').attr('method', 'post');
					$('#frmList').attr('action', './goods_ps.php');
					$('#frmList').submit();
				}
			});

		});

		$('button.checkCopy').click(function () {
			var chkCnt = $('input[name*="informCd"]:checked').length;
			if (chkCnt == 0) {
				alert('선택된 이용안내가 없습니다.');
				return;
			}
			dialog_confirm('선택한 ' + chkCnt + '개 이용안내를  정말로 복사하시겠습니까?', function (result) {
				if (result) {
					$('#frmList input[name=\'mode\']').val('goods_info_copy');
					$('#frmList').attr('method', 'post');
					$('#frmList').attr('action', './goods_ps.php');
					$('#frmList').submit();
				}
			});
		});


	});

	/**
	 * 카테고리 연결하기 Ajax layer
	 */
	function layer_register(typeStr, mode, isDisabled) {

		<?php if(gd_is_provider() === false) { ?>
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
		<?php }  else { ?>
		$("#scmLayer").show();
		<?php } ?>
	}

	//-->
</script>
