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

namespace Bundle\Component\Agreement;

use App;
use Component\Mall\Mall;
use Component\Design\ReplaceCode;

class BuyerInformUtil
{
    /** @var  \Framework\Database\DBTool $_db */
    private $_db;
    private $_buyerInform;

    public function __construct()
    {
        $this->_db = App::load('DB');
        $this->_buyerInform = new BuyerInform();
    }

    /**
     * 개인정보정보취급방침 치환코드 처리 후 반환 함수
     *
     * @return mixed
     */
    public function getPrivateWithReplaceCode($mallSno = DEFAULT_MALL_NUMBER)
    {
        $inform = $this->_buyerInform->getInformData(BuyerInformCode::BASE_PRIVATE, $mallSno);

        //--- 개인정보 관리 책임자
        $personalInfoManager = $this->getPersonalManagerByGlobals(false);

        return str_replace(array_keys($personalInfoManager), array_values($personalInfoManager), $inform['content']);
    }

    /**
     * 개인정보 관리 책임자 치환코드에 개인정보 관리 책임자 정보를 치환하여 반환하는 함수
     *
     * @param bool $isPrefixReplace
     *
     * @return array
     */
    private function getPersonalManagerByGlobals($isPrefixReplace = true)
    {
        $replaceCode = new ReplaceCode();
        $replaceCode->initWithUnsetDiff(
            [
                '{rc_mallNm}',
                '{rc_privateNm}',
                '{rc_privatePosition}',
                '{rc_privateDepartment}',
                '{rc_privatePhone}',
                '{rc_privateEmail}',
                '{rc_companyNm}',
            ]
        );
        $defineCode = $replaceCode->getDefinedCode();
        $managerInfo = [];
        foreach ($defineCode as $key => $value) {
            if ($isPrefixReplace === true) {
                $length = strlen($key) - 5;
                $key = substr($key, 4, $length);
            }
            $managerInfo[$key] = $value['val'];
        }

        return $managerInfo;
    }

    /**
     * [선택] 개인정보 수집.이용 동의
     *
     * @return string
     */
    public function getTableByPrivateApprovalOption($mallSno = DEFAULT_MALL_NUMBER)
    {
        $informData = $this->_buyerInform->getInformDataArray(BuyerInformCode::PRIVATE_APPROVAL_OPTION, null, true, $mallSno);

        $privateItem = new PrivateItem('privateApprovalOption', $informData);
        $privateItem->setCaptionText(__('[선택] 개인정보 수집.이용 동의'));

        return $this->makeTableByPrivate($privateItem);
    }

    /**
     * 약관/개인정보설정의 테이블 html 생성 함수
     *
     * @param PrivateItem $privateItem 테이블의 각 태그에 설정될 정보
     *
     * @return string
     */
    public function makeTableByPrivate($privateItem)
    {
        $table = [];
        $table[] = '<table class="table table-cols">';
        if ($privateItem->isCaptionUse()) {
            $table = $this->_makeCaptionByPrivate($privateItem, $table);
        }
        $table[] = '<colgroup><col class="width-md"/><col/></colgroup>';
        $table[] = '<tbody>';
        if ($privateItem->isRadioUse()) {
            $table = $this->_makeRadioByPrivate($privateItem, $table);
        }
        if ($privateItem->isAddButtonUse()) {
            $table[] = '<tr>';
            $table[] = '<th rowspan="2">' . $privateItem->getTextAreaHead() . '</th>';
            $table[] = '<td>';
            $table[] = '<button type="button" data-target="' . $privateItem->getName() . '" class="btn btn-sm btn-white btn-icon-plus js-add-row pull-left">' . __('추가') . '</button>';
            $table[] = '</td>';
            $table[] = '</tr>';
        } else {
            $table[] = '<tr>';
            $table[] = '<th rowspan="2">' . $privateItem->getTextAreaHead() . '</th>';
            $table[] = '</tr>';
        }
        $table[] = '<tr>';
        $table[] = '<td>';
        $table = $this->_makeTextAreaByPrivate($privateItem, $table);
        $table[] = '</td>';
        $table[] = '</tr>';
        $table[] = '</tbody>';
        $table[] = '</table>';
        $table = join('', $table);

        return $table;
    }

    /**
     * 약관/개인정보설정에서 약관 추가시 테이블의 캡션 html 배열을 추가하는 함수
     *
     * @param PrivateItem $privateItem 해당 태그의 속성 데이터
     * @param array       $table       테이블 html 배열
     *
     * @return array
     */
    private function _makeCaptionByPrivate($privateItem, $table)
    {
        $table[] = '<caption>' . $privateItem->getCaptionText();
        $table[] = '</caption>';

        return $table;
    }

    /**
     * 약관/개인정보설정에서 약관 추가시 테이블의 라디오버튼 html 배열을 추가하는 함수
     *
     * @param PrivateItem $privateItem 해당 태그의 속성 데이터
     * @param array       $table       테이블 html 배열
     *
     * @return array
     */
    private function _makeRadioByPrivate($privateItem, $table)
    {
        $table[] = '<tr>';
        $table[] = '<th>' . $privateItem->getRadioHead() . '</th>';
        $table[] = '<td>';
        foreach ($privateItem->getRadioItem() as $item) {
            $table[] = '<label class="radio-inline" for="' . $item['id'] . '">';
            $table[] = '<input id="' . $item['id'] . '" type="radio" name="' . $item['name'] . '" value="' . $item['value'] . '" ' . $item['checked'] . '/>' . $item['text'];
            $table[] = '</label>';
        }
        $table[] = '</td>';
        $table[] = '</tr>';

        return $table;
    }

    /**
     * 약관/개인정보설정에서 약관 추가시 생성되는 텍스트박스 및 버튼 html 배열을 추가하는 함수
     *
     * @param PrivateItem $privateItem 해당 태그의 속성 데이터
     * @param array       $table       테이블 html 배열
     *
     * @return array 테이블 html에 텍스트박스를 추가한 배열
     */
    private function _makeTextAreaByPrivate($privateItem, $table)
    {
        $idx = 0;

        foreach ($privateItem->getTextAreaItem() as $key => $item) {
            if (is_null($item['sno'])) {
                continue;
            }

            $table[] = '<div class="js-title-textarea-row" id="' . $privateItem->getName() . $item['sno'] . '" name="' . $privateItem->getName() . '">';
            $table[] = '<div>';
            $table[] = '<input type="text" class="form-control width80p pull-left" id="' . $privateItem->getName() . 'Title' . $item['sno'] . '" name="' . $privateItem->getTextAreaName() . 'Title[]" value="' . $item['informNm'] . '" ';
            if ($privateItem->isDisabled()) {
                $table[] = 'disabled="disabled"';
            }
            $table[] = '/>';
            if ($privateItem->isRemoveButtonUse()) {
                $table[] = '<button type="button" data-target="' . $privateItem->getName() . $item['sno'] . '" data-sno="' . $item['sno'] . '" class="btn btn-sm btn-white btn-icon-minus js-remove-row pull-right">' . __('삭제') . '</button>';
            }
            $table[] = '</div>';
            $table[] = '<div>';
            $table[] = '<input type="hidden" name="' . $privateItem->getTextAreaName() . 'Sno[]" value="' . $item['sno'] . '"/>';
            $table[] = '<textArea name="' . $privateItem->getTextAreaName() . '[]" id="' . $privateItem->getName() . 'TextArea' . $item['sno'] . '" rows="' . $privateItem->getTextAreaRows() . '" class="pull-left form-control width80p mgt5" data-index="' . $item['sno'] . '"  ';
            if ($privateItem->isDisabled()) {
                $table[] = 'disabled="disabled"';
            }
            $table[] = '>';
            $table[] = $item['content'];
            $table[] = '</textArea>';
            $table[] = '</div>';
            $table[] = '</div>';

            $idx++;
        }

        return $table;
    }

    /**
     * [선택] 개인정보 제3자 제공 동의
     *
     * @return string
     */
    public function getTableByPrivateOffer($mallSno = DEFAULT_MALL_NUMBER)
    {
        $informData = $this->_buyerInform->getInformDataArray(BuyerInformCode::PRIVATE_OFFER, null, true, $mallSno);
        $privateItem = new PrivateItem('privateOffer', $informData);
        $privateItem->setCaptionText(__('[선택] 개인정보 제3자 제공 동의'));

        return $this->makeTableByPrivate($privateItem);
    }

    /**
     * [선택] 개인정보 취급위탁 동의
     *
     * @return string
     */
    public function getTableByPrivateConsign($mallSno = DEFAULT_MALL_NUMBER)
    {
        $informData = $this->_buyerInform->getInformDataArray(BuyerInformCode::PRIVATE_CONSIGN, null, true, $mallSno);
        $privateItem = new PrivateItem('privateConsign', $informData);
        $privateItem->setCaptionText(__('[선택] 개인정보 처리ㆍ위탁 동의'));

        return $this->makeTableByPrivate($privateItem);
    }

    /**
     * 개인정보 수집ㆍ이용 동의 (비회원 주문 시)
     *
     * @return string
     */
    public function getTableByPrivateGuestOrder()
    {
        $informData = $this->_buyerInform->getInformData(BuyerInformCode::PRIVATE_GUEST_ORDER);
        $privateItem = new PrivateItem('privateGuestOrder', $informData);
        $privateItem->setCaptionText(__('개인정보 수집ㆍ이용 동의 (비회원 주문 시)'));
        $privateItem->setRadioUse(false);
        $privateItem->setAddButtonUse(false);
        $privateItem->setRemoveButtonUse(false);

        return $this->makeTableByPrivate($privateItem);
    }

    /**
     * 개인정보 수집ㆍ이용 동의 (비회원 게시글 등록 시)
     *
     * @return string
     */
    public function getTableByPrivateGuestBoardWrite()
    {
        $informData = $this->_buyerInform->getInformData(BuyerInformCode::PRIVATE_GUEST_BOARD_WRITE);
        $privateItem = new PrivateItem('privateGuestBoardWrite', $informData);
        $privateItem->setCaptionText(__('개인정보 수집ㆍ이용 동의 (비회원 게시글 등록 시)'));
        $privateItem->setRadioUse(false);
        $privateItem->setAddButtonUse(false);
        $privateItem->setRemoveButtonUse(false);

        return $this->makeTableByPrivate($privateItem);
    }

    /**
     * 개인정보 수집ㆍ이용 동의 (비회원 댓글 등록 시)
     *
     * @return string
     */
    public function getTableByPrivateGuestCommentWrite()
    {
        $informData = $this->_buyerInform->getInformData(BuyerInformCode::PRIVATE_GUEST_COMMENT_WRITE);
        $privateItem = new PrivateItem('privateGuestCommentWrite', $informData);
        $privateItem->setCaptionText(__('개인정보 수집ㆍ이용 동의 (비회원 댓글 등록 시)'));
        $privateItem->setRadioUse(false);
        $privateItem->setAddButtonUse(false);
        $privateItem->setRemoveButtonUse(false);

        return $this->makeTableByPrivate($privateItem);
    }

    /**
     * 회원/비회원 주문 시 상품 공급사 개인정보 제공 동의
     *
     * @return string
     */
    public function getTableByPrivateProvider()
    {
        $informData = $this->_buyerInform->getInformData(BuyerInformCode::PRIVATE_PROVIDER);
        $privateItem = new PrivateItem('privateProvider', $informData);
        $privateItem->setCaptionText(__('회원/비회원 주문 시 상품 공급사 개인정보 제공 동의'));
        $privateItem->setRadioUse(false);
        $privateItem->setAddButtonUse(false);
        $privateItem->setRemoveButtonUse(false);

        return $this->makeTableByPrivate($privateItem);
    }
}
