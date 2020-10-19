/**
 * Created by yjwee on 2015-09-14.
 */
/**
 * 본인확인
 * @author sunny, artherot
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */
(function ($) {
    /**
     * Identification.init() 클래스의 Property 정의
     */
    Identification = {
        ele: null										// Element (이미지를 감싸는 레이어)
        , rncheckCd: null								// 본인확인코드(ipin:아이핀,authCellphone:휴대폰인증)
        , callType: null								// 호출유형 (introadult:인트로성인,findid:아이디찾기,findpwd:비밀번호찾기,sleepMember:휴면회원)
        , rnCheckEle: null								// 본인확인벙법 Element
        , authShopUrlEle: null							// 휴대폰 인증 도메인 Element(휴대폰 인증 용)
        , authCpCodeEle: null							// 휴대폰 인증 cpCode Element(휴대폰 인증 용)
        , divLayer: null								// 기본 div 레이어 설정
        , iframeLayer: null								// 기본 iframe 레이어 설정
        , before: function (fObj) {
            return true;
        }		// 인증전 처리함수
        , success_ipin: function (fObj, data) {
        }			// 아이핀 성공 콜백함수
        , success_authCellphone: function (fObj, data) {
        }	// 휴대폰 인증 성공 콜백함수
        , fail_authentication: function (fObj, data) {
        }	// 인증 실패 콜백함수

        /**
         * Identification.init() 클래스
         * @classDescription Identification 출력하는 클래스
         * @param {Object} ele Element
         * @param {Array} opts 옵션
         * @constructor
         */
        , init: function (ele, opts) {
            opts = opts || {};
            $.extend(this, opts);
            this.ele = ele;
            this.start();
        }

        /**
         * 시작
         */
        , start: function () {
            var self = this;
            // 본인확인코드
            if (this.rnCheckEle != null) {
                this.rnCheckEle.each(function () {
                    if ($(this).get(0).checked === true) {
                        self.rncheckCd = $(this).val();
                    }
                });
            }

            if (this.rncheckCd === null) {
                return;
            }
            // before
            if (this.before() === false) {
                return;
            }
            // 기본 div 레이어 설정
            this.divLayer = '<div id="div_identify" name="div_identify" class="div_identify" style="display:none1; width:100%; height:300px; border:solid 3px #676767;"></div>';

            // 기본 iframe 레이어 설정
            //this.iframeLayer	= '<iframe id="ifm_identify" name="ifm_identify" class="ifm_identify" style="display:none1; width:100%; height:300px; border:solid 3px #676767;"></iframe>';
            this.iframeLayer = '<iframe id="ifrmHpauth" name="ifrmHpauth" class="ifm_identify" style="display:none1; width:100%; height:300px; border:solid 3px #676767;"></iframe>';

            // 본인확인
            if (this.rncheckCd == 'ipin') {
                this.ipin();
            } else if (this.rncheckCd == 'authCellphone') {
                this.authCellphone();
            }
        }

        /**
         * 아이핀
         */
        , ipin: function () {
            // 아이핀 종류에 따라서 팝업창 체크 (/module/php/Template_/Template.class.php 에서 체크)
            // @todo: 구버전 아이핀은 그냥 주석처리함... 필요할 경우 종류에 따라 체크하는 부분을 적용해야함
            // if ($('#ipin_checker').val() == 'new') {
            if ($('.div_identify').length == 0) {
                // 기본 div 레이어 출력
                $('#extra_content').append(this.divLayer);
            }

            // 팝업 차단을 위해 팝업창을 미리 띄움
            var prePopupData = {
                'url': 'about:blank',
                'target': 'popupCertKey',
                'width': '450',
                'height': '550',
                'top': '100',
                'left': '100',
                'fullscreen': 'no',
                'menubar': 'no',
                'status': 'no',
                'toolbar': 'no',
                'titlebar': 'yes',
                'location': 'no',
                'scrollbar': 'no'
            };
            var popupCertKey = popup(prePopupData);debugger;

            // NICE iPIN main 창 출력
            $.get('../member/ipin/nice_ipin_main.php', {callType: this.callType}, function (data) {
                console.log(data);
                $('#div_identify').html(data);
            });
            //} else {
            //    if ($('.ifm_identify').length == 0) {
            //        $('<iframe/>').attr({
            //            'name': 'ifm_identify',
            //            'class': 'ifm_identify'
            //        }).css('display', 'none').appendTo($('body'));
            //    }
            //    $('iframe.ifm_identify').attr('src', '../member/ipin/ipincheck_request.php?callType=' + this.callType);
            //}
        }

        /**
         * 아이핀 결과
         */
        , ipin_result: function (data) {
            if (this.callType == 'joinmember' && data.memExist != null && data.memExist == 'y') {
                if (data.memAppFl != null && data.memAppFl != 'y') {
                    alert('이미 가입이 되어 있지만 아직 승인이 되지 않았습니다.\n쇼핑몰 담당자에게 문의 하십시요!');
                } else {
                    alert('이미 가입이 되어 있습니다.');
                }
                this.fail_authentication(this.ele, data);
            } else if (this.callType == 'joinmember' && data.memRefuse != null && data.memRefuse == 'y') {
                alert('회원탈퇴 후 ' + data.rejoinDay + '일 동안 재가입할 수 없습니다. 회원님은 ' + data.memHackDt + '에 탈퇴하셨습니다.');
                this.fail_authentication(this.ele, data);
            } else if (data.minorFl == 'y' && data.strMinor != '1') {
                alert('연령 제한으로 인증 실패했습니다.');
                this.fail_authentication(this.ele, data);
            } else if (data.strRetCd == '1' && data.strRetDtlCd == 'A') {
                // 아이핀인증성공
                alert('아이핀인증이 정상처리 되었습니다.');
                this.success_ipin(this.ele, data);
            } else {
                // 아이핀인증실패
                alert('아이핀인증이 실패했습니다. ' + strMsg);
                this.fail_authentication(this.ele, data);
            }
        }

        /**
         * 휴대폰 본인확인
         */
        , authCellphone: function () {

            if ($('.ifm_identify').length == 0) {
                // 기본 iframe 레이어 출력
                $('#extra_content').append(this.iframeLayer);
            }

            // 팝업 차단을 위해 팝업창을 미리 띄움
            var prePopupData = {
                'url': 'about:blank',
                'target': 'DRMOKWindow',
                'width': '425',
                'height': '550',
                'top': '100',
                'left': '100',
                'fullscreen': 'no',
                'menubar': 'no',
                'status': 'no',
                'toolbar': 'no',
                'titlebar': 'yes',
                'location': 'no',
                'scrollbar': 'no'
            };
            var popupCertKey = popup(prePopupData);

            // 휴대폰 본인 인증 실행 페이지 (드림시큐리티 페이지)
            var authUrl = 'http://hpauthdream.godo.co.kr/module/NEW_hpauthDream_Main.php';

            // 휴대폰 본인 인증 처리 페이지
            var callbackUrl = this.authShopUrlEle + 'authCellphone_Dreamsecurity_Result.php';

            // 드림시큐리티의 Dream_urlcode 에 따른 처리
            // url코드 목록
            // 01001 : 회원가입
            // 01002 : 정보변경
            // 01003 : ID찾기
            // 01004 : 비밀번호찾기
            // 01005 : 본인확인용
            // 01006 : 성인인증
            // 01007 : 상품구매/결제
            // 01999 : 기타

            if (this.callType == 'joinmember') {			//회원가입
                var callTypeStr = 'joinmember';
            } else if (this.callType == 'findid') {		//아이디찾기
                var callTypeStr = 'findid';
            } else if (this.callType == 'findpwd') {		//비밀번호찾기
                var callTypeStr = 'findpwd';
            } else if (this.callType == 'introadult') {	//성인인증
                var callTypeStr = 'adultcheck';
            } else if (this.callType == 'sleepMember') {    // 휴면회원
                var callTypeStr = 'sleepMember';
            }

            // 드림시큐리티의 휴대폰 본인확인창 출력
            $('iframe.ifm_identify').attr('src', authUrl + '?callType=' + callTypeStr + '&shopUrl=' + callbackUrl + '&cpid=' + this.authCpCodeEle);
        }

        /**
         * 휴대폰 본인확인 결과
         */
        , authCellphone_result: function (data) {
            if (this.callType == 'joinmember' && data.memExist != null && data.memExist == 'y') {
                if (data.memAppFl != null && data.memAppFl != 'y') {
                    alert('이미 가입이 되어 있지만 아직 승인이 되지 않았습니다.\n쇼핑몰 담당자에게 문의 하십시요!');
                } else {
                    alert('이미 가입이 되어 있습니다.');
                }
                this.fail_authentication(this.ele, data);
            } else if (this.callType == 'joinmember' && data.memRefuse != null && data.memRefuse == 'y') {
                alert('회원탈퇴 후 ' + data.rejoinDay + '일 동안 재가입할 수 없습니다. 회원님은 ' + data.memHackDt + '에 탈퇴하셨습니다.');
                this.fail_authentication(this.ele, data);
            } else if (data.minorFl == 'y' && data.strMinor != '1') {
                alert('연령 제한으로 인증 실패했습니다.');
                this.fail_authentication(this.ele, data);
            } else if (data.strRetCd == 'success' && data.strRetDtlCd == '00') {
                // 휴대폰 본인확인 인증성공
                alert('휴대폰 본인확인 인증이 정상처리 되었습니다.');
                this.success_authCellphone(this.ele, data);
            } else {
                // 휴대폰 본인확인 인증실패
                alert('휴대폰 본인확인 인증이 실패했습니다. ' + strMsg);
                this.fail_authentication(this.ele, data);
            }
        }
    };

    /**
     * Identification.init() 클래스의 Property 설정
     */
    Identification.init.prototype = Identification;

    /**
     * Identification.init() 클래스 인스턴스 생성
     * @param {Array} options 옵션
     * @return {Object} Identification.init() 클래스 인스턴스
     */
    jQuery.fn.identification = function (opts) {
        return new Identification.init(this, opts);
    };

})(jQuery);
