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

use Component\Member\Group\Util as GroupUtil;
use Framework\StaticProxy\Proxy\UserFilePath;
use Exception;
use Framework\Utility\UrlUtils;
use Request;

/**
 * Class ShortUrlListController
 *
 * @package Bundle\Controller\Admin\Promotion
 * @author  Young-jin Bag <kookoo135@godo.co.kr>
 */
class PollRegisterController extends \Controller\Admin\Controller
{
    public function index()
    {
        $getValue = Request::get()->toArray();

        $poll = \App::load('\\Component\\Promotion\\Poll');
        $defaultBannerImg = $poll->getObject('bannerImagePath') . '/' . $poll->getObject('defaultBannerImg');

        if (empty($getValue['sno']) === false) {
            // --- 메뉴 설정
            $this->callMenu('promotion', 'poll', 'pollModify');
            $data = $poll->getPollData(null, $getValue['sno']);

            $item = $data['pollItem'];
            $item = json_decode($item, true);
            foreach ($item['itemAnswerType'] as $k => $v) {
                if (empty($item['itemRequired'][$k]) === true) $item['itemRequired'][$k] = '';
                $item['itemLastAnswer'][$k] = false;
                if ($v == 'obj') {
                    $itemAnswer = $item['itemAnswer'][$k];
                    $itemLastAnswer = array_pop($itemAnswer);
                    if ($itemLastAnswer == 'ETC') {
                        $item['itemLastAnswer'][$k] = true;
                        array_pop($item['itemAnswer'][$k]);
                    }
                }
            }

            if ($data['pollDeviceFl'] == 'mobile') {
                $display['pc'] = 'display-none';
                $disabled['pollResultViewFl']['Y'] = 'disabled="disabled"';
            } elseif ($data['pollDeviceFl'] == 'pc') {
                $display['mobile'] = 'display-none';
            } else {
                $disabled['pollResultViewFl']['Y'] = 'disabled="disabled"';
            }
            if ($data['pollBannerFl'] != 'upl') {
                $disabled['pollBannerImg'] =
                $disabled['pollBannerImgMobile'] = 'disabled="disabled"';
            } else {
                if (empty($data['pollBannerImg']) === false) {
                    $imgSrc = UserFilePath::data('poll') . '/' . $data['pollBannerImg'];
                    $imgSize['front'] = getimagesize($imgSrc);
                    if ($imgSize['front'][0] > 400) $imgSize['front'][0] = 400;
                }

                if (empty($data['pollBannerImgMobile']) === false) {
                    $imgMobileSrc = UserFilePath::data('poll') . '/' . $data['pollBannerImgMobile'];
                    $imgSize['mobile'] = getimagesize($imgMobileSrc);
                    if ($imgSize['mobile'][0] > 400) $imgSize['mobile'][0] = 400;
                }
            }
            if ($data['pollBannerFl'] == 'none') {
                $display['category'] = 'display-none';
            }
            if ($data['pollMemberLimitFl'] == 'unlimited') {
                $disabled['pollBannerLimitFl'] = 'disabled="disabled"';
            }
            $item = json_encode($item);
            $mode = 'modify';
        } else {
            // --- 메뉴 설정
            $this->callMenu('promotion', 'poll', 'pollRegister');
            $data = [
                'pollDeviceFl' => 'pc',
                'pollGroupFl' => 'all',
                'pollMemberLimitFl' => 'unlimited',
                'pollBannerFl' => 'def',
                'pollResultViewFl' => 'Y',
                'pollHtmlContentFl' => 'N',
                'pollViewCategory' => 'all',
                'pollHtmlContentSameFl' => 'Y',
            ];
            $pollItem = [
                'itemAnswerType' => ['obj', 'sub'],
                'itemResponseType' => ['radio', 'short'],
                'itemTitle' => ['', ''],
                'itemRequired' => ['', ''],
                'itemLastAnswer' => ['', ''],
                'itemAnswer' => [
                    ['', '']
                ],
            ];
            $display['mobile'] = 'display-none';
            $disabled['pollBannerImg'] =
            $disabled['pollBannerImgMobile'] = 'disabled="disabled"';
            $item =json_encode($pollItem);
            $mode = 'regist';
        }

        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);

        $checked['pollEndDtFl'][$data['pollEndDtFl']] =
        $checked['pollDeviceFl'][$data['pollDeviceFl']] =
        $checked['pollGroupFl'][$data['pollGroupFl']] =
        $checked['pollViewCategory'][$data['pollViewCategory']] =
        $checked['pollMemberLimitFl'][$data['pollMemberLimitFl']] =
        $checked['pollBannerLimitFl'][$data['pollBannerLimitFl']] =
        $checked['pollBannerFl'][$data['pollBannerFl']] =
        $checked['pollResultViewFl'][$data['pollResultViewFl']] =
        $checked['pollHtmlContentFl'][$data['pollHtmlContentFl']] =
        $checked['pollHtmlContentSameFl'][$data['pollHtmlContentSameFl']] = 'checked="checked"';

        if ($data['pollEndDtFl'] == 'Y') {
            $disabled['pollEndDt'] = 'disabled="disabled"';
        }

        if (empty($data['pollGroupSno']) === false) {
            $data['pollGroupNm'] = [];
            $groupSno = str_replace(INT_DIVISION, ',', $data['pollGroupSno']);
            $data['pollGroupNm'] = GroupUtil::getGroupName("sno IN (" . $groupSno . ")");
        }

        if (empty($data['pollViewPosition']) === false) {
            $viewPosition = explode(',', $data['pollViewPosition']);
            foreach ($viewPosition as $v) {
                $checked['pollViewPosition'][$v] = 'checked="checked"';
            }
            if (in_array('category', $viewPosition)) {
                if ($data['pollViewCategory'] == 'all') {
                    $checked['pollViewCategory']['all'] = 'checked="checked"';
                } else {
                    $checked['pollViewCategory']['select'] = 'checked="checked"';
                    $viewCategory = explode(STR_DIVISION, $data['pollViewCategory']);
                    $data['viewCategory'] = GroupUtil::getDiscountCategory($viewCategory);

                }
            } else {
                $disabled['pollViewCategory']['all'] =
                $disabled['pollViewCategory']['select'] =
                $disabled['pollViewCategory']['button'] = 'disabled="disabled"';
            }
        } else {
            $disabled['pollViewCategory']['all'] =
            $disabled['pollViewCategory']['select'] =
            $disabled['pollViewCategory']['button'] = 'disabled="disabled"';
        }
        unset($viewPosition);
        unset($viewCategory);

        $this->setData('mode', $mode);
        $this->setData('data', $data);
        $this->setData('checked', $checked);
        $this->setData('disabled', $disabled);
        $this->setData('display', $display);
        $this->setData('groupSno', $groupSno);
        $this->setData('defaultBannerImg', $defaultBannerImg);
        $this->setData('questionCount', $poll->questionCount);
        $this->setData('answerCount', $poll->answerCount);
        $this->setData('item', $item);
        $this->setData('imgSize', $imgSize);
        $this->setData('statusFl', $poll->getObject('statusFl'));
        $this->setData('reverseStatusFl', $poll->getObject('reverseStatusFl'));
        $this->setData('reverseStatusKey', $poll->getObject('reverseStatusKey'));
        $this->setData('bannerImagePath', $poll->getObject('bannerImagePath'));
        $this->setData('adminList', UrlUtils::getAdminListUrl());
    }
}
