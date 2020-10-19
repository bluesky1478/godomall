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
 * @link http://www.godo.co.kr
 */
namespace Bundle\Controller\Front\Goods;

use Framework\Utility\GodoUtils;
use League\Flysystem\Exception;
use Request;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertBackException;

class GoodsPsController extends \Controller\Front\Controller
{

    /**
     * 상품 상세 페이지 처리
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright Copyright (c), Godosoft
     */
    public function index()
    {
        // --- 각 배열을 trim 처리
        $post = Request::post()->toArray();
        $get = Request::get()->toArray();

        // --- 각 모드별 처리
        switch ($post['mode']) {
            // 옵션 선택
            case 'option_select':
                try {
                    // --- 상품 class
                    $goods = \App::load('\\Component\\Goods\\Goods');

                    $result = $goods->getGoodsOptionSelect($post['goodsNo'], $post['optionVal'], $post['optionKey'], $post['mileageFl']);

                    $result['log'] = 'ok';
                    echo json_encode($result);
                    exut;
                } catch (\Exception $e) {
                    echo json_encode($e);
                }
                break;

            // 오늘 본 상품 삭제
            case 'delete_today_goods':
                try {
                    // --- 상품 class
                    $goods = \App::load('\\Component\\Goods\\Goods');
                    if (!$goods->removeTodayViewedGoods($post['goodsNo'])) {
                        echo 'error';
                    }
                } catch (Exception $e) {
                    $this->json([
                        'code' => '200',
                        'message' => $e->getMessage()
                    ]);
                }

                break;

            // 최근검색어 삭제
            case 'delete_recent_keyword':
                try {
                    // --- 상품 class
                    $goods = \App::load('\\Component\\Goods\\Goods');
                    $goods->removeRecentKeyword($post['keyword']);
                    exit;

                } catch (Exception $e) {
                    $this->json([
                        'code' => '200',
                        'message' => $e->getMessage()
                    ]);
                }

                break;
            // 최근검색어 전체 삭제
            case 'delete_recent_all_keyword':
                try {
                    // --- 상품 class
                    $goods = \App::load('\\Component\\Goods\\Goods');
                    $goods->removeRecentAllKeyword();
                    exit;

                } catch (Exception $e) {
                    $this->json([
                        'code' => '200',
                        'message' => $e->getMessage()
                    ]);
                }

                break;

            case 'get_benefit':
                try {
                    // --- 상품 class
                    $cart = \App::load('\\Component\\Cart\\Cart');

                    $setData = $cart->goodsViewBenefit($post);
                    echo json_encode($setData);
                } catch (Exception $e) {
                    echo json_encode(array('message' => $e->getMessage()));
                }

                break;

            // 브랜드 가져오기
            case 'get_brand':
                try {
                    // --- 상품 class
                    $brand = \App::load('\\Component\\Category\\Brand');
                    $cateNm = $post['brand'];
                    $search = $post['search'];
                    $getData = $brand->getBrandCodeInfo(null, 4, $cateNm, true, null, $search);

                    if ($getData) {
                        $getData = array_chunk($getData, 6);
                    }

                    echo json_encode($getData);
                    exit;
                } catch (Exception $e) {
                    echo json_encode(array('message' => $e->getMessage()));
                }

                break;

            // 전체 카테고리 가져오기
            case 'get_all_category':
                try {

                    $cateDepth = gd_isset($post['cateDepth'],4);
                    $category = \App::load('\\Component\\Category\\Category');
                    $getData = $category->getCategoryCodeInfo(null, $cateDepth, false, false, 'pc');

                    if ($getData) {
                        $getData = array_chunk($getData, 6);
                    }

                    echo json_encode($getData);
                    exit;
                } catch (Exception $e) {
                    echo json_encode(array('message' => $e->getMessage()));
                }

                break;

            // 단축주소 가져오기
            case 'get_short_url':
                try {
                    $url1 = explode('?', $post['url']);
                    $url2 = explode('&', $url1[1]);
                    $url = $url1[0].'?';
                    foreach($url2 as $val) {
                        if(strpos($val, 'goodsNo') !== false) {
                            $url .= $val;
                            break;
                        }
                    }

                    $shortUrl = GodoUtils::shortUrl($url);
                    echo json_encode(['url' => urldecode($shortUrl)]);
                } catch (Exception $e) {
                    echo json_encode(array('message' => $e->getMessage()));
                }

                break;

            //재입고 알림 신청
            case 'save_restock' :
                try {
                    if (gd_is_plus_shop(PLUSSHOP_CODE_RESTOCK) !== true) {
                        throw new Exception("[플러스샵] 미설치 또는 미사용 상태입니다. 설치 완료 및 사용 설정 후 플러스샵 앱을 사용할 수 있습니다.");
                    }

                    $duplicationFl = array();
                    $goods = \App::load('\\Component\\Goods\\Goods');

                    $goodsData = $goods->getGoodsView($post['goodsNo']);
                    $useAble = $goods->setRestockUsableFl($goodsData);
                    if($useAble !== 'y'){
                        throw new Exception("재입고 신청을 할 수 없는 상태의 상품입니다.", 2);
                    }

                    //옵션이 있을시
                    if(count($post['restock_option']) > 0){
                        //옵션정보가 변경되지 않았는지 체크
                        foreach($post['restock_option'] as $key => $value){
                            list($checkOptionSno) = explode("@|@", $value);

                            $checkOptionData = $goods->getGoodsOptionInfo($checkOptionSno);
                            if(count($checkOptionData) < 1 || (int)$checkOptionData['sno'] < 1){
                                throw new Exception("옵션 정보가 변경되었습니다.\n다시 시도해 주세요.", 1);
                            }
                        }

                        foreach($post['restock_option'] as $key => $value){
                            $post['optionSno'] = $post['optionValue'] = '';
                            list($post['optionSno'], $post['optionValue']) = explode("@|@", $value);

                            //diffKey 생성
                            $post['diffKey'] = $goods->setGoodsRestockDiffKey($post);

                            //중복건 체크
                            $duplicationRestock = $goods->checkDuplicationRestock($post);

                            if($duplicationRestock === true){
                                $duplicationFl[] = 'y';
                                continue;
                            }

                            //저장
                            $insertId = $goods->saveGoodsRestock($post);
                            if(!$insertId){
                                throw new Exception("신청을 실패했습니다.\n고객센터에 문의해 주세요.", 2);
                                break;
                            }
                        }

                        if(count($duplicationFl) > 0){
                            if(count($duplicationFl) === count($post['restock_option'])){
                                throw new Exception("이미 재입고 신청이 된 상품입니다.", 1);
                            }
                            else {
                                throw new AlertCloseException(__("중복 신청건을 제외한 재입고 알림이 신청되었습니다."));
                            }
                        }
                    }
                    else {
                        //diffKey 생성
                        $post['diffKey'] = $goods->setGoodsRestockDiffKey($post);

                        //중복건 체크
                        $duplicationRestock = $goods->checkDuplicationRestock($post);
                        if($duplicationRestock === true){
                            throw new Exception("이미 재입고 신청이 된 상품입니다.", 1);
                        }
                        //옵션이 없을시
                        $insertId = $goods->saveGoodsRestock($post);
                        if(!$insertId){
                            throw new Exception("신청을 실패했습니다.\n고객센터에 문의해 주세요.", 2);
                        }
                    }

                    throw new AlertCloseException(__("재입고 알림이 신청되었습니다."));
                } catch (\Exception $e) {
                    if($e->getCode() === 1){
                        throw new AlertBackException(__($e->getMessage()));
                    }
                    else {
                        throw new AlertCloseException(__($e->getMessage()));
                    }
                }
                break;

            // 장바구니 사용 상태의 쿠폰 적용 취소처리
            case 'UserCartCouponDel':
                // --- 상품 class
                $cart = \App::load('\\Component\\Cart\\Cart');
                $cartInfo = $cart->getCartGoodsData();

                // 장바구니에 사용된 쿠폰의 유효성 체크
                if($cartInfo > 0) {
                    $reSetMemberCouponApply = false;
                    foreach ($cartInfo as $key => $value) {
                        foreach ($value as $key1 => $value1) {
                            foreach ($value1 as $key2 => $value2) {
                                if ($value2['memberCouponNo']) {
                                    $memberCouponNoArr = explode(INT_DIVISION, $value2['memberCouponNo']);
                                    foreach ($memberCouponNoArr as $memberCouponNo) {
                                        if ($memberCouponNo == $post['memberCouponNo']) {
                                            $cart->setMemberCouponDelete($value2['sno']); // 상품적용 쿠폰 제거
                                        } else {
                                            $reSetMemberCouponApply = true;
                                            $couponApply[$value2['sno']]['couponApplyNo'][] = $memberCouponNo;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // 사용가능한 쿠폰만 다시 적용
                    if($reSetMemberCouponApply) {
                        foreach ($couponApply as $cartKey => $couponApplyInfo) {
                            // 상품적용 쿠폰 적용 / 변경
                            $couponApplyNo = implode(INT_DIVISION, $couponApplyInfo['couponApplyNo']);
                            $cart->setMemberCouponApply($cartKey, $couponApplyNo);
                        }
                    }
                }
                break;

            // 쿠폰상태 확인 및 상태값 변경
            case 'checkCouponType':
                $getValue = Request::post()->toArray();
                if($getValue['mileageGiveFl']) {
                    $mileageGive = gd_policy('member.mileageGive');
                    if($mileageGive['giveFl'] == 'n') {
                        $this->json(array('isSuccess'=>false));
                        break;
                    }
                }
                // 모듈 호출
                $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
                $return = $couponAdmin->checkCouponTypeArr($getValue['couponNo']);
                $this->json(array('isSuccess'=>$return));
                break;


            // 쿠폰상태 확인 및 상태값 변경
            case 'goodsCheckCouponTypeArr':
                $coupon = \App::load('\\Component\\Coupon\\Coupon');
                $return = $coupon->goodsCheckCouponTypeArr($post['couponNo']);
                $this->json(array('isSuccess'=>$return['result'], 'setCouponApplyNo'=>$return['setCouponApplyNo']));
                break;
        }

        //상품후기 작성 후 처리(ssl보안이슈로 강제 상품경로 리다이렉트 후 처리)
        if($get['mode'] == 'openerReload'){
            $this->js('alert("' . __('저장되었습니다.') . '");opener.location.reload();self.close()');
        }
        else if($get['mode'] == 'layerReload'){
            $this->js('alert("' . __('저장되었습니다.') . '");parent.location.reload(); document.location.href="about:blank";');
        }
        exit();
    }
}
