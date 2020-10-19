/**
 * 디자인관리
 * @author sunny
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */

function _ID(obj){return document.getElementById(obj)}

var myDesignTree;
$(document).ready(function () {
	// 파일트리 출력
	myDesignTree = $('.file_tree').designTree({
		treeCallback: {
			onload : function (TREE_OBJ) {
				// 로딩시 시작아이디로
				if ($('input[name=\'headerId\']').length) {
					TREE_OBJ.defaultId = '_outline__header__'+$('input[name=\'headerId\']').val();
				} else if ($('input[name=\'footerId\']').length) {
					TREE_OBJ.defaultId = '_outline__footer__'+$('input[name=\'footerId\']').val();
				} else if ($('input[name=\'sideId\']').length) {
					TREE_OBJ.defaultId = '_outline__side__'+$('input[name=\'sideId\']').val();
				} else if ($('input[name=\'scrollId\']').length) {
					TREE_OBJ.defaultId = '_outline__scroll__'+$('input[name=\'scrollId\']').val();
				} else if ($('input[name=\'layoutId\']').length) {
					TREE_OBJ.defaultId = '_layout__'+$('input[name=\'layoutId\']').val();
				} else if ($('input[name=\'pageId\']').length) {
					TREE_OBJ.defaultId = $('input[name=\'pageId\']').val().replace(/\//g,'__');
				}

				var open_branch_txt = '';
				if (typeof TREE_OBJ.defaultId != 'undefined') {
					ids = TREE_OBJ.defaultId.split('__');
					for (i = 1; i < ids.length; i++) {
						ids[i] = ids[i-1]+'__'+ids[i];
					}
					for (i = (ids.length-2); i >= 0 ; i--) {
						open_branch_txt = "if ($('#"+ids[i]+".closed').length) TREE_OBJ.open_branch($('#"+ids[i]+".closed'), true, function(){"+open_branch_txt+"});";
					}
					eval(open_branch_txt);
				}
			}
		}
	});
});

// 미리보기
function designPagePreview() {
	DCPV.design_preview = window.open('about:blank');
	var fobj = document.frmDesign;
	var ori_action = fobj.action;
	var ori_target = fobj.target;

	try {
		if (DCTM.editor_type == "codemirror" && DCTM.textarea_view_id == DCTM.textarea_merge_body) {
			DCTC.ed1.setValue(DCTC.merge_ed.editor().getValue());
		}
	}
	catch(e) {}

	fobj.action = ori_action + "&gd_preview=1";
	fobj.target = "ifrmHidden";
	fobj.submit();

	fobj.action = ori_action;
	fobj.target = ori_target;
}

/**
 * 디자인관리 미리보기
 */
DCPV = {
	design_preview : null,
	convert : function(design_file) {
		var dfile_path = String(design_file).split("/");
		var new_design_file = dfile_path.join("/");

		return new_design_file;
	},

	// 미리보기 팝업
	preview_popup: function(linkurl, preview_file) {
		var url = linkurl+"&gd_preview=1&gd_preview_file="+preview_file;
		try {
			this.design_preview.location.replace(url);
		}
		catch(e) {
			window.open(url);
		}
	}
}

/**
 * DESIGN PAGEI MOVE
 */
function designPageMove(sobj, skinType)
{
	var selected_text = sobj.text();
	var selected_value = sobj.val();
	var selected_path = sobj.attr('path');

	if (selected_path != 'noprint' && selected_path != '') {
		if (skinType == 'front') {
			document.location.href = '../design/design_page_edit.php?designPageId=' + selected_path;
		} else {
			document.location.href = '../' + skinType + '/design_page_edit.php?designPageId=' + selected_path;
		}
	}
	else if (selected_path == 'noprint' && selected_value == 'default'){
		alert('기본타입이 감춤입니다. 감춤이면 소스를 편집할 수 없습니다.');
	}
	else if (selected_value == 'noprint'){
		alert(selected_text + '이면 소스를 편집할 수 없습니다.');
	}
	else {
		alert('파일을 선택하셔야 합니다.');
	}
}

// 디자인관리 히스토리
function get_design_history() {
	try {
		var slt_obj = document.getElementById("slt_history");
		var hx = slt_obj.options[slt_obj.selectedIndex].value;
		if (!hx) return;
		if (DCTM.textarea_view_id == DCTM.textarea_base_body) {
			DCTM.textarea_view(document.getElementById(DCTM.textarea_user_view));
		}
		DCTM.source(hx, 'user_body', 'y');
	}
	catch(e) { }
}


/*** DESIGN_CODI MAP METHOD (DCMAPM) ***/
DCMAPM = {

	file_outline: function (key)
	{
		if (key == '') return;
		if (this.point == null || this.point[ key ] == null) return;

		var key_prop = this.point[ key ]; // 외곽 위치별 속성로딩

		if ( document.getElementById( key ).value == 'default' ) var val = key_prop['default_val']; // 기본타입인 경우
		else var val = document.getElementById( key ).value; // 그외

		if ( val == 'noprint' ) // 감춤인 경우
			document.getElementById( key_prop['map_point'] ).style.background = 'url(../img/codi/' + key_prop['map_point'] + '_off.gif) no-repeat';
		else if ( val != '' ) // 그외
		{
			var list = key_prop['img_list'];
			if ( list[ val ] != '' ) document.getElementById( key_prop['map_point'] ).style.background = 'url(' + list[ val ] + ') no-repeat'; // 샘플이미지 있는 경우
			else document.getElementById( key_prop['map_point'] ).style.background = 'url(../img/codi/' + key_prop['map_point'] + '_on.gif) no-repeat'; // 샘플이미지 없는 경우
		}

		if ( key_prop['map_point'] == 'map_footer' ) document.getElementById( key_prop['map_point'] ).style.backgroundPosition  = 'bottom right';
	},

	file_float: function (float_type)
	{
		if (typeof(float_type) == 'object'){
			var ele = document.getElementsByName(float_type.name);
			for ( i = 0; i < ele.length; i++ ){
				if ( ele[i].checked ) float_type = ele[i].getAttribute('float');
			}
		}

		if ( navigator.userAgent.indexOf("MSIE") >= 0 || navigator.userAgent.indexOf("Trident") >= 0 ){
			_ID('frame_side').style.styleFloat  = float_type;
		}
		else{

			_ID('frame_side').style.cssFloat  = float_type;

			if (float_type == 'left'){
				_ID('frame_body').getElementsByTagName('div')[0].style.marginLeft = '3px';
				_ID('frame_body').getElementsByTagName('div')[0].style.marginRight = '0';
			}
			else if (float_type == 'right'){
				_ID('frame_body').getElementsByTagName('div')[0].style.marginLeft = '0';
				_ID('frame_body').getElementsByTagName('div')[0].style.marginRight = '3px';
			}


		}
	}

}

/*** DESIGN_CODI TEXTAREA METHOD (DCTM) ***/
DCTM = {
	textarea_copy_body: 'copy_body',
	textarea_user_body: 'user_body',
	textarea_base_body: 'base_body',
	textarea_user_view: 'user_view',
	textarea_base_view: 'base_view',
	textarea_view_id: 'user_body',

	/**
	 * 에디터폼 출력 (Textarea 이름, 넓이, 줄수, 속성, 파일)
	 */
	editor_form: function (t_name, t_width, t_rows, t_property, tplFile)
	{
		var html = new Array();
		var n = -1;
		html[++n] = '<style type="text/css">';
		html[++n] = '#textarea { width:' + t_width + '; margin:0px; }';
		html[++n] = '#textarea .head { padding:5px 20px 15px; background:#E8E8E8; }';
		html[++n] = '#textarea .icon { border-style: none; width: 15px; height: 15px; }';
		html[++n] = '#textarea .body { padding-bottom:5px; background:#E8E8E8; }';
		html[++n] = '#textarea #base_body { display:none; color:#00EC37; background:#000000; }';
		html[++n] = '#textarea #copy_body { display:none; }';
		html[++n] = '#textarea .tail { padding:0px 5px 5px 5px; background:#E8E8E8; }';
		html[++n] = '#user_view, #base_view { font:12px tahoma; border-style:solid; border-width:0px; margin:0px; }';
		html[++n] = '#user_view { color:#222222; background:#E8E8E8; }';
		html[++n] = '#base_view { color:#FFFFFF; background:#7F7F7F; }';
		html[++n] = '.text {font-size:11px; font-family:"돋움"; color:#646464; letter-spacing:-1;}';
		html[++n] = '.box_line {border:1px solid #cccccc; padding-top:3px; padding-left:4px; width:99%}';
		html[++n] = '</style>';
		html[++n] = '<div id="textarea">';
		html[++n] = '<div class="head form-inline">';
		html[++n] = '<label for="codeact"><input type="checkbox" name="codeact" id="codeact" onclick="DCTM.codeBaseInput( this )"> <font class="text">원본 소스로 복구</font></label>';
		//html[++n] = '<input type="button" class="icon" style="background:url('+designImgPath+'btn_webftp_design.gif);width:98px;" title="WebFTP 이미지 관리" onclick="DCTM.webftp();">';
		//html[++n] = '<input type="button" class="icon" style="background:url('+designImgPath+'btn_colortable.gif);width:38px;" title="색상표 보기" onclick="DCTM.colortable();">';
		//html[++n] = '<input type="button" class="icon" style="background:url('+designImgPath+'btn_pageurl.gif);width:74px;" title="페이지링크" onclick="DCTM.pagelink();">';
		html[++n] = '<input type="button" class="icon" style="background:url('+designImgPath+'btn_templetip.gif);width:82px;" title="템플릿 활용기초" onclick="DCTM.template();">';
		html[++n] = '<input type="button" class="icon" style="background:url('+designImgPath+'btn_designtip.gif);width:46px;" title="디자인활용팁" onclick="DCTM.manual();">';
		html[++n] = '<input type="button" class="icon" style="background:url('+designImgPath+'btn_codeinput.gif);width:46px;" title="코드입력" onclick="DCTM.put_codi();">';
		html[++n] = '<input type="button" class="icon" style="background:url('+designImgPath+'bu_01.gif);width:46px;" title="줄바꿈 설정/해지" onclick="DCTM.textarea_wrap();">';
		html[++n] = '<input type="button" class="icon" style="background:url('+designImgPath+'bu_04.gif);width:46px;" title="한칸씩늘리기▼" onmousedown="DCTM.row_start();DCTM.row_control(\'+\');" onmouseup="DCTM.row_stop();" onmouseout="DCTM.row_stop();">';
		html[++n] = '<input type="button" class="icon" style="background:url('+designImgPath+'bu_03.gif);width:46px;" title="한칸씩줄이기" onmousedown="DCTM.row_start();DCTM.row_control(\'-\');" onmouseup="DCTM.row_stop();" onmouseout="DCTM.row_stop();">';
		html[++n] = '<input type="button" class="icon" style="background:url('+designImgPath+'bu_06.gif);width:38px;" title="기본" onclick="DCTM.row_direct(' + t_rows + ');">';
		html[++n] = '<div id="resetting"></div>';
		html[++n] = '</div>';
		html[++n] = '<div class="body">';
		html[++n] = '<textarea id="user_body" name="' + t_name + '" class="box_line" rows="' + t_rows + '" onkeydown="DCTM.textarea_useTab( this, event );" wrap="off" ' + t_property + '></textarea>';
		html[++n] = '<textarea id="base_body" name="base_' + t_name + '" class="box_line" rows="' + t_rows + '" onkeydown="DCTM.textarea_useTab( this, event );" wrap="off">원본소스</textarea><textarea id="copy_body"></textarea>';
		html[++n] = '</div>';
		html[++n] = '<div class="tail form-inline">';
		html[++n] = '<input type="button" class="btn btn-sm" ID="user_view" value="편집소스" onclick="DCTM.textarea_view( this )" />';
		html[++n] = '<input type="button" class="btn btn-sm" ID="base_view" value="원본소스보기" onclick="DCTM.textarea_view( this )" />';
		html[++n] = '</div>';
		html[++n] = '</div>';
		document.write(html.join("\n"));
		html = null;

		this.source(tplFile, 'user_body');
		this.source(tplFile, 'base_body');
	},

	/**
	 * 소스보기 선택 처리
	 */
	textarea_view: function ( obj )
	{
		if ( obj.id == this.textarea_base_view )
		{
			this.textarea_view_id = this.textarea_base_body;

			document.getElementById( this.textarea_user_body ).style.display = 'none';
			document.getElementById( this.textarea_base_body ).style.display = 'block';

			document.getElementById( this.textarea_user_view ).style.color = '#FFFFFF';
			document.getElementById( this.textarea_user_view ).style.background = '#7F7F7F';

			document.getElementById( this.textarea_base_view ).style.color = '#222222';
			document.getElementById( this.textarea_base_view ).style.background = '#E8E8E8';
		}
		else
		{
			this.textarea_view_id = this.textarea_user_body;

			document.getElementById( this.textarea_user_body ).style.display = 'block';
			document.getElementById( this.textarea_base_body ).style.display = 'none';

			document.getElementById( this.textarea_user_view ).style.color = '#222222';
			document.getElementById( this.textarea_user_view ).style.background = '#E8E8E8';

			document.getElementById( this.textarea_base_view ).style.color = '#FFFFFF';
			document.getElementById( this.textarea_base_view ).style.background = '#7F7F7F';
		}
	},

	/**
	 * TEXTAREA 줄수 조절 시작
	 */
	control_stop: 1,
	row_start: function ()
	{
		this.control_stop = 0;
	},

	/**
	 * TEXTAREA 줄수 조절 멈춤
	 */
	row_stop: function ()
	{
		this.control_stop = 1;
	},

	/**
	 * TEXTAREA 줄수 조절
	 */
	row_control: function ( plug )
	{
		var TObj = eval( "document.getElementById( '" + this.textarea_view_id + "' )" );

		if ( this.control_stop != 1 && ( plug == '+' || plug == '-' ) )
		{
			if ( plug == '+' && TObj.rows >= 70 )
			{
				this.row_stop();
				return;
			}
			else if ( plug == '-' && TObj.rows <= 1 )
			{
				this.row_stop();
				return;
			}

			TObj.rows = eval( "TObj.rows " + plug + " 1" );
			setTimeout( "DCTM.row_control( '"  + plug + "' )", 100 );
		}
		else
		{
			this.row_stop();
			return;
		}
	},

	/**
	 * TEXTAREA 줄수 변경
	 */
	row_direct: function ( num )
	{
		var TObj = eval( "document.getElementById( '" + this.textarea_view_id + "' )" );
		TObj.rows = num;
	},

	/**
	 * TEXTAREA 줄바꿈 설정/해지
	 */
	textarea_wrap: function ()
	{
		if ( isNS4 == true ) alert( '익스플로러에서만 지원됩니다.' );
		else
		{
			var TObj = eval( "document.getElementById( '" + this.textarea_view_id + "' )" );

			if ( TObj.wrap == 'off' ) TObj.wrap = 'soft';
			else TObj.wrap = 'off';
		}
	},

	/**
	 * TEXTAREA 탭키 사용가능
	 */
	textarea_useTab: function ( el, e )
	{
		e = (e) ? e : ((event) ? event : null );

		if ( isNS4 == true );
		else
		{
			if ( event.keyCode == 9 )
			{
				if (window.getSelection) {
					var selectionStart = el.selectionStart;
					var selectionEnd = el.selectionEnd;
				} else {
					var t = ( el.selection = document.selection.createRange() );
				}

				var seletText = (window.getSelection) ? el.value.substring(selectionStart, selectionEnd) : t.text;

				if (event.shiftKey == false) {
					if(seletText == '') seletText = "\t";
					else  seletText = "\t" + seletText.replace( /\n/gi, '\n\t' );
				} else {
					if(seletText != '') seletText = seletText.replace( /^\t/gi, '' ).replace( /\n\t/gi, '\n' );
				}

				if(window.getSelection){
					el.value = 	el.value.substring(0,selectionStart) + seletText + el.value.substring(selectionEnd);
					el.selectionStart = el.selectionEnd = selectionStart + 1;
				} else {
					t.text = seletText;
				}

				e.preventDefault ? e.preventDefault() : event.returnValue = false;
			}
		}
	},

	/**
	 * 코디소스입력
	 */
	put_codi: function ()
	{
		var userObj = document.getElementById( this.textarea_user_body );
		userObj.value = "{ # header }" + "\n\n" + "{ # footer }" + "\n" + userObj.value;
	},

	/**
	 * 색상표 보기
	 * todo : 색상표 jquery용으로 대체
	 */
	colortable: function ()
	{
		win = popup({
	        url: '../design/help_colortable.php',
	        target: 'colortable',
	        width: 400,
	        height: 400,
	        top: 200,
	        left: 200,
	        scrollbars: 'no',
	        resizable: 'no'
	    });
	    win.focus();
	    return win;
	},

	/**
	 * 페이지 링크 보기
	 * todo : 페이지 링크 처리 할것
	 */
	pagelink: function ()
	{
		win = popup({
	        url: '../design/popup.link.php',
	        target: 'pagelink',
	        width: 700,
	        height: 700,
	        top: 100,
	        left: 50,
	        scrollbars: 'yes',
	        resizable: 'yes'
	    });
	    win.focus();
	    return win;
	},

	/**
	 * WebFTP
	 * todo : WebFTP 링크 처리 할것
	 */
	webftp: function ()
	{
		win = popup({
	        url: '../design/popup.webftp.php',
	        target: 'webftp',
	        width: 900,
	        height: 800,
	        top: 50,
	        left: 50,
	        scrollbars: 'yes',
	        resizable: 'yes'
	    });
	    win.focus();
	    return win;
	},

	/**
	 * manual
	 * @todo : 추후 링크 주소 변경
	 */
	manual: function ()
	{
		var hrefStr = 'https://edu.godo.co.kr/study/basic-html-list.php';
		var win = window.open( hrefStr, 'manual' );
		win.focus();
	},

	/**
	 * Template_ 기초 활용팁
	 * @todo : 추후 링크 주소 변경
	 */
	template: function ()
	{
	    win = popup({
	        url: 'http://gongji.godo.co.kr/userinterface/help_template.php',
	        target: 'template',
	        width: 900,
	        height: 800,
	        top: 50,
	        left: 50,
	        scrollbars: 'yes',
	        resizable: 'yes'
	    });
	    win.focus();
	    return win;
	}
}

// DCTM.codeBaseInput 정의
DCTM.codeBaseInput = function( CObj, auto ) {
	var idObj = document.getElementById('resetting').getElementsByTagName("label")[0];

	var codyObj = document.getElementById( this.textarea_copy_body );
	var userObj = document.getElementById( this.textarea_user_body );
	var baseObj = document.getElementById( this.textarea_base_body );

	if ( CObj.checked )
	{
		if ( baseObj.value == '' )
		{
			if ( auto != true ) alert( "본 스킨은 원본소스를 지원하지 않습니다." );
			CObj.checked = false;
			idObj.style.color = '#000000';
			idObj.style.fontWeight = 'normal';
		}
		else
		{
			codyObj.value = userObj.value;
			userObj.value = baseObj.value;
			idObj.style.color = '#bf0000';
			idObj.style.fontWeight = 'bold';
		}
	}
	else
	{
		userObj.value = codyObj.value;
		idObj.style.fontColor = '#000000';
		idObj.style.color = '#000000';
		idObj.style.fontWeight = 'normal';
	}

	this.textarea_view( document.getElementById(this.textarea_user_view) );
}

// Design Codi Textarea Codemirror
DCTC = {
	cm_defopt : {
		mode : "text/html",
//		autoCloseTags: true,
//		autoCloseBrackets: true,
		indentUnit : 4,
		tabSize : 4,
		indentWithTabs : true,
		styleActiveLine: true,
		lineNumbers: true,
//		viewportMargin: Infinity,
		matchTags: true,
		matchBrackets : true,
        extraKeys: {
			"Ctrl-F": "findPersistent",
			'Ctrl-Z': function(cm) {
                DCTC.prevent_first_undo(cm);
            }
		}
    },
	ed1 : null,
	ed2 : null,
	merge_ed : null,
	te_code1_wrap : null,
	te_code2_wrap : null,
	te_merge_wrap : null,
	te_merge_title : null,
	code1 : null,
	code2 : null,
	is_init_html : false,

	init : function(opts) {
		try {
			var _dctc = this;
			this.te_code1_wrap = $("#"+opts["code1_wrap"]);
			this.te_code2_wrap = $("#"+opts["code2_wrap"]);
			this.te_merge_wrap = $("#"+opts["merge_wrap"]);
			this.te_merge_title = $("#"+opts["merge_title"]);
			this.code1 = document.getElementById(opts["code1"]);
			this.code2 = document.getElementById(opts["code2"]);

			this.set_overrides();

			$(this.code1).change(function() {
				if (_dctc.ed1) _dctc.ed1.setValue(this.value);
				if (_dctc.merge_ed) _dctc.merge_ed.editor().setValue(this.value);
			});

			$(this.code2).change(function() {
				if (_dctc.ed2) _dctc.ed2.setValue(this.value);
				if (_dctc.merge_ed) _dctc.merge_ed.editor().setValue(this.value);
			});
		}
		catch(e) {
			if (DCTM.destroy) DCTM.destroy();

			//throw null;
		}
	},

	destroy : function() {
		if (ori_DCTM) {
			DCTM.codeBaseInput = ori_DCTM.codeBaseInput;
			DCTM.textarea_view = ori_DCTM.textarea_view;
			DCTM.row_control = ori_DCTM.row_control;
			DCTM.row_direct = ori_DCTM.row_direct;
			DCTM.textarea_wrap = ori_DCTM.textarea_wrap;
		}

		$(this.code1).unbind("change");
		$(this.code2).unbind("change");

		if (this.ed1) {
			this.ed1.toTextArea();
			this.ed1 = null;
		}
		if (this.ed2) {
			this.ed2.toTextArea();
			this.ed2 = null;
		}
		if (this.merge_ed) {
			this.merge_ed = null;
		}
	},

	init_view : function(el, opt) {
		try {
			var defopt = this.cm_defopt;
			if (opt) for(var key in opt) defopt[key] = opt[key];

			return CodeMirror.fromTextArea(el, defopt);
		}
		catch(e) {
			if (DCTM.destroy) DCTM.destroy();

			//throw null;
		}
	},

	view_code1 : function() {
		this.te_code2_wrap.hide();
		this.te_merge_wrap.hide();
		this.te_merge_title.hide();
		this.te_code1_wrap.show();

		if (this.ed1 == null) this.ed1 = this.init_view(this.code1, { readOnly : false });
		else {
			if (this.merge_ed) $(this.code1).val(this.merge_ed.editor().getValue()).trigger("change");
			this.ed1.setOption("readOnly", false); // 버그?
		}
	},

	view_code2 : function() {
		var from_merge = false;
		if (this.merge_ed && this.te_merge_wrap.is(":visible")) from_merge = true;

		this.te_code1_wrap.hide();
		this.te_merge_wrap.hide();
		this.te_merge_title.hide();
		this.te_code2_wrap.show();

		if (this.ed2 == null) {
			this.ed2 = this.init_view(this.code2, { readOnly : true });
			this.ed2.getWrapperElement().style.backgroundColor = "#DFDFDF";
		}
		else this.ed2.setOption("readOnly", true); // 버그?

		if (from_merge) {
			$(this.code1).val(this.merge_ed.editor().getValue()).trigger("change");
			if (this.ed1) this.ed1.setOption("readOnly", false); // 버그?
		}
	},

	view_merge : function(ed2_val) {
		// 편집 소스 가져오기
		var ed1_val = null;
		if (this.ed1) ed1_val = this.ed1.getValue();
		else ed1_val = this.code1.value;

		// 원본 소스 가져오기
		if (!ed2_val) ed2_val = (this.ed2)? this.ed2.getValue() : this.code2.value;

		this.te_code1_wrap.hide();
		this.te_code2_wrap.hide();
		this.te_merge_wrap.show();
		this.te_merge_title.show();

		if (this.merge_ed) {
			this.merge_ed.editor().setValue(ed1_val);
			this.merge_ed.right.orig.setValue(ed2_val);
		}
		else {
			this.te_merge_wrap.empty();

			var defopt = this.cm_defopt;
			defopt['value'] = ed1_val;
			defopt['orig'] = ed2_val;
			defopt['readOnly'] = false;
			defopt['lineWrapping'] = true;
			defopt['highlightDifferences'] = true;

			this.merge_ed = CodeMirror.MergeView(this.te_merge_wrap.get(0), defopt);
			this.merge_ed.right.orig.getWrapperElement().style.backgroundColor = "#DFDFDF";
			this.merge_ed.right.orig.setOption("styleActiveLine", false);
		}
	},

	refresh_view : function() {
		if (this.te_code1_wrap.is(":visible") && this.ed1 != null) this.ed1.refresh();
		if (this.te_code2_wrap.is(":visible") && this.ed2 != null) this.ed2.refresh();
		if (this.te_merge_wrap.is(":visible") && this.merge_ed != null) {
			this.merge_ed.editor().refresh();
			this.merge_ed.right.orig.refresh();
		}
	},

	set_overrides : function() {
		var _dctc = this;

		/*-------------------------------------
		 원본소스 입력
		-------------------------------------*/
		DCTM.codeBaseInput = function ( CObj, auto )
		{
			var idObj = document.getElementById('resetting').getElementsByTagName("label")[0];

			var codyObj = document.getElementById( this.textarea_copy_body );
			var userObj = document.getElementById( this.textarea_user_body );
			var baseObj = document.getElementById( this.textarea_base_body );

			if ( CObj.checked )
			{
				if ( baseObj.value == '' )
				{
					if ( auto != true ) alert( "본 스킨은 원본소스를 지원하지 않습니다." );
					CObj.checked = false;
					idObj.style.color = '#000000';
					idObj.style.fontWeight = 'normal';
				}
				else
				{
					codyObj.value = userObj.value;
					$(userObj).val(baseObj.value).trigger("change");
					idObj.style.color = '#bf0000';
					idObj.style.fontWeight = 'bold';
				}
			}
			else
			{
				$(userObj).val(codyObj.value).trigger("change");
				idObj.style.fontColor = '#000000';
				idObj.style.color = '#000000';
				idObj.style.fontWeight = 'normal';
			}

			if (this.textarea_view_id == this.textarea_base_body) this.textarea_view( $("#"+this.textarea_user_view).get(0) );
		};

		/*-------------------------------------
		 소스보기 선택처리
		-------------------------------------*/
		DCTM.textarea_view = function ( obj )
		{
			switch(obj.id) {
				case this.textarea_base_view : {
					if (this.textarea_view_id == this.textarea_base_body) return;
					this.textarea_view_id = this.textarea_base_body;

					_dctc.view_code2();

					document.getElementById( this.textarea_user_view ).style.color = '#FFFFFF';
					document.getElementById( this.textarea_user_view ).style.background = '#7F7F7F';

					document.getElementById( this.textarea_base_view ).style.color = '#222222';
					document.getElementById( this.textarea_base_view ).style.background = '#E8E8E8';

					document.getElementById( this.textarea_merge_view ).style.color = '#FFFFFF';
					document.getElementById( this.textarea_merge_view ).style.background = '#7F7F7F';
					document.getElementById("merge_source").disabled = true;

					break;
				}
				case this.textarea_user_view : {
					if (this.textarea_view_id == this.textarea_user_body) return;
					this.textarea_view_id = this.textarea_user_body;

					_dctc.view_code1();

					document.getElementById( this.textarea_user_view ).style.color = '#222222';
					document.getElementById( this.textarea_user_view ).style.background = '#E8E8E8';

					document.getElementById( this.textarea_base_view ).style.color = '#FFFFFF';
					document.getElementById( this.textarea_base_view ).style.background = '#7F7F7F';

					document.getElementById( this.textarea_merge_view ).style.color = '#FFFFFF';
					document.getElementById( this.textarea_merge_view ).style.background = '#7F7F7F';
					document.getElementById("merge_source").disabled = true;

					break;
				}
				case this.textarea_merge_view : {
					if (this.editor_type != "codemirror") return;
					if (this.textarea_view_id == this.textarea_merge_body) return;
					this.textarea_view_id = this.textarea_merge_body;

					//_dctc.view_merge();
					$("#merge_source").prop("disabled", false).trigger("change");

					document.getElementById( this.textarea_user_view ).style.color = '#FFFFFF';
					document.getElementById( this.textarea_user_view ).style.background = '#7F7F7F';

					document.getElementById( this.textarea_base_view ).style.color = '#FFFFFF';
					document.getElementById( this.textarea_base_view ).style.background = '#7F7F7F';

					document.getElementById( this.textarea_merge_view ).style.color = '#222222';
					document.getElementById( this.textarea_merge_view ).style.background = '#E8E8E8';

					break;
				}
			}
		};

		/*-------------------------------------
		 TEXTAREA 줄수 조절
		-------------------------------------*/
		DCTM.row_control = function ( plug )
		{
			var te_body = $("#"+this.textarea_user_body+"_wrap").parent();
			var body_height = te_body.height();

			if ( this.control_stop != 1 && ( plug == '+' || plug == '-' ) )
			{
				if ( plug == '+' && parseInt(body_height, 10) >= 920 )
				{
					this.row_stop();
					return false;
				}
				else if ( plug == '-' && parseInt(body_height, 10) <= 40 )
				{
					this.row_stop();
					return false;
				}

				te_body.height((body_height + parseInt(plug+"20", 10)) + "px");
				DCTC.refresh_view();

				setTimeout( "DCTM.row_control( '"  + plug + "' )", 100 );
			}
			else
			{
				this.row_stop();
				return false;
			}
		};

		/*-------------------------------------
		 TEXTAREA 줄수 변경
		-------------------------------------*/
		DCTM.row_direct = function ( num )
		{
			$("#"+this.textarea_user_body+"_wrap").parent().height("740px");
		};

		/*-------------------------------------
		 TEXTAREA 줄바꿈 설정/해지
		-------------------------------------*/
		DCTM.textarea_wrap = function ()
		{
			if (DCTM.textarea_view_id == DCTM.textarea_merge_body) {
				alert("Diff 모드에서 줄바꿈은 지원하지 않습니다.");
				return; // merge_view 적용시 줄바꿈불가.
			}
			switch(DCTM.textarea_view_id) {
				case DCTM.textarea_user_body : {
					if (DCTC.ed1) DCTC.ed1.setOption("lineWrapping", !DCTC.ed1.getOption("lineWrapping"));
					break;
				}
				case DCTM.textarea_base_body : {
					if (DCTC.ed2) DCTC.ed2.setOption("lineWrapping", !DCTC.ed2.getOption("lineWrapping"));
					break;
				}
			}
		};
	},

	// 기본 소스에서 첫 ctrl+z로 방지
    prevent_first_undo : function(cm) {
        var history = cm.getHistory().done;
        var changesCnt = 0;

        $.each(history, function (index, value) {
            if (Object.keys(value).indexOf('changes') != -1) {
                changesCnt++;
            }
        });

        // 편집소스, 소스비교 조건 구분
        if ($(DCTC.te_merge_wrap.selector).css('display') !== 'none') {
            if (changesCnt > 0) {
                cm.execCommand('undo');
            }
		} else {
            if (changesCnt > 1) {
                cm.execCommand('undo');
            }
		}
    }
}
// Design Codi Textarea Codemirror

// 객체 복사.
function copy_ref_object(src) {
	var desc = {};
	for(var k in src) {
		desc[k] = src[k];
	}
	return desc;
}

var isNS4 = ( navigator.userAgent.indexOf('MSIE') < 0 && navigator.userAgent.indexOf('Trident') < 0 ? true : false );

// codemirror, js, css import
var designPath = get_script_dirpath('script/design/design.js');
var designScriptPath = designPath + 'script/design/';
var designImgPath = designPath + 'img/';

var html = new Array();
var n = -1;
html[++n] = '<link rel="stylesheet" type="text/css" href="'+designScriptPath+'codemirror/lib/codemirror.css" charset="UTF-8">';
html[++n] = '<link rel="stylesheet" type="text/css" href="'+designScriptPath+'codemirror/addon/dialog/dialog.css" charset="UTF-8">';
html[++n] = '<link rel="stylesheet" type="text/css" href="'+designScriptPath+'codemirror/addon/merge/merge.css" charset="UTF-8">';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/lib/codemirror.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/mode/xml/xml.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/mode/javascript/javascript.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/mode/css/css.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/mode/htmlmixed/htmlmixed.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/addon/merge/dep/diff_match_patch.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/addon/merge/merge.js" charset="UTF-8"></script>';
//html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/addon/edit/closebrackets.js" charset="UTF-8"></script>';
//html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/addon/edit/closetag.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/addon/edit/matchbrackets.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/addon/fold/xml-fold.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/addon/edit/matchtags.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/addon/selection/active-line.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/addon/dialog/dialog.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/addon/search/searchcursor.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/addon/search/search.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/addon/scroll/annotatescrollbar.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/addon/search/matchesonscrollbar.js" charset="UTF-8"></script>';
html[++n] = '<script type="text/javascript" src="'+designScriptPath+'codemirror/addon/search/jump-to-line.js" charset="UTF-8"></script>';
document.write(html.join('\n'));
html = null;

// DCTM을 ori_DCTM으로 백업
var ori_DCTM = copy_ref_object(DCTM);

// DCTM.source 재정의
DCTM.source = function (tplFile, body, historyFile)
{
	if (body != 'user_body' && body != 'base_body') return;

	var urlParam = '';
	if (historyFile == 'y') {
		urlParam = '&historyFile=' + historyFile;
	}

	//var urlStr = '../design/common_ax.php?mode=getTextarea&skinType=' + this.skinType + '&body=' + body + '&tplFile=' + tplFile + urlParam + '&dummy=' + new Date().getTime();
	var urlStr = 'common_ax.php?mode=getTextarea&skinType=' + this.skinType + '&body=' + body + '&tplFile=' + tplFile + urlParam + '&dummy=' + new Date().getTime();

	$.ajax({
		url : urlStr,
		type : 'get',
		success : function (res) {
			try {
				$('#' + body).val(res).trigger('change');
			}
			catch(e) {}
		}
	});
};

// DCTM.write 재정의
DCTM.write = function(t_name, t_width, t_rows, t_property, tplFile, skinType) {

	// 스킨 타입
	this.skinType = skinType;

	// 기본 폼
	this.editor_form(t_name, t_width, t_rows, t_property, tplFile);

	// textarea 확장.
	try {
		var html = new Array();
		var n = -1;
		html[++n] = '<style>';
		html[++n] = '#textarea #merge_view {font:9pt tahoma; border-style:solid; border-width:0; margin:0;}';
		html[++n] = '#textarea .user_body_wrap .CodeMirror {height:100%;}';
		html[++n] = '#textarea .base_body_wrap .CodeMirror {height:100%;}';
		html[++n] = '#textarea .merge_body_wrap .CodeMirror-merge-copy {display:none;}';
		html[++n] = '#textarea .merge_body_wrap .CodeMirror-merge,';
		html[++n] = '#textarea .merge_body_wrap .CodeMirror-merge-pane,';
		html[++n] = '#textarea .merge_body_wrap .CodeMirror {height:100%;}';
		html[++n] = '#textarea .CodeMirror-matchingtag {background: rgba(255, 150, 0, .3);}';
		html[++n] = '#textarea .user_body_wrap textarea, #textarea .base_body_wrap textarea {border:solid 1px #CCCCCC;}';
		html[++n] = '</style>';
		document.write(html.join('\n'));
		html = null;

		this.textarea_merge_body = 'merge_body_wrap';
		this.textarea_merge_view = 'merge_view';
		this.editor_type = 'textarea';

		// 에디터 종류 todo : 추후 삭제
		var html = new Array();
		var n = -1;
		html[++n] = '<span id="editortype" class="editortype">에디터종류 : ';
		html[++n] = '<label class="radio-inline"><input type="radio" name="_et" value="textarea" onclick="DCTM.set_textarea();" />단순 에디터</label>';
		html[++n] = '<label class="radio-inline"><input type="radio" name="_et" value="codemirror" onclick="DCTM.set_codemirror();" checked="checked" />문법강조 에디터(소스비교, IE8이하 지원안됨)</label>';
		html[++n] = '</span>';
		//$("#resetting").append(html.join('\n'));
		html = null;

		// 익스플로러 9 이상인 경우에만 codemirror 사용
		var ck_et = getCookie('DCTM_et');
		var ie_ver = navigator.appVersion.match(/MSIE\s*[0-9\.]*\s*;/gi);
		if (ie_ver) {
			if(Number(ie_ver[0].replace(/[^0-9\.]*/g, '')) < 9) {
				ck_et = 'textarea';
			}
		}

		switch(ck_et) {
			case 'textarea' : {
				DCTM.set_textarea();
				$('#textarea [name="_et"][value="textarea"]').prop('checked', true);
				break;
			}
			default : {
				DCTM.set_codemirror();
				$('#textarea [name="_et"][value="codemirror"]').prop('checked', true);
				break;
			}
		}
	}
	catch(e) {
		if (DCTM.destroy) DCTM.destroy();
	}
}

// 새 DCTM을 기존 DCTM으로 변경
DCTM.destroy = function() {
	this.set_textarea();
	DCTM = copy_ref_object(ori_DCTM);
};

// 기본 확장 함수
DCTM.set_ex = function() {
	$("#" + this.textarea_user_body).wrap("<div id='"+this.textarea_user_body+"_wrap' class='"+this.textarea_user_body+"_wrap' style='height:100%;'></div>");
	$("#" + this.textarea_base_body).wrap("<div id='"+this.textarea_base_body+"_wrap' class='"+this.textarea_base_body+"_wrap' style='height:100%;'></div>");

	$("#"+this.textare_user_body+",#"+this.textare_base_body).show();
}

// 기본 확장 함수 취소
DCTM.unset_ex = function() {
	if ($("#" + this.textarea_user_body+"_wrap").length != 0) $("#" + this.textarea_user_body).unwrap();
	if ($("#" + this.textarea_base_body+"_wrap").length != 0) $("#" + this.textarea_base_body).unwrap();
}

// codemirror 설정
DCTM.set_codemirror = function() {
	// 스킨 타입
	if (document.compatMode.toLowerCase() == 'backcompat') {
		//alert('지원하지 않는 브라우저입니다.');
		//$('#textarea [name="_et"][value="'+this.editor_type+'"]').prop('checked', true);
		return;
	}
	switch(this.editor_type) {
		case 'codemirror' : return;
	};

	try {
		this.set_ex();
		this.editor_type = 'codemirror';
		//$('#textarea [name="_et"][value="codemirror"]').prop('checked', true);

		// merge_body_wrap(div), merge_view(버튼) 추가
		$("#textarea .head").append("<div id='merge_body_title' style='text-align:center; height:25px; line-height:25px; border-bottom:solid 1px #000000; display:none;'><div style='float:left; width:50%; background-color:#FFFFFF;'>편집창</div><div style='width:50%; margin-left:50%; background-color:#DFDFDF;'>원본창</div></div>");
		$("#" + this.textarea_user_body + "_wrap").parent().append("<div id='merge_body_wrap' class='merge_body_wrap' style='height:100%;'></div>");
		$("#" + this.textarea_user_view).parent().append("<div class='form-group' style='padding-top:3px; display:inline;' id='"+this.textarea_merge_view+"_wrap'><input type='button' clsas='btn btn-sm' id='"+this.textarea_merge_view+"' value='소스비교' onclick='DCTM.textarea_view( this )'><select class='form-control input-sm' id='merge_source' onclick='DCTM.textarea_view( this )' disabled='disabled'><option value=''>원본소스</option>"+(($("#slt_history >option[value!='']").length > 0) ? $("#slt_history").html() : "")+"</select></div>");
		$("#merge_source").change(function() {
			if (DCTM.textarea_view_id != DCTM.textarea_merge_body) return;
			var merge_source_val = $("#merge_source").val();
			if (!merge_source_val) DCTC.view_merge();
			else {
				$.ajax({
					url : 'common_ax.php?mode=getTextarea&skinType=' + DCTM.skinType + '&body=user_body&tplFile=' + merge_source_val + '&historyFile=y&dummy=' + new Date().getTime(),
					type : 'get',
					success : function (res) {
						try {
							DCTC.view_merge(res);
						}
						catch(e) {}
					}
				});
			}
		}).find(">option").each(function (){
			var te_self = $(this);
			te_self.html("편집소스와 "+te_self.html()+" 비교");
		});

		DCTC.init({
			code1_wrap : "user_body_wrap",
			code2_wrap : "base_body_wrap",
			merge_wrap : "merge_body_wrap",
			merge_title : "merge_body_title",
			code1 : "user_body",
			code2 : "base_body"
		});

		$("#textarea .body").height($("#textarea .body").outerHeight());

		var tmp_textarea_view_id = this.textarea_view_id;
		this.textarea_view_id = "";
		this.textarea_view($("#"+tmp_textarea_view_id.replace(/_body$/g, "_view")).get(0));

		$("#"+this.textarea_user_body).trigger("change");

		setCookie("DCTM_et", "codemirror");
	}
	catch(e) {
		this.unset_codemirror();
		//throw null;
	}
};

// codemirror 해제
DCTM.unset_codemirror = function() {
	if (this.textarea_view_id == this.textarea_merge_body) {
		this.textarea_view($("#"+this.textarea_user_view).get(0));
	}

	DCTC.destroy();

	$("#" + this.textarea_merge_view + "_wrap").remove();
	$("#" + this.textarea_merge_body).remove();

	$("#textarea .body").height("99%");

	this.unset_ex();
}

// textare 설정
DCTM.set_textarea = function() {
	switch(this.editor_type) {
		case "codemirror" : {
			this.unset_codemirror();
			break;
		}
		default : return;
	};

	this.unset_ex();
	this.editor_type = "textarea";
	//$("#textarea [name='_et'][value='textarea']").prop("checked", true);

	$("#"+this.textarea_user_body + ", #"+this.textarea_base_body).show();
	if(this.textarea_view_id == this.textarea_base_body) this.textarea_view($("#"+this.textarea_base_view).get(0));
	else this.textarea_view($("#"+this.textarea_user_view).get(0));

	setCookie("DCTM_et", "textarea");
}

// DCTM override


function getCookie( name )
{
	var nameOfCookie = name + "=";
	var x = 0;
	while ( x <= document.cookie.length )
	{
		var y = (x+nameOfCookie.length);
		if ( document.cookie.substring( x, y ) == nameOfCookie ) {
			if ( (endOfCookie=document.cookie.indexOf( ";", y )) == -1 )
				endOfCookie = document.cookie.length;
			return unescape( document.cookie.substring( y, endOfCookie ) );
		}
		x = document.cookie.indexOf( " ", x ) + 1;
		if ( x == 0 )
			break;
	}
	return "";
}

function setCookie( name, value, expires, path, domain, secure ){

	var curCookie = name + "=" + escape( value ) +
		( ( expires ) ? "; expires=" + expires.toGMTString() : "" ) +
		( ( path ) ? "; path=" + path : "" ) +
		( ( domain ) ? "; domain=" + domain : "" ) +
		( ( secure ) ? "; secure" : "" );

	document.cookie = curCookie;
}
