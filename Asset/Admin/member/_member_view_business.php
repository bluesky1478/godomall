<div class="table-title div-business gd-help-manual">
    사업자정보
</div>
<div class="input_wrap form-inline div-business">
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col class="<?=(!empty($widthClass)) ? $widthClass : 'width-3xl'?>"/>
            <col class="width-sm"/>
            <col class="<?=(!empty($widthClass)) ? $widthClass : ''?>"/>
        </colgroup>
        <tr>
            <th>상호</th>
            <td>
                <span title="회사명를 입력해주세요!">
                    <input type="text" name="company" class="form-control" value="<?= $data['company']; ?>" maxlength="50" data-pattern="gdMemberNmGlobal"/>
                </span>
                <span id="companyMsg" class="input_error_msg"></span>
            </td>
            <th>사업자번호</th>
            <td>
                <span title="사업자번호를 입력해주세요!">
                    <input type="text" name="busiNo[]" size="3" maxlength="3" class="form-control js-number"
                           value="<?= $data['busiNo'][0]; ?>"/>
                    -
                    <input type="text" name="busiNo[]" size="2" maxlength="2" class="form-control js-number"
                           value="<?= $data['busiNo'][1]; ?>"/>
                    -
                    <input type="text" name="busiNo[]" size="5" maxlength="5" class="form-control js-number"
                           value="<?= $data['busiNo'][2]; ?>"/>
                    <input type="hidden" id="busiNo" name="fullBusiNo" class="error" value="<?= implode($data['busiNo']); ?>" data-overlap-businofl="<?= $joinField['busiNo']['overlapBusiNoFl']; ?>" data-charlen="<?= $joinField['busiNo']['charlen']; ?>" data-oldbusino="<?= implode($data['busiNo']); ?>"/>
                    <?php if ($joinField['busiNo']['overlapBusiNoFl'] == 'y') { ?>
                    <button type="button" id="overlap_busiNo" class="btn btn-gray btn-sm">중복확인</button>
                    <?php } ?>
                </span>
            </td>
        </tr>
        <tr>
            <th>대표자명</th>
            <td class="input_area" colspan="3">
                <span title="대표자를 입력해주세요!">
                    <input type="text" name="ceo" class="form-control" value="<?= $data['ceo']; ?>" data-pattern="gdEngKor" maxlength="20"/>
                </span>
            </td>

        </tr>
        <tr>
            <th>업태</th>
            <td>
                <span title="업태를 입력해주세요!">
                    <input type="text" name="service" class="form-control" value="<?= $data['service']; ?>" data-pattern="gdEngKor" maxlength="30"/>
                </span>
            </td>
            <th>종목</th>
            <td>
                <span title="종목을 입력해주세요!">
                    <input type="text" name="item" class="form-control" value="<?= $data['item']; ?>" data-pattern="gdEngKor" maxlength="30"/>
                </span>
            </td>
        </tr>
        <tr>
            <th>사업장 주소</th>
            <td class="input_area" colspan="3">
                <div class="form-inline mgb5">
                    <span title="우편번호를 입력해주세요!">
                        <input type="text" size="7" maxlength="5" name="comZonecode" class="form-control"
                               value="<?= $data['comZonecode'] ?>"/>
                        <input type="hidden" name="comZipcode" value="<?= $data['comZipcode'] ?>"/>
                        <span id="comZipcodeText" class="number <?php if (strlen($data['comZipcode']) != 7) {
                            echo 'display-none';
                        } ?>">(<?php echo $data['comZipcode']; ?>)
                        </span>
                    </span>
                    <input type="button"
                           onclick="postcode_search('comZonecode', 'comAddress', 'comZipcode');"
                           value="우편번호찾기" class="btn btn-sm btn-gray"/>
                </div>
                <div>
                    <span title="주소를 입력해주세요!">
                        <input type="text" name="comAddress" class="form-control width-2xl"
                               value="<?= $data['comAddress']; ?>"/>
                    </span>
                    <span title="상세주소를 입력해주세요!">
                        <input type="text" name="comAddressSub"
                               class="form-control width-2xl"
                               value="<?= $data['comAddressSub']; ?>"/>
                    </span>
                </div>
            </td>
        </tr>
    </table>
</div>
