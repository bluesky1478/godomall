<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Board;


use Component\PlusShop\PlusReview\PlusReviewConfig;
use Framework\Utility\ArrayUtils;

class PlusReviewArticleConfigController extends \Controller\Admin\Controller
{
    private $authWriteStatus = [
        'p1' => '결제완료',
        'd1' => '배송중',
        'd2' => '배송완료',
        's1' => '구매확정',
    ];

    private $authWriteStatusDuration = [1 , 2 , 3 , 4 , 5 , 6 , 7 , 8 , 9 , 10 , 11 , 12 , 13 , 14 , 15 , 20 , 25 , 30];
    private $mileageAddDuration = [1 , 2 , 3 , 4 , 5 , 6 , 7 , 8 , 9 , 10 , 11 , 12 , 13 , 14 , 15 , 20 , 25 , 30];

    public function index()
    {
        $this->callMenu('board', 'plusReview', 'plusReviewArticleConfig');

        $config = new PlusReviewConfig();
        $data = $config->getFormValue();
        $mileageConfig = gd_mileage_give_info();
        $rentalDisk = \App::load('\\Component\\Mall\\RentalDisk');
        $disk = $rentalDisk->diskUsage();
        $diskLimit = false;
        if ($disk['limitCheck'] === true) {
            // 디스크사용량이 85% 넘는경우
            if (($disk['usedPer'] > 85)) {
                $diskLimit = true;
            }
        }
        if (is_array($data['data']['exceptGoods'])) {
            $goods = \App::load('\\Component\\Goods\\Goods');
            $goodsData = $goods->getGoodsDataDisplay(implode(INT_DIVISION, $data['data']['exceptGoods']));
            if (is_array($goodsData)) {
                $data['data']['exceptGoods'] = $goodsData;
            }
        }

        $this->setData('goodsPagetCntList',[8,16,24,32,40]);
        $this->setData('checked',$data['checked']);
        $this->setData('selected',$data['selected']);
        $this->setData('diskLimit',$diskLimit);
        $this->setData('data',$data['data']);
        $this->setData('totalMigratioCount',$config->getCanMigratioCount());
        $this->setData('migrationInfo',$config->getMigrationGoodsReviewInfo());
        $this->setData('checkMigration',$config->checkMigration());
        $this->setData('authWriteStatus', $this->authWriteStatus);
        $this->setData('authWriteStatusDuration', ArrayUtils::changeKeyValue($this->authWriteStatusDuration));
        $this->setData('mileageAddDuration', ArrayUtils::changeKeyValue($this->mileageAddDuration));
        $this->setData('mileageUseFl', $mileageConfig['basic']['payUsableFl']);
        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);
    }
}
