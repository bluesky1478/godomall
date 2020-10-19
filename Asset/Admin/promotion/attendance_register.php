<?php
/**
 * @var Framework\Object\SimpleStorage $data
 * @var array                          $checked
 * @var array                          $selected
 * @var array                          $image
 * @var array                          $couponData
 * @var array                          $benefitGiveFl
 * @var string                         $disabledModify
 * @var boolean                        $hasWaitEvent
 */

$isModify = strpos(Request::getRequestUri(), 'modify') > 0;
?>
<form id="frm" action="../promotion/attendance_ps.php" method="post" enctype="multipart/form-data">
    <div class="page-header js-affix affix-top" style="width: auto;">
        <h3>출석체크 <?= isset($sno) ? '수정' : '등록' ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./attendance_list.php');" />
            <input type="button" value="저장" class="btn btn-red btn-register">
        </div>
    </div>
    <input type="hidden" name="mode" id="mode" value="<?= $mode ?>">
    <input type="hidden" name="sno" id="sno" value="<?= $sno ?>">
    <input type="hidden" name="stampPathTemp" id="stampPathTemp" value="<?= $data->get('stampPath', '') ?>">
    <div class="table-title ">기본 설정</div>
    <div class="form-inline">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>

            <tr>
                <th class="require">출석체크 이벤트명</th>
                <td>
                    <label for="title"></label>
                    <input type="text" name="title" id="title" class="form-control width-2xl" maxlength="30" value="<?= $data->get('title', '') ?>"/>
                </td>
            </tr>
            <tr>
                <th class="require">이벤트 기간</th>
                <td>
                    <div class="input-group js-datetimepicker">
                        <input type="text" class="form-control" placeholder="" name="startDt" id="startDt" <?php echo 'value="' . $data->get('startDt', '') . '" ' . $disabledModify ?>/>
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    ~
                    <div class="input-group js-datetimepicker endDatetime">
                        <input type="text" class="form-control" placeholder="" name="endDt" id="endDt" <?php echo 'value="' . $data->get('endDt', '') . '" ' ?>/>
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="eventEndDtFl" id="eventEndDtFl" value="y"
                            <?php
                            echo $checked['eventEndDtFl']['y'];
                            if ($hasWaitEvent) {
                                echo ' ' . $disabledModify;
                            }
                            ?>
                        />
                        종료기간 제한없음
                    </label>
                </td>
            </tr>
            <tr>
                <th>진행범위</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="deviceFl" value="pc" <?= $checked['deviceFl']['pc'] . ' ' . $disabledModify ?>/>
                        PC쇼핑몰
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="deviceFl" value="mobile" <?= $checked['deviceFl']['mobile'] . ' ' . $disabledModify ?>/>
                        모바일쇼핑몰
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="deviceFl" value="all" <?= $checked['deviceFl']['all'] . ' ' . $disabledModify ?>/>
                        PC+모바일
                    </label>
                </td>
            </tr>
            <tr>
                <th>참여가능<br/>회원등급 선택</th>
                <td>
                    <div class="mgb5">
                        <label class="radio-inline">
                            <input type="radio" name="groupFl" value="all" <?= $checked['groupFl']['all'] . ' ' . $disabledModify ?>/>
                            전체회원
                        </label>
                    </div>
                    <div class="mgb5" id="groupFlSelect">
                        <label class="radio-inline">
                            <input type="radio" name="groupFl" value="select" <?= $checked['groupFl']['select'] . ' ' . $disabledModify ?>/>
                            특정 회원등급
                        </label>
                        <button type="button" class="btn btn-sm btn-gray" id="btnSelectGroup" <?= $disabledModify ?>>회원등급 선택</button>
                        <?php
                        $groupListHtml = [];
                        $groupHiddenHtml = [];
                        $groupSno = $data->get('groupSno', '');
                        $selectedGroupHtml = '<div class="selected-btn-group mgt5';
                        if (is_array($groupSno)) {
                            $selectedGroupHtml .= ' active';
                            $groupListHtml[] = '<h5>선택된 회원등급</h5>';
                            foreach ($groupSno as $index => $item) {
                                $groupListHtml[] = '<span id="info_member_list_group_' . $index . '" class="btn-group btn-group-xs">';
                                $groupListHtml[] = '<input type="hidden" name="groupSno[]" value="' . $index . '"/>';
                                $groupListHtml[] = '<span class="btn">' . $item . '</span>';
                                $groupListHtml[] = '<button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_member_list_group_' . $index . '" ' . $disabledModify . '>삭제</button>';
                                $groupListHtml[] = '</span>';
                            }
                        }
                        $selectedGroupHtml .= '" id="member_groupLayer_list">';
                        $selectedGroupHtml .= join('', $groupListHtml);
                        $selectedGroupHtml .= '</div>';
                        echo $selectedGroupHtml;
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>출석방법</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="methodFl" value="stamp" <?= $checked['methodFl']['stamp'] . ' ' . $disabledModify ?>/>
                        스탬프형
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="methodFl" value="login" <?= $checked['methodFl']['login'] . ' ' . $disabledModify ?>/>
                        로그인형
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="methodFl" value="reply" <?= $checked['methodFl']['reply'] . ' ' . $disabledModify ?>/>
                        댓글형
                    </label>
                </td>
            </tr>
            <tr>
                <th class="require">이벤트 조건</th>
                <td>
                    <div class="mgb5">
                        <label class="radio-inline">
                            <input type="radio" name="conditionFl" value="sum" <?= $checked['conditionFl']['sum'] . ' ' . $disabledModify ?>/>
                            누적 출석횟수
                        </label>
                        <input id="conditionCountBySum" name="conditionCountBySum" type="text" class="form-control width-3xs" <?php echo 'value="' . $data->get('conditionCountBySum', '') . '" ' . $disabledModify ?>/>
                        <label for="conditionCountBySum"></label>
                        회 달성 시 혜택지급
                    </div>
                    <div class="mgb5">
                        <label class="radio-inline">
                            <input type="radio" name="conditionFl" value="continue" <?= $checked['conditionFl']['continue'] . ' ' . $disabledModify ?>/>
                            연속 출석횟수
                        </label>
                        <input id="conditionCountByContinue" name="conditionCountByContinue" type="text" class="form-control width-3xs" <?php echo 'value="' . $data->get('conditionCountByContinue', '') . '" ' . $disabledModify ?>/>
                        <label for="conditionCountByContinue"></label>
                        회 달성 시 혜택지급
                    </div>
                    <div>
                        <label class="radio-inline">
                            <input type="radio" name="conditionFl" value="each" <?= $checked['conditionFl']['each'] . ' ' . $disabledModify ?>/>
                            출석할 때마다 혜택지급
                        </label>
                    </div>
                </td>
            </tr>
            <?php if (gd_use_mileage() || gd_use_coupon()) { ?>
                <tr>
                    <th>혜택지급 방법</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="benefitGiveFl" value="auto" <?= $checked['benefitGiveFl']['auto'] . ' ' . $disabledModify ?>/>
                            자동지급
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="benefitGiveFl" value="manual" <?= $checked['benefitGiveFl']['manual'] . ' ' . $disabledModify ?>/>
                            수동지급
                        </label>
                    </td>
                </tr>
                <?php
            } else {
                echo '<input type="hidden" name="benefitGiveFl" value="manual" />';
            }
            ?>
            <?php if (gd_use_mileage() || gd_use_coupon()) { ?>
                <tr id="trBenefitFlag">
                    <th class="require">이벤트 조건달성 시<br/>지급혜택</th>
                    <td>
                        <div class="mgb5 <?php if (!gd_use_mileage()) echo 'display-none' ?>">
                            <label class="radio-inline">
                                <input type="radio" name="benefitFl" value="mileage" <?= $checked['benefitFl']['mileage'] ?>/>
                                마일리지
                            </label>
                            <input id="benefitMileage" name="benefitMileage" type="text" class="form-control width-3xs" value="<?= $data->get('benefitMileage', '') ?>">
                            원
                            <label for="benefitMileage"></label>
                        </div>
                        <div class="mgb5 <?php if (!gd_use_coupon()) echo 'display-none' ?>">
                            <label class="radio-inline">
                                <input type="radio" name="benefitFl" value="coupon" <?= $checked['benefitFl']['coupon'] ?>/>
                                쿠폰
                            </label>
                            <label for="benefitCouponSno">
                                <select class="form-control " id="benefitCouponSno" name="benefitCouponSno" aria-required="true">
                                    <option value="">쿠폰 선택</option>
                                    <?php
                                    foreach($couponData as $val) {
                                        $couponSelected = ($data->get('benefitCouponSno', '') == $val['couponNo']) ? 'selected="selected"' : '';
                                        $couponDisabled = ($val['couponType'] == 'f') ? 'disabled' : '';
                                        if ($val['couponType'] != 'f' || $data->get('benefitCouponSno', '') == $val['couponNo']) {
                                            ?>
                                            <option value="<?= $val['couponNo'] ?>" <?=$couponSelected?> <?=$couponDisabled?>><?= $couponDisabled ? '(발급종료)':''; ?><?= $val['couponNm'] ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </label>
                            <button type="button" class="btn btn-sm btn-gray js-coupon-register" id="btnCouponRegister">신규쿠폰 등록</button>
                            <div id="benefitCouponLink" class="pdl30">
                                <?php if($data->get('benefitCouponSno', '')) {?>
                                    <a href="coupon_regist.php?couponNo=<?=$data->get('benefitCouponSno', '')?>" target="_blank">선택 쿠폰 상세보기 ></a>
                                <?php } ?>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <div class="table-title ">이벤트 화면 설정</div>
    <div class="form-inline">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr class="div-login-hidden" id="divHead">
                <th>상단 영역</th>
                <td>
                    <div class="mgb5">
                        <label class="radio-inline">
                            <input type="radio" name="designHeadFl" value="default" <?= $checked['designHeadFl']['default'] ?>/>
                            기본 상단 영역
                        </label>
                        <br/> <img src="<?= UserFilePath::data('attendance', 'etc')->www() . '/banner-attendance-check.png' ?>" alt="(기본 상단 샘플이미지)" class="width50p"/>
                    </div>
                    <div class="mgb5">
                        <label class="radio-inline">
                            <input type="radio" name="designHeadFl" value="html" <?= $checked['designHeadFl']['html'] ?>/>
                            html 직접 편집
                        </label>
                    </div>
                    <?php
                    $designHead = '<textarea id="designHead" name="designHead" class="width90p';
                    if ($data->get('designHeadFl', '') != 'html') {
                        $designHead .= ' display-none';
                    }
                    $designHead .= '">' . gd_htmlspecialchars_stripslashes($data->get('designHead', '')) . '</textarea>';
                    echo $designHead;
                    ?>
                </td>
            </tr>
            <tr class="div-login-hidden" id="divBody">
                <th>본문 스킨</th>
                <td>
                    <div id="divBodyStamp" class="<?= $data->get('methodFl', 'stamp') == 'stamp' ? '' : 'display-none' ?>">
                        <div class="mgb5">
                            <label class="radio-inline">
                                <input type="radio" name="designBodyFl" value="stamp1" <?= $checked['designBodyFl']['stamp1'] ?>/>
                                스탬프형1
                            </label>
                            <img src="<?= UserFilePath::data('attendance', 'etc')->www() . '/stamp1.jpg' ?>" alt="(스탬프형1 샘플이미지)" id=""/>
                        </div>
                        <div class="mgb5">
                            <label class="radio-inline">
                                <input type="radio" name="designBodyFl" value="stamp2" <?= $checked['designBodyFl']['stamp2'] ?>/>
                                스탬프형2
                            </label>
                            <img src="<?= UserFilePath::data('attendance', 'etc')->www() . '/stamp2.jpg' ?>" alt="(스탬프형2 샘플이미지)" id=""/>
                        </div>
                    </div>
                    <div id="divBodyReply" class="<?= $data->get('methodFl', 'stamp') == 'reply' ? '' : 'display-none' ?>">
                        <div class="mgb5">
                            <label class="radio-inline">
                                <input type="radio" name="designBodyFl" value="reply1" <?= $checked['designBodyFl']['reply1'] ?>/>
                                댓글형1
                            </label>
                            <img src="<?= UserFilePath::data('attendance', 'etc')->www() . '/reply1.jpg' ?>" alt="(댓글1 샘플이미지)" id=""/>
                        </div>
                        <div class="mgb5">
                            <label class="radio-inline">
                                <input type="radio" name="designBodyFl" value="reply2" <?= $checked['designBodyFl']['reply2'] ?>/>
                                댓글형2
                            </label>
                            <img src="<?= UserFilePath::data('attendance', 'etc')->www() . '/reply2.jpg' ?>" alt="(댓글2 샘플이미지)" id=""/>
                        </div>
                    </div>
                </td>
            </tr>
            <tr class="div-login-hidden" id="divFooter">
                <th>하단 영역</th>
                <td>
                    <textarea class="width90p" id="editor" name="designFooter"><?= gd_htmlspecialchars_stripslashes($data->get('designFooter', '')) ?></textarea>
                </td>
            </tr>
            <tr class="div-login-hidden" id="divStampImage">
                <th>스탬프 이미지</th>
                <td>
                    <div class="mgb5">
                        <label class="radio-inline">
                            <input type="radio" name="stampFl" value="default" <?= $checked['stampFl']['default'] ?>/>
                            기본 스탬프 이미지
                        </label>
                        <img src="<?= UserFilePath::data('attendance', 'icon')->www() . '/attendance-check.png' ?>" alt="(스탬프 이미지)" id=""/>
                    </div>
                    <div class="mgb5">
                        <label class="radio-inline">
                            <input type="radio" name="stampFl" value="upload" <?= $checked['stampFl']['upload'] ?>/>
                            이미지 직접 등록 (54 x 54 pixel 권장)
                        </label>
                        <input type="file" id="stampPath" name="stampPath"/>
                        <?php
                        if ($data->get('stampPath', '') != '') {
                            $stampPath = '<img src="' . UserFilePath::data('attendance', 'upload')->www() . '/' . $data->get('stampPath') . '" alt="(등록된 스탬프 이미지)" class="width5p"/>';
                            echo $stampPath;
                        }
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>출석완료 시 메시지</th>
                <td>
                    <label for="completeComment"></label>
                    <input type="text" name="completeComment" id="completeComment" class="form-control width-3xl" maxlength="50" value="<?= $data->get('completeComment', '출석이 완료되었습니다. 내일도 참여해주세요.') ?>"/>
                </td>
            </tr>
            <tr>
                <th>이벤트 조건달성 시<br/>메시지</th>
                <td>
                    <label for="conditionComment"></label>
                    <input type="text" name="conditionComment" id="conditionComment" class="form-control width-3xl" maxlength="50" value="<?= $data->get('conditionComment', '축하드립니다! 출석목표가 달성되었습니다.') ?>"/>
                </td>
            </tr>

        </table>
    </div>
</form>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js" charset="utf-8"></script>
<script type="text/javascript">
    var attendance = (function ($, _, window, document, undefined) {
        var groupSno, designHeadFl, beforeEndDt;
        var $form, $endDt, $deviceFl, $groupFl, $benefitFl, $benefitGiveFl, $conditionFl, $designBodyFl, $designHeadFl, $methodFl;
        var $benefitMileage, $benefitCouponSno, $divBodyReply, $divBodyStamp, $loginHidden, $divStampImage;
        var validator, validate_settings;
        var changedEndDateTime = false;
        var isAlertOpen = false;
        var triggerTarget = {
            "radio": []
        };
        var $methodFlValue = '<?= $data->get('methodFl') ?>';

        validate_settings = {
            ignore: '.ignore',
            //            debug: true,
            rules: {title: "required", startDt: "required", endDt: "required"},
            messages: {
                title: {
                    required: '이벤트명을 입력해주세요.'
                },
                startDt: {
                    required: '이벤트 기간을 선택해주세요.'
                },
                endDt: {
                    required: '이벤트 기간을 선택해주세요.'
                }
            },
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            }
        };

        var init = function () {
            $form = $('#frm');
            $endDt = $('#endDt');
            $divBodyReply = $('#divBodyReply');
            $divBodyStamp = $('#divBodyStamp');
            $loginHidden = $('.div-login-hidden');
            $designBodyFl = $(':radio[name="designBodyFl"]');
            $designHeadFl = $(':radio[name="designHeadFl"]');
            $divStampImage = $('#divStampImage');

            validator = $form.validate(validate_settings);

            $('#btnSelectGroup').click(function () {
                if ($groupFl.val() == 'all') {
                    $groupFl.eq(1).prop('checked', true);
                }

                layer_add_info('member_group', {
                    "parentFormID": "member_groupLayer_list",
                    "dataInputNm": "groupSno",
                    "dataFormID": "info_member_list_group"
                })
            });

            $('.btn-register').click(save);

            $('#eventEndDtFl').change(function () {
                var $endDt = $('#endDt');
                if ($(this).prop('checked')) {
                    beforeEndDt = $endDt.val();
                    $endDt.val('');
                    $endDt.rules('remove');
                } else {
                    $endDt.val(beforeEndDt);
                }
            });

            <?php if($isModify){?>
            $endDt.focusin(function () {
                beforeEndDt = this.value;
            });
            $endDt.focusout(function () {
                if (beforeEndDt !== this.value) {
                    changedEndDateTime = true;
                }
            });
            <?php } ?>

            $methodFl = $(':radio[name="methodFl"]');
            $methodFl.filter(':checked').trigger('change');

            $designHeadFl.change(function () {
                designHeadFl = this.value;
                $('#designHead').toggleClass('display-none');
                init_editor();
            });

            $deviceFl = $(':radio[name="deviceFl"]');
            $deviceFl.change(function () {
                var value = $(this).val();
                if (value == 'pc') {
                    $methodFl.prop('disabled', false);
                } else {
                    $methodFl.filter(':last').prop('disabled', true);
                    if($methodFl.eq('2').prop('checked') == true){
                        $methodFl.eq('0').prop('checked', true).trigger('change');
                    }
                }
            });

            $groupFl = $(':radio[name="groupFl"]');
            $groupFl.change(function () {
                var $groupList = $('#member_groupLayer_list');
                if ($(this).val() == 'select') {
                    $groupList.addClass('active');
                } else {
                    $groupList.removeClass('active');
                }
            });

            $conditionFl = $(':radio[name="conditionFl"]');
            $conditionFl.change(function () {
                var value = $(this).val();
                var $conditionCountBySum = $('#conditionCountBySum');
                var $conditionCountByContinue = $('#conditionCountByContinue');
                if (value == 'sum') {
                    $conditionCountBySum.rules('add', {
                        required: true, messages: {
                            required: "누적 출석횟수를 입력해주세요."
                        }
                    });
                    $conditionCountByContinue.rules('remove');
                } else if (value == 'continue') {
                    $conditionCountByContinue.rules('add', {
                        required: true, messages: {
                            required: "연속 출석횟수를 입력해주세요."
                        }
                    });
                    $conditionCountBySum.rules('remove');
                } else {
                    $conditionCountBySum.rules('remove');
                    $conditionCountByContinue.rules('remove');
                    $benefitGiveFl.filter('[value=auto]').prop('checked', true).trigger('change');
                    $benefitFl.filter('[value=mileage]').prop('checked', true);
                }
            });

            $benefitFl = $(':radio[name="benefitFl"]');
            $benefitMileage = $('#benefitMileage');
            $benefitCouponSno = $('#benefitCouponSno');
            var benefitMileageRule = {required: true, messages: {required: "이벤트 조건달성 시 지급혜택의 마일리지를 설정해주세요."}};
            var benefitCouponRule = {required: true, messages: {required: "이벤트 조건달성 시 지급혜택의 쿠폰을 설정해주세요."}};
            $benefitFl.change(function () {
                var value = $(this).val();
                if (value == 'mileage') {
                    $benefitMileage.rules('add', benefitMileageRule);
                    $benefitCouponSno.rules('remove');
                } else {
                    if ($conditionFl.filter(':checked').val() == 'each') {
                        mileage_alert('출석할 때마다 혜택지급인 경우 마일리지 지급만 가능합니다.');
                        $benefitFl.filter(':eq(0)').prop('checked', true);
                    } else {
                        $benefitCouponSno.rules('add', benefitCouponRule);
                        $benefitMileage.rules('remove');
                    }
                }
            });

            $benefitCouponSno.change(function () {
                $("#benefitCouponLink").children().remove();
                $("#benefitCouponLink").append('<a href="coupon_regist.php?couponNo=' + $(this).val() + '" target="_blank">선택 쿠폰 상세보기 ></a>');
            });

            var $benefitFlTr;
            $benefitGiveFl = $(':radio[name="benefitGiveFl"]');
            var $benefitValue = $(':radio[name=benefitGiveFl]:checked').val();
            if($benefitValue == 'manual'){
                $("#trBenefitFlag").addClass('display-none');
            }
            $benefitGiveFl.change(function () {
                var value = $(this).val();
                $benefitFlTr = $benefitFl.closest('tr');
                if (value == 'manual') {
                    if ($conditionFl.filter(':checked').val() == 'each') {
                        mileage_alert('출석할 때마다 혜택지급인 경우 자동지급만 가능합니다.');
                        $benefitGiveFl.filter(':eq(0)').prop('checked', true);
                    } else {
                        $benefitFlTr.addClass('display-none');
                        try {
                            $benefitMileage.rules('remove');
                            $benefitCouponSno.rules('remove');
                        } catch (e) {
                            window.console.log('jquery validation error', e);
                        }
                    }
                } else {
                    $($benefitFl).each(function () {
                        change_trigger_by_radio($(this));
                    });
                    $benefitFlTr.removeClass('display-none');
                }
            });

            triggerTarget.radio.push($deviceFl);
            triggerTarget.radio.push($groupFl);
            triggerTarget.radio.push($benefitFl);
            triggerTarget.radio.push($benefitGiveFl);
            triggerTarget.radio.push($conditionFl);
            triggerTarget.radio.push($methodFl);

            $.each(triggerTarget.radio, function () {
                $(this).each(function () {
                    if (!this.disabled) {
                        change_trigger_by_radio($(this));
                    }
                });
            });

        };

        var mileage_alert = function (message) {
            if (!isAlertOpen) {
                dialog_alert(message, undefined, {
                    callback: function () {
                        isAlertOpen = false;
                    }
                });
                isAlertOpen = true;
            }
        };

        var change_trigger_by_radio = function ($radio) {
            if ($radio.prop('checked')) {
                $radio.trigger('change');
            }
        };

        var save = function () {
            if (changedEndDateTime) {
                dialog_confirm('이벤트 종료일자 수정 시 출석체크 이벤트 조건의 기준에 영향을 미쳐 회원의 컴플레인이 발생할 수 있습니다. 이대로 저장하시겠습니까? ', function (result) {
                    if (result) {
                        save_submit();
                    }
                });
            } else {
                save_submit();
            }
        };

        var save_submit = function () {
            if ($groupFl.eq(1).prop('checked') && $('input[name="groupSno[]"]').length < 1) {
                alert('선택된 회원 등급이 없습니다.');
                return;
            }
            if ($designHeadFl.eq(1).prop('checked')) {
                oEditors.getById["designHead"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
            }
            oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.

            // 수정일때는 수정폼 submit전에 input중 benefitGiveFl 항목을 disabled 제거
            <?php if($isModify){?>
            $(':radio[name="benefitGiveFl"]').prop("disabled", false);
            <?php } ?>

            $form.submit();
        };

        var init_editor = function () {
            $('#designHead').next('iframe').remove();
            if (designHeadFl == 'default') {
                return;
            }
            nhn.husky.EZCreator.createInIFrame({
                oAppRef: oEditors,
                elPlaceHolder: 'designHead',
                sSkinURI: "<?=PATH_ADMIN_GD_SHARE?>script/smart/SmartEditor2Skin.php",
                htParams: {
                    bUseToolbar: true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
                    bUseVerticalResizer: true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
                    bUseModeChanger: true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
                    //aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
                    fOnBeforeUnload: function () {
                        //alert("완료!");
                    }
                }, //boolean
                fOnAppLoad: function () {
                    //예제 코드
                    //oEditors.getById["editor"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
                },
                fCreator: "createSEditor2"
            });
        };

        var method_change = function() {
            $methodFl = $(':radio[name="methodFl"]');
            $methodFl.change(function () {
                var value = $(this).val();
                if (value == 'login') {
                    $loginHidden.removeClass('display-none');
                    $loginHidden.addClass('display-none');
                } else {
                    $loginHidden.removeClass('display-none');
                        if (value == 'reply') {
                            ($methodFlValue != value) ? $designBodyFl.eq(2).prop('checked', true) : $designBodyFl.eq(3).prop('checked', true);
                            $divBodyReply.removeClass('display-none');
                            $divBodyStamp.addClass('display-none');
                            $divStampImage.addClass('display-none');
                        } else {
                            ($methodFlValue != value) ? $designBodyFl.eq(0).prop('checked', true) : $designBodyFl.eq(1).prop('checked', true);
                            $divBodyReply.addClass('display-none');
                            $divBodyStamp.removeClass('display-none');
                            $divStampImage.removeClass('display-none');
                        }
                    }
            });
        }

        $(document).ready(function () {
            <?php
            echo 'groupSno="' . $data->get('groupSno', '') . '";';
            echo 'designHeadFl="' . $data->get('designHeadFl', 'default') . '";';
            ?>

            init();
            init_editor();
            method_change();
        });
    })($, _, window, document);
</script>
