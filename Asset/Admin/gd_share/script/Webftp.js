/**
 * Webftp
 * @author sunny
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */
(function($) {
	/**
	 * Webftp.init() 클래스의 Property 정의
	 */
	Webftp = {
		ele: null // Element
		, imgPath: '' // 이미지경로
		, lister: {
			areaEle: null // 이미지리스터 영역 element
			, renameBoxEle: null // 이미지 이름바꾸기 폼 element
			, deleteBoxEle: null // 이미지 삭제 폼 element
			, loadingImage: null // 로딩이미지
			, type: 'gellery' // 리스팅 타입(gellery, normal)
			, imgData: null // 이미지데이터
			, nowPageNo: null // 페이지번호
		}
		, tree: {
			areaEle: null // 트리영역 element
			, treeEle: null // 트리 element
			, menuEle: null // 트리메뉴 element
			, treeObj: {} // 트리 object
			, topPath: 'data' // 최상위 경로
		}
		, upload: {
			areaEle: null // 업로드 element
			, statusEle: null // 업로드 element
			, btnUploadStart: null // 업로드시작 버튼
			, btnUploadClose: null // 닫기 버튼
		}
		, uploadSize: '0M' // 업로드 제한용량
		, diskErrno: null // 계정용량 에러코드

		/**
		 * Webftp.init() 클래스
		 * @classDescription Webftp 출력하는 클래스
		 * @param {Object} ele Element
		 * @param {Array} opts 옵션
		 * @return {Object} new instance 리턴
		 * @constructor
		 */
		, init: function(ele, opts) {
			opts = opts || {};
			if (opts.lister) {
				this.lister = $.extend({},this.lister,opts.lister);
				delete opts.lister;
			}
			if (opts.tree) {
				this.tree = $.extend({},this.tree,opts.tree);
				delete opts.tree;
			}
			$.extend( this, opts );
			this.ele = ele;
			this.imgPath = get_script_dirpath('script/Webftp.js')+'image/';
			this.start();
		}

		/**
		 * 시작
		 */
		, start: function() {
			var self = this;
			// 초기값
			$.ajax({
				type: 'GET'
				, url: '../share/popup_webftp_ax.php?mode=init'
				, dataType: 'json'
				, async: false
				, success: function(data) {
					self.uploadSize = data.uploadSize;
					self.diskErrno = (data.diskErrno == '' ? null : data.diskErrno);
				}
			});
			// 로딩이미지
			this.set_loading();
			// 트리
			this.set_tree();
			// 파일업로드
			this.set_upload();
		}

		/**
		 * 로딩이미지 정의
		 */
		, set_loading: function() {
			this.lister.loadingImage = $(new Image());
			this.lister.loadingImage.width(75);
			this.lister.loadingImage.height(75);
			this.lister.loadingImage.css({'margin':'0px auto','display':'block'});
			this.lister.loadingImage.attr('src',this.imgPath+'ico_noimg_75.gif');
		}

		/**
		 * 이미지리스트 출력
		 */
		, print_lister: function() {
			if (this.lister.type == 'gellery') {
				this.galleryView(4, 3);
			} else {
				alert('리스팅은 아직 지원하지 않음.');
			}
		}

		/**
		 * 갤러리형 이미지리스터
		 *
		 * galleryView는 this.lister.imgData의 자료를 토대로 this.lister.areaEle에
		 * 갤러리형태의 목록보기를 가능하게 해준다.
		 * 그리고 그 안에서 일어나는 대부분의 이벤트및 처리는 galleryView안에서만 처리한다
		 * 밖에서 처리하는 공유함수는 원본보기,삭제,이름바꾸기,정렬순서변경(데이터만)등이 있다.
		*/
		, galleryView: function(x,y) {
			var self = this;
			this.lister.areaEle.addClass('imageGallery');

			this.lister.areaEle.children().remove();//안쪽내용초기화

			var nowPage;
			var pieceWidth = Math.floor((this.lister.areaEle.width()-40)/x);
			var pieceHeight = Math.floor(this.lister.areaEle.height()/y);
			var totalImg = this.lister.imgData.length;
			var totalPage = Math.ceil(totalImg/(x*y));
			var sideBar = $('<div />').css({'float':'right','height':this.lister.areaEle.height(),'background-color':'#eeeeee','width':40});

			// 페이지 정렬 버튼 element
			var pageSortBtn = $('<div />').css({'width':40,'margin':'5px 0px 0px 0px','font-size':'7pt','text-align':'center','cursor':'pointer'}).text('정렬').click(function() {
				if (pageSort.css('display') == 'none') {
					pageSort.show();
				} else {
					pageSort.hide();
				}
			});
			var pageSort = $('<div />').addClass('pageSort');
			var pageSortNameAsc = $('<div />').text('이름↑').click(function() {
				self.dataSortName('asc');
				pageView(nowPage);
				pageSort.hide();
			});
			var pageSortNameDesc = $('<div />').text('이름↓').click(function() {
				self.dataSortName('desc');
				pageView(nowPage);
				pageSort.hide();
			});
			var pageSortDateAsc = $('<div />').text('날짜↑').click(function() {
				self.dataSortDate('asc');
				pageView(nowPage);
				pageSort.hide();
			});
			var pageSortDateDesc = $('<div />').text('날짜↓').click(function() {
				self.dataSortDate('desc');
				pageView(nowPage);
				pageSort.hide();
			});
			var pageSortSizeAsc = $('<div />').text('파일크기↑').click(function() {
				self.dataSortSize('asc');
				pageView(nowPage);
				pageSort.hide();
			});
			var pageSortSizeDesc = $('<div />').text('파일크기↓').click(function() {
				self.dataSortSize('desc');
				pageView(nowPage);
				pageSort.hide();
			});
			pageSort.append(pageSortNameAsc).append(pageSortNameDesc).append(pageSortDateAsc).append(pageSortDateDesc).append(pageSortSizeAsc).append(pageSortSizeDesc);

			// 페이징 element
			if (totalPage == 0) totalPage = 1;
			var pageInfo = $('<div />').css({'width':40,'margin':'5px 0px 0px 0px','font-size':'7pt','text-align':'center'}).text('1/'+totalPage);
			var pageSlider = $('<div />').css({'height':this.lister.areaEle.height()-95,'margin':'10px 0px 0px 15px'}).slider({
				orientation: 'vertical'
				, value:1
				, min: -totalPage
				, max: -1
				, step: 1
				, slide: function(event, ui) {
					pageInfo.text(-ui.value+'/'+totalPage);
				}
				, change: function(event, ui) {
					self.lister.nowPageNo = null;
					pageView(-ui.value);
				}
			});
			var pageUp=$('<div />').css({'width':40,'margin':'4px 0px 0px 0px','font-size':'7pt','text-align':'center','cursor':'pointer'}).text('△').click(function() {
				if (nowPage != 1) {
					pageSlider.slider('value',-(nowPage-1));
				}
			});
			var pageDown=$('<div />').css({'width':40,'margin':'4px 0px 0px 0px','font-size':'7pt','text-align':'center','cursor':'pointer'}).text('▽').click(function() {
				if (nowPage < totalPage) {
					pageSlider.slider('value',-(nowPage+1));
				}
			});

			// 슬라이드
			this.lister.areaEle.wheel(function(e,d) {
				if (d == 1 && nowPage != 1) {
					pageSlider.slider('value',-(nowPage-1));
				} else if (d == -1 && nowPage < totalPage) {
					pageSlider.slider('value',-(nowPage+1));
				}
			});
			sideBar.append(pageSortBtn).append(pageInfo).append(pageUp).append(pageSlider).append(pageDown).append(pageSort);

			// 페이지영역
			var htmlUl = $(document.createElement('ul')).css({'float':'left','list-style':'none','margin':'0px','padding':'0px','overflow':'hidden','height':this.lister.areaEle.height(),'width':pieceWidth*x});
			this.lister.areaEle.append(sideBar).append(htmlUl);

			// 데이터출력
			var pageView = function(p) {
				var i,l;
				htmlUl.children().remove();

				if (p*x*y > totalImg) l = totalImg;
				else l = p*x*y;

				pageInfo.text(p + '/' + totalPage);
				nowPage = p;

				for (i = (p-1)*(x*y); i<l; i++) {
					var htmlImgSize = $('<div />').css({'font-size':'7pt','text-align':'center','clear':'both','letter-spacing':'-1','line-height':'8pt','color':'#555555'});
					var htmlImgName = $('<div />').css({'font-size':'8pt','text-align':'center','clear':'both','letter-spacing':'-1','line-height':'10pt','color':'#777777'});
					var htmlImgDate = $('<div />').css({'font-size':'7pt','text-align':'center','clear':'both','letter-spacing':'-1','line-height':'8pt','color':'#777777'});
					var htmlImgByte = $('<div />').css({'font-size':'7pt','text-align':'center','clear':'both','letter-spacing':'0','line-height':'8pt','color':'#777777'});
					var htmlLi = $('<li />');

					htmlImgName.text(self.lister.imgData[i].name);
					htmlImgDate.text(self.lister.imgData[i].mtime);
					htmlImgByte.text(self.lister.imgData[i].size+'B');
					htmlLi.data('data',self.lister.imgData[i]).width(pieceWidth-10).height(pieceHeight-10)
						.css({'float':'left','overflow':'hidden','margin':'4px 4px','cursor':'default','display':'inline','position':'relative'})
						.addClass('gallery_img');

					var liMenu = $('<div />').addClass('liMenu').css({'width':pieceWidth,'opacity':0});
					var liMenuView = $('<div />').addClass('liMenuView').css({'opacity':0});
					var liMenuRename = $('<div />').addClass('liMenuRename').css({'opacity':0});
					var liMenuDelete = $('<div />').addClass('liMenuDelete').css({'opacity':0});
					var liMenuCopy = $('<div id="cl_'+i+'"></div>');

					// 퀵메뉴:원본보기
					liMenuView.attr('title', '원본보기').hover(function(){
						$(this).css('opacity',1);
					},function(){
						$(this).css('opacity',0.5);
					}).click(function(){
						image_viewer($(this).parent().data('data').url);
					});

					// 퀵메뉴:이름바꾸기
					liMenuRename.attr('title', '이름바꾸기').hover(function(){
						$(this).css('opacity',1);
					},function(){
						$(this).css('opacity',0.5);
					}).click(function(){
						self.renameFile($(this).parent().data('data'));
					});

					// 퀵메뉴:삭제
					liMenuDelete.attr('title', '삭제').hover(function(){
						$(this).css('opacity',1);
					},function(){
						$(this).css('opacity',0.5);
					}).click(function(){
						self.deleteFile($(this).parent().data('data'));
					});

					// 이미지오버효과
					htmlLi.hover(function(){
						$(this).find('.liMenu').css({'opacity':1});
						$(this).find('.liMenuView').css({'opacity':0.5});
						$(this).find('.liMenuRename').css({'opacity':0.5});
						$(this).find('.liMenuDelete').css({'opacity':0.5});
						$(this).find('.liMenuCopy').css({'opacity':0.5});
						$(this).addClass('gallery_img_over');
					},function(){
						$(this).find('.liMenu').css({'opacity':0});
						$(this).find('.liMenuView').css({'opacity':0});
						$(this).find('.liMenuRename').css({'opacity':0});
						$(this).find('.liMenuDelete').css({'opacity':0});
						$(this).find('.liMenuCopy').css({'opacity':0});
						$(this).removeClass('gallery_img_over');
					}).click(function(){
						htmlUl.find('li').removeClass('gallery_img_select');
						$(this).addClass('gallery_img_select');
					});

					// 이미지출력
					regexp = new RegExp('\.(bmp|gif|jpg|jpeg|png)$', 'i');
					if (regexp.test(self.lister.imgData[i].url) && self.lister.imgData[i].width) {
						var htmlImg = $(new Image());
						htmlImg.attr('src',self.lister.imgData[i].url).css({'margin':'5px auto 5px auto'});
						htmlLi.append(htmlImg).append(htmlImgSize).append(htmlImgName).append(htmlImgDate).append(htmlImgByte);
						htmlImg.width(self.lister.imgData[i].width);
						htmlImg.height(self.lister.imgData[i].height);
						htmlImg.css('display','block').next().text(self.lister.imgData[i].width+' x '+self.lister.imgData[i].height);
						htmlLi.append(liMenu).append(liMenuView).append(liMenuRename).append(liMenuDelete).append(liMenuCopy);
						var img = htmlImg;
					} else {
						var loadingClone = self.lister.loadingImage.clone();
						htmlLi.append(loadingClone).append(htmlImgSize).append(htmlImgName).append(htmlImgDate).append(htmlImgByte);
						htmlLi.append(liMenu).append(liMenuRename).append(liMenuDelete).append(liMenuCopy);
						var img = loadingClone;
					}
					var maxWidth = pieceWidth-25;
					var maxHeight = pieceHeight-65;
					var displayWidth = imgWidth = parseInt(img.css('width').replace('px',''));
					var displayHeight = imgHeight = parseInt(img.css('height').replace('px',''));
					var ratioHeight = maxHeight / imgHeight;
					var ratioWidth = maxWidth / imgWidth;
					var ratio=ratioHeight > ratioWidth ? ratioWidth : ratioHeight;

					if (ratio < 1) {
						displayWidth = Math.round(imgWidth * ratio);
						displayHeight = Math.round(imgHeight * ratio);
						img.width(displayWidth).height(displayHeight);
					}
					if (displayHeight + 10 < maxHeight) {
						var m = Math.round((maxHeight - displayHeight) / 2);
						img.css('margin-top',m).css('margin-bottom',m);
					}
					htmlUl.append(htmlLi);

					// URL 복사
					clipboard({'id':'cl_'+i,'imgUrl':self.imgPath+'../../../module/script/extern/jquery/jstree/themes/summer/copycl.gif','copyText':self.lister.imgData[i].url,'callBack':'clipboardDone','width':50,'height':20,'cls':'liMenuCopy'});
					htmlLi.find('.liMenuCopy').attr('title', 'URL 복사').css('opacity',0).hover(function(){
						$(this).css('opacity',1);
					},function(){
						$(this).css('opacity',0.5);
					});
				};

				// 페이지번호
				if (self.lister.nowPageNo == null) {
					self.lister.nowPageNo = nowPage;
				}
			};

			if (self.lister.nowPageNo == null) {
				pageView(1);
			} else {
				pageView(self.lister.nowPageNo);
			}
		}

		/**
		 * 파일 이름바꾸기 처리
		 * @param {Object} imgData 정보
		 */
		, renameFile: function(imgData) {
			var self = this;
			this.lister.renameBoxEle.find('.input').val(imgData.name);
			this.lister.renameBoxEle.find('.btn_no').unbind('click').click(function(){
				self.lister.areaEle.unblock();
			});
			this.lister.renameBoxEle.find('.btn_save').unbind('click').click(function(){
				$.ajax({
					type: 'GET'
					, url: '../share/popup_webftp_ax.php?mode=renameFile'
					, data: ({'id':self.tree.treeObj.selected.attr('id'),'oldName':imgData.name,'newName':self.lister.renameBoxEle.find('.input').val()})
					, dataType: 'text'
					, async: false
					, success: function(data) {
						self.lister.areaEle.unblock();
						self.tree.treeObj.getList(self.tree.treeObj.selected.attr('id'));
					}
				});
			});
			this.lister.areaEle.block({
	            message: this.lister.renameBoxEle
	            , css: {'width':'200px', 'cursor':'default'}
				, overlayCSS: { 'cursor':'default' }
	        });
		}

		/**
		 * 파일 삭제 처리
		 * @param {Object} imgData 정보
		 */
		, deleteFile: function(imgData) {
			var self = this;
			this.lister.deleteBoxEle.find('.btn_no').unbind('click').click(function(){
				self.lister.areaEle.unblock();
			});
			this.lister.deleteBoxEle.find('.btn_yes').unbind('click').click(function(){
				$.ajax({
					type: 'GET'
					, url: '../share/popup_webftp_ax.php?mode=deleteFile'
					, data: ({'id':self.tree.treeObj.selected.attr('id'),'filename':imgData.name})
					, dataType: 'text'
					, async: false
					, success: function(data) {
						self.lister.areaEle.unblock();
						self.tree.treeObj.getList(self.tree.treeObj.selected.attr('id'));
					}
				});
			});
			this.lister.areaEle.block({
	            message: this.lister.deleteBoxEle
	            , css: {'width':'200px', 'cursor':'default'}
				, overlayCSS: { 'cursor':'default' }
	        });
		}

		/**
		 * 데이터정렬순서변경(이름)
		 * @param {String} s 정렬옵션
		 */
		, dataSortName: function(s) {
			this.lister.imgData.sort(function(param1,param2) {
				if (s == 'asc') {
					return (param1.name < param2.name) ? -1 : 1;
				} else {
					return (param1.name > param2.name) ? -1 : 1;
				}
			});
		}

		/**
		 * 데이터정렬순서변경(날짜)
		 * @param {String} s 정렬옵션
		 */
		, dataSortDate: function(s) {
			this.lister.imgData.sort(function(param1,param2) {
				if (s == 'asc') {
					return (param1.mtime < param2.mtime) ? -1 : 1;
				} else {
					return (param1.mtime > param2.mtime) ? -1 : 1;
				}
			});
		}

		/**
		 * 데이터정렬순서변경(파일사이즈)
		 * @param {String} s 정렬옵션
		 */
		, dataSortSize: function(s) {
			this.lister.imgData.sort(function(param1,param2) {
				if (s == 'asc') {
					return (param1.size < param2.size) ? -1 : 1;
				} else {
					return (param1.size > param2.size) ? -1 : 1;
				}
			});
		}

		/**
		 * 트리
		 */
		, set_tree: function() {
			var self = this;

			// 트리 구조 배치
			this.tree.menuEle = $('<div />').addClass('menu').appendTo($(this.tree.areaEle));
			this.tree.treeEle = $('<div />').addClass('tree').appendTo($(this.tree.areaEle));

			var menuHtml = '';
			menuHtml += '<a href="#" rel="create" class="create" title="하위 폴더 생성"></a>';
			menuHtml += '<a href="#" rel="rename" class="rename" title="폴더 이름 변경"></a>';
			menuHtml += '<a href="#" rel="remove" class="remove" title="폴더 삭제"></a>';
			menuHtml += '<a href="#" rel="open_all" class="open_all" title="모두확장"></a>';
			menuHtml += '<a href="#" rel="close_all" class="close_all" title="모두접기"></a>';
			menuHtml += '<a href="#" rel="refresh" class="refresh" title="새로고침"></a>';
			menuHtml += '<span id="btn_upload_placeholder"></span>';
			this.tree.menuEle.append(menuHtml);

			// 트리 출력
			this.tree.treeObj = $.tree.create();
			this.tree.treeObj.init(this.tree.treeEle, {
				data: {
					type: 'json'
					, async: true
					, opts: {
						url: '../share/popup_webftp_ax.php?mode=dir'
					}
				}
				, ui: {
					animation: 0
					, theme_name: 'summer'
				}
				, plugins: {
					contextmenu: { // 오른쪽 마우스버튼을 메뉴접근처리
						items: {
							create: {
								label: '하위 폴더 생성'
								, icon: 'create'
								, visible: function (NODE, TREE_OBJ) { if(NODE.length != 1) return false; return TREE_OBJ.check('creatable', NODE); }
								, action: function (NODE, TREE_OBJ) {
									if (TREE_OBJ.selected === undefined || TREE_OBJ.selected === false || $(NODE).attr('id') != TREE_OBJ.selected.attr('id')) {
										TREE_OBJ.select_branch(NODE);
									}
									TREE_OBJ.create();
								}
								, separator_after : true
							}
							, rename: {
								label: '폴더 이름 변경'
								, icon: 'rename'
								, visible: function (NODE, TREE_OBJ) { if(NODE.length != 1) return false; return TREE_OBJ.check('renameable', NODE); }
								, action: function (NODE, TREE_OBJ) {
									TREE_OBJ.rename(NODE);
								}
							}
							, remove: {
								label: '폴더 삭제'
								, icon: 'remove'
								, visible: function (NODE, TREE_OBJ) { var ok = true; $.each(NODE, function () { if(TREE_OBJ.check('deletable', this) == false) ok = false; return false; }); return ok; }
								, action: function (NODE, TREE_OBJ) {
									if (TREE_OBJ.selected === undefined || TREE_OBJ.selected === false || $(NODE).attr('id') != TREE_OBJ.selected.attr('id')) {
										TREE_OBJ.select_branch(NODE);
									}
									TREE_OBJ.remove();
								}
							}
						}
					}
				}
				, callback: {
					error: function(TEXT, TREE_OBJ) {
						alert(TEXT);
					}
					, onchange: function(NODE, TREE_OBJ) {
						TREE_OBJ.getList(NODE.id); //이미지목록받기
					}
					, beforecreate: function(NODE, REF_NODE, TYPE, TREE_OBJ) {
						var res = false;
						$.ajax({
							type: 'GET'
							, url: '../share/popup_webftp_ax.php?mode=createDir'
							, data: ({'id':TREE_OBJ.selected.attr('id')})
							, dataType: 'text'
							, async: false
							//, global: false
							, error: function(XMLHttpRequest, textStatus, errorThrown) {
								res = false;
							}
							, success: function(data) {
								// 텍스트 변경
								$('a',NODE).text(data);
								// id 설정
								if (TREE_OBJ.selected.attr('id') == '' || TREE_OBJ.selected.attr('id') == self.tree.topPath) {
									$(NODE).attr('id',data);
								} else {
									$(NODE).attr('id',TREE_OBJ.selected.attr('id')+'_-_'+data);
								}
								res = true;
							}
						});
						return res;
					}
					, beforedelete: function(NODE, TREE_OBJ) {
						if (confirm('선택한 디렉토리를 삭제하시겠습니까?') === false){
							return false;
						}

						var res = false;
						$.ajax({
							type: 'GET'
							, url: '../share/popup_webftp_ax.php?mode=deleteDir'
							, data: ({'id':TREE_OBJ.selected.attr('id')})
							, dataType: 'text'
							, async: false
							//, global: false
							, error: function(XMLHttpRequest, textStatus, errorThrown) {
								res = false;
							}
							, success: function(data) {
								res = true;
							}
						});
						return res;
					}
					, onrename: function(NODE, TREE_OBJ, RB) {
						TREE_OBJ.lock(true); //rename처리는 여러가지 이유로 실패할수있으니. 이곳에서 lock을 걸어논다.
						$.ajax({
							type: 'GET'
							, url: '../share/popup_webftp_ax.php?mode=renameDir'
							, data: ({'id':NODE.id,'newName':$(NODE).children('a:visible').text().replace(unescape('%A0'), '')})
							, dataType: 'text'
							, async: false
							//, global: false
							, error: function(XMLHttpRequest, textStatus, errorThrown) {
								$.tree.rollback(RB);// rename이 실패되었으므로 rollback한다.
								TREE_OBJ.lock(false);
							}
							, success: function(data) {
								TREE_OBJ.lock(false);//성공했으면 lock을 푼다.
								/*
								디렉토리에 대한 rename처리는 곧 jstree의 id값변경을 말한다.
								id값이 변경되면 하위디렉토리의 모든 id값도 변경되야된다.
								그러므로 우선 현재단계에서는 디렉토리목록을 닫고, 윗단계로올라가서
								refresh를 해주어 id값이 갱신되도록한다.
								*/
								var id = $(NODE).children('a:visible').text().replace(unescape('%A0'), '');
								$(NODE).attr('id', id);
								TREE_OBJ.close_branch(NODE);
								TREE_OBJ.select_branch(NODE);
								TREE_OBJ.refresh();
							}
						});
					}
					, onload : function(TREE_OBJ) {
						if (!TREE_OBJ.selected) {
							TREE_OBJ.select_branch(TREE_OBJ.container.find('li:eq(0)'));
						}
					}
				}
			});

			// 파일목록을 받는다.
			this.tree.treeObj.getList = function(id){
				$.ajax({
					type: 'GET'
					, url: '../share/popup_webftp_ax.php?mode=listFile'
					, data: ({'id':id})
					, dataType: 'json'
					, async: false
					, success: function(data) {
						self.lister.imgData = data;
						self.print_lister();
					}
				});
			};

			// jstree상단에 있는 버튼들에 대한 설정
			$(this.tree.menuEle)
				.find('a').not('.lang')
					.bind('click', function () {
						self.tree.treeObj[$(this).attr('rel')]();
						this.blur();
					})
					.end().end()
				.children('.cmenu')
					.hover( function() { $(this).addClass('hover'); }, function() { $(this).removeClass('hover'); });
		}

		/**
		 * 파일업로드
		 */
		, set_upload: function() {
			var self = this;

			// 업로드 상태화면의 불투명 백그라운드
			$('<div />').addClass('containerBlock').css({'position':'absolute','left':'0px','top':'0px','width':'830px','height':'550px','z-index':'1','display':'none','background-color':'#666666','opacity':0.5}).prependTo(self.upload.areaEle.parent());

			// 리사이즈 이벤트
			$('input[name=resize][value=no]', self.upload.areaEle).click(function() {
				$('input[name=resizePx]', self.upload.areaEle).prop('disabled',true);
			});
			$('input[name=resize][value=yes]', self.upload.areaEle).click(function() {
				$('input[name=resizePx]', self.upload.areaEle).prop('disabled',false);
			});

			// swfupload모듈정의
			var fileSizeLimit = 1024 * parseInt(self.uploadSize.replace('M','').replace('m',''));
			var swfuploadConfig = {
				// Backend Settings
				upload_url: '../share/popup_webftp_ax.php'
				, post_params: {}

				// File Upload Settings
				, file_size_limit: fileSizeLimit // 업로드 제한용량
				, file_types: '*.ai;*.bmp;*.jpg;*.jpeg;*.gif;*.png;*.swf;*.csv;*.doc;*.hwp;*.ppt;*.xls;*.txt;*.zip;*.js;*.ico;*.xml'
				, file_types_description : 'Image Files'
				, file_upload_limit : '0'
				, file_queue_limit : '0' // 파일예약최대수(0:무한대)

				// Event Handler : 초기화하기
				, file_dialog_start_handler: function() {
					var stats;
					do {
						stats = this.getStats();
						this.cancelUpload();
					} while (stats.files_queued !== 0);
					$('table tbody tr', self.upload.statusEle).remove();
				}

				// 파일 예약하기
				, file_queued_handler: function(file) {
					$('.containerBlock').show();
					self.upload.areaEle.show();

					var dialogList = $('table tbody', self.upload.statusEle);
					var iptFilename=convertKonglish(file.name);
					iptFilename = iptFilename.replace(/[^A-Za-z0-9_\-\.\(\)]+/,'');

					var appendHtml=
						'<tr>'+
						'<td><input type="text" value="'+iptFilename+'" /></td>'+
						'<td><div class="pb_back"><div class="pb">'+file.name+'</div></div></td>'+
						'<td><input type="radio" name="ow_'+file.id+'" checked="checked" value="yes"/>예 <input type="radio" name="ow_'+file.id+'" value="no"/>아니요</td>'+
						'</tr>';
					dialogList.append(appendHtml);
					dialogList.find('tr:last').data('fileId',file.id);

					swfobj.addPostParam('uploadDir',self.tree.treeObj.selected.attr('id'));
					self.upload.btnUploadStart.show();
				}

				// 파일예약에러헨들러
				, file_queue_error_handler: function(file, errorCode, message) {
					switch (errorCode) {
						case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
							alert(file.name+'은 업로드하실수 없습니다.\n파일은 '+self.uploadSize+'B 이하만 업로드 가능합니다');
							break;
						case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
							alert(file.name+'은 업로드하실수 없습니다.\n0바이트 파일은 업로드 할 수 없습니다.');
							break;
						default:
							alert(file.name+'은 업로드하실수 없습니다.');
							break;
					}
				}

				// 파일예약완료핸들러
				, file_dialog_complete_handler: function(numFilesSelected, numFilesQueued) {
				}

				// 업로드시작
				, upload_start_handler: function(file) {
					return true;
				}

				// 업로드진행핸들러
				, upload_progress_handler: function(file, bytesLoaded, bytesTotal) {
					var eachFile;
					var dialogList = $('table tbody tr', self.upload.statusEle).each(function() {
						if ($(this).data('fileId') == file.id) {
							eachFile = $(this);
							return;
						}
					});
					var widthPx = Math.ceil((bytesLoaded / bytesTotal) * 160);
					eachFile.find('.pb').css('background-position',widthPx+'px 0px');
				}

				// 업로드에러헨들러
				, upload_error_handler: function (file, errorCode, message) {
					var eachFile;
					var dialogList = $('table tbody tr', self.upload.statusEle).each(function() {
						if ($(this).data('fileId') == file.id) {
							eachFile = $(this);
							return;
						}
					});

					switch (errorCode) {
						case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
							eachFile.remove();
							break;
						default:
							eachFile.find('.pb_back').css('background-color','#CC0000');
							break;
					}

					switch (errorCode) {
						case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
							this.debug("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
							break;
						case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
							this.debug("Error Code: No backend file, File name: " + file.name + ", Message: " + message);
							break;
						case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
							this.debug("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
							break;
						case SWFUpload.UPLOAD_ERROR.IO_ERROR:
							this.debug("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
							break;
						case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
							this.debug("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
							break;
						case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
							this.debug("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
							break;
						case SWFUpload.UPLOAD_ERROR.SPECIFIED_FILE_ID_NOT_FOUND:
							this.debug("Error Code: The file was not found, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
							break;
						case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
							this.debug("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
							break;
						case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
							break;
						case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
							break;
						default:
							this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
							break;
					}
				}

				// 업로드 성공 핸들러
				, upload_success_handler: function(file, serverData) {
					if (!serverData) return;
					eval("var data = " + serverData);
					if (data && data.success === false) {
						this.uploadError(file, -250, data.message);
						alert(file.name+'은 업로드하실수 없습니다.\n('+data.message+')');
					}
				}

				// 업로드 완료 핸들러
				, upload_complete_handler: function(file) {
					if (this.getStats().files_queued === 0) {
						$('.containerBlock').hide();
						self.upload.areaEle.hide();
						self.tree.treeObj.getList(this.settings.post_params.uploadDir);
					} else {
						this.startUpload();
					}
				}

				// 버튼세팅
				, button_image_url: this.imgPath+'design/btn_swfupload.gif'
				, button_placeholder_id : 'btn_upload_placeholder'
				, button_width: 54
				, button_height: 16

				// 플래쉬세팅
				, flash_url: this.imgPath+'../../../module/script/extern/swfupload/swfupload.swf'
				, custom_settings: {}

				// Debug Settings
				, debug: false
			};

			// swfupload 인스턴스 선언
			if (this.diskErrno == null) {
				var swfobj = new SWFUpload(swfuploadConfig);
				$(this.tree.menuEle).find('.swfupload').attr('title', '사진올리기');
				swfobj.clickUpload = function(instance) {
					$('table tbody tr', self.upload.statusEle).each(function(){
						var thisValue=$(this).find('input').val();
						instance.addFileParam($(this).data('fileId'),'saveName',thisValue);
						var isOverwrite=$(this).find('input[type=radio]:checked').val();
						instance.addFileParam($(this).data('fileId'),'isOverwrite',isOverwrite);
					});
					instance.addPostParam('saveName',$(this).find(':text').val());
					instance.addPostParam('mode','uploadFile');
					if ($('input[name=resize][value=yes]:checked', self.upload.areaEle).length == 1) {
						instance.addPostParam('resize',$('input[name=resizePx]', self.upload.areaEle).val());
					} else {
						instance.addPostParam('resize',0);
					}
					instance.addPostParam('PHPSESSID',$('input[name=PHPSESSID]', self.upload.areaEle).val());
					instance.startUpload();
				};
			} else {
				var title = '';
				var msg = '<span class="button small gray"><button type="button" onclick="$.unblockUI()">닫기</button></span>';
				if (this.diskErrno == '001') {
					title = '현재 계정용량이 부족합니다.';
					msg = '파일이 업로드되지 않습니다.<br/>계정용량 서비스를 신청해주시기 바랍니다.' + msg;
				} else if (this.diskErrno == '002') {
					title = '계정용량 서비스기간이 만료되었습니다.';
					msg = '파일이 업로드되지 않습니다.<br/>기간연장 또는 서비스변경신청을 해주시기 바랍니다.' + msg;
				}
				var imgEle = $('<img/>').attr({
					'src':this.imgPath+'design/btn_swfupload.gif'
					, 'alt':'사진올리기'
				}).click(function() {
					$.warnUI(title,msg,0);
				}).appendTo($('#btn_upload_placeholder').css({'width':'54px','height':'16px','overflow':'hidden','display':'inline-block','margin-top':'2px'}));
			}

			// 업로드시작 버튼 이벤트
			self.upload.btnUploadStart.click(function(){
				$(this).hide();
				var postData = {};
				var n = 0;
				$('table tbody tr', self.upload.statusEle).each(function() {
					if ($(this).find(':radio:checked').val() == 'no') {
						postData['fileId['+n+']'] = $(this).data('fileId');
						postData['fileName['+n+']'] = $(this).find(':text').val();
						n++;
					}
				});

				if (n) {
					postData['mode'] = 'existFile';
					postData['id'] = swfobj.settings.post_params.uploadDir;
					$.ajax({
						type: 'POST'
						, url: '../share/popup_webftp_ax.php'
						, data: postData
						, dataType: 'text'
						, async: false
						, success: function(data) {
							$('table tbody tr', self.upload.statusEle).each(function(){
								if ($(this).data('fileId') == data) {
									alert($(this).find(':text').val()+'는 중복된 파일명입니다');
									$(this).find(':text').focus();
								}
							});
							$(self.upload.btnUploadStart).show();
						}
					});
				} else {
					swfobj.clickUpload(swfobj);
				}
			});

			// 닫기 버튼 이벤트
			self.upload.btnUploadClose.click(function() {
				var stats;
				do {
					stats = swfobj.getStats();
					swfobj.cancelUpload();
				} while (stats.files_queued !== 0);

				$('.containerBlock').hide();
				self.upload.areaEle.hide();
			});
		}
	};

	/**
	 * Webftp.init() 클래스의 Property 설정
	 */
	Webftp.init.prototype = Webftp;

	/**
	 * Webftp.init() 클래스 인스턴스 생성
	 * @param {Array} options 옵션
	 * @return {Object} Webftp.init() 클래스 인스턴스
	 */
	jQuery.fn.webftp = function(options) {
		var firstEle = $(this)[0];
		options = $.extend({
			lister: {
				areaEle: $('#filelist', $(firstEle))
				, renameBoxEle: $('#rename_box', $(firstEle))
				, deleteBoxEle: $('#delete_box', $(firstEle))
			}
			, tree: {
				areaEle: $('.webftp_tool #dir_tree')
			}
			, upload: {
				areaEle: $('#upload_box', $(firstEle))
				, statusEle: $('#upload_status', $(firstEle))
				, btnUploadStart: $('div.btn_upload_start', $(firstEle))
				, btnUploadClose: $('div.btn_upload_close', $(firstEle))
			}
		}, options);
		return new Webftp.init(firstEle, options);
	};

})(jQuery);