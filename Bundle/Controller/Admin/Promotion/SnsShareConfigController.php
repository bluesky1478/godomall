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
namespace Bundle\Controller\Admin\Promotion;

use Component\Promotion\SocialShare;

class SnsShareConfigController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        $this->callMenu('promotion', 'sns', 'snsShare');

        // 소셜공유 클래스 로드
        $socialShare = new SocialShare();

        // 소셜공유 설정 조회
        $data = $socialShare->getConfig();

        if ($data['kakaoConnectLink1'] !== 'self') {
            $display['kakaoConnectLink1'] = 'display-none';
        }
        if ($data['kakaoConnectLink2'] !== 'self') {
            $display['kakaoConnectLink2'] = 'display-none';
        }

        $connectLink = [
            'goods' => '상품 상세 페이지 : goods/goods_view.php',
            'main' => '메인 페이지 : main/index.php',
            'self' => '직접 입력',
        ];

        $this->setData('data', $data);
        $this->setData('display', $display);
        $this->setData('connectLink', $connectLink);
        $this->setData('snsShareDefaultTitle', \Globals::get('gMall.mallTitle'));

        // 치환코드 정보
        $this->setData('replaceKey', [
            'mallNm' => SocialShare::MALL_NAME_REPLACE_KEY,
            'goodsNm' => SocialShare::GOODS_NAME_REPLACE_KEY,
            'brandNm' => SocialShare::BRAND_NAME_REPLACE_KEY,
        ]);
    }
}
