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
namespace Bundle\Controller\Front\Share;

use Component\Payment\CashReceipt;
use Component\Member\Util\MemberUtil;
use Request;
use UserFilePath;
use Session;

/**
 * 영수증 출력
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class ShowTaxInvoiceController extends \Controller\Front\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        try {
            //--- 세금계산서 관련 정보
            $tax	= \App::load('\\Component\\Order\\Tax');
            $data	= $tax->getOrderTaxInvoice(Request::get()->get('orderNo'));

             if (MemberUtil::checkLogin() == 'member' || MemberUtil::checkLogin() == 'guest') {
               if((MemberUtil::checkLogin() == 'member'  && $data['orderMemNo'] != Session::get('member.memNo')) ||( MemberUtil::checkLogin() == 'guest' && $data['orderName'] != Session::get('guest.orderNm')) )   {
                   throw new \Exception(__('회원정보가 존재하지 않습니다.'));
               }
            } else {
                throw new \Exception(__('회원정보가 존재하지 않습니다.'));
            }

            // 인감 이미지는 기존은 세금계산서 정보에, 현재는 기본 정보에 등록됨
            $taxInvoice = gd_policy('order.taxInvoice');
            $basicData = gd_policy('basic.info');
            if (empty($taxInvoice['taxStampIamge']) === false) {
                $sealPath = UserFilePath::data('etc', $taxInvoice['taxStampIamge'])->www();
            } else if (empty($basicData['stampImage']) === false) {
                $sealPath = UserFilePath::data('etc', $basicData['stampImage'])->www();
            } else {
                $sealPath = '';
            }

            if(Session::has('manager')) $printUpdateFl = "n";
            else $printUpdateFl = "y";

            $modeStr = Request::get()->get('modeStr');
            gd_isset($modeStr,'blue');


            // 출력 종류
            if ($modeStr == 'blue'){
                $classids = array( 'cssblue' );				//-- 공급받는자용
            } else if ($modeStr == 'red'){
                $classids = array( 'cssred' );				//-- 공급자용
            } else {
                $classids = array( 'cssblue', 'cssred' );	//-- 공급받는자용, 공급자용
            }
            $headuser = array( 'cssblue'=>__('공급받는자보관용'), 'cssred'=>__('공급자보관용') );

            // 세금계산서 이용안내
            if (gd_isset($taxInvoice['taxInvoiceUseFl']) == 'y') {
                $taxInvoiceInfo = gd_policy('order.taxInvoiceInfo');
                if ($taxInvoice['taxinvoiceInfoUseFl'] == 'y') {
                    $this->setData('taxinvoiceInfo', nl2br($taxInvoiceInfo['taxinvoiceInfo']));
                }
            }
            unset($taxInvoice, $basicData);

            $this->setData('orderNo', Request::get()->get('orderNo'));
            $this->setData('data', $data);
            $this->setData('classids', $classids);
            $this->setData('sealPath', $sealPath);
            $this->setData('headuser', $headuser);
            $this->setData('printUpdateFl', $printUpdateFl);

        }
        catch (\Exception $e) {

        }

    }
}
