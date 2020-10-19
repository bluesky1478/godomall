/**
 * 카테고리 Tree (used jsTree 0.9.9a2)
 *
 * @author artherot
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */

$(function () {

	var param	= jQuery.url.param('cateType');
	var cateCd	= jQuery.url.param('cateCd');

	// 카테고리 종류 체크
	if (typeof param == 'undefined') {
		var cateText	= '카테고리';
		var parameter	= '';
		var detailFile	= 'category_config.php';
	} else if (typeof param == 'string') {
		var parameter	= '?cateType='+param;
		var detailFile	= 'category_config.php'+parameter;
		if (param == 'brand') {
			var cateText	= '브랜드';
		}
	}

	catePanel = {};
	catePanel.jsTree = $.tree.create();
	catePanel.jsTree.init($('#categoryTree'),{
		data	: {
			type	: 'json',
			async	: true,
			opts	: {
				async	: true,
				method	: 'POST',
				url		: 'category_tree_json.php'+parameter
			}
		},
		ui		: {
			animation	: 0,
			theme_name	: 'summer'
		},
		rules	: {
			valLength	: 10,
			use_max_children	: true,
			use_max_depth		: true,
			max_children: -1,
			max_depth	: 4,
			cb_select	: null // 선택시 콜백함수.
		},
		lang : {
			new_node	: '새 '+cateText,
			loading		: 'Loading ...'
		},
		plugins	: {
			contextmenu		: {
				items : {
					create : {
						label	: '하위 '+cateText+' 생성',
						visible	: function (NODE, TREE_OBJ) {
							if($(NODE).attr('linkType') == 'division') {
								return -1;
							}
							if($(NODE).attr('rel') == 'end') {
								return false;
							}
							if(NODE.length != 1) {
								return 0;
							}
							return TREE_OBJ.check("creatable", NODE);
						}
					},
					rename : {
						label	: cateText+' 이름 변경'
					},
					remove : {
						label	: cateText+' 삭제'
					}
				}
			}
		},
		callback : {
			// 초기 로딩시
			onload		: function(TREE_OBJ) {

				/*
				if (typeof param != 'undefined' && (typeof cateCd == 'undefined' ||  cateCd =='')) {
					TREE_OBJ.open_all();
				} 전체 열리지 않도록 */

				if(cateCd) TREE_OBJ.select_cate(cateCd,1);

			},
			beforedata: function(NODE, TREE_OBJ) {
				return { cateCd : $(NODE).attr('id') || '' };
			},
			// 카테고리 생성시
			oncreate	: function(NODE,REF_NODE,TYPE,TREE_OBJ,RB) {
				var cateNm	= TREE_OBJ.get_text(NODE).trim();
				$.post('category_ps.php'+parameter,{ mode:'subcreate', cateCd:REF_NODE.id, cateNm:cateNm, createType:TYPE }, function(data){
					NODE.id = data;
				});
			},
			// 카테고리 이름 변경시
			onrename	: function(NODE,TREE_OBJ,RB) {
				var cateNm	= TREE_OBJ.get_text(NODE).trim();
				var chkval	= cateNm.trim();
				if(chkval.length > parseInt(TREE_OBJ.settings.rules.valLength)){
					alert('※ '+cateText+' 명은 '+TREE_OBJ.settings.rules.valLength+'자 이상 작성 하실수 없습니다.\n해당 '+cateText+'명을 수정해주세요.');
					catePanel.jsTree.rename();
					return false;
				}
				$.post('category_ps.php'+parameter,{ mode:'rename', cateCd:NODE.id, cateNm:cateNm }, function(data){
					TREE_OBJ.refresh();
				});
			},
			// 카테고리 삭제 전 처리
			beforedelete: function (NODE,TREE_OBJ) {
				if ($('#mappingMode').val() == 'm') {
					alert('처리해야할 이동 관련 상품 매핑 작업이 남아 있습니다. "상품매핑"을 먼저 실행후 삭제 처리 하시기 바랍니다.');
					return false;
				} else {
					var cateNm	= TREE_OBJ.get_text(NODE).trim();
					if($(NODE).attr('linkType') == 'division') {
						return confirm('※ '+cateNm+'\' 그룹(구분) 카테고리를 정말로 삭제 하시겠습니까?');
					} else {
						return confirm('[\''+cateNm+'\' '+cateText+' 삭제]\n\n※ '+cateText+' 삭제시 하위 '+cateText+'도 삭제가 됩니다.\n※ '+cateText+'를 삭제하면 복구가 불가능 합니다.\n※ '+cateText+'에 연결된 상품은 삭제 되지 않습니다.\n\n\''+cateNm+'\' 카테고리를 정말로 삭제 하시겠습니까?');
					}
				}
			},
			// 카테고리 삭제시
			ondelete	: function(NODE,TREE_OBJ,RB) {

				$.post('category_ps.php'+parameter,{ mode:'delete', cateCd:NODE.id }, function(data){
					if(data){
						alert(cateText+' 삭제에 실패하였습니다. 다시 확인 해주시기 바랍니다.');
					} else {
						$('#categoryTree').goodsMappingProgress();
						//alert('상품과 '+cateText+'의 관계를 위해 "상품 매핑"을 실행하여 주십시요.\n\n - 상단 "상품 매핑" 버튼을 실행해 주세요.\n - "상품매핑" 작업은 삭제 작업 완료후 일괄 처리도 가능합니다.');
						//$('#goodsMapping').slideDown('slow');
						//$('#mappingMode').val('d');
					}
					TREE_OBJ.refresh();
				});
			},
			// 카테고리 이동 전 처리
			beforemove	: function(NODE,REF_NODE,TYPE,TREE_OBJ) {
				if ($('#mappingMode').val() == 'd') {
					alert('처리해야할 삭제 관련 상품 매핑 작업이 남아 있습니다. "상품매핑"을 먼저 실행후 이동 처리 하시기 바랍니다.');
					return false;
				} else {
					if($(REF_NODE).attr('linkType') == 'division') {
						if($(NODE).attr('linkType') == 'division') {
							alert('그룹(구분) '+cateText+'에는 하위 '+cateText+'를 둘 수 없습니다.')
						} else {
							alert('그룹(구분) '+cateText+'에 이동하실 수 없습니다.')
						}
						return false;
					}
					return true;
				}
			},
			// 카테고리 이동시
			onmove		: function(NODE,REF_NODE,TYPE,TREE_OBJ,RB) {
				$.post('category_ps.php'+parameter,{ mode:'move', cateCd:NODE.id, targetCateCd:REF_NODE.id, moveLoc:TYPE }, function(data){
					if(data == 0){
						alert(cateText+' 이동에 실패 하였습니다.');
					} else {
						$('#categoryTree').goodsMappingProgress();
						//alert('상품과 '+cateText+'의 관계를 위해 "상품 매핑"을 실행하여 주십시요.\n\n - 상단 "상품 매핑" 버튼을 실행해 주세요.\n - "상품매핑" 작업은 이동 작업 완료후 일괄 처리도 가능합니다.');
						//$('#goodsMapping').slideDown('slow');
						//$('#mappingMode').val('m');
					}
					TREE_OBJ.refresh();
				});
			},
			// 해당 카테고리 선택시
			onselect	: function(NODE,TREE_OBJ) {
				var cateNm	= TREE_OBJ.get_text(NODE).trim();
				if(NODE.id){
					$('#categoryDetail').load(detailFile, { mode:'modify', cateCd:NODE.id }, TREE_OBJ.settings.rules.cb_select);
				}
			}
		}
	});

	// 하위 카테고리 생성 체크
	catePanel.jsTree.create_check = function () {
		var ref_node	= this.selected;
		if(!this.check('clickable', ref_node)){
			alert('하위 '+cateText+'를 생성할 부모 '+cateText+'를 선택하세요!')
			return false;
		}
		if($(ref_node).attr('linkType') == 'division') {
			alert('그룹(구분) '+cateText+'는 하위 '+cateText+'를 생성할 수 없습니다.')
			return false;
		}
		if($(ref_node).attr('rel') == 'end') {
			alert('하위 '+cateText+'는 '+catePanel.jsTree.settings.rules.max_depth+'차까지만 생성 하실 수 있습니다.')
			return false;
		}
		catePanel.jsTree.create();
		return false;
	}

	// 하위 카테고리 이름 변경 체크
	catePanel.jsTree.rename_check = function () {
		var ref_node	= this.selected;
		if(!this.check('clickable', ref_node)){
			alert('이름을 변경할 '+cateText+'를 선택하세요!')
			return false;
		}
		catePanel.jsTree.rename();
		return false;
	}

	// 하위 카테고리 삭제 체크
	catePanel.jsTree.remove_check = function () {
		var ref_node	= this.selected;
		if(!this.check('clickable', ref_node)){
			alert('삭제할 '+cateText+'를 선택하세요!')
			return false;
		}
		catePanel.jsTree.remove();
		return false;
	}

	// 카테고리가 선택되어 있다면 선택 해제
	catePanel.jsTree.deselect_check = function () {
		var ref_node	= this.selected;
		if(this.check('clickable', ref_node)){
			catePanel.jsTree.deselect_branch(ref_node);
		}
		return true;
	}


	// 카테고리가 선택되어 있다면 선택 해제
	catePanel.jsTree.select_cate = function (cateCd,depth) {

		if(cateCd.length /3 == depth) {
			catePanel.jsTree.select_branch("#"+cateCd);
		} else {
			var openCate = cateCd.substr(0, depth*3);
			catePanel.jsTree.open_branch("#"+openCate,false,function() {
				catePanel.jsTree.select_cate(cateCd,depth+1);
			});
		}
	}


	// 키보드 제어
	$(document)
		//.bind('keydown', 'up',		function() { catePanel.jsTree.get_prev(); return false; })
		//.bind('keydown', 'down',	function() { catePanel.jsTree.get_next(); return false; })
		//.bind('keydown', 'left',	function() { catePanel.jsTree.get_left(); return false; })
		//.bind('keydown', 'right',	function() { catePanel.jsTree.get_right(); return false; })
		.bind('keydown', 'return',	function() { if(catePanel.jsTree.hovered) catePanel.jsTree.select_branch(catePanel.jsTree.hovered); return false; })
		.bind('keydown', 'f2',		function() { if(catePanel.jsTree.selected) catePanel.jsTree.rename(); return false; })
		.bind('keydown', 'del',		function() { if(catePanel.jsTree.selected) catePanel.jsTree.remove(); return false; })
		.bind('keydown', 'insert',	function() { if(catePanel.jsTree.selected) catePanel.jsTree.create_check(); return false; })
});
