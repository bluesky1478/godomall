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

/**
 * Class 개인정보수집동의항목 테이블관련 객체
 * @package Bundle\Component\Agreement
 * @author  yjwee
 */
class PrivateItem
{
    private $_disabled = false;
    private $_addButtonUse = true;
    private $_removeButtonUse = true;
    private $_captionUse = true;
    private $_captionText = '';
    private $_captionTooltipUse = true;
    private $_captionTooltipValue = '';
    private $_captionTooltipText = '';
    private $_radioUse = true;
    // __('사용여부');
    // __('사용');
    // __('사용하지 않음');
    // __('내용입력');
    private $_radioHead = '사용여부';
    private $_radioItem = [
        [
            'value' => 'y',
            'text'  => '사용',
        ],
        [
            'value' => 'n',
            'text'  => '사용하지 않음',
        ],
    ];
    private $_textAreaHead = '내용입력';
    private $_textAreaRows = 6;
    private $_textAreaName = '';
    private $_textAreaItem = [];
    private $_name;

    public function __construct($name = null, array $inform = null)
    {
        if (is_null($name) === false) {
            $this->_name = $name;
        }
        if (is_null($inform) === false) {
            $this->registerInformData($inform);
        }
    }

    public function registerInformData($inform)
    {
        gd_isset($inform[0]['modeFl'], 'n');

        $tableData = [];
        $tableData['checked'][$inform[0]['modeFl']] = 'checked="checked"';
        $this->_textAreaName = $this->_name;

        if ($this->_radioUse === true) {
            $this->_radioItem = [
                [
                    'id'      => $this->_name . 'ModeFlY',
                    'name'    => $this->_name . 'ModeFl',
                    'value'   => 'y',
                    'checked' => gd_isset($tableData['checked']['y'], ''),
                    'text'    => __('사용'),
                ],
                [
                    'id'      => $this->_name . 'ModeFlN',
                    'name'    => $this->_name . 'ModeFl',
                    'value'   => 'n',
                    'checked' => gd_isset($tableData['checked']['n'], ''),
                    'text'    => __('사용하지 않음'),
                ],
            ];
        }

        foreach ($inform as $item) {
            $tableData['rows'][] = [
                'content'  => $item['content'],
                'informNm' => $item['informNm'],
                'sno'      => $item['sno'],

            ];
        }
        foreach ($tableData['rows'] as $key => $item) {
            $this->_textAreaItem[] = [
                'sno'      => $item['sno'],
                'informNm' => $item['informNm'],
                'content'  => $item['content'],
            ];
        }
    }

    /**
     * 객체의 프로퍼티를 배열로 반환하는 함수
     *
     * @return array
     */
    public function toArray()
    {
        $array['caption'] = [
            'use'     => $this->_captionUse,
            'text'    => $this->_captionText,
            'tooltip' => [
                'use'  => $this->_captionTooltipUse,
                'text' => $this->_captionTooltipText,
            ],
        ];
        $array['textArea'] = [
            'head' => $this->_textAreaHead,
            'rows' => $this->_textAreaRows,
            'name' => $this->_textAreaName,
            'item' => $this->_textAreaItem,
        ];
        $array['radio'] = [
            'use'  => $this->_radioUse,
            'head' => $this->_radioHead,
            'item' => $this->_radioItem,
        ];

        return $array;
    }

    /**
     * @return string
     */
    public function getCaptionText()
    {
        return $this->_captionText;
    }

    /**
     * @param string $captionText
     */
    public function setCaptionText($captionText)
    {
        $this->_captionText = $captionText;
    }

    /**
     * @return string
     */
    public function getCaptionTooltipText()
    {
        return $this->_captionTooltipText;
    }

    /**
     * @param string $captionTooltipText
     */
    public function setCaptionTooltipText($captionTooltipText)
    {
        $this->_captionTooltipText = $captionTooltipText;
    }

    /**
     * @return boolean
     */
    public function isCaptionTooltipUse()
    {
        return $this->_captionTooltipUse;
    }

    /**
     * @param boolean $captionTooltipUse
     */
    public function setCaptionTooltipUse($captionTooltipUse)
    {
        $this->_captionTooltipUse = $captionTooltipUse;
    }

    /**
     * @return string
     */
    public function getCaptionTooltipValue()
    {
        return $this->_captionTooltipValue;
    }

    /**
     * @param string $captionTooltipValue
     */
    public function setCaptionTooltipValue($captionTooltipValue)
    {
        $this->_captionTooltipValue = $captionTooltipValue;
    }

    /**
     * @return boolean
     */
    public function isCaptionUse()
    {
        return $this->_captionUse;
    }

    /**
     * @param boolean $captionUse
     */
    public function setCaptionUse($captionUse)
    {
        $this->_captionUse = $captionUse;
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param null $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getRadioHead()
    {
        return $this->_radioHead;
    }

    /**
     * @param string $radioHead
     */
    public function setRadioHead($radioHead)
    {
        $this->_radioHead = $radioHead;
    }

    /**
     * @return array
     */
    public function getRadioItem()
    {
        return $this->_radioItem;
    }

    /**
     * @param array $radioItem
     */
    public function setRadioItem($radioItem)
    {
        $this->_radioItem = $radioItem;
    }

    /**
     * @return boolean
     */
    public function isRadioUse()
    {
        return $this->_radioUse;
    }

    /**
     * @param boolean $radioUse
     */
    public function setRadioUse($radioUse)
    {
        $this->_radioUse = $radioUse;
    }

    /**
     * @return string
     */
    public function getTextAreaHead()
    {
        return $this->_textAreaHead;
    }

    /**
     * @param string $textAreaHead
     */
    public function setTextAreaHead($textAreaHead)
    {
        $this->_textAreaHead = $textAreaHead;
    }

    /**
     * @return array
     */
    public function getTextAreaItem()
    {
        return $this->_textAreaItem;
    }

    /**
     * @param array $textAreaItem
     */
    public function setTextAreaItem($textAreaItem)
    {
        $this->_textAreaItem = $textAreaItem;
    }

    /**
     * @return string
     */
    public function getTextAreaName()
    {
        return $this->_textAreaName;
    }

    /**
     * @param string $textAreaName
     */
    public function setTextAreaName($textAreaName)
    {
        $this->_textAreaName = $textAreaName;
    }

    /**
     * @return int
     */
    public function getTextAreaRows()
    {
        return $this->_textAreaRows;
    }

    /**
     * @param int $textAreaRows
     */
    public function setTextAreaRows($textAreaRows)
    {
        $this->_textAreaRows = $textAreaRows;
    }

    /**
     * @return boolean
     */
    public function isAddButtonUse()
    {
        return $this->_addButtonUse;
    }

    /**
     * @param boolean $buttonUse
     */
    public function setAddButtonUse($buttonUse)
    {
        $this->_addButtonUse = $buttonUse;
    }

    /**
     * @return boolean
     */
    public function isRemoveButtonUse()
    {
        return $this->_removeButtonUse;
    }

    /**
     * @param boolean $removeButtonUse
     */
    public function setRemoveButtonUse($removeButtonUse)
    {
        $this->_removeButtonUse = $removeButtonUse;
    }

    /**
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->_disabled;
    }

    /**
     * @param boolean $disabled
     */
    public function setDisabled($disabled)
    {
        $this->_disabled = $disabled;
    }
}
