<div class="table-title gd-help-manual">부가정보</div>
<div class="input_wrap form-inline">
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col class="<?= (!empty($widthClass)) ? $widthClass : 'width-3xl' ?>"/>
            <col class="width-sm"/>
            <col class="<?= (!empty($widthClass)) ? $widthClass : '' ?>"/>
        </colgroup>
        <tr>
            <th>팩스번호</th>
            <td>
                <span title="팩스번호를 입력해주세요!">
                    <input type="text" name="fax" value="<?= $data['fax'] ?>" maxlength="12" class="form-control js-number-only width-md"/>
                </span>
            </td>
            <th>직업</th>
            <td class="input_area" colspan="3">
                <?= gd_select_box('job', 'job', $joinField['job']['data'], null, $data['job'], '선택'); ?>
            </td>
        </tr>
        <tr>
            <th>성별</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="sexFl" value="m" <?= $checked['sexFl']['m']; ?>>
                    남자
                </label>
                <label class="radio-inline">
                    <input type="radio" name="sexFl" value="w" <?= $checked['sexFl']['w']; ?>>
                    여자
                </label>
            </td>
            <th>생일</th>
            <td>
                <?= gd_select_box(
                    'calendarFl', 'calendarFl', [
                    's' => '양력',
                    'l' => '음력',
                ], null, $data['calendarFl'], '선택'
                ); ?>
                <span title="생일을 입력해주세요!">
                    <input type="text" class="js-datepicker" name="birthDt"
                           value="<?= $data['birthDt']; ?>"/>
                </span>
            </td>
        </tr>
        <tr>
            <th>결혼여부</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="marriFl" value="n"
                           data-target="input[name=marriDate]" <?= $checked['marriFl']['n']; ?>>
                    미혼
                </label>
                <label class="radio-inline">
                    <input type="radio" name="marriFl" value="y"
                           data-target="input[name=marriDate]" <?= $checked['marriFl']['y']; ?>>
                    기혼
                </label>
            </td>
            <th>결혼기념일</th>
            <td>
                <span title="결혼기념일을 입력해주세요!">
                    <input type="text" class="js-datepicker" name="marriDate"
                           value="<?= $data['marriDate']; ?>"/>
                </span>
            </td>
        </tr>
        <tr>
            <th>추천인아이디</th>
            <td>
                <span id="recomm" title="추천인 아이디를 입력해주세요!">
                    <?php if (empty(gd_isset($data['recommId'], ''))) { ?>
                        <input type="text" name="recommId" id="recommId" class="form-control error"
                               value="<?= gd_isset($data['recommId'], ''); ?>"/>
                        <input type="hidden" name="chkRecommendId" value=""/>
                        <input type="button" id="btnRecommendCheck" value="확인" class="btn btn-gray btn-sm"/>
                    <?php } else {
                        echo gd_isset($data['recommId'], '');
                        echo '<input type="hidden" name="recommId" value="' . gd_isset($data['recommId']) . '"/>';
                        ?>
                    <?php } ?>
                </span>
                <span id="recommIdMsg" class="input_error_msg"></span>

            </td>
            <th>추천받은횟수</th>
            <td><?= gd_isset($data['recommendCnt'], '0'); ?>회</td>
        </tr>
        <tr>
            <th>관심분야</th>
            <td class="input_area" colspan="3">
                <?= gd_check_box('interest[]', $joinField['interest']['data'], $data['interest'], $checkBoxRow) ?>
            </td>
        </tr>
        <tr>
            <th>개인정보유효기간</th>
            <td class="input_area" colspan="3">
                <label class="radio-inline">
                    <input type="radio" name="expirationFl" value="1" <?= $checked['expirationFl']['1']; ?>>
                    1년
                </label>
                <label class="radio-inline">
                    <input type="radio" name="expirationFl" value="3" <?= $checked['expirationFl']['3']; ?>>
                    3년
                </label>
                <label class="radio-inline">
                    <input type="radio" name="expirationFl" value="5" <?= $checked['expirationFl']['5']; ?>>
                    5년
                </label>
                <label class="radio-inline">
                    <input type="radio" name="expirationFl" value="999" <?= $checked['expirationFl']['999']; ?>>
                    탈퇴 시 <?= $data['lifeMemberConversionDt']; ?>
                </label>
            </td>
        </tr>
        <tr>
            <th>남기는 말씀</th>
            <td class="input_area" colspan="3">
                <span title="메모를 작성해 주세요!">
                <textarea name="memo" rows="5" cols=""
                          class="form-control width90p js-maxlength"><?= $data['memo']; ?></textarea>
                </span>
            </td>
        </tr>
    </table>
</div>

<?php if (count($joinField['ex']) > 0) { ?>
    <div class="table-title gd-help-manual">
        추가정보
    </div>
    <div class="input_wrap form-inline">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col class="<?= (!empty($widthClass)) ? $widthClass : 'width-3xl' ?>"/>
                <col class="width-sm"/>
                <col class="<?= (!empty($widthClass)) ? $widthClass : '' ?>"/>
            </colgroup>
            <?= $htmlExtra; ?>
        </table>
    </div>
<?php } ?>

<div class="table-title gd-help-manual">
    개인정보 수집&middot;이용 선택동의 내역
</div>
<div class="form-inline">
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th>개인정보<br/>수집&middot;이용</th>
            <td>
                <?php
                if (isset($data['privateApprovalOptionFl']) && is_array($data['privateApprovalOptionFl']) && isset($privateApprovalOption)) {
                    foreach ($privateApprovalOption as $index2 => $item2) {
                        if($data['privateApprovalOptionFl'][$item2['sno']] == 'y'){
                            echo '<div>- ' . $item2['informNm'] . ' : 동의함</div>';
                        } elseif ($data['privateApprovalOptionFl'][$item2['sno']] == 'n') {
                            echo '<div>- ' . $item2['informNm'] . ' : 동의안함</div>';
                        }
                    }
                } else {
                    echo '사용안함';
                }
                ?>
            </td>
        </tr>
        <tr>
            <th>개인정보 취급위탁</th>
            <td>
                <?php
                if (isset($data['privateConsignFl']) && is_array($data['privateConsignFl']) && isset($privateConsign)) {
                    foreach ($privateConsign as $index2 => $item2) {
                        if($data['privateConsignFl'][$item2['sno']] == 'y'){
                            echo '<div>- ' . $item2['informNm'] . ' : 동의함</div>';
                        } elseif ($data['privateConsignFl'][$item2['sno']] == 'n') {
                            echo '<div>- ' . $item2['informNm'] . ' : 동의안함</div>';
                        }
                    }
                } else {
                    echo '사용안함';
                }
                ?>
            </td>
        </tr>
        <tr>
            <th>개인정보<br/>제3자 제공</th>
            <td>
                <?php
                if (isset($data['privateOfferFl']) && is_array($data['privateOfferFl']) && isset($privateOffer)) {
                    foreach ($privateOffer as $index2 => $item2) {
                        if($data['privateOfferFl'][$item2['sno']] == 'y'){
                            echo '<div>- ' . $item2['informNm'] . ' : 동의함</div>';
                        } elseif ($data['privateOfferFl'][$item2['sno']] == 'n') {
                            echo '<div>- ' . $item2['informNm'] . ' : 동의안함</div>';
                        }
                    }
                } else {
                    echo '사용안함';
                }
                ?>
            </td>
        </tr>
    </table>
</div>
