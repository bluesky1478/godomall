<?php
//펼침,닫힘 정보
$toggle = gd_policy('display.toggle');
$SessScmNo = Session::get('manager.scmNo');

// 순서 재정렬
$cate->setCategoryResort($data['cateCd']);

// 등록 수정에 따른 명칭
if ($data['mode'] == 'register') {
	$title    = '등록 - [1차 '.$info['cateTitle'].']';
} else {
	$title    = '수정 - ['.$data['cateNm'].']';
}

// 카테고리에 따른 처리 페이지
if ($info['cateType'] == 'goods') {
	$formParam    = '';
	$viewUrl    = 'goods_list.php';
	$themeUrl    = 'category_theme_register.php';
	$toggleId = 'category';
} else {
	$formParam    = '?cateType='.$info['cateType'];
	$viewUrl    = 'goods_list.php';
	$themeUrl    = 'brand_theme_register.php';
	$toggleId = 'brand';
}

// 익스 6 인 경우 에디터 처리
if (preg_match('/MSIE (6|7)/', Request::getUserAgent())) {
	$editorView    = false;
} else {
	$editorView    = true;
}
?>
<?php if ($data['mode'] != 'onload') {?>
	<script type="text/javascript">
		<!--
		$(document).ready(function(){

			$("#frmCateDetail").validate({
				submitHandler: function (form) {
					<?php if ($data['divisionFl'] == 'n') {?>
					oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
					oEditors.getById["editor2"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
					oEditors.getById["editor3"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                    oEditors.getById["editor4"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                    oEditors.getById["editor5"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                    oEditors.getById["editor6"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
					<?php } ?>

					if($('input[name="sortAutoFl"]:checked').val() =='y') {
						$(".js-sorttype-n").html('');
					} else {
						$(".js-sorttype-y").html('');
					}

					<?php if ($gGlobal['isUse'] === true && $data['parentMallDisplay'] == false) { ?>
					if($("input[name*='mallDisplay[]']:checked").length ==0) {
						alert('노출상점을 설정해주세요.');
						return false;
					}
					<?php } ?>

                    $("input[name='cateOnlyAdultDisplayFl']").prop("disabled",false);
                    $("input[name='catePermissionDisplayFl']").prop("disabled",false);

					form.target='ifrmProcess';
					form.submit();
				},
				// onclick: false, // <-- add this option
				rules: {
					cateNm: 'required'
				},
				messages: {
					cateNm: {
						required: '<?=$info['cateTitle'];?>명을 입력하세요.'
					}
				}
			});


			viewThemeConfig('tbl_pcThemeInfo',$('select[name="pcThemeCd"]').val());
			viewThemeConfig('tbl_mobileThemeInfo',$('select[name="mobileThemeCd"]').val());
			viewThemeConfig('tbl_recomPcThemeInfo',$('select[name="recomPcThemeCd"]').val());
			viewThemeConfig('tbl_recomMobileThemeInfo',$('select[name="recomMobileThemeCd"]').val());


			<?php if ($data['divisionFl'] == 'y') {?>
			display_toggle('y');
			<?php }?>

			<?php if (empty($manualData['menuKey']) === false) {?>

			// 메뉴얼 링크
			$(".gd-help-manual").each(function() {
				$(this).append(' <a href="<?=sprintf($manualData['manual_url'], $manualData['menuCode'], $manualData['menuKey'], $manualData['menuFile']);?>#' + $(this).text().trim().replace(/\s/g, '') + '" target="_blank" class="link-help">페이지 도움말</a>');
			});
			<?php }?>

			<?php if (empty($tooltipData) === false) {?>

			// 툴팁 데이터
			var tooltipData = <?=$tooltipData;?>;
			$(".table.table-cols th").each(function() {
				var titleName = $(this).text().trim().replace(/ /gi, '');
				for (var i in tooltipData) {
					if (tooltipData[i].attribute == titleName) {
						$(this).append('<button type="button" class="btn btn-xs js-tooltip" title="' + tooltipData[i].content + '" data-placement="right"><span class="icon-tooltip"></span></button>');
					}
				}
			});

			// 툴팁 초기화
			var option = {
				trigger: 'hover',
				container: '#content',
			};
			$('.js-tooltip').tooltip(option);
			<?php }?>

			initDepthToggle(<?=$SessScmNo?>);//4depth 메뉴 보임안보임처리

			$('input[name="cateImgMobileFl"]').click(function(e){
				if($(this).prop( "checked" )) {
					$(".js-cate-img-mobile").hide();
				} else {
					$(".js-cate-img-mobile").show();
				}
			});

			<?php if ($data['cateImgMobileFl'] =='y') {?>
			$(".js-cate-img-mobile").hide();
			<?php } ?>



			$('input[name="sortAutoFl"]').click(function(e){
				if($(this).val() =='y') {
					$(".js-sorttype-y").show();
					$(".js-sorttype-n").hide();
				} else {
					$(".js-sorttype-y").hide();
					$(".js-sorttype-n").show();
				}
			});

			$('input[name="recomSortAutoFl"]').click(function(e){
				if($(this).val() =='y') {
					$(".js-sorttype-recom-y").show();
					$(".js-recom-auto-goods").show();
					$(".js-recom-manual-goods").hide();
				} else {
					$(".js-sorttype-recom-y").hide();
					$(".js-recom-auto-goods").hide();
					$(".js-recom-manual-goods").show();
				}
			});

			$('input[maxlength]').maxlength({
				showOnReady: true,
				alwaysShow: true
			});

			<?php if ($gGlobal['isUse'] === true) { ?>
			//카테고리명 기준몰 동일 여부 체크
			$(".js-global-catenm input[type='text']").each(function() {
                var cateNm = $('input[name="cateNm"]').val();
				if($(this).val() =='') {
					$(this).prop("readonly",true);
					$(this).closest("tr").find("input[type='checkbox']").prop('checked',true);
				}

                var checked = $(this).closest("tr").find("input[type='checkbox']").prop('checked');
                if (checked === true) {
                    $(this).val(cateNm);
                }
			});

			$(".js-global-catenm  input:checkbox").click(function () {
				if($(this).is(":checked")) {
                    var catNm = $("input[name='cateNm']").val();
					$(this).closest("tr").find("input[type='text']").val(catNm).prop('readonly',true);
				} else {
					$(this).closest("tr").find("input[type='text']").prop('readonly',false);
				}
			});


			if($("input[name*='mallDisplay[]']:checked").length == <?=count($gGlobal['useMallList'])?>) {
				$("input[name*='mallDisplay[]']").prop("checked",false);
				$("input[name='mallDisplay[]'][value='all']").prop("checked",true);
			}

			$("input[name*='mallDisplay[]']").click(function () {
				if($(this).is(":checked") && $(this).val() !='all') {
					$("input[name='mallDisplay[]'][value='all']").prop("checked",false);
				}
			});

			<?php } ?>


            $("#btnDescriptionNevi, #btnDescriptionNeviMobile,#btnDescriptionRecom, #btnDescriptionRecomMobile,#btnDescriptionList, #btnDescriptionListMobile").click(function () {
                var target = $(this).data('target');
                var type = $(this).data('type');

                if (type == 'pc') {
                    $('#btn'+target).addClass('active');
                    $('#btn'+target+'Mobile').removeClass('active');
                    $('#textarea'+target).show();
                    $('#textarea'+target+'Mobile').hide();
                } else {
                    if($(this).parents("ul").find("input[type='checkbox']").prop('checked') == false) {
                        $('#btn'+target).removeClass('active');
                        $('#btn'+target+'Mobile').addClass('active');
                        $('#textarea'+target).hide();
                        $('#textarea'+target+'Mobile').show();
                    }
                }
                return false;
            });


            $("input[name='cateHtml1SameFl'] , input[name='cateHtml2SameFl'], input[name='cateHtml3SameFl']").click(function () {
                var target = $(this).data('target');
                if($(this).prop('checked')) {
                    $("#btn"+target+"Mobile").addClass("nav-none");
                    $("#btn"+target+"Mobile a").css("background","#F6F6F6");
                    $("#btn"+target).click();
                } else {
                    $("#btn"+target+"Mobile").removeClass("nav-none");
                    $("#btn"+target+"Mobile a").css("background","");
                }
            });

			init_translate();

			<?php if($data['cateOnlyAdultFl'] =='n') { ?>
            $("input[name='cateOnlyAdultDisplayFl']").prop("disabled",true);
            $("input[name='cateOnlyAdultDisplayFl']").closest("label").css("color","#999999");
            <?php } ?>

            <?php if($data['catePermission'] =='0') { ?>
            $("input[name='catePermissionDisplayFl']").prop("disabled",true);
            $("input[name='catePermissionDisplayFl']").closest("label").css("color","#999999");
            <?php } ?>

            $('input[name="cateOnlyAdultFl"]').click(function(e){
                if($(this).val() =='n') {
                    $("input[name='cateOnlyAdultDisplayFl']").prop("disabled",true);
                    $("input[name='cateOnlyAdultDisplayFl']").closest("label").css("color","#999999");
                } else {
                    $("input[name='cateOnlyAdultDisplayFl']").prop("disabled",false);
                    $("input[name='cateOnlyAdultDisplayFl']").closest("label").css("color","#333333");
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

			if (typeStr == 'goods') {
				addParam['layerTitle'] = '<?=$info['cateTitle'];?> 추천상품에 진열할 상품 설정';
				addParam['dataInputNm']		= 'recomGoodsNo';
				addParam['callFunc']		= 'display_recom_goods';

			}
			if (typeStr == 'member_group') {
				addParam['layerTitle'] = '회원 등급 선택';
				addParam['dataInputNm'] = "memberGroupNo";
			}

			if (!_.isUndefined(isDisabled) && isDisabled == true) {
				addParam.disabled = 'disabled';
			}

			layer_add_info(typeStr, addParam);
		}

		function display_recom_goods(data) {

			data.dataInputNm = 'recomGoodsNo';


			$.each(data.info, function (key, val) {
				var addHtml = "";

				addHtml += '<tr id="' + data.dataFormID + '_' + val.goodsNo + '">';
				addHtml += '<td class="center"><input type="checkbox" id="recom_goods_' + val.goodsNo + '" name="recom_goods_' + val.goodsNo + '" value="'+val.goodsNo+'"><input type="hidden" name="' + data.dataInputNm + '[]" value="' + val.goodsNo + '" /></td>';
				addHtml += '<td class="center" id="recom_number_' + val.goodsNo + '">'+(key+1)+'</td>';
				addHtml += '<td><a href="<?=URI_HOME;?>goods/goods_view.php?goodsNo=' + val.goodsNo + '" target="_blank"><img src="' + val.goodsImg + '" align="absmiddle" width="30" alt="' + val.goodsNm + '" title="' + val.goodsNm + '" /></a></td>';
                addHtml += '<td><a href="../goods/goods_register.php?goodsNo=' + val.goodsNo + '" target="_blank">' + val.goodsNm + '</a></td>';
				addHtml += '<td class="center">' + val.goodsPrice + '</td>';
				addHtml += '<td >' + val.scmNm + '</td>';
				addHtml += '<td class="center">' + val.totalStock + '</td>';
				addHtml += '<td class="center">' + val.stockTxt + '</td>';
				addHtml += '<td class="center">' + val.displayPc + '</td>';
				addHtml += '<td class="center">' + val.displayMobile + '</td>';
				addHtml += '</tr>';


				$("#" + data.parentFormID).append(addHtml);

			});

			$('td[id*=\'recom_number_\']').each(function (key) {
				$(this).html(key+1);
			});


			$("#goodsLayer  tfoot").hide();
		}


		/**
		 * 출력 여부
		 *
		 * @param string arrayID 해당 ID
		 * @param string modeStr 출력 여부 (show or hide)
		 */
		function display_toggle(modeStr)
		{
			if (modeStr == 'n') {
				$('.hideDivisionFl').show();
				$('#dv_divisionEx').hide();
			} else if (modeStr == 'y') {
				$('.hideDivisionFl').hide();
				$('#dv_divisionEx').show();
			}
		}


		/**
		 * 테마상세 팝업창
		 *
		 * @author artherot
		 * @param string orderNo 주문 번호
		 */
		function theme_register_popup(themeNo)
		{
			var path = get_script_dirpath('_template/script/common.js');

			win = popup({
				url: path+'goods/<?=$themeUrl;?>?popupMode=yes&themeId='+themeNo
				, target: 'theme_register'
				, width: 860
				, height: 600
				, scrollbars: 'yes'
				, resizable: 'yes'
			});
			win.focus();
			return win;
		}

        /**
         * 테마 수정 정보 업데이트
         *
         * @param string themeCd 테마코드
         * @param string themeNm 테마명
         */
        function update_theme_info(themeCd,themeNm)
        {
            $(".js-theme-"+themeCd).html(themeNm);
        };

		<?php if ($editorView === false) {?>
		/**
		 * 에디터 열기
		 *
		 * @author artherot
		 * @param integer loc 에디터 번호
		 */
		function editor_open(loc)
		{
			$('.editorZoneBtn'+loc).attr('class','display-none');
			$('.editorZone'+loc).attr('class','display-block');

			if ($('#editorZoneChk').val() != 'y') {
				$('#editorZoneChk').val('y');
				setup_tinyMce();
			}
		}
		<?php }?>

		/**
		 * 테마보기
		 */
		function viewThemeConfig(tbl,themeCd) {
			var parameters = {
				'mode': 'theme_ajax',
				'themeCd': themeCd
			};

			$.post("../goods/display_config_ps.php",parameters,
				function(data){
                    $("#"+tbl+" .js_tbl_theme_themeCd").data('code',data.themeCd);
					$.each(data, function(i,item){
						$("#"+tbl+" .tbl_theme_"+i).html(item);
					});
				}, "json");
		}

		/**
		 * 테마 입력
		 *
		 * @author artherot
		 * @param string orderNo 주문 번호
		 */
		function add_theme_popup(themeCate,themeCdNm, mobileFl)
		{

			window.open('../goods/display_config_theme_register.php?popupMode=yes&themeCate='+themeCate+'&addTheme='+themeCdNm+'&mobileFl='+mobileFl, 'member_crm', 'width=1210, height=700, scrollbars=yes');
		};

        /**
         * 테마 수정
         *
         * @param string themeCd 테마코드
         */
        function modify_theme_popup(val)
        {
            var themeCd = $(val).data('code');
            var addTheme = $(val).data('target');

            window.open('../goods/display_config_theme_register.php?popupMode=yes&addTheme='+addTheme+'&callFunc=update_theme_info&themeCd='+themeCd, 'theme_popup', 'width=1210, height=700, scrollbars=yes');
        };

        /**
         * 테마 수정 정보 업데이트
         *
         * @param string themeCd 테마코드
         * @param string themeNm 테마명
         */
        function update_theme_info(themeCd,themeNm,target)
        {
            viewThemeConfig("tbl_"+target+"Info",themeCd);
            $('select[name="'+target+'Cd"] option:selected').text(themeNm);
        };


		//추가상품삭제
		function delete_option() {

			if($('input[name="recomSortAutoFl"]:checked').val() == 'y') {

				$("[id^='recom_goods_']:checked").each(function () {
					field_remove('info_goods_' + $(this).val());
				});

				$('td[id*=\'recom_number_\']').each(function (key) {
					$(this).html(key + 1);
				});

			} else {

				$('#add_goods'+activeGoodsBody+' input[name="itemGoodsNo[]"]:checked').each(function () {
					//field_remove('tbl_add_goods_' + $(this).val());
					$(this).closest("tr").remove();

				});

				var cnt = 1;
				$('#add_goods'+activeGoodsBody+' input[name="itemGoodsNo[]"]').each(function () {
					$('#add_goods'+activeGoodsBody+' .addGoodsNumber_'+$(this).val()).html(cnt);
					cnt++;
				});
			}
		}


		function set_cate_permission(val) {
			if(val =='2') {
				$("#btn_member_group").attr("disabled",false);
				layer_register('member_group');

			} else {
				$("#btn_member_group").attr("disabled",true);
				$("#member_groupLayer").html('');
			}

			if(val =='0') {
                $("input[name='catePermissionDisplayFl']").prop("disabled",true);
                $("input[name='catePermissionDisplayFl']").closest("label").css("color","#999999");
            } else {
                $("input[name='catePermissionDisplayFl']").prop("disabled",false);
                $("input[name='catePermissionDisplayFl']").closest("label").css("color","#333333");
            }
		}

		/*
		 * 추천상품 선택
		 */
		function select_recom_goods() {
			if($('input[name="recomSortAutoFl"]:checked').val() == 'y') {
				layer_register('goods','select');
			} else {
				var mobileFl = "";
				var displayPcFl =  $("input[name='recomDisplayFl']:checked").val();
				var displayMobileFl =  $("input[name='recomDisplayMobileFl']:checked").val();

				if(displayPcFl =='y' && displayMobileFl =='n' ) mobileFl = "n";
				else if(displayPcFl =='n' && displayMobileFl =='y' ) mobileFl = "y";
				else mobileFl = "all";

				window.open('../share/popup_goods.php?mobileFl='+mobileFl, 'popup_goods_search', 'width=1255, height=790, scrollbars=no');
			}
		}

		var activeGoodsBody = "0";
		/*
		 * 추천상품 수동 선택
		 */
		function setAddGoods(frmData) {
			var addHtml = "";
			var cnt = frmData.info.length;

			$.each(frmData.info, function (key, val) {
				var stockText = "";
				// 상품 재고
				if (val.stockFl == 'n') {
					totalStock    = '∞';
				} else {
					totalStock    = val.totalStock ;
				}


				if(val.soldOutFl =='y' || totalStock== 0) stockText = "품절";
				else stockText = "";


				if(val.sortFix == true) {
					sortFix = "checked = 'checked'";
					tableCss = "style='background:#d3d3d3' class='add_goods_fix'";
				} else {
					sortFix = '';
					tableCss = "class='add_goods_free'";
				}

				if(val.goodsDisplayFl =='y') goodsDisplayFlText = "노출함";
				else goodsDisplayFlText = "노출안함";

				if(val.goodsDisplayMobileFl =='y') goodsDisplayMobileFlText = "노출함";
				else goodsDisplayMobileFlText = "노출안함";


				addHtml += '<tr id="tbl_add_goods_'+val.goodsNo+'" '+tableCss+'>';
				addHtml += '<td class="center">';
				addHtml += '<input type="hidden" name="itemGoodsNm[]" value="'+val.goodsNm+'" />';
				addHtml += '<input type="hidden" name="itemGoodsPrice[]" value="'+val.goodsPrice+'" />';
				addHtml += '<input type="hidden" name="itemScmNm[]" value="'+val.scmNm+'" />';
				addHtml += '<input type="hidden" name="itemTotalStock[]" value="'+val.totalStock+'" />';
				addHtml += '<input type="hidden" name="itemBrandNm[]" value="'+val.brandNm+'" />';
				addHtml += '<input type="hidden" name="itemMakerNm[]" value="'+val.makerNm+'" />';
				addHtml += '<input type="hidden" name="itemImage[]" value="'+val.image+'" />';
				addHtml += '<input type="hidden" name="itemSoldOutFl[]" value="'+val.soldOutFl+'" />';
				addHtml += '<input type="hidden" name="itemStockFl[]" value="'+val.stockFl+'" />';
				addHtml += '<input type="hidden" name="itemGoodsDisplayFl[]" value="'+val.goodsDisplayFl+'" />';
				addHtml += '<input type="hidden" name="itemGoodsDisplayMobileFl[]" value="'+val.goodsDisplayMobileFl+'" />';
				addHtml += '<input type="hidden" name="itemGoodsSellFl[]" value="'+val.goodsSellFl+'" />';
				addHtml += '<input type="hidden" name="itemGoodsSellMobileFl[]" value="'+val.goodsSellMobileFl+'" />';
				addHtml += '<input type="checkbox" name="itemGoodsNo[]" id="layer_goods_'+val.goodsNo+'"  value="'+val.goodsNo+'"/></td>';
				addHtml += '<td class="center number addGoodsNumber_'+val.goodsNo+'">'+(cnt)+'</td>';
				addHtml += '<td class="center">'+decodeURIComponent(val.image)+'</td>';
				addHtml += '<td><a href="../goods/goods_register.php?goodsNo=' + val.goodsNo + '" target="_blank">'+val.goodsNm+'</a><input type="hidden" name="goodsNoData[]" value="'+val.goodsNo+'" />';
				addHtml += '<input type="checkbox" name="sortFix[]" class="layer_sort_fix_'+val.goodsNo+'"  value="'+val.goodsNo+'" '+sortFix+' style="display:none" ></td>';
				addHtml += '<td class="center">'+val.goodsPrice+'</td>';
				addHtml += '<td class="center">'+val.scmNm+'</td>';
				addHtml += '<td class="center">'+totalStock+'</td>';
				addHtml += '<td class="center">'+stockText+'</td>';
				addHtml += '<td class="center js-goodschoice-hide">'+goodsDisplayFlText+'</td>';
				addHtml += '<td class="center js-goodschoice-hide">'+goodsDisplayMobileFlText+'</td>';

				addHtml += '</tr>';

				cnt--;
			});

			$("#add_goods"+activeGoodsBody).html(addHtml);

			var cnt = 1;
			$('#add_goods'+activeGoodsBody+' input[name="itemGoodsNo[]"]').each(function () {
				$('#add_goods'+activeGoodsBody+' .addGoodsNumber_'+$(this).val()).html(cnt);
				cnt++;
			});
		}
		//-->
	</script>

	<form id="frmCateDetail" name="frmCateDetail" action="category_ps.php<?=$formParam;?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="mode" value="<?=$data['mode'];?>" />
		<input type="hidden" name="cateCd" value="<?=$data['cateCd'];?>" />
		<input type="hidden" name="cateSort" value="<?=$data['cateSort'];?>" />
		<?php if ($data['mode'] == 'modify' && $data['divisionFl'] == 'y') {?>
			<input type="hidden" name="divisionFl" value="<?=$data['divisionFl'];?>" />
		<?php }?>

		<div class="table-title gd-help-manual">
			<?=$info['cateTitle'];?> 정보
		<span class="depth-toggle">
			<a href="./goods_sort_<?=$info['cateType']  == 'goods' ? 'category' :  'brand'; ?>.php?cateGoods[]=<?=$data['cateCd'];?>"  target ="_blank" class="displayCategory btn btn-sm btn-gray" >상품진열</a>
			<button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="<?=$toggleId?>Info"><span>닫힘</span></button>
		</span>
		</div>

		<input type="hidden" id="depth-toggle-hidden-<?=$toggleId?>Info" value="<?=$toggle[$toggleId.'Info_'.$SessScmNo]?>">
		<div id="depth-toggle-line-<?=$toggleId?>Info" class="depth-toggle-line display-none"></div>
		<div id="depth-toggle-layer-<?=$toggleId?>Info">

			<table class="table table-cols">
				<colgroup><col style="width:160px;" /><col/><col style="width:160px;" /><col/></colgroup>
				<?php
				if ($data['mode'] == 'modify') {
					?>
					<tr>
						<th>현재 <?=$info['cateTitle'];?> <input type="button" class="btn btn-sm btn-gray" value="삭제" onclick="$.tree.reference('categoryTree').remove_check();"></th>
						<td colspan="3">
							<div><?=$cate->getCategoryPosition($data['cateCd']);?></div>
							<?php if ($data['divisionFl'] == 'n') {?>
							<?php if ($gGlobal['isUse'] === true) { ?>

								<span class="dropdown">
									<a href="#" class="dropdown-toggle btn btn-sm btn-default" id="categoryUrl" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">화면바로보기</a>
									<ul class="dropdown-menu gnb-dropdown-menu" aria-labelledby="categoryUrl">
										<?php
										foreach ($gGlobal['useMallList'] as $val) { ?>
										<li class="dropdown-item"><a href="<?=URI_HOME . ($val['domainFl'] == 'kr' ? '' : $val['domainFl'].'/')?>goods/<?=$viewUrl;?>?<?=$info['cateType']  == 'goods' ? 'cate' :  'brand'; ?>Cd=<?=$data['cateCd'];?>"  target="_blank"> <span
													class="flag flag-16 flag-<?= $val['domainFl'] ?>"></span> <?= $val['mallName'] ?></a></li>
										<?php } ?>
									</ul>
								</span>

								<span class="dropdown">
									<a href="#" class="dropdown-toggle btn btn-sm btn-default js-category-copy" id="categoryUrlCopy" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">주소복사</a>
									<ul class="dropdown-menu gnb-dropdown-menu" aria-labelledby="categoryUrlCopy">
										<?php
										foreach ($gGlobal['useMallList'] as $val) { ?>
											<li class="dropdown-item"><a class='js-clipboard'  title="<?= $val['mallName'] ?> " data-category-title="<?=$info['cateTitle']?>" data-clipboard-text="<?=URI_HOME . ($val['domainFl'] == 'kr' ? '' : $val['domainFl'].'/')?>goods/<?=$viewUrl;?>?<?=$info['cateType']  == 'goods' ? 'cate' :  'brand'; ?>Cd=<?=$data['cateCd'];?>"  target="_blank"> <span
														class="flag flag-16 flag-<?= $val['domainFl'] ?>"></span> <?= $val['mallName'] ?></a></li>
										<?php } ?>
									</ul>
								</span>
								<?php } else { ?>
								<a class="btn btn-sm btn-default" href="<?=URI_HOME;?>goods/<?=$viewUrl;?>?<?=$info['cateType']  == 'goods' ? 'cate' :  'brand'; ?>Cd=<?=$data['cateCd'];?>"  target="_blank">화면바로보기</a>
								<a class="btn btn-sm btn-red js-clipboard" title="" data-category-title="<?=$info['cateTitle']?>" data-clipboard-text="<?=URI_HOME;?>goods/<?=$viewUrl;?>?<?=$info['cateType']  == 'goods' ? 'cate' :  'brand'; ?>Cd=<?=$data['cateCd'];?>">주소복사</a>
								<?php } ?>
							<?php }?>
						</td>
					</tr>
					<?php if ($data['divisionFl'] == 'n') {?>
						<tr>
							<th>등록 상품수</th>
                            <td colspan="3">
                                <?=number_format($goodsLinkCnt); ?> 개가 등록되어 있습니다.
                                <span class="notice-ref notice-sm">
                                    <?php if ($info['cateType'] == 'goods') { ?>
                                        (하위 <?=$info['cateTitle'];?>까지 포함)
                                    <?php } else { ?>
                                        (하위 <?=$info['cateTitle'];?>등록상품 <?php echo $brandLinkUse != 'y' ? '미' : '';?>포함)
                                    <?php } ?>
                                </span>
                            </td>
						</tr>
					<?php }?>
					<?php
				}
				?>
				<?php if ($gGlobal['isUse'] === true ) { ?>
					<?php if($data['parentMallDisplay'] == false) { ?>
						<tr>
							<th>노출상점</th>
							<td colspan="3">
								<label class="checkbox-inline">
									<input type="checkbox" name="mallDisplay[]" value="all" <?= gd_isset($checked['mallDisplay']['all']); ?>/>전체
								</label>
								<?php
								foreach ($gGlobal['useMallList'] as $val) {
										?>
										<label class="checkbox-inline">
											<input type="checkbox" name="mallDisplay[]"
												   value="<?= $val['sno'] ?>" <?= gd_isset($checked['mallDisplay'][$val['sno']]); ?>/><span
												class="flag flag-16 flag-<?= $val['domainFl'] ?>"></span> <?= $val['mallName'] ?>
										</label>
									<?php
								}
								?>
								<div>
									<label class="checkbox-inline">
										<input type="checkbox" name="mallDisplaySubFl" value="y" <?= gd_isset($checked['mallDisplaySubFl']['y']); ?>/>하위 <?=$info['cateTitle'];?> 동일 적용
									</label>
								</div>
							</td>
						</tr>
					<?php } else { ?>
						<tr>
							<th>노출상점</th>
							<td colspan="3">
								<input type="hidden" name="mallDisplay[]" value="<?=$data['mallDisplay']?>">
								<?php
								foreach ($gGlobal['useMallList'] as $val) {
									if(strpos($data['mallDisplay'],$val['sno']) !== false)  {
										?>
										<span class="flag flag-16 flag-<?= $val['domainFl'] ?>"></span> <?= $val['mallName'] ?>
									<?php }
								}
								?>
							</td>
						</tr>
					<?php } ?>
				<?php } ?>
				<?php if ($gGlobal['isUse'] === true) { ?>
					<tr>
						<th class="require"><?=$info['cateTitle'];?>명</th>
						<td colspan="3">
							<div>
								<table class="table table-cols" style="width:500px">
									<tr>
										<th>
											기준몰
										</th>
										<td>
											<input type="text" name="cateNm" value="<?= $data['cateNm']; ?>" class="form-control width-lg js-maxlength" maxlength="30"/>
										</td>
									</tr>
									<tbody class="js-global-catenm">
									<?php
									foreach ($gGlobal['useMallList'] as $val) {
										if ($val['standardFl'] == 'n') {
											?>
											<tr>
												<th>
													<span class="flag flag-16 flag-<?= $val['domainFl'] ?>"></span>
												</th>
												<td>
													<input type="text" name="globalData[<?= $val['sno'] ?>][cateNm]" value="<?= $data['globalData'][ $val['sno']]['cateNm']; ?>" <?=gd_isset($readonly['readonly'][$val['sno']]);?> class="form-control width-lg js-maxlength" maxlength="30"/>
													<div>
														<label class="checkbox-inline">
															<input type="checkbox" name="globalData[<?= $val['sno'] ?>][cateNmGlobalFl]" value="<?= $val['sno'] ?>" <?= gd_isset($checked['cateNmGlobalFl'][$val['sno']]); ?>> 기준몰 <?=$info['cateTitle'];?>명 공통사용
														</label>
														<a class="btn btn-sm btn-black js-translate-google" data-language="<?= $val['domainFl'] ?>" data-target-name="cateNm">참고 번역</a>
													</div>
												</td>
											</tr>
										<?php }
									}?>
									</tbody>
								</table>

								<?php if ($data['mode'] == 'modify' && $data['divisionFl'] == 'n') {?>
									현재 <?=$info['cateTitle'];?> 코드 : <?=$data['cateCd'];?>
								<?php }?>
							</div>
							<div>
								<div class="notice-info">
									기본적으로 입력된 텍스트로 해당 <?=$info['cateTitle'];?> 보여집니다<br/>
									<?=$info['cateTitle'];?>를 이미지로 노출하려면 아래 "<?=$info['cateTitle'];?> 이미지등록" 항목에 이미지를 등록하세요.
								</div>
							</div>
						</td>
					</tr>
				<?php } else { ?>
					<tr>
						<th class="require"><?=$info['cateTitle'];?>명</th>
						<td colspan="3">
							<div>
								<input type="text" name="cateNm" value="<?=$data['cateNm'];?>" class="form-control width-lg js-maxlength" maxlength="30"/>
								<?php if ($data['mode'] == 'modify' && $data['divisionFl'] == 'n') {?>
									현재 <?=$info['cateTitle'];?> 코드 : <?=$data['cateCd'];?>
								<?php }?>
							</div>
							<div>
								<div class="notice-info">
									기본적으로 입력된 텍스트로 해당 <?=$info['cateTitle'];?> 보여집니다<<br/>
									<?=$info['cateTitle'];?>를 이미지로 노출하려면 아래 "<?=$info['cateTitle'];?> 이미지등록" 항목에 이미지를 등록하세요.
								</div>
							</div>
						</td>
					</tr>
				<?php } ?>
				<?php if ($data['mode'] == 'register') {?>
					<tr>
						<th><?=$info['cateTitle'];?> 타입</th>
						<td colspan="3">
							<div><label class="radio-inline" title="일반 <?=$info['cateTitle'];?>"><input type="radio" name="divisionFl" value="n" onclick="display_toggle(this.value);" <?=gd_isset($checked['divisionFl']['n']);?> />일반 <?=$info['cateTitle'];?></label> <span class="notice-ref notice-sm">(<?=$info['cateTitle'];?> 페이지가 있고, 상품연결이 되는 일반적인 <?=$info['cateTitle'];?>입니다.)</span></div>
							<div><label class="radio-inline" title="그룹(구분) <?=$info['cateTitle'];?>"><input type="radio" name="divisionFl" value="y"  onclick="display_toggle(this.value);" <?=gd_isset($checked['divisionFl']['y']);?> />그룹(구분) <?=$info['cateTitle'];?></label> <span class="notice-ref notice-sm">(<?=$info['cateTitle'];?> 페이지가 없고, 상품연결이 안되는 그룹(구분) <?=$info['cateTitle'];?>입니다.)</span></div>
						</td>
					</tr>
				<?php }?>
				<tr>
					<th>PC쇼핑몰<br/>노출상태</th>
					<td colspan="3">
						<label class="radio-inline"><input type="radio" name="cateDisplayFl" value="y" <?=gd_isset($checked['cateDisplayFl']['y']);?> />노출함</label>
						<label class="radio-inline"><input type="radio" name="cateDisplayFl" value="n" <?=gd_isset($checked['cateDisplayFl']['n']);?> />노출안함</label>
					</td>
				</tr>
				<tr>
					<th>모바일쇼핑몰<br/>노출상태</th>
					<td colspan="3">
						<label class="radio-inline"><input type="radio" name="cateDisplayMobileFl" value="y" <?=gd_isset($checked['cateDisplayMobileFl']['y']);?> />노출함</label>
						<label class="radio-inline"><input type="radio" name="cateDisplayMobileFl" value="n" <?=gd_isset($checked['cateDisplayMobileFl']['n']);?> />노출안함</label>
					</td>
				</tr>
				<tr>
					<th>PC쇼핑몰<br/><?=$info['cateTitle'];?> 이미지 등록</th>
					<td>
						<p><label class="checkbox-inline"><input type="checkbox" name="cateImgMobileFl" value="y" <?=gd_isset($checked['cateImgMobileFl']['y']);?> />모바일 쇼핑몰과 동일 적용</label></p>
						<label><input type="file" name="cateImg"></label> <?php if($data['cateImg']) { ?><label><img src="/data/category/<?=$data['cateImg']?>?<?=time()?>" style="max-width:200px;"><input type="hidden" name="cateImg" value="<?=$data['cateImg']?>"> <input type="checkbox" name="cateImgDel" value="y">삭제</label><?php } ?>
					</td>
					<th>PC쇼핑몰<br/> 마우스오버 이미지 등록</th>
					<td >
						<label><input type="file" name="cateOverImg"></label>  <?php if($data['cateOverImg']) { ?><label><img src="/data/category/<?=$data['cateOverImg']?>?<?=time()?>"  style="max-width:200px;"><input type="hidden" name="cateOverImg" value="<?=$data['cateOverImg']?>"> <input type="checkbox" name="cateOverImgDel" value="y">삭제</label><?php } ?>
					</td>
					</td>
				</tr>
				<tr class="js-cate-img-mobile">
					<th>모바일쇼핑몰<br/><?=$info['cateTitle'];?> 이미지 등록</th>
					<td colspan="3">
						<label><input type="file" name="cateImgMobile"></label> <?php if($data['cateImgMobile'] && $data['cateImgMobileFl'] =='n') { ?><label><img src="/data/category/<?=$data['cateImgMobile']?>?<?=time()?>" style="max-width:200px;"><input type="hidden" name="cateImgMobile" value="<?=$data['cateImgMobile']?>"> <input type="checkbox" name="cateImgMobileDel" value="y">삭제</label><?php } ?>
					</td>
				</tr>
				<?php if ($data['divisionFl'] == 'n'){?>
                    <?php if($data['parentOnlyAdult'] == false) { ?>
                    <tr class="hideDivisionFl">
                        <th>성인인증</th>
                        <td colspan="3">
                            <div class="radio">
                                <label class="radio-inline">
                                    <input name="cateOnlyAdultFl" value="n" type="radio" <?=gd_isset($checked['cateOnlyAdultFl']['n']); ?>>사용안함
                                </label>
                                <label class="radio-inline">
                                    <input name="cateOnlyAdultFl" value="y" type="radio" <?=gd_isset($checked['cateOnlyAdultFl']['y']); ?> >사용함
                                </label>
                                <label class="checkbox-inline"> ( <input type="checkbox" name="cateOnlyAdultDisplayFl" value="y" <?=gd_isset($checked['cateOnlyAdultDisplayFl']['y']);?> />미인증 고객 <?=$info['cateTitle'];?> 노출함 )</label>
                                <label class="checkbox-inline"><input type="checkbox" name="cateOnlyAdultSubFl" value="y" <?=gd_isset($checked['cateOnlyAdultSubFl']['y']);?> />하위  <?=$info['cateTitle'];?> 동일 적용</label>
                            </div>
                            <p class="notice-info mgb10 if-btn">
                                해당 카테고리의 상품리스트 페이지 접근시 성인인증확인 인트로 페이지가 출력되어 보여집니다. <br/>
                                <?php if (gd_is_provider() === false && !gd_use_ipin() && !gd_use_auth_cellphone() ) { ?> 성인인증 기능은 별도의 인증 서비스 신청완료 후 이용 가능합니다.<br/>

                                    <a href="../policy/member_auth_cellphone.php" target="_blank" class="btn-link">휴대폰인증 설정 바로가기</a>  <a href="../policy/member_auth_ipin.php" target="_blank" class="btn-link">아이핀인증 설정 바로가기</a>
                                    <br/><?php } ?>

                            </p>
                            <p class="notice-danger">
                                구 실명인증 서비스는 성인인증 수단으로 연결되지 않습니다.<br/>
                            </p>
                        </td>
                    </tr>
                    <?php } else { ?>
                    <input type="hidden" name="cateOnlyAdultFl" value="<?php echo $data['cateOnlyAdultFl'];?>">
                    <input type="hidden" name="cateOnlyAdultDisplayFl" value="<?php echo $data['cateOnlyAdultDisplayFl'];?>">
                    <?php } ?>
					<?php if($data['parentPermission'] == false) { ?>
						<tr class="hideDivisionFl">
							<th>접근 권한</th>
							<td colspan="3">
								<?php foreach($group as $k =>$v) { ?>
									<label class="radio-inline"><input type="radio" name="catePermission" value="<?=$k?>" <?=gd_isset($checked['catePermission'][$k]);?> onclick="set_cate_permission(this.value)" /> <?=$v?></label>
								<?php } ?>
								<input type="button" value="회원등급 선택"  class="btn btn-sm btn-gray" id="btn_member_group" onclick="layer_register('member_group')" <?php if($data['catePermission'] !='2') echo "disabled"; ?>>
                                <label class="checkbox-inline">( <input type="checkbox" name="catePermissionDisplayFl" value="y" <?=gd_isset($checked['catePermissionDisplayFl']['y']);?> /> 접근불가 고객  <?=$info['cateTitle'];?> 노출함 )</label>
								<label class="checkbox-inline"><input type="checkbox" name="catePermissionSubFl" value="y" <?=gd_isset($checked['catePermissionSubFl']['y']);?> />하위  <?=$info['cateTitle'];?> 동일 적용</label>
								<div id="member_groupLayer" class="selected-btn-group <?=is_array($data['catePermissionGroup']) ? 'active' : ''?>">
									<?php if ($data['catePermissionGroup']) { ?>
										<h5>선택된 회원등급</h5>
										<?php foreach ($data['catePermissionGroup'] as $k => $v) { ?>

											<div id="info_member_group_<?= $k ?>" class="btn-group btn-group-xs">
												<input type="hidden" name="memberGroupNo[]" value="<?= $k ?>">
												<input type="hidden" name="memberGroupNoNm[]" value="<?= $v ?>">
												<span  class="btn"><?= $v ?></span>
												<button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#info_member_group_<?= $k ?>">삭제</button>
											</div>
										<?php }
									} ?>
								</div>
							</td>
						</tr>
                    <?php } else { ?>
                    <input type="hidden" name="catePermission" value="<?php echo $data['catePermission'];?>">
                    <?php foreach ($data['catePermissionGroup'] as $key => $value) { ?>
                    <input type="hidden" name="memberGroupNo[]" value="<?php echo $key;?>">
                    <?php } ?>
                    <?php } ?>
					<tr class="hideDivisionFl">
						<th>상품진열 타입</th>
						<td colspan="3"><input type="hidden" name="sortAutoFlChk"  value="<?=$data['sortAutoFl']?>" >
							<div style="float:left;">
								<label class="radio-inline"><input type="radio" name="sortAutoFl" value="y" <?=gd_isset($checked['sortAutoFl']['y']);?> /> 자동진열</label><br/>
								<label class="radio-inline"><input type="radio" name="sortAutoFl" value="n" <?=gd_isset($checked['sortAutoFl']['n']);?>  /> 수동진열</label>
							</div>
							<div style="margin-left:15px;float:left;">
								<label class="js-sorttype-y" <?php if($data['sortAutoFl'] =='n') { echo "style='display:none'"; } ?>><?=gd_select_box('sortType', 'sortType', $data['sortList'], null, $data['sortType'], null); ?></label>
								<label class="js-sorttype-n"  <?php if($data['sortAutoFl'] =='y') { echo "style='display:none'"; } ?>><?=gd_select_box('sortType', 'sortType', ['top'=>'신규 등록 상품 위로','bottom'=>'신규 등록 상품 아래로'], null, $data['sortType'], null); ?></label>
							</div>
							<div class="text-info mgt5" style="clear:both;">수동진열 : [<?=$info['cateTitle'];?> 페이지 상품진열]에서 진열순서를 별도로 설정할 수 있습니다.</div>
						</td>
					</tr>
					<tr class="hideDivisionFl">
						<th>PC쇼핑몰<br/>테마선택</th>
						<td><div class="form-inline">
								<input type="hidden" name="pcThemeCdChk"  value="<?=$data['pcThemeCd']?>" >
								<select name="pcThemeCd" onchange="viewThemeConfig('tbl_pcThemeInfo',this.value);" class="form-control">
									<?php foreach($pcThemeList as $k => $v) { ?>
										<option value="<?=$v['themeCd']?>" <?=gd_isset($selected['pcThemeCd'][$v['themeCd']]); ?> ><?=$v['themeNm']?></option>
									<?php } ?>
								</select>
								<input type="button" value="테마 등록" class="btn btn-sm btn-black" onclick="add_theme_popup('<?=$themeCate?>','pcThemeCd','n')"  /></div>
						</td>
						<th>모바일쇼핑몰<br/>테마선택</th>
						<td><div class="form-inline">
								<input type="hidden" name="mobileThemeCdChk"  value="<?=$data['mobileThemeCd']?>" >
								<select name="mobileThemeCd" onchange="viewThemeConfig('tbl_mobileThemeInfo',this.value);" class="form-control">
									<?php foreach($mobileThemeList as $k => $v) { ?>
										<option value="<?=$v['themeCd']?>" <?=gd_isset($selected['mobileThemeCd'][$v['themeCd']]); ?> ><?=$v['themeNm']?></option>
									<?php } ?>
								</select>
								<input type="button" value="테마 등록" class="btn btn-sm btn-black" onclick="add_theme_popup('<?=$themeCate?>','mobileThemeCd','y')"  /></div>
						</td>
					</tr>
				<?php }?>
			</table>
		</div>


		<div id="dv_divisionEx" class="display-none">
			<div class="table-title gd-help-manual">
				그룹(구분) <?=$info['cateTitle'];?> 활용예제
			</div>
			<table class="input width90p">
				<colgroup><col class="width50p" /><col class="width50p" /></colgroup>
				<tr>
					<td><img src="<?=PATH_ADMIN_GD_SHARE?>img/img_info_cate01.gif" alt="활용예제1" /></td>
					<td><img src="<?=PATH_ADMIN_GD_SHARE?>img/img_info_cate02.gif" alt="활용예제2" /></td>
				</tr>
			</table>
		</div>
		<?php
		// 구분자인 경우 출력 금지
		if ($data['divisionFl'] == 'n') {
		?>
		<div class="hideDivisionFl">
			<div class="table-title">
                <span class="gd-help-manual">선택된 PC쇼핑몰 테마 정보</span>
				<span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="<?=$toggleId?>PcTemaInfo"><span>닫힘</span></button></span>

			</div>
			<input type="hidden" id="depth-toggle-hidden-<?=$toggleId?>PcTemaInfo" value="<?=$toggle[$toggleId.'PcTemaInfo_'.$SessScmNo]?>">
			<div id="depth-toggle-line-<?=$toggleId?>PcTemaInfo" class="depth-toggle-line display-none"></div>
			<div id="depth-toggle-layer-<?=$toggleId?>PcTemaInfo">
				<table class="table table-cols" id="tbl_pcThemeInfo">
					<colgroup>
						<col style="width:160px;" />
						<col/>
						<col style="width:160px;" />
						<col/>
					</colgroup>
                    <tr>
                        <th >테마명</th>
                        <td  colspan="3"><span class="tbl_theme_themeNm"></span> <input type="button" value="수정" class="btn btn-sm btn-white js_tbl_theme_themeCd" data-code="" data-target="pcTheme" onclick="modify_theme_popup(this)"  /></td>
                    </tr>
                    <tr>
						<th >이미지 설정</th>
						<td  colspan="3" class="tbl_theme_imageCdNm">  </td>
					</tr>
					<tr>
						<th >상품 노출 개수</th>
						<td  colspan="3"  class="tbl_theme_cntNm">  </td>
					</tr>
					<tr>
						<th >품절상품 노출</th>
						<td  class="tbl_theme_soldOutFlNm">  </td>
						<th >품절상품 진열 </th>
						<td  class="tbl_theme_soldOutDisplayFlNm">  </td>
					</tr>
					<tr>
						<th >품절 아이콘 노출</th>
						<td  class="tbl_theme_soldOutIconFlNm">  </td>
						<th >아이콘 노출</th>
						<td  class="tbl_theme_iconFlNm">  </td>
					</tr>
					<tr>
						<th >노출항목 설정</th>
						<td  colspan="3"  class="tbl_theme_displayFieldNm">  </td>
					</tr>
					<tr>
						<th >디스플레이 유형</th>
						<td  colspan="3"  class="tbl_theme_displayTypeNm">  </td>
					</tr>
				</table>
			</div>

			<div class="table-title">
                <span class="gd-help-manual">선택된 모바일쇼핑몰 테마 정보</span>
				<span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="<?=$toggleId?>MobileThemeInfo"><span>닫힘</span></button></span>
			</div>

			<input type="hidden" id="depth-toggle-hidden-<?=$toggleId?>MobileThemeInfo" value="<?=$toggle[$toggleId.'MobileThemeInfo_'.$SessScmNo]?>">
			<div id="depth-toggle-line-<?=$toggleId?>MobileThemeInfo" class="depth-toggle-line display-none"></div>
			<div id="depth-toggle-layer-<?=$toggleId?>MobileThemeInfo">

				<table class="table table-cols" id="tbl_mobileThemeInfo">
					<colgroup>
						<col style="width:160px;" />
						<col/>
						<col style="width:160px;" />
						<col/>
					</colgroup>
                    <tr>
                        <th >테마명</th>
                        <td  colspan="3"><span class="tbl_theme_themeNm"></span> <input type="button" value="수정" class="btn btn-sm btn-white js_tbl_theme_themeCd" data-code="" data-target="mobileTheme" onclick="modify_theme_popup(this)"  /></td>
                    </tr>
					<tr>
						<th >이미지 설정</th>
						<td  colspan="3" class="tbl_theme_imageCdNm">  </td>
					</tr>
					<tr>
						<th >상품 노출 개수</th>
						<td  colspan="3"  class="tbl_theme_cntNm">  </td>
					</tr>
					<tr>
						<th >품절상품 노출</th>
						<td  class="tbl_theme_soldOutFlNm">  </td>
						<th >품절상품 진열 </th>
						<td  class="tbl_theme_soldOutDisplayFlNm">  </td>
					</tr>
					<tr>
						<th >품절 아이콘 노출</th>
						<td  class="tbl_theme_soldOutIconFlNm">  </td>
						<th >아이콘 노출</th>
						<td  class="tbl_theme_iconFlNm">  </td>
					</tr>
					<tr>
						<th >노출항목 설정</th>
						<td  colspan="3"  class="tbl_theme_displayFieldNm">  </td>
					</tr>
					<tr>
						<th >디스플레이 유형</th>
						<td  colspan="3"  class="tbl_theme_displayTypeNm">  </td>
					</tr>
				</table>
			</div>


			<?php if($data['parentRecomGoods'] == false) { ?>
			<div id="dv_cateConf">
				<div class="table-title">
                    <span class="gd-help-manual">추천상품 정보</span>
					<span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="<?=$toggleId?>GoodsMd"><span>닫힘</span></button></span>
				</div>

				<input type="hidden" id="depth-toggle-hidden-<?=$toggleId?>GoodsMd" value="<?=$toggle[$toggleId.'GoodsMd_'.$SessScmNo]?>">
				<div id="depth-toggle-line-<?=$toggleId?>GoodsMd" class="depth-toggle-line display-none"></div>
				<div id="depth-toggle-layer-<?=$toggleId?>GoodsMd">

					<table class="table table-cols">
						<colgroup><col style="width:160px;"  /><col/><col style="width:160px;"  /><col/></colgroup>
						<tr>
							<th>적용범위</th>
							<td colspan="3"><label class="checkbox-inline"><input type="checkbox" name="recomSubFl" value="y" <?=gd_isset($checked['recomSubFl']['y']);?> />하위  <?=$info['cateTitle'];?> 동일 적용</label></td>
						</tr>
						<tr>
							<th>PC쇼핑몰<br/>노출상태</th>
							<td colspan="3">
								<label class="radio-inline"><input type="radio" name="recomDisplayFl" value="y" <?=gd_isset($checked['recomDisplayFl']['y']);?> />노출함</label>
								<label class="radio-inline"><input type="radio" name="recomDisplayFl" value="n" <?=gd_isset($checked['recomDisplayFl']['n']);?> />노출안함</label>
							</td>
						</tr>
						<tr>
							<th>모바일쇼핑몰<br/>노출상태</th>
							<td colspan="3">
								<label class="radio-inline"><input type="radio" name="recomDisplayMobileFl" value="y" <?=gd_isset($checked['recomDisplayMobileFl']['y']);?> />노출함</label>
								<label class="radio-inline"><input type="radio" name="recomDisplayMobileFl" value="n" <?=gd_isset($checked['recomDisplayMobileFl']['n']);?> />노출안함</label>
							</td>
						</tr>
						</tr>
						<tr>
							<th>상품진열 타입</th>
							<td colspan="3">

								<div style="float:left;">
									<label class="radio-inline"><input type="radio" name="recomSortAutoFl" value="y" <?=gd_isset($checked['recomSortAutoFl']['y']);?> /> 자동진열</label><br/>
									<label class="radio-inline"><input type="radio" name="recomSortAutoFl" value="n" <?=gd_isset($checked['recomSortAutoFl']['n']);?>  /> 수동진열</label>
								</div>
								<div style="margin-left:15px;float:left;">
									<label class="js-sorttype-recom-y" <?php if($data['recomSortAutoFl'] =='n') { echo "style='display:none'"; } ?>><?=gd_select_box('recomSortType', 'recomSortType', $data['sortList'], null, $data['recomSortType'], null); ?></label>
								</div>
						</tr>
						<tr>
							<th>PC쇼핑몰<br/>테마선택</th>
							<td><div class="form-inline">
									<input type="hidden" name="recomPcThemeCdChk"  value="<?=$data['recomPcThemeCd']?>" >
									<select name="recomPcThemeCd" onchange="viewThemeConfig('tbl_recomPcThemeInfo',this.value);" class="form-control">
										<?php foreach($recomPcThemeList as $k => $v) { ?>
											<option value="<?=$v['themeCd']?>" <?=gd_isset($selected['recomPcThemeCd'][$v['themeCd']]); ?> ><?=$v['themeNm']?></option>
										<?php } ?>
									</select>
									<input type="button" value="테마 등록" class="btn btn-sm btn-black" onclick="add_theme_popup('D','recomPcThemeCd','n')" />
								</div>
							</td>
							<th>모바일쇼핑몰<br/>테마선택</th>
							<td><div class="form-inline">
									<input type="hidden" name=recomMobileThemeCdChk"  value="<?=$data['recomMobileThemeCd']?>" >
									<select name="recomMobileThemeCd" onchange="viewThemeConfig('tbl_recomMobileThemeInfo',this.value);" class="form-control">
										<?php foreach($recomMobileThemeList as $k => $v) { ?>
											<option value="<?=$v['themeCd']?>" <?=gd_isset($selected['recomMobileThemeCd'][$v['themeCd']]); ?> ><?=$v['themeNm']?></option>
										<?php } ?>
									</select>
									<input type="button" value="테마 등록" class="btn btn-sm btn-black" onclick="add_theme_popup('D','recomMobileThemeCd','y')"  /></div>
							</td>
						</tr>
					</table>
				</div>

				<div class="table-title">
                    <span class="gd-help-manual">선택된 PC쇼핑몰 추천상품 테마 정보</span>
					<span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="<?=$toggleId?>PcMdTemaInfo"><span>닫힘</span></button></span>
				</div>

				<input type="hidden" id="depth-toggle-hidden-<?=$toggleId?>PcMdTemaInfo" value="<?=$toggle[$toggleId.'PcMdTemaInfo_'.$SessScmNo]?>">
				<div id="depth-toggle-line-<?=$toggleId?>PcMdTemaInfo" class="depth-toggle-line display-none"></div>
				<div id="depth-toggle-layer-<?=$toggleId?>PcMdTemaInfo">

					<table class="table table-cols"  id="tbl_recomPcThemeInfo">
						<colgroup>
							<col style="width:160px;" />
							<col/>
							<col style="width:160px;"  />
							<col/>
						</colgroup>
                        <tr>
                            <th >테마명</th>
                            <td  colspan="3"><span class="tbl_theme_themeNm"></span> <input type="button" value="수정" class="btn btn-sm btn-white js_tbl_theme_themeCd" data-code="" data-target="recomPcTheme" onclick="modify_theme_popup(this)"  /></td>
                        </tr>
						<tr>
							<th >이미지 설정</th>
							<td  colspan="3" class="tbl_theme_imageCdNm">  </td>
						</tr>
						<tr>
							<th >상품 노출 개수</th>
							<td  colspan="3"  class="tbl_theme_cntNm">  </td>
						</tr>
						<tr>
							<th >품절상품 노출</th>
							<td  class="tbl_theme_soldOutFlNm">  </td>
							<th >품절상품 진열 </th>
							<td  class="tbl_theme_soldOutDisplayFlNm">  </td>
						</tr>
						<tr>
							<th >품절 아이콘 노출</th>
							<td  class="tbl_theme_soldOutIconFlNm">  </td>
							<th >아이콘 노출</th>
							<td  class="tbl_theme_iconFlNm">  </td>
						</tr>
						<tr>
							<th >노출항목 설정</th>
							<td  colspan="3"  class="tbl_theme_displayFieldNm">  </td>
						</tr>
						<tr>
							<th >갤러리형</th>
							<td  colspan="3"  class="tbl_theme_displayTypeNm">  </td>
						</tr>
					</table>
				</div>


				<div class="table-title">
                    <span class="gd-help-manual">선택된 모바일쇼핑몰 추천상품 테마 정보</span>
					<span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="<?=$toggleId?>MobileMdTemaInfo"><span>닫힘</span></button></span>
				</div>

				<input type="hidden" id="depth-toggle-hidden-<?=$toggleId?>MobileMdTemaInfo" value="<?=$toggle[$toggleId.'MobileMdTemaInfo_'.$SessScmNo]?>">
				<div id="depth-toggle-line-<?=$toggleId?>MobileMdTemaInfo" class="depth-toggle-line display-none"></div>
				<div id="depth-toggle-layer-<?=$toggleId?>MobileMdTemaInfo">

					<table class="table table-cols"  id="tbl_recomMobileThemeInfo">
						<colgroup>
							<col style="width:160px;" />
							<col/>
							<col style="width:160px;"  />
							<col/>
						</colgroup>
                        <tr>
                            <th >테마명</th>
                            <td  colspan="3"><span class="tbl_theme_themeNm"></span> <input type="button" value="수정" class="btn btn-sm btn-white js_tbl_theme_themeCd" data-code="" data-target="recomMobileTheme" onclick="modify_theme_popup(this)"  /></td>
                        </tr>
						<tr>
							<th >이미지 설정</th>
							<td  colspan="3" class="tbl_theme_imageCdNm">  </td>
						</tr>
						<tr>
							<th >상품 노출 개수</th>
							<td  colspan="3"  class="tbl_theme_cntNm">  </td>
						</tr>
						<tr>
							<th >품절상품 노출</th>
							<td  class="tbl_theme_soldOutFlNm">  </td>
							<th >품절상품 진열 </th>
							<td  class="tbl_theme_soldOutDisplayFlNm">  </td>
						</tr>
						<tr>
							<th >품절 아이콘 노출</th>
							<td  class="tbl_theme_soldOutIconFlNm">  </td>
							<th >아이콘 노출</th>
							<td  class="tbl_theme_iconFlNm">  </td>
						</tr>
						<tr>
							<th >노출항목 설정</th>
							<td  colspan="3"  class="tbl_theme_displayFieldNm">  </td>
						</tr>
						<tr>
							<th >갤러리형</th>
							<td  colspan="3"  class="tbl_theme_displayTypeNm">  </td>
						</tr>
					</table>
				</div>

				<div class="table-title">
                    <span class="gd-help-manual">선택된 추천상품</span>
					<span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="<?=$toggleId?>SelectMd"><span>닫힘</span></button></span>
				</div>

				<input type="hidden" id="depth-toggle-hidden-<?=$toggleId?>SelectMd" value="<?=$toggle[$toggleId.'SelectMd_'.$SessScmNo]?>">
				<div id="depth-toggle-line-<?=$toggleId?>SelectMd" class="depth-toggle-line display-none"></div>
				<div id="depth-toggle-layer-<?=$toggleId?>SelectMd">
					<div class="js-recom-auto-goods" <?php if($data['recomSortAutoFl'] =='n') { echo "style='display:none'"; }  ?>>
						<table class="table table-rows"  id="goodsLayer"  >
							<tr>
								<th class="center width-3xs "><input type="checkbox" id="allCheck" value="y" onclick="check_toggle(this.id,'recom_goods_');" /></th>
								<th class="center width-3xs ">번호</th>
								<th class="width-2xs">이미지</th>
								<th >상품명</th>
								<th class="center width10p">판매가</th>
								<th class="center width10p" >공급사</th>
								<th class="center width5p">재고</th>
								<th class="center width5p">품절상태</th>
								<th class="center width10p">PC쇼핑몰 노출상태</th>
								<th class="center width10p">모바일쇼핑몰 노출상태</th>
							</tr>
							<?php if ($data['recomGoodsNo'] && $data['recomSortAutoFl'] =='y' ) {
								foreach ($data['recomGoodsNo'] as $key => $val) {

									list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);

									$goodsDisplay = $goodsDisplayMobile = '노출함';
									if ($val['goodsDisplayFl'] != 'y') $goodsDisplay = '노출안함';
									if ($val['goodsDisplayMobileFl'] != 'y') $goodsDisplayMobile = '노출안함';

									?>

									<tr id="info_goods_<?=$val['goodsNo'];?>">
										<td class="center"><input type="hidden" name="recomGoodsNo[]" value="<?=$val['goodsNo']?>" /><input type="checkbox" id="recom_goods_<?=$val['goodsNo'];?>" name="recom_goods_<?=$val['goodsNo'];?>" value="<?=$val['goodsNo'];?>" /></td>
										<td class="center"  id="recom_number_<?=$val['goodsNo'];?>"><?=number_format($key+1);?></td>
										<td >
											<div class="width-2xs">
												<?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank', 'id="goodsImage_'.$val['goodsNo'].'"');?>
											</div>
										</td>
										<td>
                                            <a href="../goods/goods_register.php?goodsNo=<?=$val['goodsNo'];?>" target="_blank"><?=$val['goodsNm'];?></a>
											<input type="hidden" id="goodsNm_<?=$val['goodsNo'];?>" value="<?=gd_htmlspecialchars($val['goodsNm']);?>" />
										</td>
										<td class="center"><?=number_format($val['goodsPrice']);?> 원</td>
										<td><?=$val['scmNm'];?></td>
										<td class="center"><?=$totalStock;?></td>
										<td class="center"><?=$stockText ?></td>
										<td class="center"><?=$goodsDisplay ?></td>
										<td class="center"><?=$goodsDisplayMobile ?></td>
									</tr>
								<?php }
							} else {  ?>
								<tfoot><tr><td class="no-data" colspan="10">등록된 상품이 없습니다.</td></tr></tfoot>
							<?php } ?>
						</table>
					</div>
					<div class="js-recom-manual-goods" <?php if($data['recomSortAutoFl'] =='y') { echo "style='display:none'"; }  ?>>
						<table cellpadding="0" cellpadding="0" width="100%" id="tbl_add_goods_set" class="table table-rows">
							<thead>
							<tr>
								<th class="center width-3xs "><input type="checkbox" id="allCheck" value="y" class="js-checkall" data-target-name="itemGoodsNo[]"/></th>
								<th class="center width-3xs ">번호</th>
								<th class="width-2xs">이미지</th>
								<th >상품명</th>
								<th class="center width10p">판매가</th>
								<th class="center width10p" >공급사</th>
								<th class="center width5p">재고</th>
								<th class="center width5p">품절상태</th>
								<th class="center width10p">PC쇼핑몰 노출상태</th>
								<th class="center width10p">모바일쇼핑몰 노출상태</th>
							</tr>
							</thead>

							<tbody id="add_goods0"  class="active">
							<?php if ($data['recomGoodsNo'] && $data['recomSortAutoFl'] =='n') {

								$arrGoodsDisplay = array('y' => '노출함', 'n' => '노출안함');
								$arrGoodsSell = array('y' => ' 판매함', 'n' => '판매안함');

								foreach ($data['recomGoodsNo'] as $key => $val) {

									list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);

									?>

									<tr id="tbl_add_goods_<?=$val['goodsNo']; ?>" <?php if ($data['fixGoodsNo'] && in_array($val['goodsNo'], array_values($data['fixGoodsNo']))) { ?>style='background:#d3d3d3' class="add_goods_fix" <?php } else { ?>class="add_goods_free" <?php } ?>>
										<td class="center">
											<input type="hidden" name="itemGoodsNm[]" value="<?= strip_tags($val['goodsNm']) ?>"/>
											<input type="hidden" name="itemGoodsPrice[]" value="<?=gd_currency_display($val['goodsPrice']) ?>"/>
											<input type="hidden" name="itemScmNm[]" value="<?= $val['scmNm'] ?>"/>
											<input type="hidden" name="itemTotalStock[]" value="<?= $val['totalStock'] ?>"/>
											<input type="hidden" name="itemImage[]" value="<?= rawurlencode(gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank')); ?>"/>
											<input type="hidden" name="itemBrandNm[]" value="<?= gd_isset($val['brandNm']) ?>"/>
											<input type="hidden" name="itemMakerNm[]" value="<?= gd_isset($val['makerNm']) ?>"/>
											<input type="hidden" name="itemSoldOutFl[]" value="<?= gd_isset($val['soldOutFl']) ?>"/>
											<input type="hidden" name="itemStockFl[]" value="<?= gd_isset($val['stockFl']) ?>"/>
											<input type="hidden" name="itemGoodsDisplayFl[]" value="<?=gd_isset($val['goodsDisplayFl'])?>" />
											<input type="hidden" name="itemGoodsDisplayMobileFl[]" value="<?=gd_isset($val['goodsDisplayMobileFl'])?>" />
											<input type="hidden" name="itemGoodsSellFl[]" value="<?=gd_isset($val['goodsSellFl'])?>" />
											<input type="hidden" name="itemGoodsSellMobileFl[]" value="<?=gd_isset($val['goodsSellMobileFl'])?>" />
											<input type="checkbox" name="itemGoodsNo[]" id="layer_goods_<?=$val['goodsNo']; ?>" value="<?=$val['goodsNo']; ?>"/>
										</td>
										<td class="center number addGoodsNumber_<?=$val['goodsNo']; ?>"><?=number_format($key+1);?></td>
										<td class="center"><?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?></td>
										<td>
                                            <a href="../goods/goods_register.php?goodsNo=<?=$val['goodsNo'];?>" target="_blank"><?=$val['goodsNm'];?></a>
											<input type="hidden" name="goodsNoData[]" value="<?= $val['goodsNo'] ?>"/>
											<input type="checkbox" name="sortFix[]" class="layer_sort_fix_<?=$val['goodsNo']; ?>" value="<?=$val['goodsNo']; ?>" <?php if ($data['fixGoodsNo'] && in_array($val['goodsNo'], $data['fixGoodsNo'])) {
												echo "checked='true'";
											} ?> style="display:none">
										</td>
										<td class="center"><?=gd_currency_display($val['goodsPrice']); ?></td>
										<td class="center"><?=$val['scmNm']; ?></td>
										<td class="center"><?= $totalStock ?></td>
										<td class="center"><?= $stockText ?></td>
										<td class="center js-goodschoice-hide"><?=$arrGoodsDisplay[$val['goodsDisplayFl']]; ?></td>
										<td class="center js-goodschoice-hide"><?=$arrGoodsDisplay[$val['goodsDisplayMobileFl']]; ?></td>
									</tr>
									<?php
								} ?>


							<?php } else {  ?>
								<tr id="tbl_add_goods_tr_none"><td colspan="11" class="no-data">선택된 상품이 없습니다.</td></tr>
							<?php }
							?>
							</tbody>
						</table>
					</div>

					<div class="table-action">
						<div class="pull-left">
							<button type="button" class="btn  btn-white"  onclick="delete_option()">선택 삭제</button>
						</div>
						<div class="pull-right">
							<button type="button" class="btn btn-white" onclick="select_recom_goods()">상품 선택</button>
						</div>
					</div>

					<br/>
				</div>

				<?php } ?>
				<div class="table-title">
                    <span class="gd-help-manual"><?=$info['cateTitle'];?> 네비게이션 상단 영역 꾸미기</span>
					<span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="<?=$toggleId?>NeviGnb"><span>닫힘</span></button></span>
				</div>
				<input type="hidden" id="depth-toggle-hidden-<?=$toggleId?>NeviGnb" value="<?=$toggle[$toggleId.'NeviGnb_'.$SessScmNo]?>">
				<div id="depth-toggle-line-<?=$toggleId?>NeviGnb" class="depth-toggle-line display-none"></div>
				<div id="depth-toggle-layer-<?=$toggleId?>NeviGnb">
                    <ul class="nav nav-tabs nav-tabs-sm">
                        <li class="active display-inline" id="btnDescriptionNevi" data-type="pc" data-target="DescriptionNevi">
                            <a href="#textareaDescriptionNevi">PC쇼핑몰 상세 설명</a></li>
                        <li class="nav-none display-inline" id="btnDescriptionNeviMobile" data-type="mobile" data-target="DescriptionNevi">
                            <a href="#textareaDescriptionNeviMobile">모바일쇼핑몰 상세 설명</a></li>
                        <li style="padding-left:10px;padding-top:5px"> <label class="checkbox-inline"><input type="checkbox" value="y"  <?=gd_isset($checked['cateHtml1SameFl']['y']); ?> name="cateHtml1SameFl" data-target="DescriptionNevi"/> PC/모바일 상세설명 동일사용</label></li>
                    </ul>
                    <div id="textareaDescriptionNevi">
                        <textarea name="cateHtml1" rows="3" style="width:100%; height:400px;" id="editor" class="form-control"><?=$data['cateHtml1'];?></textarea>
                    </div>
                    <div id="textareaDescriptionNeviMobile">
                        <textarea name="cateHtml1Mobile" rows="3" style="width:100%; height:400px;" id="editor4" class="form-control"><?=$data['cateHtml1Mobile'];?></textarea>
                    </div>
				</div>

				<div class="table-title mgt35">
                    <span class="gd-help-manual"><?=$info['cateTitle'];?> 추천 상품 상단 영역 꾸미기</span>
					<span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="<?=$toggleId?>MdGnb"><span>닫힘</span></button></span>
				</div>
				<input type="hidden" id="depth-toggle-hidden-<?=$toggleId?>MdGnb" value="<?=$toggle[$toggleId.'MdGnb_'.$SessScmNo]?>">
				<div id="depth-toggle-line-<?=$toggleId?>MdGnb" class="depth-toggle-line display-none"></div>
				<div id="depth-toggle-layer-<?=$toggleId?>MdGnb">
                    <ul class="nav nav-tabs nav-tabs-sm">
                        <li class="active display-inline" id="btnDescriptionRecom" data-type="pc" data-target="DescriptionRecom">
                            <a href="#textareaDescriptionRecom">PC쇼핑몰 상세 설명</a></li>
                        <li class="nav-none display-inline" id="btnDescriptionRecomMobile" data-type="mobile" data-target="DescriptionRecom">
                            <a href="#textareaDescriptionRecomMobile">모바일쇼핑몰 상세 설명</a></li>
                        <li style="padding-left:10px;padding-top:5px"> <label class="checkbox-inline"><input type="checkbox" value="y"  <?=gd_isset($checked['cateHtml2SameFl']['y']); ?> name="cateHtml2SameFl" data-target="DescriptionRecom"/> PC/모바일 상세설명 동일사용</label></li>
                    </ul>
                    <div id="textareaDescriptionRecom">
                        <textarea name="cateHtml2" rows="3" style="width:100%; height:400px;" id="editor2" class="form-control"><?=$data['cateHtml2'];?></textarea>
                    </div>
                    <div id="textareaDescriptionRecomMobile">
                        <textarea name="cateHtml2Mobile" rows="3" style="width:100%; height:400px;" id="editor5" class="form-control"><?=$data['cateHtml2Mobile'];?></textarea>
                    </div>
				</div>


				<div class="table-title mgt35">
                    <span class="gd-help-manual"><?=$info['cateTitle'];?> 리스트 상단 영역 꾸미기</span>
					<span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="<?=$toggleId?>ListGnb"><span>닫힘</span></button></span>
				</div>
				<input type="hidden" id="depth-toggle-hidden-<?=$toggleId?>ListGnb" value="<?=$toggle[$toggleId.'ListGnb_'.$SessScmNo]?>">
				<div id="depth-toggle-line-<?=$toggleId?>ListGnb" class="depth-toggle-line display-none"></div>
				<div id="depth-toggle-layer-<?=$toggleId?>ListGnb">
                    <ul class="nav nav-tabs nav-tabs-sm">
                        <li class="active display-inline" id="btnDescriptionList" data-type="pc" data-target="DescriptionList">
                            <a href="#textareaDescriptionList">PC쇼핑몰 상세 설명</a></li>
                        <li class="nav-none display-inline" id="btnDescriptionListMobile" data-type="mobile" data-target="DescriptionList">
                            <a href="#textareaDescriptionListMobile">모바일쇼핑몰 상세 설명</a></li>
                        <li style="padding-left:10px;padding-top:5px"> <label class="checkbox-inline"><input type="checkbox" value="y"  <?=gd_isset($checked['cateHtml3SameFl']['y']); ?> name="cateHtml3SameFl" data-target="DescriptionList"/> PC/모바일 상세설명 동일사용</label></li>
                    </ul>
                    <div id="textareaDescriptionList">
                        <textarea name="cateHtml3" rows="3" style="width:100%; height:400px;" id="editor3" class="form-control"><?=$data['cateHtml3'];?></textarea>
                    </div>
                    <div id="textareaDescriptionListMobile">
                        <textarea name="cateHtml3Mobile" rows="3" style="width:100%; height:400px;" id="editor6" class="form-control"><?=$data['cateHtml3Mobile'];?></textarea>
                    </div>
				</div>


                <div class="table-title mgt35">
                    <span class="gd-help-manual"><?=$info['cateTitle'];?> 개별 SEO 태그 설정</span>
                    <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="seoTag"><span>닫힘</span></button></span>
                    <div class="pull-right form-inline">
                        <button type="button" class="btn btn-sm btn-gray js-code-view" data-target="<?=$info['cateType']  == 'goods' ? 'category' :  'brand'; ?>">치환코드 보기</button>
                    </div>
                </div>
                <?php include($seoTagFrm); ?>

			</div>
			<?php
			}
			?>
		</div>

	</form>
<?php }?>

<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript">
	var uploadImages = [];

	function addUploadImages(data) {
		uploadImages.push(data);
	}

	function cleanUploadImages() {
		uploadImages = null;
	}

	var oEditors = [];
	nhn.husky.EZCreator.createInIFrame({
		oAppRef: oEditors,
		elPlaceHolder: "editor",
		sSkinURI: "<?=PATH_ADMIN_GD_SHARE?>script/smart/SmartEditor2Skin.php",
		htParams: {
			bUseToolbar: true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
			bUseVerticalResizer: true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
			bUseModeChanger: true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
			//aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
			fOnBeforeUnload: function () {
				//alert("완료!");
			}
		}, //boolean
		fOnAppLoad: function () {
			//예제 코드
			//oEditors.getById["editor"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
			toggleSelectionDisplay('<?=$toggleId?>NeviGnb');
		},
		fCreator: "createSEditor"
	});
    nhn.husky.EZCreator.createInIFrame({
        oAppRef: oEditors,
        elPlaceHolder: "editor4",
        sSkinURI: "<?=PATH_ADMIN_GD_SHARE?>script/smart/SmartEditor2Skin.php",
        htParams: {
            bUseToolbar: true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
            bUseVerticalResizer: true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
            bUseModeChanger: true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
            //aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
            fOnBeforeUnload: function () {
                //alert("완료!");
            }
        }, //boolean
        fOnAppLoad: function () {
            //예제 코드
            //oEditors.getById["editor"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
            $("#textareaDescriptionNeviMobile").hide();
            toggleSelectionDisplay('<?=$toggleId?>NeviGnb');
        },
        fCreator: "createSEditor4"
    });

	nhn.husky.EZCreator.createInIFrame({
		oAppRef: oEditors,
		elPlaceHolder: "editor2",
		sSkinURI: "<?=PATH_ADMIN_GD_SHARE?>script/smart/SmartEditor2Skin.php",
		htParams: {
			bUseToolbar: true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
			bUseVerticalResizer: true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
			bUseModeChanger: true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
			//aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
			fOnBeforeUnload: function () {
				//alert("완료!");
			}
		}, //boolean
		fOnAppLoad: function () {
			//예제 코드
			//oEditors.getById["editor"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
			toggleSelectionDisplay('<?=$toggleId?>MdGnb');
		},
		fCreator: "createSEditor2"
	});
    nhn.husky.EZCreator.createInIFrame({
        oAppRef: oEditors,
        elPlaceHolder: "editor5",
        sSkinURI: "<?=PATH_ADMIN_GD_SHARE?>script/smart/SmartEditor2Skin.php",
        htParams: {
            bUseToolbar: true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
            bUseVerticalResizer: true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
            bUseModeChanger: true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
            //aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
            fOnBeforeUnload: function () {
                //alert("완료!");
            }
        }, //boolean
        fOnAppLoad: function () {
            //예제 코드
            //oEditors.getById["editor"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
            $("#textareaDescriptionRecomMobile").hide();
            toggleSelectionDisplay('<?=$toggleId?>MdGnb');
        },
        fCreator: "createSEditor5"
    });

	nhn.husky.EZCreator.createInIFrame({
		oAppRef: oEditors,
		elPlaceHolder: "editor3",
		sSkinURI: "<?=PATH_ADMIN_GD_SHARE?>script/smart/SmartEditor2Skin.php",
		htParams: {
			bUseToolbar: true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
			bUseVerticalResizer: true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
			bUseModeChanger: true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
			//aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
			fOnBeforeUnload: function () {
				//alert("완료!");
			}
		}, //boolean
		fOnAppLoad: function () {
			//예제 코드
			//oEditors.getById["editor"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
			toggleSelectionDisplay('<?=$toggleId?>ListGnb');
		},
		fCreator: "createSEditor3"
	});
    nhn.husky.EZCreator.createInIFrame({
        oAppRef: oEditors,
        elPlaceHolder: "editor6",
        sSkinURI: "<?=PATH_ADMIN_GD_SHARE?>script/smart/SmartEditor2Skin.php",
        htParams: {
            bUseToolbar: true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
            bUseVerticalResizer: true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
            bUseModeChanger: true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
            //aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
            fOnBeforeUnload: function () {
                //alert("완료!");
            }
        }, //boolean
        fOnAppLoad: function () {
            //예제 코드
            //oEditors.getById["editor"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
            $("#textareaDescriptionListMobile").hide();
            toggleSelectionDisplay('<?=$toggleId?>ListGnb');
        },
        fCreator: "createSEditor6"
    });
</script>
