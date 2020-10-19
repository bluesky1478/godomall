/**
 * Img Host
 * @author sunny
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */
(function($) {
	var self;
	/**
	 * ImgHost.init() 클래스의 Property 정의
	 */
	ImgHost = {
		ele: null // Element
		, isFtp: null // FTP여부
		, chkEle: null // 선택박스 Element
		, btnReplaceEle: null // 전환버튼 Element
		, btnRestoreEle: null // 복원버튼 Element
		, num: 1 // 1회갯수
		, total: 0 // 전체갯수
		, point: 0 // 처리번호

		/**
		 * ImgHost.init() 클래스
		 * @classDescription Coverflow 출력하는 클래스
		 * @param {Object} ele Element
		 * @param {Array} opts 옵션
		 * @return {Object} new instance 리턴
		 * @constructor
		 */
		, init: function(ele, opts) {
			// element 정의
			this.chkEle = $('input[type=checkbox][name=\'chk[]\']',ele);
			this.btnReplaceEle = $('#btnReplace',ele);
			this.btnRestoreEle = $('#btnRestore',ele);
			// option 정의
			self = this;
			opts = opts || {};
			$.extend( this, opts );
			this.ele = ele;
			// 버튼이벤트
			if (this.btnReplaceEle != null) {
				this.btnReplaceEle.click(function(){
					self.replace();
				});
			}
			if (this.btnRestoreEle != null) {
				this.btnRestoreEle.click(function(){
					self.restore();
				});
			}
		}

		/**
		 * 이미지호스팅 전환요청
		 */
		, replace: function() {
			this.total = $(this.chkEle).filter(':checked').length;
			this.point = 0;
			if (this.total == 0) {
				alert('전환 할 상품을 선택해주세요.');
				$(this.chkEle).eq(0).focus();
				return;
			}
			if (this.isFtp == null){
				this.ftpDisplayForm('putReplace');
				return;
			}
			self.send('putReplace');
		}

		/**
		 * 이미지 복원요청
		 */
		, restore: function() {
			this.total = $(this.chkEle).filter(':checked').length;
			this.point = 0;
			if (this.total == 0) {
				alert('복원 할 상품을 선택해주세요.');
				$(this.chkEle).eq(0).focus();
				return;
			}
			if (this.isFtp == null){
				this.ftpDisplayForm('putRestore');
				return;
			}
			self.send('putRestore');
		}

		/**
		 * 전송
		 * @param string mode 전송모드(putReplace-이미지호스팅 전환요청, putRestore-복원요청)
		 */
		, send: function(mode) {
			// data
			var data = 'mode='+mode;
			if (mode == 'putReplace' && $('input[name=replaceDeleteFl]:checked').length == 1) {
				data += '&replaceDeleteFl=y';
			} else if (mode == 'putRestore' && $('input[name=restoreDeleteFl]:checked').length == 1) {
				data += '&restoreDeleteFl=y';
			}
			// 상품코드들
			var goods = new Array();
			var start = this.point - 1;
			if (start < 0) {
				var limit = ':lt('+this.num+')';
			} else {
				var limit = ':gt('+start+'):lt('+this.num+')';
			}
			$(this.chkEle).filter(':checked').filter(limit).each(function(){
				goods.push($(this).val());
				self.point++;
			});
			data += '&goods='+goods.join(',');
			// 전송
			$.ajax({
				type: 'POST'
				, url: '../goods/goods_imghost_ps.php?dummy=' + new Date().getTime()
				, data: data
				, dataType: 'json'
				, async: true
				, beforeSend: function(XMLHttpRequest) {
					if ($('.process').length == 0) {
						layer_popup(processHtml, '이미지 전송');
					}
					$('.process #message').html('<b style="font-size:16pt;">' + self.point + ' / ' + self.total + ' 처리중입니다.</b> 잠시만 기다려주세요.');
				}
				, success: function(response) {
				    $.each(response, function(goodsno,cnt) {
				    	$(self.chkEle).filter('[value='+goodsno+']').parents('tr:first').find('.in').text(cnt).css('color','#627DCE');
				    });
					if (self.point != self.total) {
						self.send(mode);
					} else if (self.point == self.total) {
						$('.process #loading').hide();
						$('.process #message').html('<b style="font-size:16pt;">' + self.total + '건이 처리완료되었습니다.</b> <img src="../_template/image/main/btn_m_set_ok.gif" alt="확인" class="layer_close" />');
						$('.process #message .layer_close').click(function(){
							$.unblockUI();
						}).css('cursor','pointer');
					}
				}
				, error: function(XMLHttpRequest, textStatus, errorThrown) {
					$.unblockUI();
				}
			});
		}

		/**
		 * FTP 폼 출력
		 * @param string mode 전송모드(putReplace-이미지호스팅 전환요청, putRestore-복원요청)
		 */
		, ftpDisplayForm : function (mode)
		{
			layer_popup(ftpHtml, '요청');
			$('.frmFtp form[name=frmFtp] input[name=userid]').keyup(function() {
				$('.frmFtp #domain').text($(this).val());
			});
			$('.frmFtp form[name=frmFtp]').submit(function() {
				if ($('.frmFtp form[name=frmFtp] input[name=userid]').val() == '') {
					alert('FTP ID를 입력해주세요.');
					$('.frmFtp form[name=frmFtp] input[name=userid]').focus();
					return false;
				}
				if ($('.frmFtp form[name=frmFtp] input[name=pass]').val() == '') {
					alert('FTP Password를 입력해주세요.');
					$('.frmFtp form[name=frmFtp] input[name=pass]').focus();
					return false;
				}
				var parameters = $('.frmFtp form[name=frmFtp]').serialize();
				$.ajax({
					type: 'POST'
					, url: '../goods/goods_imghost_ps.php?dummy=' + new Date().getTime()
					, data: 'mode=ftpVerify&'+parameters
					, async: false
					, beforeSend: function(XMLHttpRequest) {
						$('.frmFtp #wait').show();
						$('.frmFtp #confirm').hide();
					}
					, success: function(response) {
						if (response == 'true') {
							self.isFtp = true;
							$.unblockUI();
							self.send(mode);
						}
					}
					, complete: function() {
						$('.frmFtp #confirm').show();
						$('.frmFtp #wait').hide();
					}
				});
				return false;
			});
			$('.frmFtp .layer_close').click(function(){
				$.unblockUI();
			}).css('cursor','pointer');
		}
	};

	/**
	 * FTP 폼 HTML
	 */
	var ftpHtml = '\
	<div class="frmFtp" style="border:solid 3px #676767; background:#fff; padding:10px;">\
	<form name="frmFtp" method="post">\
	<div style="font-size:11px; letter-spacing:-1px; color:#777; font-weight:bold; margin-bottom:10px;">고도의 이미지호스팅 신청고객은 고객님의 FTP 계정정보를 입력하세요.</div>\
	\
	<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center">\
	<colgroup><col width="105" /></colgroup>\
	<tr height="28">\
		<th style="color:#777; text-align:right; padding-right:10px;">FTP 도메인</th>\
		<td style="background:#eee;font-weight:bold;" class="font-eng">ftp://<span id="domain"></span>.godohosting.com</td>\
	</tr>\
	<tr height="28">\
		<th style="color:#777; text-align:right; padding-right:10px;">FTP ID</th>\
		<td style="border-bottom:solid 1px #ddd;"><input name="userid" type="text" style="width:99%; height:18px; border-width:0;" class="font-eng"/></td>\
	</tr>\
	<tr height="28">\
		<th style="color:#777; text-align:right; padding-right:10px;">FTP Password</th>\
		<td style="border-bottom:solid 1px #ddd;"><input name="pass" type="password" style="width:99%; height:18px; border-width:0;" class="font-eng"/></td>\
	</tr>\
	</table>\
	<div class="center" id="confirm" style="margin-top:10px;">\
	<input type="image" src="../_template/image/main/btn_m_set_ok.gif" value="확인" class="vtop" />\
	<img src="../_template/image/main/btn_m_set_cancel.gif" alt="취소" class="layer_close" />\
	</div>\
	<div class="center" id="wait" style="margin-top:10px; font-size:11px; color:#777; display:none; font-weight:bold;">처리중입니다. 잠시만 기다려주세요.</div>\
	</form>\
	</div>\
	';

	/**
	 * 진행상황 HTML
	 */
	var processHtml = '\
	<div class="process" style="border:solid 3px #676767; background:#fff; padding:10px;">\
	<div style="text-align:center; margin-top:20px;" id="loading"><img src="/admin/gd_share/img/icon_loading.gif" alt=""/></div>\
	<div style="text-align:center; margin:20px 0;" id="message"></div>\
	</div>\
	';

	/**
	 * ImgHost.init() 클래스의 Property 설정
	 */
	ImgHost.init.prototype = ImgHost;

	/**
	 * ImgHost.init() 클래스 인스턴스 생성
	 * @param {Array} options 옵션
	 * @return {Object} ImgHost.init() 클래스 인스턴스
	 */
	jQuery.fn.imgHost = function(options) {
		return new ImgHost.init(this, options);
	};

})(jQuery);
