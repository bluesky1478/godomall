<script type="text/javascript">
<!--
/**
 * 에디터 callback
 *
 * @author sj, artherot
 */
function callback_editor(response, status, xhr)
{
	var browserVer	= parseInt(jQuery.browser.version);

	if (status != 'error' && browserVer != '6' && browserVer != '7') {
		setup_tinyMce();
	}
}

$(document).ready(function(){


	// 카테고리 트리의 글자수 및 카테고리 차수 설정
	$.tree.reference('categoryTree').settings.rules.valLength	= <?=$data['nameLength'];?>;
	$.tree.reference('categoryTree').settings.rules.max_depth	= <?=$data['cateDepth'];?>;
	//$.tree.reference('categoryTree').settings.rules.cb_select	= callback_editor;

	config_load();
})

/**
 * 1차 카테고리 만들기 화면
 *
 * @author artherot
 */
function config_load()
{
	$('#categoryDetail').load('category_config.php?cateType=<?=$data['cateType'];?>', { mode:'register'}, $.tree.reference('categoryTree').settings.rules.cb_select);

}

/**
 * 상품 자동 매핑 처리
 */
jQuery.fn.goodsMappingProgress = function() {
	var obj	= $(this);
	obj.css('background-color', '#F0F0EE');
	var loadingText	 = '<div id="goodsMappingLoading" style="position:relative;">';
	loadingText		+= '<div style="position:absolute;top:50px;left:20px;border:solid 3px #676767;background-color:#FFFFFF;width:160px;text-align:center;color:#0079b6;">';
	loadingText		+= '<div>상품 매핑 중입니다.</div>';
	loadingText		+= '</div>';
	loadingText		+= '</div>';
	obj.prepend(loadingText);
	$.post('./category_ps.php?cateType=<?=$data['cateType'];?>', {'mode':'goods_mapping_layer'}, function(data) {
		if (data) {
			alert(data);
		}
		$("#goodsMappingLoading").remove();
		obj.css('background-color', '');
	});
};

$(document).ready(function(){
	$('.save').click(function () {
		$("#frmCateDetail").submit();
	});
	$('#informationTd').append($('.information'));

    // https://clipboardjs.com
    var clipboard = new Clipboard('.js-clipboard');
    clipboard.on('success', function (e){
        var title = $(e.trigger).attr('title') == undefined ? '' : $(e.trigger).attr('title');
        alert('[' + title + ' ' + $(e.trigger).data('category-title') + ' 쇼핑몰 주소] 정보를 클립보드에 복사했습니다.\n<code>Ctrl+V</code>를 이용해서 사용하세요.');
        e.clearSelection();
    });
    clipboard.on('error', function (e) {
        console.error('Action:', e.action);
        console.error('Trigger:', e.trigger);
    });
});


//-->
</script>

<div>


	<div class="page-header js-affix">
		<h3><?=$data['cateTitle'];?> 관리 </h3>
		<div class="btn-group">
			<input type="submit" value="저장" class="btn btn-red save" />
		</div>
	</div>


	<div class="mgt0">
		<div id="goodsMapping" class="list<?php if (empty($mapping)) { echo ' display-none'; }?>">
			<form id="frmMapping" name="frmMapping" action="./category_ps.php?cateType=<?=$data['cateType'];?>" method="post" target="ifrmProcess">
			<input type="hidden" name="cateType" value="<?=$data['cateType'];?>" />
			<input type="hidden" name="mode" value="goods_mapping" />
			<input type="hidden" id="mappingMode" value="<?=gd_isset($mapping);?>" />
			<span class="button red"><input type="submit" value="상품 매핑" /></span>
			</form>
		</div>
	</div>

	<div class="pull-left form-inline">
		<input type="button" value="1차 <?=$data['cateTitle'];?> 생성" onclick="$.tree.reference('categoryTree').deselect_check();config_load();" class="btn btn-white btn-sm" />
		<input type="button" value="하위 <?=$data['cateTitle'];?> 생성" onclick="$.tree.reference('categoryTree').create_check();" class="btn btn-white btn-sm" />
		<div class="category_panel">
			<div class="menu">
				<span class="category_icon"><img src="<?=PATH_ADMIN_GD_SHARE;?>img/ico_tree_<?=$data['cateType'];?>.gif" alt="<?=$data['cateTitle'];?>" /></span>
				<span class="division"></span>
				<a rel="open_all" class="open_all" title="전체열기" onclick="$.tree.reference('categoryTree').open_all();"></a>
				<a rel="close_all" class="close_all" title="전체닫기" onclick="$.tree.reference('categoryTree').close_all();"></a>
				<a rel="refresh" class="refresh" title="새로고침" onclick="$.tree.reference('categoryTree').refresh();"></a>
				<a rel="rename" class="rename" title="<?=$data['cateTitle'];?> 이름변경" onclick="$.tree.reference('categoryTree').rename_check();"></a>
				<a rel="remove" class="remove" title="<?=$data['cateTitle'];?> 삭제" onclick="$.tree.reference('categoryTree').remove_check();"></a>
			</div>
			<div class="tree" id="categoryTree"></div>
		</div>
	</div>
	<div class="category-detail-info" id="categoryDetail"></div>
</div>
<table style="width: 100%"><tr><td id="informationTd"></td></tr></table>
