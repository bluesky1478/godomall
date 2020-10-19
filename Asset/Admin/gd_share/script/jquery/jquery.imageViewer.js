/**
 * 이미지뷰어
 * @author oneorzero, sunny
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */
(function($) {
	/**
	 * ImageViewer.init() 클래스의 Property 정의
	 */
	ImageViewer = {
		ele: null // Element (이미지를 감싸는 레이어)
		, viewMode: 'auto' // View모드는 auto(화면사이즈에맞게) , ori(원본사이즈)가 있습니다.
		, wrapEle: null // 전체를 감싸는 레이어
		, btnAutoEle: null // '화면사이즈에맞게' 버튼
		, btnOriEle: null // '원본사이즈' 버튼
		, zoomRatioEle: null // 줌비율 출력영역
		, viewImage: null // 이미지객체
		, viewImageOWidth: 0 // 이미지의 원본 가로 사이즈(OriginalWidth)가 저장될 변수입니다.
		, viewImageOHeight: 0 // 이미지의 원본 세로 사이즈(OriginalHeight)가 저장될 변수입니다.
		, mouseoffSet: {} // 마우스좌표가 들어갈곳

		/**
		 * ImageViewer.init() 클래스
		 * @classDescription ImageViewer 출력하는 클래스
		 * @param {Object} ele Element
		 * @param {Array} opts 옵션
		 * @return {Object} new instance 리턴
		 * @constructor
		 */
		, init: function(ele, opts) {
			opts = opts || {};
			$.extend( this, opts );
			this.ele = ele;
			this.start();
		}

		/**
		 * 시작
		 */
		, start: function() {
			var self = this;
			// 현재 주소로부터 이미지경로 추출
			var tmp = window.location.href;
			var imagePath = tmp.substr(tmp.lastIndexOf('?')+1);
			// 이미지로딩
			var img = new Image();
			this.viewImage = $(img).load(function () {
				self.defineSize();
				$(self.ele).append(this);
			}).attr('src', imagePath);


			// '화면사이즈에맞게' 버튼
			this.btnAutoEle.click(function(){
				self.viewMode = 'auto';
				self.imageReplace();
			});

			// '원본사이즈' 버튼
			this.btnOriEle.click(function(){
				self.viewMode = 'ori';
				self.imageReplace();
			});
		}

		/**
		 * 사이즈 정의
		 */
		, defineSize: function() {
			var self = this;
			this.viewImageOWidth = this.viewImage.context.width;
			this.viewImageOHeight = this.viewImage.context.height;

			// 이미지 원본사이즈가 너무 작은 경우에는 윈도우사이즈를 축소시킨다
			if (this.viewImageOWidth < 800 && this.viewImageOHeight < 550) {
				var windowWidth = this.viewImageOWidth + 10;
				var windowHeight = this.viewImageOWidth + 10;
				if (windowWidth < 300) windowWidth = 300;
				if (windowHeight < 300) windowHeight = 300;
				window.resizeTo(windowHeight,windowHeight);
			}

			// 처음표시를 위해 실행한다.
			this.imageReplace();

			// 리사이즈시 작동되도록 등록한다.
			$(window).resize(function(){
				self.imageReplace();
			});
		}

		/**
		 * 이미지로딩,화면리사이즈,View모드변환시 실행되는 함수이다
		 */
		, imageReplace: function() {
			var self = this;
			var wrapWidth = this.wrapEle.width(); // 화면의 가로사이즈
			var wrapHeight = this.wrapEle.height() - 50; // 화면의 세로사이즈-(하단 툴바사이즈)

			if (this.viewMode == 'auto') {
				// View모드가 '화면사이즈에맞게'인 경우
				// 이미지 원본사이즈와 화면사이즈로 이미지를 얼마나 줄여야되는지 비율(ratio)을 알아낸다.
				var ratioHeight = wrapHeight / this.viewImageOHeight;
				var ratioWidth = wrapWidth / this.viewImageOWidth;
				var ratio = ratioHeight> ratioWidth ? ratioWidth : ratioHeight;

				// 이미지를 확대시켜야 되는경우에는 원본으로 나타낸다.
				if (ratio > 1) {
					ratio = 1;
				}

				// 비율 표시
				this.zoomRatioEle.text(parseInt(ratio*100) + '%');

				// 화면에 표시할 이미지사이즈를 구한다.
				var displayImgWidth = Math.round(this.viewImageOWidth * ratio);
				var displayImgHeight = Math.round(this.viewImageOHeight * ratio);

				// 이미지 세로에 여백이 있을경우 중간정렬시킨다.
				this.viewImage.css('margin-top',parseInt((wrapHeight-displayImgHeight)/2));

				// 이미지사이즈를 설정하고 css를 설정한다
				this.viewImage.width(displayImgWidth).height(displayImgHeight);
				this.viewImage.css({'cursor':'default'});

			} else {
				//View모드가 '원본사이즈'인경우
				this.zoomRatioEle.text('100%'); // 비율은 무조건 100
				this.ele.css('overflow','auto'); // 이미지사이즈가 화면보다 큰경우 스크롤이 생기게 한다.

				// 화면에 표시할 사이즈는 이미지원본사이즈와 동일
				var displayImgWidth = this.viewImageOWidth;
				var displayImgHeight = this.viewImageOHeight;

				// 이미지 세로에 여백이 있을 경우 중간 정렬시킨다.
				if (wrapHeight > displayImgHeight) {
					this.viewImage.css('margin-top',parseInt((wrapHeight-displayImgHeight)/2));
				} else {
					this.viewImage.css('margin-top','0px');
				}

				// 드래그에 따라 이미지 스크롤위치를 변화시킨다.
				this.mouseoffSet = {}; // 마우스좌표가 들어갈곳

				this.viewImage.bind('dragstart', function(event){
					self.mouseoffSet.x = self.ele.attr('scrollLeft') + event.offsetX;
					self.mouseoffSet.y = self.ele.attr('scrollTop') + event.offsetY;
				});

				this.viewImage.bind('drag', function(event){
					self.ele.attr('scrollLeft', self.mouseoffSet.x-event.offsetX);
					self.ele.attr('scrollTop', self.mouseoffSet.y-event.offsetY);
				});

				// 이미지사이즈를 설정하고 css를 설정한다
				this.viewImage.width(displayImgWidth).height(displayImgHeight);
				this.viewImage.css({'cursor':'move'}); // 원본보기할때에는 커서모양이 변한다.
			}

			this.ele.width(wrapWidth);
			this.ele.height(wrapHeight);
		}
	};

	/**
	 * ImageViewer.init() 클래스의 Property 설정
	 */
	ImageViewer.init.prototype = ImageViewer;

	/**
	 * ImageViewer.init() 클래스 인스턴스 생성
	 * @param {Array} options 옵션
	 * @return {Object} ImageViewer.init() 클래스 인스턴스
	 */
	jQuery.fn.imageViewer = function(opts) {
		return new ImageViewer.init(this, opts);
	};

})(jQuery);
