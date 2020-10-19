<form id="frmMemberModifyEvent" name="frmMemberModifyEvent" action="../member/member_modify_event_ps.php" method="post">
    <input type="hidden" name="mode" value="<?=$mode;?>"/>
    <input type="hidden" name="mallSno" value="<?=$mall['mallSno'];?>"/>
    <input type="hidden" name="mallNm" value="<?=$mall['mallNm'];?>"/>
    <input type="hidden" name="sno" value="<?=$eventInfo['sno'];?>"/>
    <input type="hidden" name="managerNo" value="<?=$eventInfo['managerNo'];?>"/>
    <input type="hidden" name="eventStatusFl" value="<?=$eventInfo['eventStatusFl'];?>"/>
    <input type="hidden" name="expirationFl" value="<?=$expirationFl;?>"/>

    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>
    <ul class="nav nav-tabs mgb20" role="tablist">
        <?php if ($mode == 'register') {
            foreach ($gGlobal['useMallList'] as $val) { ?>
                <li role="presentation" class="<?= $mall['mallSno'] == $val['sno'] ? 'active' : ''; ?>">
                    <a href="#<?= $val['domainFl'] ?>" role="tab" data-toggle="tab" aria-controls="<?= $val['sno'] ?>">
                        <span class="flag flag-16 flag-<?= $val['domainFl'] ?>"></span> <?= $mall['mallSno'] == $val['sno'] ? $val['mallName'] : ''; ?>
                    </a>
                </li>
            <?php }
        } else { ?>
            <li class="active">
                <a><span class="flag flag-16 flag-<?=$eventInfo['domainFl'];?>"></span> <?=$eventInfo['mallName'];?></a>
            </li>
        <?php } ?>
    </ul>
    <div class="table-title gd-help-manual">기본정보</div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require">이벤트 유형</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="eventType" value="modify" <?=$checked['eventType']['modify'];?> />회원정보 수정 이벤트
                </label>
                <label class="radio-inline">
                    <input type="radio" name="eventType" value="life" <?=$checked['eventType']['life'];?>  />평생회원 이벤트
                </label>
            </td>
        </tr>
        <tr>
            <th class="require">이벤트명</th>
            <td><input type="text" name="eventNm" value="<?=$eventInfo['eventNm'];?>" class="form-control js-maxlength width-2xl" maxlength="30"/></td>
        </tr>
        <tr>
            <th>설명</th>
            <td><input type="text" name="eventDescription" value="<?=$eventInfo['eventDescription'];?>" class="form-control" maxlength="250"/></td>
        </tr>
        <tr>
            <th class="require">진행기간</th>
            <td>
                <div class="form-inline">
                    <div class="input-group js-datepicker">
                        <input type="text" name="eventStartDt[date]" class="form-control" placeholder="수기입력 가능" value="<?=$eventInfo['display']['eventStartDate'];?>">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar"></span>
                        </span>
                    </div>
                    <input type="text" name="eventStartDt[time]" class="form-control js-timepicker" placeholder="수기입력 가능" value="<?=$eventInfo['display']['eventStartTime'];?>">
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" name="eventEndDt[date]" class="form-control" placeholder="수기입력 가능" value="<?=$eventInfo['display']['eventEndDate'];?>">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar"></span>
                        </span>
                    </div>
                    <input type="text" name="eventEndDt[time]" class="form-control js-timepicker" placeholder="수기입력 가능" value="<?=$eventInfo['display']['eventEndTime'];?>">
                </div>
            </td>
        </tr>
        <tr class="member_modify_event_view <?= $checked['eventType']['modify'] ? '' : 'display-none'; ?>">
            <th class="require">이벤트 항목</th>
            <td>
                <div>
                    <div class="width300 flo-left">
                        <div class="table-action select-field-header">
                            <div class="pull-left">전체 항목</div>
                        </div>
                        <div class="js-field-select-wrapper">
                            <table class="js-field-default table table-rows">
                                <tbody>
                                <?php
                                foreach($search['fieldList'] as $key => $value) { ?>
                                    <tr class="default_field" data-field-key="<?=$key;?>" data-field-name="<?=$value;?>">
                                        <td><?=$value;?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="width-2xs flo-left ta-c pdt100">
                        <p><button type="button" class="btn btn-sm btn-white btn-icon-left js-move-left">추가</button></p>
                        <p><button type="button" class="btn btn-sm btn-white btn-icon-right js-move-right">삭제</button></p>
                        <p><button type="button" class="btn btn-sm btn-white btn-icon-left-all js-move-left-all">전체<br/>추가</button></p>
                        <p><button type="button" class="btn btn-sm btn-white btn-icon-right-all js-move-right-all">전체<br/>삭제</button></p>
                    </div>
                    <div class="width300 flo-left">
                        <div class="table-action select-field-header">
                            <div class="pull-left">선택한 항목</div>
                        </div>
                        <div class="js-field-select-wrapper">
                            <table class="js-field-select table table-rows" data-toggle="" data-use-row-attr-func="false" data-reorderable-rows="true">
                                <tbody>
                                <?php
                                if ($eventInfo['display']['eventApplyField']) {
                                    foreach ($eventInfo['display']['eventApplyField'] as $key => $value) {
                                        if ($value == 'addressSub' || $value == 'cellPhoneCountryCode') {
                                            continue;
                                        }?>
                                    <tr class="select_field_<?=$value;?>" data-field-key="<?=$value;?>" data-field-name="<?=$fieldList[$value];?>" >
                                        <td>
                                            <input type="hidden" name="eventApplyField[]" value="<?=$value;?>">
                                            <?php if ($eventInfo['mallSno'] == DEFAULT_MALL_NUMBER && $value == 'address') { ?>
                                            <input type="hidden" name="eventApplyField[]" value="addressSub">
                                            <?php } else if ($eventInfo['mallSno'] > DEFAULT_MALL_NUMBER && $value == 'cellPhone') { ?>
                                            <input type="hidden" name="eventApplyField[]" value="cellPhoneCountryCode">
                                            <?php } ?>
                                            <?=$search['fieldList'][$value];?>
                                        </td>
                                    </tr>
                                <?php
                                    }
                                } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="notice-info clear-both">Shift 버튼을 누른 상태에서 선택하면 여러 항목을 동시에 선택할 수 있습니다.</div>
            </td>
        </tr>
        <tr class="member_modify_event_view <?= $checked['eventType']['modify'] ? '' : 'display-none'; ?>">
            <th>이벤트 대상 제외<br/>가입기간 설정</th>
            <td>
                <div class="form-inline mgb5">
                    <label class="radio-inline">
                        <input type="radio" name="exceptJoinType" value="unlimit" <?= $checked['exceptJoinType']['unlimit'] ?> />제한 없음
                    </label>
                </div>
                <div class="form-inline mgb5">
                    <input type="radio" name="exceptJoinType" value="date" <?= $checked['exceptJoinType']['date']; ?> />
                    <div class="input-group js-fulltimepicker js-label">
                        <input type="text" name="exceptJoinStartDt" class="form-control" value="<?=$eventInfo['exceptJoinStartDt'];?>" maxlength="20" placeholder="수기입력 가능">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar"></span>
                        </span>
                    </div> ~
                    <div class="input-group js-fulltimepicker js-label">
                        <input type="text" name="exceptJoinEndDt" class="form-control" value="<?=$eventInfo['exceptJoinEndDt'];?>" maxlength="20" placeholder="수기입력 가능">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar"></span>
                        </span>
                    </div>
                </div>
                <div class="form-inline mgb5">
                    <label class="radio-inline">
                        <input type="radio" name="exceptJoinType" value="day" <?= $checked['exceptJoinType']['day'] ?> />가입 후
                        <?= gd_select_box('exceptJoinDay', 'exceptJoinDay', $search['exceptJoinDayList'], ' 일', $eventInfo['exceptJoinDay'], '선택', null, 'js-label'); ?>
                        일 동안 이벤트 대상 제외
                    </label>
                </div>
            </td>
        </tr>
        <tr class="member_modify_event_view <?= $checked['eventType']['modify'] ? '' : 'display-none'; ?>">
            <th>혜택지급 조건</th>
            <td>
                <div class="mgb5">
                    <label class="radio-inline">
                        <input type="radio" name="benefitCondition" value="some" <?= $checked['benefitCondition']['some']; ?> />선택한 항목 중 1개 이상 수정 시 혜택 지급
                    </label>
                </div>
                <div class="mgb5">
                    <label class="radio-inline">
                        <input type="radio" name="benefitCondition" value="all" <?= $checked['benefitCondition']['all']; ?> />선택한 항목을 모두 수정 시 혜택 지급
                    </label>
                </div>
                <div class="mgb5">
                    <span class="notice-info">모두 수정 시 혜택 지급의 경우, 회원정보 수정 시 선택한 항목을 모두 한번에 수정해야만 지급 됩니다.</span>
                </div>
            </td>
        </tr>
        <tr class="member_modify_event_view <?= $checked['eventType']['modify'] ? '' : 'display-none'; ?>">
            <th>관리자 수정 회원</th>
            <td>
                <label class="checkbox-inline"><input type="checkbox" name="adminModifyFl" value="y" <?= $checked['adminModifyFl']['y']; ?> />관리자가 어드민에서 회원정보 수정한 회원도 적용</label>
            </td>
        </tr>
        <tr>
            <th>혜택지급 방법</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="benefitProvideType" value="auto" <?=$checked['benefitProvideType']['auto'];?> <?=$disabled['benefitProvideType']['auto'];?> />자동지급
                </label>
                <label class="radio-inline">
                    <input type="radio" name="benefitProvideType" value="manual" <?=$checked['benefitProvideType']['manual'];?> <?=$disabled['benefitProvideType']['manual'];?> />수동지급
                </label>
                <div class="mgt5 mgb5">
                    <span class="notice-info" id="benefitProvideTypeInfo">이벤트 기간 내 혜택 제공은 1회로 제한됩니다.</span>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">지급혜택</th>
            <td id="autoBenefit" class="<?=$disabled['benefit']['auto'];?>">
                <div class="form-inline mgb5">
                    <label class="radio-inline">
                        <input type="radio" name="benefitType" value="mileage" <?=$checked['benefitType']['mileage'];?> <?=$disabled['benefitType']['mileage'];?> />
                        <?= gd_display_mileage_name(); ?>
                        <input type="text" name="benefitMileage" class="form-control js-number mgl10 js-label" value="<?=$eventInfo['display']['benefitMileage'];?>" maxlength="12" <?=$disabled['benefitMileage'];?> />
                        <?= gd_display_mileage_unit(); ?>
                    </label>
                </div>
                <div class="form-inline mgb5">
                    <label class="radio-inline">
                        <input type="radio" name="benefitType" value="coupon" <?=$checked['benefitType']['coupon'];?> <?=$disabled['benefitType']['coupon'];?>/>
                        쿠폰
                        <select class="form-control " id="benefitCouponSno" name="benefitCouponSno" class="mgl34 js-label" <?=$disabled['benefitCouponSno']?>>
                            <option value="">쿠폰 선택</option>
                            <?php
                            foreach($search['couponData'] as $val) {
                                $couponSelected = ($eventInfo['benefitCouponSno'] == $val['couponNo']) ? 'selected="selected"' : '';
                                $couponDisabled = ($val['couponType'] == 'f') ? 'disabled' : '';
                                if ($val['couponType'] != 'f' || $eventInfo['benefitCouponSno'] == $val['couponNo']) {
                                    ?>
                                    <option value="<?= $val['couponNo'] ?>" <?=$couponSelected?> <?=$couponDisabled?>><?= $couponDisabled ? '(발급종료)':''; ?><?= $val['couponNm'] ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                        <button type="button" class="btn btn-sm btn-gray mgl8 js-coupon-register" data-type="memberModifyEvent" <?=$disabled['benefitCouponRegister'];?> >신규쿠폰 등록</button>
                        <div id="benefitCouponLink" class="pdl30">
                            <?php if ($eventInfo['benefitCouponSno'] > 0) { ?>
                            <a href="../promotion/coupon_regist.php?couponNo=<?=$eventInfo['benefitCouponSno'];?>" target="_blank">선택 쿠폰 상세보기 ></a>
                            <?php } ?>
                        </div>
                    </label>
                </div>
            </td>
            <td id="manualBenefit" class="c-gdred bold <?=$disabled['benefit']['manual'];?>">
                ※ "수동지급"의 경우, 참여 내역만 별도로 제공(엑셀)하며, 직접 고객에게 혜택을 별도로 지급하셔야 합니다.
            </td>
        </tr>
    </table>
    <div class="notice-info display-none" id="memberLifeEventInfo">회원 가입 항목에서 ‘개인정보유효기간’이 미사용중인 경우 회원 가입 또는 회원정보 수정 시 평생회원 이벤트에 정상적으로 참여할 수 없습니다. <br> 회원 가입 항목은 <a href="../member/member_joinitem.php" target="_blank" class="notice-ref notice-sm btn-link">“회원 > 회원 관리 > 회원 가입 항목 관리”</a>에서 설정할 수 있습니다.</div>

    <div class="table-title">이벤트 팝업 설정</div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tr>
            <th>노출 페이지</th>
            <td>
                <label class="checkbox-inline"><input type="checkbox" name="loginDisplayFl" data-type="login" value="y" <?php echo gd_isset($checked['loginDisplayFl']['y']); ?> />로그인 완료 즉시</label>
                <label class="checkbox-inline"><input type="checkbox" name="mainDisplayFl" data-type="main" value="y" <?php echo gd_isset($checked['mainDisplayFl']['y']); ?> />메인</label>
                <label class="checkbox-inline"><input type="checkbox" name="mypageDisplayFl" data-type="mypage" value="y" <?php echo gd_isset($checked['mypageDisplayFl']['y']); ?> />마이페이지</label>
            </td>
        </tr>
        <tr>
            <th class="form-inline">창위치</th>
            <td class="form-inline">
                상단에서 : <input type="text" name="popupPositionT" required value="<?= $eventInfo['popupPositionT'] ?>" class="js-number form-control"> px &nbsp;&nbsp;
                좌측에서 : <input type="text" name="popupPositionL" required value="<?= $eventInfo['popupPositionL'] ?>" class="js-number form-control"> px
            </td>
        </tr>
        <tr>
            <th class="form-inline">오늘하루 보이지 않음</th>
            <td class="form-inline">
                <label><input type="checkbox" name="todayUnSeeFl" value="y" <?= $checked['todayUnSeeFl']['y'] ?>> `오늘 하루 보이지 않음`기능을 사용합니다.</label>
            </td>
        </tr>
        <tr>
            <th class="form-inline">팝업 내용</th>
            <td class="form-inline">
                <div class="mgb5">
                    <label class="radio-inline">
                        <input type="radio" name="popupContentType" value="default" <?= $checked['popupContentType']['default']; ?> />기본팝업
                        <u class="js-btn-preview-template" data-target="popupTemplate">(미리보기)</u>
                        <div class="popupTemplate" style="top:-250px;left:200px;position:absolute;display:none"><img
                                    src="<?= PATH_ADMIN_GD_SHARE ?>img/member/<?=$eventInfo['memberEventPopupView']?>"></div>
                    </label>
                </div>
                <div class="mgb5">
                    <label class="radio-inline">
                        <input type="radio" name="popupContentType" value="direct" <?= $checked['popupContentType']['direct']; ?> />html직접 편집
                    </label>
                </div>
                <div id="directHtmlEditor" class="<?= $checked['popupContentType']['direct'] ? '' : 'display-none'; ?>">
                    <!-- editor tool : start -->
                    <textarea name="popupContent" style="width:100%; height:400px;" id="editor" class="form-control"><?=$eventInfo['popupContent']; ?></textarea>

                    <!-- editor tool : end -->
                </div>
            </td>
        </tr>
    </table>
</form>

<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js?ss=<?= date('YmdHis') ?>" charset="utf-8"></script>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 해외 상점 탭 설정
        $('li[role="presentation"]').on('click', function (e) {
            e.preventDefault();
            var controls = $(e.target).attr('aria-controls');
            if (typeof controls === 'undefined') {
                controls = $(e.target).closest('a').attr('aria-controls');
            }
            var url = '../member/member_modify_event_register.php?mallSno=' + controls;
            logger.debug('tab click location: ' + url);
            window.location.href = url;
        });

        // 쿠폰 상세보기
        $('#benefitCouponSno').on('change', function () {
            $("#benefitCouponLink").children().remove();
            $("#benefitCouponLink").append('<a href="../promotion/coupon_regist.php?couponNo=' + $(this).val() + '" target="_blank">선택 쿠폰 상세보기 ></a>');
        });

        // 제외 가입기간 처리
        $('.js-label').on('click', function () {
            if ($(this).siblings('input:radio').length == 1) {
                $(this).siblings('input:radio').trigger('click');
            }
        });

        // 수동지급 처리
        $('input[name="benefitProvideType"]').on('click', function () {
            if ($(this).val() === 'auto') {
                $('#autoBenefit').removeClass('display-none');
                $('#manualBenefit').addClass('display-none');
                $('input[name="benefitType"]').removeAttr('disabled');
                $('input[name="benefitMileage"]').removeAttr('disabled');
                $('select[name="benefitCouponSno"]').removeAttr('disabled');
            } else {
                $('#autoBenefit').addClass('display-none');
                $('#manualBenefit').removeClass('display-none');
                $('input[name="benefitType"]').attr('disabled', 'disabled');
                $('input[name="benefitMileage"]').attr('disabled', 'disabled');
                $('select[name="benefitCouponSno"]').attr('disabled', 'disabled');
            }
        });

        // 날짜 선택시 시간 디폴트 처리
        $('.js-datepicker').datetimepicker().on('dp.change', function(){
            if ($(this).find('input').attr('name') === 'eventStartDt[date]' && $('input[name="eventStartDt[time]"]').val() === '') {
                $('input[name="eventStartDt[time]"]').val('00:00');
            } else if ($(this).find('input').attr('name') === 'eventEndDt[date]' && $('input[name="eventEndDt[time]"]').val() === '') {
                $('input[name="eventEndDt[time]"]').val('23:59');
            }
        });

        // 이벤트 유형
        $('input[name="eventType"]').on('click', function () {
            var countryCode = $('input[name="mallNm"]').val();
            var popupContentView = ($(this).val() === 'modify') ? "/admin/gd_share/img/member/member_modify_event_" + countryCode + "_01.png" : "/admin/gd_share/img/member/member_life_event_" + countryCode + "_01.png";
            var popupContent = ($(this).val() === 'modify') ? "/admin/gd_share/img/member/member_modify_event_" + countryCode + "_02.png" : "/admin/gd_share/img/member/member_life_event_" + countryCode + "_02.png";
            var popupContentBtn = ($(this).val() === 'modify') ? "/admin/gd_share/img/member/member_modify_event_" + countryCode + "_03.png" : "/admin/gd_share/img/member/member_life_event_" + countryCode + "_03.png";
            if ($(this).val() === 'modify') {
                $('#benefitProvideTypeInfo').text('이벤트 기간 내 혜택 제공은 1회로 제한됩니다.');
                $('.member_modify_event_view').show();
                $('#memberLifeEventInfo').hide();
            } else {
                $('#benefitProvideTypeInfo').text('혜택은 평생회원으로 최초 가입/전환 시 1회만 지급됩니다.');
                $('.member_modify_event_view').hide();
                $('#memberLifeEventInfo').show();
            }
            $('.popupTemplate img').attr('src', popupContentView);
            $("textarea#editor").text("<div style='text-align: center;'><img src='" + popupContent +"' /><br><br><p><a href='../mypage/my_page_password.php' class='btn_event'><img src='" + popupContentBtn +"' /></a></p></div>");
            oEditors.getById["editor"].setIR("<div style='text-align: center;'><img src='" + popupContent +"' /><br><br><p><a href='../mypage/my_page_password.php' class='btn_event'><img src='" + popupContentBtn +"' /></a></p></div>");
        });

        // 팝업 내용 방식
        $('input[name="popupContentType"]').on('click', function () {
            if ($(this).val() === 'direct') {
                $('#directHtmlEditor iframe').css('height', '449px');
                $('#directHtmlEditor').show();
                oEditors.getById["editor"].exec("CHANGE_EDITING_MODE", ["WYSIWYG"]);
            } else {
                $('#directHtmlEditor').hide();
            }
        });

        // 폼 체크
        $("#frmMemberModifyEvent").validate({
            submitHandler: function (form) {
                if ($('input[name="eventType"]:checked').val() === 'modify' && $('.js-field-select tr').length === 0) {
                    alert('이벤트 항목을 지정해주세요.');
                    return false;
                }

                if ($('input[name="eventType"]:checked').val() === 'life' && $('input[name="expirationFl"]').val() === 'n') {
                    alert('회원 가입항목 관리 > 개인정보유효기간 설정시에만 등록이 가능합니다.');
                    return false;
                }

                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);    // 에디터의 내용이 textarea에 적용됩니다.
                form.target = 'ifrmProcess';
                form.submit();
            },
            invalidHandler: function (form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    alert(validator.errorList[0].message);
                    validator.errorList[0].element.focus();
                }
            },
            rules: {
                eventNm: {
                    required: true
                },
                'eventStartDt[date]': {
                    required: true
                },
                'eventStartDt[time]': {
                    required: true
                },
                'eventEndDt[date]': {
                    required: true
                },
                'eventEndDt[time]': {
                    required: true
                },
                exceptJoinStartDt: {
                    required: function() {
                        return $('input[name="exceptJoinType"]:checked').val() === 'date';
                    }
                },
                exceptJoinEndDt: {
                    required: function() {
                        return $('input[name="exceptJoinType"]:checked').val() === 'date';
                    }
                },
                exceptJoinDay: {
                    required: function() {
                        return $('input[name="exceptJoinType"]:checked').val() === 'day';
                    }
                },
                benefitMileage: {
                    required: function() {
                        return $('input[name="benefitProvideType"]:checked').val() === 'auto' && $('input[name="benefitType"]:checked').val() === 'mileage';
                    },
                    min: function() {
                        if ($('input[name="benefitProvideType"]:checked').val() === 'auto' && $('input[name="benefitType"]:checked').val() === 'mileage')
                            return 1;
                        else
                            return false;
                    }
                },
                benefitCouponSno: {
                    required: function() {
                        return $('input[name="benefitProvideType"]:checked').val() === 'auto' && $('input[name="benefitType"]:checked').val() === 'coupon';
                    }
                }
            },
            messages: {
                eventNm: '이벤트명을 입력해주세요.',
                'eventStartDt[date]': '이벤트 시작일을 지정해주세요.',
                'eventStartDt[time]': '이벤트 시작시간을 지정해주세요.',
                'eventEndDt[date]': '이벤트 종료일을 지정해주세요.',
                'eventEndDt[time]': '이벤트 종료시간을 지정해주세요.',
                exceptJoinStartDt: '이벤트 대상 제외 가입기간 시작일을 지정해주세요.',
                exceptJoinEndDt: '이벤트 대상 제외 가입기간 종료일을 지정해주세요.',
                exceptJoinDay: '이벤트 대상 제외 가입기간을 지정해주세요.',
                benefitMileage: {
                    required: '마일리지 지급혜택 금액을 입력하세요.',
                    min: '마일리지 지급혜택 금액을 입력하세요.'
                },
                benefitCouponSno: '쿠폰을 선택해주세요.'
            }
        });

        // 이벤트 항목 선택
        var lastSelectedRowIndex = 0;
        $('.js-field-select-wrapper').on('click', 'tr', function (e) {
            var selectedRowIndex = parseInt($(this).index());
            var selectedRow = $(this);

            if (e.shiftKey) {
                var top = Math.max(lastSelectedRowIndex, selectedRowIndex);
                var bottom = Math.min(lastSelectedRowIndex, selectedRowIndex);

                for (var i = bottom; i <= top; i++) {
                    selectedRow.closest('table').find('tbody tr').eq(i).addClass('selected').css('background','#fcf8e3');
                }
            } else {
                if (selectedRow.hasClass('selected')) {
                    selectedRow.removeClass('selected').css('background','#ffffff');
                } else {
                    selectedRow.addClass('selected').css('background','#fcf8e3');
                }
            }

            lastSelectedRowIndex = selectedRowIndex;
        });

        // 이벤트 항목 추가
        $('button[class*="js-move-left"]').on('click', function() {
            var selectedField = null;

            if ($(this).hasClass('js-move-left')) {
                selectedField = $('.js-field-default tr.selected');
                if (selectedField.length === 0 ) {
                    alert("이동할 항목을 선택해주세요.");
                    return false;
                }
            } else {
                selectedField = $('.js-field-default tr');
            }

            // 항목 추가
            addField(selectedField);
        });

        // 선택한 항목 삭제
        $('button[class*="js-move-right"]').on('click', function() {
            var removeField = null;

            if ($(this).hasClass('js-move-right')) {
                removeField = $(".js-field-select tr.selected");
                if (removeField.length === 0 ) {
                    alert("삭제할 항목을 선택해주세요.");
                    return false;
                }
            } else {
                removeField = $(".js-field-select tr");
            }
            removeField.each(function () {
                $(this).remove();
            });
        });

        // 이벤트 팝업 설정 > 팝업내용> 기본팝업 > 미리보기
        $('.js-btn-preview-template').mouseover(function () {
            var targetClass = $(this).data('target');
            $('.' + targetClass).show();
        })

        $('.js-btn-preview-template').mouseout(function () {
            var targetClass = $(this).data('target');
            $('.' + targetClass).hide();
        })
    });

    /**
     * 이벤트 항목 추가
     *
     * @param fieldsList 선택한 이벤트 항목 (object)
     */
    function addField(fieldsList) {
        var checkCnt = 0;

        fieldsList.each(function () {
            var key = $(this).data('field-key');
            var name = $(this).data('field-name');
            var addHtml = '<input type="hidden" name="eventApplyField[]" value="' + key + '" />';

            if (key === 'address') {
                addHtml += '<input type="hidden" name="eventApplyField[]" value="addressSub" />';
            }

            if ($('input[name="mallSno"]').val() > 1 && key === 'cellPhone') {
                addHtml += '<input type="hidden" name="eventApplyField[]" value="cellPhoneCountryCode" />';
            }

            if ($('.js-field-select .select_field_' + key).length === 0) {
                $('.js-field-select tbody').append('<tr class="select_field_' + key + '" data-field-key="' + key + '" data-field-name="' + name + '"><td>' + name + addHtml + '</td></tr>');
            } else {
                checkCnt++;
            }
            $(this).removeClass('selected').css('background','#ffffff');
        });

        if (checkCnt > 0 ) {
            alert("중복된 항목은 추가 되지 않습니다.");
        }
    }
    //-->
</script>
