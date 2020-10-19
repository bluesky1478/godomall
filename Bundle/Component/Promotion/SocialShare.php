<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Component\Promotion;

use App;
use Exception;
use Framework\Utility\ComponentUtils;
use Framework\Utility\UrlUtils;
use Globals;
use Request;
use Respect\Validation\Validator as v;
use SocialLinks\Page as SocialLink;
use UserFilePath;

/**
 * Class SocialShare
 *
 * @see     SocialLinks https://github.com/oscarotero/social-links
 * @package Bundle\Component\Promotion
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class SocialShare extends \Component\AbstractComponent
{
    /**
     * @var 스토리지 저장용 키
     */
    const PROMOTION_SOCIAL_SHARE_KEY = 'promotion.snsShare';

    /**
     * @var SNS에 공유할 이미지 파일명
     */
    const DEFAULT_IMAGE_NAME = 'snsRepresentImage';

    /**
     * @var 치환코드
     */
    const MALL_NAME_REPLACE_KEY = '{rc_mallNm}';
    const GOODS_NAME_REPLACE_KEY = '{rc_goodsNm}';
    const BRAND_NAME_REPLACE_KEY = '{rc_brandNm}';

    /**
     * @var array
     */
    private $_config = [];

    /**
     * @var array
     */
    private $_replaceText = [];

    /**
     * @var null|string
     */
    private $_skinPath = null;

    /**
     * SocialShare 생성자.
     *
     * @param array $replaceText 치환코드키 -> 변환될 값 배열
     */
    public function __construct(array $replaceText = null)
    {
        // 모바일 여부에 따라 스킨 경로 지정
        if (!Request::isMobile()) {
            $this->_skinPath = PATH_SKIN;
        } else {
            $this->_skinPath = PATH_MOBILE_SKIN;
        }

        // 설정 초기화
        if ($replaceText !== null && is_array($replaceText)) {
            $this->_replaceText = array_replace(
                [
                    self::MALL_NAME_REPLACE_KEY => Globals::get('gMall.mallNm'),
                ],
                $replaceText
            );
        }
    }

    /**
     * setConfig
     *
     * @param array $data 사용자 요청 데이터
     *
     * @throws Exception
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function setConfig($data)
    {
        // 카카오앱키 체크
        if (empty($data['kakaoAppKey']) === false && !v::alnum()->noWhitespace()->length(32)->validate($data['kakaoAppKey'])) {
            throw new Exception(__('카카오앱키는 공백없는 32자리 입니다.'));
        }
        gd_isset($data['useKakaoLinkCommerce'], 'n');
        // 저장
        ComponentUtils::setPolicy(self::PROMOTION_SOCIAL_SHARE_KEY, $data);
        $this->_config = $this->_initConfig();
    }

    /**
     * 설정 데이터 가져오기
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getConfig()
    {
        if (empty($this->_config)) {
            $this->_config = $this->_initConfig();
        }

        return $this->_config;
    }

    public function getKakaoLinkInfo($goodsView)
    {
        $soldoutDisplay = (Request::isMobile()) ? gd_policy('soldout.mobile') : gd_policy('soldout.pc');
        $goodsKakaoLinkInfo['objectType'] = ($this->_config['useKakaoLinkCommerce'] === 'y') ? 'commerce' : 'feed';

        if ($goodsView['soldOut'] === 'y' && $soldoutDisplay['soldout_price'] !== 'price') {    //  품절상품 가격표시 설정 가격 대체 문구 or 이미지 노출
            $goodsKakaoLinkInfo['objectType'] = 'feed';
        }

        if (empty($goodsView['goodsPriceString']) === false) {  //  가격대체문구 설정 상품
            $goodsKakaoLinkInfo['objectType'] = 'feed';
        }

        if ($goodsView['goodsPermissionPriceStringFl'] === 'y') {
            $goodsKakaoLinkInfo['objectType'] = 'feed';
        }

        if ($goodsKakaoLinkInfo['objectType'] === 'commerce') {
            if ($goodsView['timeSaleFl']) {             // 타임세일 여부
                $goodsKakaoLinkInfo['regularPrice'] = $goodsView['oriGoodsPrice'];
                $goodsKakaoLinkInfo['discountPrice'] = $goodsView['goodsPrice'];
                $goodsKakaoLinkInfo['discountValue'] = ($goodsView['oriGoodsPrice'] - $goodsView['goodsPrice'])/$goodsView['oriGoodsPrice'] * 100;
                $goodsKakaoLinkInfo['goodsDiscountUnit'] = 'percent';
            } else {
                if ($goodsView['goodsDiscountFl'] === 'y') {    // 상품 할인 여부
                    $goodsKakaoLinkInfo['regularPrice'] = $goodsView['goodsPrice'];
                    $goodsKakaoLinkInfo['discountPrice'] = $goodsView['goodsDiscountPrice'];
                    $goodsKakaoLinkInfo['discountValue'] = $goodsView['goodsDiscount']; // 할인 타입에 따라 discountRate와 fixedDiscountPrice로 나뉨
                } else {
                    $goodsKakaoLinkInfo['regularPrice'] = $goodsView['goodsPrice'];
                    unset($goodsKakaoLinkInfo['discountPrice']);
                    unset($goodsKakaoLinkInfo['discountRate']);
                    unset($goodsKakaoLinkInfo['fixedDiscountPrice']);
                }
                $goodsKakaoLinkInfo['goodsDiscountUnit'] = $goodsView['goodsDiscountUnit'];
            }
        }

        switch ($this->_config['kakaoConnectLink1']) {
            case 'goods':
                if (Request::isMobile()) {
                    $goodsKakaoLinkInfo['kakaoConnectLink1'] = Request::getScheme() . '://' . substr(Request::getHost(), 2, strlen(Request::getHost())) . Request::getRequestUri();
                    $goodsKakaoLinkInfo['mkakaoConnectLink1'] = Request::getDomainUrl() . Request::getRequestUri();
                } else {
                    $goodsKakaoLinkInfo['kakaoConnectLink1'] = Request::getDomainUrl() . Request::getRequestUri();
                    $goodsKakaoLinkInfo['mkakaoConnectLink1'] = Request::getScheme() . '://m.' . Request::getHost() . Request::getRequestUri();
                }
                break;
            case 'main':
                if (Request::isMobile()) {
                    $goodsKakaoLinkInfo['kakaoConnectLink1'] = Request::getScheme() . '://' . substr(Request::getHost(), 2, strlen(Request::getHost()));
                    $goodsKakaoLinkInfo['mkakaoConnectLink1'] = Request::getDomainUrl();
                } else {
                    $goodsKakaoLinkInfo['kakaoConnectLink1'] = Request::getDomainUrl();
                    $goodsKakaoLinkInfo['mkakaoConnectLink1'] = Request::getScheme() . '://m.' . Request::getHost();
                }
                break;
            case 'self':
                $goodsKakaoLinkInfo['kakaoConnectLink1'] = $goodsKakaoLinkInfo['mkakaoConnectLink1'] = $this->_config['selfKakaoConnectLink1'];
                break;
        }

        switch ($this->_config['kakaoConnectLink2']) {
            case 'goods':
                if (Request::isMobile()) {
                    $goodsKakaoLinkInfo['kakaoConnectLink2'] = Request::getScheme() . '://' . substr(Request::getHost(), 2, strlen(Request::getHost())) . Request::getRequestUri();
                    $goodsKakaoLinkInfo['mkakaoConnectLink2'] = Request::getDomainUrl() . Request::getRequestUri();
                } else {
                    $goodsKakaoLinkInfo['kakaoConnectLink2'] = Request::getDomainUrl() . Request::getRequestUri();
                    $goodsKakaoLinkInfo['mkakaoConnectLink2'] = Request::getScheme() . '://m.' . Request::getHost() . Request::getRequestUri();
                }
                break;
            case 'main':
                if (Request::isMobile()) {
                    $goodsKakaoLinkInfo['kakaoConnectLink2'] = Request::getScheme() . '://' . substr(Request::getHost(), 2, strlen(Request::getHost()));
                    $goodsKakaoLinkInfo['mkakaoConnectLink2'] = Request::getDomainUrl();
                } else {
                    $goodsKakaoLinkInfo['kakaoConnectLink2'] = Request::getDomainUrl();
                    $goodsKakaoLinkInfo['mkakaoConnectLink2'] = Request::getScheme() . '://m.' . Request::getHost();
                }
                break;
            case 'self':
                $goodsKakaoLinkInfo['kakaoConnectLink2'] = $goodsKakaoLinkInfo['mkakaoConnectLink2'] = $this->_config['selfKakaoConnectLink2'];
                break;
        }

        // 주소에 포트가 들어가는 경우 처리가 되지 않는 오류 수정
        $kakaoShareOriginalUrl = parse_url($goodsKakaoLinkInfo['kakaoConnectLink1']);
        unset($kakaoShareOriginalUrl['port']);
        $goodsKakaoLinkInfo['kakaoConnectLink1'] = UrlUtils::buildUrl($kakaoShareOriginalUrl);
        $kakaoShareOriginalUrl = parse_url($goodsKakaoLinkInfo['kakaoConnectLink2']);
        unset($kakaoShareOriginalUrl['port']);
        $goodsKakaoLinkInfo['kakaoConnectLink2'] = UrlUtils::buildUrl($kakaoShareOriginalUrl);
        $goodsKakaoLinkInfo['mkakaoConnectLink1'] = str_replace('m.www', 'm', $goodsKakaoLinkInfo['mkakaoConnectLink1']);
        $goodsKakaoLinkInfo['mkakaoConnectLink2'] = str_replace('m.www', 'm', $goodsKakaoLinkInfo['mkakaoConnectLink2']);

        $goodsKakaoLinkInfo['kakaoConnectLink1'] = urldecode($goodsKakaoLinkInfo['kakaoConnectLink1']);
        $goodsKakaoLinkInfo['mkakaoConnectLink1'] = urldecode($goodsKakaoLinkInfo['mkakaoConnectLink1']);
        $goodsKakaoLinkInfo['kakaoConnectLink2'] = urldecode($goodsKakaoLinkInfo['kakaoConnectLink2']);
        $goodsKakaoLinkInfo['mkakaoConnectLink2'] = urldecode($goodsKakaoLinkInfo['mkakaoConnectLink2']);
        $goodsKakaoLinkInfo['kakaoLinkTemplate'] = $this->getKakaoLinkTemplate($goodsKakaoLinkInfo, $goodsKakaoLinkInfo['goodsDiscountUnit']);

        return $goodsKakaoLinkInfo;
    }

    /**
     * 설정에 따라 화면에 출력할 데이터 반환
     *
     * @param mixed $image 이미지
     *
     * @return array 화면에 출력할 데이터
     */
    public function getTemplateData($goodsView = null)
    {
        // 공유할 공통 데이터 및 반환데이터 초기화
        $shareOriginalUrl = Request::getDomainUrl() . Request::getRequestUri();
        parse_str(Request::getQueryString(), $getParams);
        $shareShortUrl = $shareOriginalUrl;
        $image = (!empty($goodsView['social'])) ? $goodsView['social'] : null;
        $data = [];
        $metaTags = [];
        if (substr($image, 0, 4) != 'http') $image = URI_HOME . $image;

        /* 에이스카운터 사용여부 체크 */
        $acecounterScript = \App::load('\\Component\\Nhn\\AcecounterCommonScript');
        $acecounterUse = $acecounterScript->getAcecounterUseCheck();
        if ($acecounterUse) {
            $addScript = '
                var sns = $(this).data("sns");
                var ments = "";
                switch(sns) {
                    case "facebook" :
                        ments = "페이스북";
                    break;
                    case "twitter" :
                        ments = "트위터";
                    break;
                    case "pinterest" :
                        ments = "핀터레스트";
                    break;
                    case "kakaolink" :
                        ments = "카카오링크";
                    break;
                    case "kakaostory" :
                        ments = "카카오스토리";
                    break;
                    default:
                        ments = "카카오스토리";
                    break;
                }
                if(ments != "") _AceTM.SNS(ments);
                console.log("_AceTM.SNS("+ments+");");
                ';
        }

        $config = $this->getConfig();
        // 사용여부
        $data['useFl'] = ($config['useSnsShare'] == 'y');

        // 카카오를 제외한 공통 템플릿
        if (Request::isMobileDevice()) {
            $commonTemplate = '<li><a href="%s" %s target="_blank" class="btn-social-popup"><img src="' . $this->_skinPath . 'img/btn/sns-%s.png"><br /><span>%s</span></a></li>';
        } else {
            $commonTemplate = '<li><a href="%s" %s class="btn-social-popup"><img src="' . $this->_skinPath . 'img/btn/sns-%s.png"><br /><span>%s</span></a></li>';
        }

        // 페이스북 템플릿 적용 (사용여부와 상관없이 메타 태그 노출)
        $socailLink = new SocialLink(
            [
                'url'   => $shareOriginalUrl,
                'title' => $this->_replaceTemplate($config['facebookTitle']),
                'text'  => $this->_replaceTemplate($config['facebookDesc']),
                'image' => $image,
            ]
        );

        // 페이스북 메타태그 (체크 : https://developers.facebook.com/tools/debug/)
        $socialOpengraph = $socailLink->Opengraph();
        $socialOpengraph->addMeta('type', 'product');

        // 이미지 여부에 따른 이미지 너비 높이 지정
        if ($image !== null) {
            $imagePath = parse_url($image);
            $imageAbsPath = USERPATH . substr(implode(DIRECTORY_SEPARATOR, explode('/', $imagePath['path'])), 1);
            if (is_file($imageAbsPath)) {
                $size = getimagesize($imageAbsPath);
                $width = $size[0] > 100 ? $size[0] : 100;
                $height = $size[1] > 100 ? $size[1] : 100;
                $socialOpengraph->addMeta('image:width', $width);
                $socialOpengraph->addMeta('image:height', $height);
            }
        }

        foreach ($socialOpengraph as $val) {
            $metaTags[] = $val;
        }

        // 페이스북 사용여부에 따른 버튼 설정
        if ($config['useFacebook'] == 'y') {
            $data['shareBtn']['facebook'] = sprintf(
                $commonTemplate,
                $socailLink->facebook->shareUrl,
                'data-width="750" data-height="300" data-sns="facebook"',
                'facebook',
                __('페이스북')
            );
        }

        // 트위터 템플릿 적용 (사용여부와 상관없이 메타 태그 노출)
        $socailLink = new SocialLink(
            [
                'url'   => $shareOriginalUrl,
                'title' => $this->_replaceTemplate($config['twitterTitle']),
                'image' => $image,
            ]
        );

        // 트위터 메타태그
        foreach ($socailLink->Twittercard() as $val) {
            $metaTags[] = $val;
        }

        // 트위터 사용여부에 따른 버튼 설정
        if ($config['useTwitter'] == 'y') {
            $data['shareBtn']['twitter'] = sprintf(
                $commonTemplate,
                $socailLink->twitter->shareUrl,
                'data-width="500" data-height="250" data-sns="twitter"',
                'twitter',
                __('트위터')
            );
        }

        // 핀터레스트 템플릿 적용
        if ($config['usePinterest'] == 'y') {
            $socailLink = new SocialLink(
                [
                    'url'   => $shareOriginalUrl,
                    'title' => $this->_replaceTemplate($config['pinterestTitle']),
                    'image' => $image,
                ]
            );
            $data['shareBtn']['pinterest'] = sprintf(
                $commonTemplate,
                $socailLink->pinterest->shareUrl,
                'data-width="750" data-height="570" data-sns="pinterest"',
                'pinterest',
                __('핀터레스트')
            );
        }

        // 팝업으로 열기 위한 스크립트 추가
        if (Request::isMobile()) {
            $data['shareBtn']['javascript'] = '
                <script type="text/javascript">
                $(function () {
                $(".js-share-view li a, .ly_share_box li a").click(function(e){
                    ' . $addScript . '
                });
                });
                </script>
            ';
        } else {
            // 스킨 버전에 따른 스크립트 popup 함수 변경 처리
            if (gd_is_skin_division()) {
                $funcName = "gd_popup";
            } else {
                $funcName = "popup";
            }

            $data['shareBtn']['javascript'] = '
                <script type="text/javascript">
                $(function () {
                    $(".btn-social-popup").click(function(e){
                    e.preventDefault();
                    ' . $addScript . '
                    ' . $funcName . '({
                        url: $(this).prop("href"),
                        target: "_blank",
                        width: $(this).data("width"),
                        height: $(this).data("height"),
                        resizable: "no",
                        scrollbars: "no"
                    });
                });
                });
                </script>
            ';
        }

        // 카카오톡링크 템플릿 적용
        if ($this->_config['useKakaoLink'] == 'y') {
            if (v::alnum()->noWhitespace()->length(32)->validate($this->_config['kakaoAppKey'])) {

                //직접 업로드 이미지 경로 변경
                if (strtolower(substr($image, 0, 4)) != 'http') {
                    $image = 'http://' . Request::getHost() . $image;
                }

                $kakaoLinkInfo = $this->getKakaoLinkInfo($goodsView);

                if (Request::isMobileDevice() && Request::isMobile()) {
                    // 주소에 포트가 들어가는 경우 처리가 되지 않는 오류 수정
                    $kakaoShareOriginalUrl = parse_url($shareOriginalUrl);
                    unset($kakaoShareOriginalUrl['port']);
                    $kakaoShareOriginalUrl = UrlUtils::buildUrl($kakaoShareOriginalUrl);

                    // 카카오 링크내 파라미터 한글 오류 수정
                    $kakaoShareOriginalUrl = urldecode($kakaoShareOriginalUrl);

                    if ($kakaoLinkInfo['objectType'] === 'feed') {
                        // 링크 출력 데이터
                        $data['shareBtn']['kakaolink'] = sprintf(
                            $kakaoLinkInfo['kakaoLinkTemplate'],    // 카카오톡링크 모바일 전용 템플릿
                            $this->_config['kakaoAppKey'],
                            str_replace("\r\n", "\\n", addslashes($this->_replaceTemplate(htmlspecialchars_decode($this->_config['kakaoLinkTitle'], ENT_QUOTES)))),
                            $image,
                            $kakaoShareOriginalUrl,
                            $kakaoShareOriginalUrl,
                            $this->_replaceTemplate($this->_config['kakaoLinkButtonTitle']),
                            $kakaoLinkInfo['mkakaoConnectLink1'],
                            $kakaoLinkInfo['kakaoConnectLink1'],
                            $this->_replaceTemplate($this->_config['kakaoLinkButtonTitle2']),
                            $kakaoLinkInfo['mkakaoConnectLink2'],
                            $kakaoLinkInfo['kakaoConnectLink2'],
                            str_replace("\r\n", "\\n", addslashes($this->_replaceTemplate(htmlspecialchars_decode($this->_config['kakaoLinkTitle'], ENT_QUOTES)))),
                            $image,
                            $this->_replaceTemplate($this->_config['kakaoLinkButtonTitle']),
                            $kakaoShareOriginalUrl
                        );
                    } elseif ($kakaoLinkInfo['objectType'] === 'commerce') {
                        // 링크 출력 데이터
                        $data['shareBtn']['kakaolink'] = sprintf(
                            $kakaoLinkInfo['kakaoLinkTemplate'],        // 카카오톡링크 모바일 전용 템플릿
                            $this->_config['kakaoAppKey'],
                            str_replace("\r\n", "\\n", addslashes($this->_replaceTemplate(htmlspecialchars_decode($this->_config['kakaoLinkTitle'], ENT_QUOTES)))),
                            $image,
                            $kakaoShareOriginalUrl,
                            $kakaoShareOriginalUrl,
                            (int)$kakaoLinkInfo['regularPrice'],
                            (int)$kakaoLinkInfo['discountPrice'],
                            (int)$kakaoLinkInfo['discountValue'],
                            $this->_replaceTemplate($this->_config['kakaoLinkButtonTitle']),
                            $kakaoLinkInfo['mkakaoConnectLink1'],
                            $kakaoLinkInfo['kakaoConnectLink1'],
                            $this->_replaceTemplate($this->_config['kakaoLinkButtonTitle2']),
                            $kakaoLinkInfo['mkakaoConnectLink2'],
                            $kakaoLinkInfo['kakaoConnectLink2'],
                            str_replace("\r\n", "\\n", addslashes($this->_replaceTemplate(htmlspecialchars_decode($this->_config['kakaoLinkTitle'], ENT_QUOTES)))),
                            $image,
                            $this->_replaceTemplate($this->_config['kakaoLinkButtonTitle']),
                            $kakaoShareOriginalUrl
                        );
                    }
                } else { // 카카오톡링크 프론트 전용 템플릿
                    if (Request::isMobile()) {
                        $shareOriginalUrl_pc = Request::getScheme() . '://' . substr(Request::getHost(), 2, strlen(Request::getHost())) . Request::getRequestUri();
                        $shareOriginalUrl_m = $shareOriginalUrl;
                    } else {
                        $shareOriginalUrl_pc = $shareOriginalUrl;
                        $shareOriginalUrl_m = Request::getScheme() . '://m.' . Request::getHost() . Request::getRequestUri();
                    }

                    // 주소에 포트가 들어가는 경우 처리가 되지 않는 오류 수정
                    $kakaoShareOriginalUrl = parse_url($shareOriginalUrl_pc);
                    unset($kakaoShareOriginalUrl['port']);
                    $shareOriginalUrl_pc = UrlUtils::buildUrl($kakaoShareOriginalUrl);
                    $shareOriginalUrl_m = str_replace('m.www', 'm', $shareOriginalUrl_m);

                    // 카카오 링크내 파라미터 한글 오류 수정
                    $shareOriginalUrl_pc = urldecode($shareOriginalUrl_pc);
                    $shareOriginalUrl_m = urldecode($shareOriginalUrl_m);

                    if ($kakaoLinkInfo['objectType'] === 'feed') {
                        // 링크 출력 데이터
                        $data['shareBtn']['kakaolink'] = sprintf(
                            $kakaoLinkInfo['kakaoLinkTemplate'],
                            $this->_config['kakaoAppKey'],
                            str_replace("\r\n", "\\n", addslashes($this->_replaceTemplate(htmlspecialchars_decode($this->_config['kakaoLinkTitle'], ENT_QUOTES)))),
                            $image,
                            $shareOriginalUrl_m,
                            $shareOriginalUrl_pc,
                            $this->_replaceTemplate($this->_config['kakaoLinkButtonTitle']),
                            $kakaoLinkInfo['mkakaoConnectLink1'],
                            $kakaoLinkInfo['kakaoConnectLink1'],
                            $this->_replaceTemplate($this->_config['kakaoLinkButtonTitle2']),
                            $kakaoLinkInfo['mkakaoConnectLink2'],
                            $kakaoLinkInfo['kakaoConnectLink2']
                        );
                    } elseif ($kakaoLinkInfo['objectType'] === 'commerce') {
                        // 링크 출력 데이터
                        $data['shareBtn']['kakaolink'] = sprintf(
                            $kakaoLinkInfo['kakaoLinkTemplate'],
                            $this->_config['kakaoAppKey'],
                            str_replace("\r\n", "\\n", addslashes($this->_replaceTemplate(htmlspecialchars_decode($this->_config['kakaoLinkTitle'], ENT_QUOTES)))),
                            $image,
                            $shareOriginalUrl_m,
                            $shareOriginalUrl_pc,
                            (int)$kakaoLinkInfo['regularPrice'],
                            (int)$kakaoLinkInfo['discountPrice'],
                            (int)$kakaoLinkInfo['discountValue'],
                            $this->_replaceTemplate($this->_config['kakaoLinkButtonTitle']),
                            $kakaoLinkInfo['mkakaoConnectLink1'],
                            $kakaoLinkInfo['kakaoConnectLink1'],
                            $this->_replaceTemplate($this->_config['kakaoLinkButtonTitle2']),
                            $kakaoLinkInfo['mkakaoConnectLink2'],
                            $kakaoLinkInfo['kakaoConnectLink2']
                        );
                    }
                }
            }
        }

        // 카카오스토리 템플릿 적용
        if ($config['useKakaoStory'] == 'y') {

            //직접 업로드 이미지 경로 변경
            if (strtolower(substr($image, 0, 4)) != 'http') {
                $image = 'http://' . Request::getHost() . $image;
            }

            if (Request::isMobileDevice() && Request::isMobile()) {
                // 카카오스토리 모바일 템플릿
                $kakaoStoryTemplate = '
                    <li><a href="javascript:shareStory();" id="shareKakaoStoryBtn" data-sns="kakaostory"><img src="' . $this->_skinPath . 'img/btn/sns-kakaostory.png"><br /><span>' . __('카카오스토리') . '</span></a></li>
                    <script type="text/javascript" src="' . $this->_skinPath . 'js/kakao/kakao.min.js"></script>
                    <script type="text/javascript">
                        //<![CDATA[
                        Kakao.init("%s");
                        function shareStory() {
                            Kakao.Story.open({
                                text: "%s",
                                url: "%s",
                                urlInfo: {
                                    title: "%s",
                                    desc: "%s",
                                    name: "%s",
                                    images: ["%s"]
                                }
                            });
                        }
                        //]]>
                    </script>
                ';
                // 주소에 포트가 들어가는 경우 처리가 되지 않는 오류 수정
                $kakaoShareOriginalUrl = parse_url($shareOriginalUrl);
                unset($kakaoShareOriginalUrl['port']);
                $kakaoShareOriginalUrl = UrlUtils::buildUrl($kakaoShareOriginalUrl);

                $data['shareBtn']['kakaostory'] = sprintf(
                    $kakaoStoryTemplate,
                    $config['kakaoAppKey'],
                    str_replace("\r\n", "\\n", addslashes($this->_replaceTemplate(htmlspecialchars_decode($config['kakaoStoryText'], ENT_QUOTES)))),
                    $kakaoShareOriginalUrl,
                    $this->_replaceTemplate($config['kakaoStoryTitle']),
                    $this->_replaceTemplate($config['kakaoStoryDesc']),
                    $this->_replaceText[self::MALL_NAME_REPLACE_KEY],
                    $image
                );
            } else {
                // 카카오스토리 프론트 템플릿
                $kakaoStoryTemplate = '
                    <li><a href="javascript:shareStory();" id="shareKakaoStoryBtn" data-sns="kakaostory"><img src="' . PATH_SKIN . 'img/btn/sns-kakaostory.png"><br /><span>' . __('카카오스토리') . '</span></a></li>
                    <script type="text/javascript" src="' . PATH_SKIN . 'js/kakao/kakao.min.js"></script>
                    <script type="text/javascript">
                        //<![CDATA[
                        Kakao.init("%s");
                        function shareStory() {
                            ' . $addScript . '
                            Kakao.Story.share({
                                text: "%s",
                                url: "%s"
                            });
                        }
                        //]]>
                    </script>
                ';
                $data['shareBtn']['kakaostory'] = sprintf(
                    $kakaoStoryTemplate,
                    $config['kakaoAppKey'],
                    str_replace("\r\n", "\\n", addslashes($this->_replaceTemplate(htmlspecialchars_decode($config['kakaoStoryText'], ENT_QUOTES)))),
                    $shareOriginalUrl
                );
            }

            // og protocol 메타태그
            if (!isset($socialOpengraph)) {
                $socailLink = new SocialLink(
                    [
                        'url'   => $shareOriginalUrl,
                        'title' => $this->_replaceTemplate($config['kakaoStoryTitle']),
                        'text'  => $this->_replaceTemplate($config['kakaoStoryDesc']),
                        'image' => $image,
                    ]
                );

                $socialOpengraph = $socailLink->Opengraph();
                $socialOpengraph->addMeta('type', 'product');
                foreach ($socialOpengraph as $val) {
                    $metaTags[] = $val;
                }
            }
        }

        // 상품URL 복사하기
        if ($config['useCopy'] == 'y') {
            $data['shareUrl'] = $shareShortUrl;
        }

        // 메타태그 설정
        $data['metaTags'] = $metaTags;

        return $data;
    }

    public function getKakaoLinkTemplate($kakaoLinkInfo, $discountType) {
        $kakaoLinkTemplate = '<li><a href="javascript:;" id="shareKakaoLinkBtn" data-sns="kakaolink"><img src="' . $this->_skinPath . 'img/btn/sns-kakaolink.png"><br /><span>카카오톡링크</span></a></li>
                    <script type="text/javascript" src="' . $this->_skinPath . 'js/kakao/kakao.min.js"></script>';

        $kakaoLinkTemplateArr = [
            'mobile_feed' => '<script type="text/javascript">
                        //<![CDATA[
                        Kakao.init("%s");
                        if (Kakao.VERSION >= "1.17.3") {
                            Kakao.Link.createDefaultButton({
                                container: "#shareKakaoLinkBtn",
                                objectType: "feed",
                                content: {
                                    title: "%s",
                                    imageUrl: "%s",
                                    link: {
                                        mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                        webUrl: "%s"                                        
                                    }                                    
                                },
                                buttons: [
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    },
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    }
                                ]
                            });
                        } else {
                            Kakao.Link.createTalkLinkButton({
                                container: "#shareKakaoLinkBtn",
                                label: "%s",
                                image: {
                                    src: "%s",
                                    width: "500",
                                    height: "500"
                                },
                                webButton: {
                                    text: "%s",
                                    url: "%s" // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                }
                            });
                        }
                        //]]>
                    </script>',
            'mobile_commerce_nondiscount' => '<script type="text/javascript">
                            //<![CDATA[
                            Kakao.init("%s");
                            Kakao.Link.createDefaultButton({
                                container: "#shareKakaoLinkBtn",
                                objectType: "commerce",
                                content: {
                                    title: "%s",
                                    imageUrl: "%s",
                                    link: {
                                        mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                        webUrl: "%s"
                                    }
                                },
                                commerce: {
                                    regularPrice: %d,
//                                    discountPrice: %d,
                                    discountRate: %d
                                },
                                buttons: [
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    },
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    }
                                ]
                            });
                            //]]>
                        </script>',
            'mobile_commerce_percentDiscount' => '<script type="text/javascript">
                        //<![CDATA[
                        Kakao.init("%s");
                        if (Kakao.VERSION >= "1.17.3") {
                            Kakao.Link.createDefaultButton({
                                container: "#shareKakaoLinkBtn",
                                objectType: "commerce",
                                content: {
                                    title: "%s",
                                    imageUrl: "%s",
                                    link: {
                                        mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                        webUrl: "%s"                                        
                                    }                                    
                                },
                                commerce: {
                                    regularPrice: %d,
                                    discountPrice: %d,
                                    discountRate: %d    
                                },
                                buttons: [
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    },
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    }
                                ]
                            });
                        } else {
                            Kakao.Link.createTalkLinkButton({
                                container: "#shareKakaoLinkBtn",
                                label: "%s",
                                image: {
                                    src: "%s",
                                    width: "500",
                                    height: "500"
                                },
                                webButton: {
                                    text: "%s",
                                    url: "%s" // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                }
                            });
                        }
                        //]]>
                    </script>',
            'mobile_commerce_priceDiscount' => '<script type="text/javascript">
                        //<![CDATA[
                        Kakao.init("%s");
                        if (Kakao.VERSION >= "1.17.3") {
                            Kakao.Link.createDefaultButton({
                                container: "#shareKakaoLinkBtn",
                                objectType: "commerce",
                                content: {
                                    title: "%s",
                                    imageUrl: "%s",
                                    link: {
                                        mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                        webUrl: "%s"                                        
                                    }                                    
                                },
                                commerce: {
                                    regularPrice: %d,
                                    discountPrice: %d,
                                    fixedDiscountPrice: %d    
                                },
                                buttons: [
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    },
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    }
                                ]
                            });
                        } else {
                            Kakao.Link.createTalkLinkButton({
                                container: "#shareKakaoLinkBtn",
                                label: "%s",
                                image: {
                                    src: "%s",
                                    width: "500",
                                    height: "500"
                                },
                                webButton: {
                                    text: "%s",
                                    url: "%s" // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                }
                            });
                        }
                        //]]>
                    </script>',
            'pc_feed' => '<script type="text/javascript">
                            //<![CDATA[
                            Kakao.init("%s");
                            Kakao.Link.createDefaultButton({
                                container: "#shareKakaoLinkBtn",
                                objectType: "feed",
                                content: {
                                    title: "%s",
                                    imageUrl: "%s",
                                    link: {
                                        mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                        webUrl: "%s"                                        
                                    }                                    
                                },
                                buttons: [
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    },
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    }
                                ]
                            });
                            //]]>
                        </script>',
            'pc_commerce_nondiscount' => '<script type="text/javascript">
                            //<![CDATA[
                            Kakao.init("%s");
                            Kakao.Link.createDefaultButton({
                                container: "#shareKakaoLinkBtn",
                                objectType: "commerce",
                                content: {
                                    title: "%s",
                                    imageUrl: "%s",
                                    link: {
                                        mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                        webUrl: "%s"
                                    }
                                },
                                commerce: {
                                    regularPrice: %d,
//                                    discountPrice: %d,
                                    discountRate: %d
                                },
                                buttons: [
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    },
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    }
                                ]
                            });
                            //]]>
                        </script>',
            'pc_commerce_percentDiscount' => '<script type="text/javascript">
                            //<![CDATA[
                            Kakao.init("%s");
                            Kakao.Link.createDefaultButton({
                                container: "#shareKakaoLinkBtn",
                                objectType: "commerce",
                                content: {
                                    title: "%s",
                                    imageUrl: "%s",
                                    link: {
                                        mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                        webUrl: "%s"
                                    }
                                },
                                commerce: {
                                    regularPrice: %d,
                                    discountPrice: %d,
                                    discountRate: %d
                                },
                                buttons: [
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    },
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    }
                                ]
                            });
                            //]]>
                        </script>',
            'pc_commerce_priceDiscount' => '<script type="text/javascript">
                            //<![CDATA[
                            Kakao.init("%s");
                            Kakao.Link.createDefaultButton({
                                container: "#shareKakaoLinkBtn",
                                objectType: "commerce",
                                content: {
                                    title: "%s",
                                    imageUrl: "%s",
                                    link: {
                                        mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                        webUrl: "%s"
                                    }
                                },
                                commerce: {
                                    regularPrice: %d,
                                    discountPrice: %d,
                                    fixedDiscountPrice: %d
                                },
                                buttons: [
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    },
                                    {
                                        title: "%s",
                                        link: {
                                            mobileWebUrl: "%s", // 앱 설정의 웹 플랫폼에 등록한 도메인의 URL이어야 합니다.
                                            webUrl: "%s"
                                        }
                                    }
                                ]
                            });
                            //]]>
                        </script>',
        ];

        if (Request::isMobileDevice() && Request::isMobile()) {
            if ($kakaoLinkInfo['objectType'] === 'feed') {
                $kakaoLinkTemplate .= $kakaoLinkTemplateArr['mobile_feed'];
            } elseif ($kakaoLinkInfo['objectType'] === 'commerce') {
                if (empty($kakaoLinkInfo['discountPrice'])) {   // 할인X
                    $kakaoLinkTemplate .= $kakaoLinkTemplateArr['mobile_commerce_nondiscount'];
                } else {
                    if ($discountType === 'percent') {
                        $kakaoLinkTemplate .= $kakaoLinkTemplateArr['mobile_commerce_percentDiscount'];
                    } elseif ($discountType === 'price') {
                        $kakaoLinkTemplate .= $kakaoLinkTemplateArr['mobile_commerce_priceDiscount'];
                    }
                }
            }
        } else {    // 프런트
            if ($kakaoLinkInfo['objectType'] === 'feed') {
                $kakaoLinkTemplate .= $kakaoLinkTemplateArr['pc_feed'];
            } elseif ($kakaoLinkInfo['objectType'] === 'commerce') {
                if (empty($kakaoLinkInfo['discountPrice'])) {   // 할인X
                    $kakaoLinkTemplate .= $kakaoLinkTemplateArr['pc_commerce_nondiscount'];
                } else {
                    if ($discountType === 'percent') {
                        $kakaoLinkTemplate .= $kakaoLinkTemplateArr['pc_commerce_percentDiscount'];
                    } elseif ($discountType === 'price') {
                        $kakaoLinkTemplate .= $kakaoLinkTemplateArr['pc_commerce_priceDiscount'];
                    }
                }
            }
        }

        return $kakaoLinkTemplate;

    }

    /**
     * 메타태그 생성용
     *
     * @param string $title      SNS 공유 제목
     * @param string $descrytion SNS 공유 내용
     * @param mixed  $image      이미지 도메인 포함한 절대 경로
     *
     * @return array
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function createMetaData($title, $descrytion, $image = null)
    {
        // 공유할 공통 데이터 및 반환데이터 초기화
        $shareOriginalUrl = Request::getDomainUrl() . Request::getRequestUri();
        $metaTags = [];

        // 443 port 제거
        if (preg_match('/^https:/', $shareOriginalUrl) === 1) {
            $shareOriginalUrl = str_replace(':443', '', $shareOriginalUrl);
        }
        if (preg_match('/^https:/', $image) === 1) {
            $image = str_replace(':443', '', $image);
        }

        // 소셜링크 설정
        $socailLink = new SocialLink(
            [
                'url'   => $shareOriginalUrl,
                'title' => $title,
                'text'  => $descrytion,
                'image' => $image,
            ]
        );

        $config = App::getConfig('app');

        // 페이스북 메타태그
        // 테스트 : https://developers.facebook.com/tools/debug/
        // 오픈그래프 메뉴얼 : https://developers.facebook.com/docs/sharing/webmasters#markup
        $socialOpengraph = $socailLink->Opengraph();
        $socialOpengraph->addMeta('locale', $config->getLocale());

        // 이미지 여부에 따른 이미지 너비 높이 지정
        if ($image !== null) {
            $imagePath = parse_url($image);
            $imageAbsPath = USERPATH . substr(implode(DIRECTORY_SEPARATOR, explode('/', $imagePath['path'])), 1);
            if (is_file($imageAbsPath)) {
                // 파일 사이즈 체크
                $size = getimagesize($imageAbsPath);

                // 파일이 이미지인 경우만 처리
                if ($size !== false) {
                    // 너비 태그 추가
                    if ($size[0] > 0) {
                        $socialOpengraph->addMeta('image:width', $size[0]);
                    }

                    // 높이 태그 추가
                    if ($size[1] > 0) {
                        $socialOpengraph->addMeta('image:height', $size[1]);
                    }
                }
            }
        }

        foreach ($socialOpengraph as $val) {
            $metaTags[] = $val;
        }

        // 트위터 메타태그
        foreach ($socailLink->Twittercard() as $val) {
            $metaTags[] = $val;
        }

        return $metaTags;
    }

    /**
     * 인터셉트내에서 해당 메서드를 호출해
     * 헤더내에 메타태그를 직접 추가
     *
     * @return array 메타 string 데이터
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function insertSnsMetaData()
    {
        // 이미지 처리
        $image = null;
        $config = $this->getConfig();
        if (empty($config['snsRepresentImage']) === false) {
            $image = Request::getDomainUrl() . UserFilePath::data('common', $config['snsRepresentImage'])->www();
        }

        if ($config['snsRepresentTitle'] == '{=gMall.mallNm}') {
            $config['snsRepresentTitle'] = Globals::get('gMall.mallNm');
        }

        return $this->createMetaData(gd_isset($config['snsRepresentTitle'], Globals::get('gMall.mallTitle')), $config['snsRepresentDescription'], $image);
    }

    private function _initConfig()
    {
        $config = ComponentUtils::getPolicy(self::PROMOTION_SOCIAL_SHARE_KEY);

        // promotion.snsShare 기본설정 세팅
        $config['snsRepresentImage'] = gd_isset($config['snsRepresentImage'], '');
        $config['snsRepresentDescription'] = gd_isset($config['snsRepresentDescription'], Globals::get('gMall.mallNm'));

        // promotion.snsShare 사용설정 세팅
        $config['useSnsShare'] = gd_isset($config['useSnsShare'], 'y');
        $config['useKakaoLink'] = gd_isset($config['useKakaoLink'], 'y');
        $config['useKakaoLinkCommerce'] = gd_isset($config['useKakaoLinkCommerce'], 'y');
        $config['kakaoLinkTitle'] = gd_isset($config['kakaoLinkTitle'], self::GOODS_NAME_REPLACE_KEY);
        $config['kakaoLinkButtonTitle'] = gd_isset($config['kakaoLinkButtonTitle'], self::MALL_NAME_REPLACE_KEY);
        $config['kakaoStoryTitle'] = gd_isset($config['kakaoStoryTitle'], self::GOODS_NAME_REPLACE_KEY);
        $config['kakaoStoryDesc'] = gd_isset($config['kakaoStoryDesc'], self::MALL_NAME_REPLACE_KEY);
        $config['facebookTitle'] = gd_isset($config['facebookTitle'], self::GOODS_NAME_REPLACE_KEY);
        $config['facebookDesc'] = gd_isset($config['facebookDesc'], self::MALL_NAME_REPLACE_KEY);
        $config['twitterTitle'] = gd_isset($config['twitterTitle'], self::GOODS_NAME_REPLACE_KEY);
        $config['pinterestTitle'] = gd_isset($config['pinterestTitle'], '[' . self::MALL_NAME_REPLACE_KEY . '] ' . self::GOODS_NAME_REPLACE_KEY);

        $config['useKakaoStory'] = gd_isset($config['useKakaoStory'], 'y');
        $config['useFacebook'] = gd_isset($config['useFacebook'], 'y');
        $config['useTwitter'] = gd_isset($config['useTwitter'], 'y');
        $config['usePinterest'] = gd_isset($config['usePinterest'], 'y');
        $config['useCopy'] = gd_isset($config['useCopy'], 'y');

        return $config;
    }

    /**
     * 생성자에서 받은 값으로 치환코드를 일괄 변경
     *
     * @param $text
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    private function _replaceTemplate($text)
    {
        foreach ($this->_replaceText as $key => $val) {
            $text = str_replace($key, $val, $text);
        }

        return $text;
    }
}
