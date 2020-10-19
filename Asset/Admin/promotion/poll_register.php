<form id="frmPoll" name="frmPoll" action="../promotion/poll_ps.php" method="post" target="ifrmProcess" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?= $mode; ?>"/>
    <input type="hidden" name="sno" value="<?= $data['sno']; ?>"/>
    <input type="hidden" name="pollCode" value="<?= $data['pollCode']; ?>"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./poll_list.php');" />
            <input type="submit" value="저장" class="btn btn-red btn-save"/>
        </div>
    </div>

    <h5 class="table-title">기본설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th class="require">
                설문 제목
            </th>
            <td colspan="3">
                <div class="form-inline">
                    <input type="text" name="pollTitle" value="<?= $data['pollTitle'] ?>" class="form-control width-3xl" maxlength="30"/>
                </div>
            </td>
        </tr>
        <?php if ($mode == 'modify') { ?>
            <tr>
                <th>
                    설문 페이지 주소
                </th>
                <td colspan="3">
                    <?php
                    if (in_array($data['pollDeviceFl'], ['all', 'pc'])) {
                        $clipboard = URI_HOME . 'service' . DS . 'poll_register.php?code=' . $data['pollCode'];
                        ?>
                        <div class="form-inline mgb5">
                            <button type="button" title="<?php echo $data['pollTitle']?>" class="btn btn-white btn-sm js-clipboard" data-clipboard-text="<?php echo $clipboard; ?>">PC</button> <?php echo $clipboard; ?>
                        </div>
                    <?php } ?>
                    <?php
                    if (in_array($data['pollDeviceFl'], ['all', 'mobile'])) {
                        $clipboardMobile = URI_MOBILE . 'service' . DS . 'poll_register.php?code=' . $data['pollCode'];
                        ?>
                        <div class="form-inline">
                            <button type="button" title="<?php echo $data['pollTitle']?>" class="btn btn-white btn-sm js-clipboard" data-clipboard-text="<?php echo $clipboardMobile; ?>">모바일</button> <?php echo $clipboardMobile; ?>
                        </div>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <th class="require">
                설문 기간
            </th>
            <td <?= $mode == 'regist' ? 'colspan="3"' : ''; ?>>
                <div class="form-inline">
                    <div class="input-group js-datetimepicker">
                        <input type="text" name="pollStartDt" class="form-control" placeholder="수기입력 가능" value="<?= gd_isset($data['pollStartDt']) ?>">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    ~
                    <div class="input-group js-datetimepicker">
                        <input type="text" name="pollEndDt" class="form-control" placeholder="수기입력 가능" value="<?= gd_isset($data['pollEndDt']) ?>" <?= $disabled['pollEndDt']; ?>>
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    <div class="input-group">
                        <label><input type="checkbox" name="pollEndDtFl" value="Y" <?= gd_isset($checked['pollEndDtFl']['Y']); ?>>종료기간 제한없음</label>
                    </div>
                </div>
            </td>
            <?php if ($mode == 'modify') { ?>
                <th>
                    진행상태
                </th>
                <td>
                    <?php echo $statusFl[$data['pollStatusFl']]; ?>
                    <label class="checkbox-inline"><input type="checkbox" name="pollStatusFl" value="<?php echo $reverseStatusKey[$data['pollStatusFl']]; ?>"> <?php echo $reverseStatusFl[$data['pollStatusFl']]; ?></label>
                </td>
            <?php } ?>
        </tr>
        <tr>
            <th class="require">
                진행범위
            </th>
            <td colspan="3">
                <label class="radio-inline"><input type="radio" name="pollDeviceFl" value="pc" <?= gd_isset($checked['pollDeviceFl']['pc']); ?>/>PC쇼핑몰</label>
                <label class="radio-inline"><input type="radio" name="pollDeviceFl" value="mobile" <?= gd_isset($checked['pollDeviceFl']['mobile']); ?>/>모바일쇼핑몰</label>
                <label class="radio-inline"><input type="radio" name="pollDeviceFl" value="all" <?= gd_isset($checked['pollDeviceFl']['all']); ?>/>PC+모바일</label>
            </td>
        </tr>
        <tr>
            <th class="require">
                참여대상 선택
            </th>
            <td colspan="3">
                <label class="radio-inline mgb5"><input type="radio" name="pollGroupFl" value="all" <?= $checked['pollGroupFl']['all'] ?>>전체(회원+비회원)</label><br />
                <label class="radio-inline mgb5"><input type="radio" name="pollGroupFl" value="member" <?= $checked['pollGroupFl']['member'] ?>>회원전용(비회원제외)</label><br />
                <label class="radio-inline"><input type="radio" name="pollGroupFl" value="select" <?= $checked['pollGroupFl']['select'] ?> onclick="layer_register('member_selectLayer_list','groupSno','info_member_list_group')">특정회원등급</label>
                <label>
                    <button type="button" class="btn btn-sm btn-gray js-group-select">회원등급 선택</button>
                </label>
                <div id="member_selectLayer_list" class="selected-btn-group <?= is_array($data['pollGroupNm']) === true ? 'active' : ''; ?>">
                    <?php if (is_array($data['pollGroupNm']) === true ) { ?>
                        <h5>선택된 회원등급</h5>
                        <?php foreach ($data['pollGroupNm'] as $k => $v) { ?>
                            <span id="info_member_list_group_<?= $k ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="groupSno[]" value="<?= $k ?>"/>
                                <span class="btn"><?= $v ?></span>
                                <button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#info_member_list_group_<?= $k ?>">삭제</button>
                            </span>
                        <?php }
                    } ?>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">
                참여 인원 제한
            </th>
            <td colspan="3">
                <div class="form-inline mgb5">
                    <label class="radio-inline">
                        <input type="radio" name="pollMemberLimitFl" value="unlimited" <?=$checked['pollMemberLimitFl']['unlimited'];?>/>무제한
                    </label>
                </div>
                <div class="form-inline mgb5">
                    <label class="radio-inline">
                        <input type="radio" name="pollMemberLimitFl" value="max" <?=$checked['pollMemberLimitFl']['max'];?>/>최대
                        <input type="text" name="pollMemberLimitCnt" class="form-control js-number mgl10 mgr5 js-label" value="<?=$data['pollMemberLimitCnt'];?>"/>명
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="pollBannerLimitFl" class="mgl30" value="y" <?= $checked['pollBannerLimitFl']['Y']; ?><?= $disabled['pollBannerLimitFl']; ?> />최대 참여 인원 도달 시, 설문배너 미노출
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">
                설문배너 이미지
            </th>
            <td valign="top" colspan="3">
                <label class="radio-inline mgb5">
                    <input type="radio" name="pollBannerFl" value="def" <?= $checked['pollBannerFl']['def'] ?>>기본 배너이미지 <img src="<?php echo $defaultBannerImg; ?>" width="400" alt="기본 배너이미지" />
                </label><br />
                <input type="radio" name="pollBannerFl" value="upl" <?= $checked['pollBannerFl']['upl'] ?>>이미지 직접 등록 <span class="notice-info">jpg, jpeg, png, gif만 등록 가능하며, 기본 배너 이미지는 PC 790x100 / 모바일 750x140 pixel 입니다.</span><br />
                <table class="table table-cols" style="margin-top:5px;">
                    <colgroup>
                        <col class="width-md"/>
                        <col/>
                    </colgroup>
                    <tbody>
                    <tr class="poll-banner-img <?php echo $display['pc']; ?>">
                        <th>PC용</th>
                        <td class="form-inline">
                            <input type="file" id="pollBannerImg" class="form-control" name="pollBannerImg" <?php echo $disabled['pollBannerImg']?>/>
                            <?php if ($data['pollBannerImg']) {?>
                                <img src="<?php echo $bannerImagePath . '/' . $data['pollBannerImg']; ?>" width="<?php echo $imgSize['front'][0]; ?>" alt="<?php echo $data['pollTitle']?>" />
                                <label><input type="checkbox" name="pollBannerImgDel" value="Y">삭제</label>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr class="poll-banner-img-mobile <?php echo $display['mobile']; ?>">
                        <th>모바일용</th>
                        <td class="form-inline">
                            <input type="file" id="pollBannerImgMobile" class="form-control" name="pollBannerImgMobile" <?php echo $disabled['pollBannerImgMobile']?>/>
                            <?php if ($data['pollBannerImgMobile']) {?>
                                <img src="<?php echo $bannerImagePath . '/' . $data['pollBannerImgMobile']; ?>" width="<?php echo $imgSize['mobile'][0]; ?>" alt="<?php echo $data['pollTitle']?>" />
                                <label><input type="checkbox" name="pollBannerImgMobileDel" value="Y">삭제</label>
                            <?php } ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <label class="radio-inline">
                    <input type="radio" name="pollBannerFl" value="none" <?= $checked['pollBannerFl']['none'] ?>>배너이미지 노출안함
                </label>
            </td>
        </tr>
        <tr class="category <?php echo $display['category']; ?>" data-handler="category">
            <th class="require">
                설문배너 위치
            </th>
            <td colspan="3">
                <label class="checkbox-inline mgb5"><input type="checkbox" name="pollViewPosition[]" value="main" <?= $checked['pollViewPosition']['main'] ?>> 쇼핑몰 메인</label><br />
                <label class="checkbox-inline mgb5"><input type="checkbox" name="pollViewPosition[]" value="category" <?= $checked['pollViewPosition']['category'] ?>> 카테고리 페이지</label><br />
                <div class="pdlr30">
                    <label class="radio-inline mgb5"><input type="radio" name="pollViewCategory" value="all" <?= $checked['pollViewCategory']['all'] . ' ' . $disabled['pollViewCategory']['all']; ?>> 전체 카테고리</label>
                    <label class="radio-inline mgb5"><input type="radio" name="pollViewCategory" value="select" onclick="layer_register_category()" <?= $checked['pollViewCategory']['select'] . ' ' . $disabled['pollViewCategory']['select']; ?>> 특정 카테고리 <button type="button" class="btn btn-sm btn-gray js-view-position-select" <?= $disabled['pollViewCategory']['button']; ?>>설문배너 위치 선택</button></label>
                </div>
                <div id="categoryRowArea" class="<?= is_array($data['viewCategory']) === true ? '' : 'display-none'; ?>">
                    <table class="table table-cols active mgt10 mgb0">
                        <thead>
                        <tr>
                            <th class="width10p no-border">번호</th>
                            <th class="width80p no-border">카테고리</th>
                            <th class="width10p no-border">삭제</th>
                        </tr>
                        </thead>
                    </table>
                    <table class="table table-cols" id="categoryRow">
                        <?php
                        if (is_array($data['viewCategory']) === true) {
                            foreach ($data['viewCategory'] as $k => $v) {
                                ?>
                                <tr id="categoryId_<?php echo $v['cateCd']; ?>">
                                    <td class="center"><?php echo $k + 1; ?>
                                        <input type="hidden" name="category[]" value="<?php echo $v['cateCd']; ?>">
                                        <input type="hidden" name="categoryNm[]" value="<?php echo $v['cateNm']; ?>">
                                    </td>
                                    <td><?php echo $v['cateNm']; ?></td>
                                    <td class="center">
                                        <button type="button" class="btn btn-gray btn-sm" data-toggle="delete"
                                                data-target="#categoryId_<?php echo $v['cateCd']; ?>">삭제
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </table>
                    <button type="button" class="btn btn-gray btn-sm" onclick="allDel('categoryRow');">전체삭제</button>
                </div>
            </td>
        </tr>
        <tr>
            <th>
                참여완료 시<br />결과보기
            </th>
            <td colspan="3">
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="pollResultViewFl" value="Y" <?= gd_isset($checked['pollResultViewFl']['Y']) . ' ' . $disabled['pollResultViewFl']['Y']; ?>/>제공함</label>
                    <label class="radio-inline"><input type="radio" name="pollResultViewFl" value="N" <?= gd_isset($checked['pollResultViewFl']['N']); ?>/>제공안함</label>
                </div>
                <div class="pull-left notice-info">
                    결과보기 화면은 PC쇼핑몰에서만 지원됩니다.
                </div>
            </td>
        </tr>
        <tr>
            <th>
                설문조사 참여 시<br />마일리지 지급설정
            </th>
            <td colspan="3">
                <div class="form-inline">
                    마일리지 <input type="text" name="pollMileage" value="<?= $data['pollMileage'] ?>" class="form-control width-xs" maxlength="8"/>원 지급
                </div>
            </td>
        </tr>
        </tbody>
    </table>

    <h5 class="table-title">내용 설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>
                상단 안내 영역
            </th>
            <td colspan="3">
                <label class="radio-inline mgb5"><input type="radio" name="pollHtmlContentFl" value="N" <?= gd_isset($checked['pollHtmlContentFl']['N']); ?>/>없음</label>
                <label class="radio-inline mgb5"><input type="radio" name="pollHtmlContentFl" value="Y" <?= gd_isset($checked['pollHtmlContentFl']['Y']); ?>/>html 직접 편집</label>
                <div id="descriptionArea" class="<?= $data['pollHtmlContentFl'] == 'Y' ? '' : 'display-none'; ?>">
                    <ul class="nav nav-tabs nav-tabs-sm">
                        <li class="active display-inline" id="btnDescriptionShop">
                            <a href="#textareaDescriptionShop">PC쇼핑몰 상세 설명</a></li>
                        <li class="nav-none display-inline" id="btnDescriptionMobile">
                            <a href="#textareaDescriptionMobile">모바일쇼핑몰 상세 설명</a></li>
                        <li style="padding-left:10px;padding-top:5px"> <label class="checkbox-inline"><input type="checkbox" value="Y"  <?php echo gd_isset($checked['pollHtmlContentSameFl']['Y']); ?> name="pollHtmlContentSameFl" /> PC/모바일 상세설명 동일사용</label></li>
                    </ul>
                    <div id="textareaDescriptionShop">
                        <textarea name="pollHtmlContent" rows="3" style="width:100%; height:400px;" id="editor" class="form-control"><?php echo stripslashes($data['pollHtmlContent']); ?></textarea>
                    </div>
                    <div id="textareaDescriptionMobile">
                        <textarea name="pollHtmlContentMobile" rows="3" style="width:100%; height:400px;" id="editor2" class="form-control"><?php echo stripslashes($data['pollHtmlContentMobile']); ?></textarea>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">
                설문 항목
            </th>
            <td class="item-area" colspan="3">
                <div>
                    <button type="button" id="obj" class="btn btn-white checkCopy btn-add">
                        <img src="<?= PATH_ADMIN_GD_SHARE . 'img/btn_icon_plus.png' ?>" alt="" class="va-m">
                        <span class="va-m">객관식 추가</span>
                    </button>
                    <button type="button" id="sub" class="btn btn-white checkCopy btn-add">
                        <img src="<?= PATH_ADMIN_GD_SHARE . 'img/btn_icon_plus.png' ?>" alt="" class="va-m">
                        <span class="va-m">주관식 추가</span>
                    </button>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<script type="text/javascript" src="/admin/gd_share/script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="/admin/gd_share/script/smart/js/editorLoad.js" charset="utf-8"></script>
<script type="text/javascript">
    var questionCount = 0;
    var radioStorage = [];
    nhn.husky.EZCreator.createInIFrame({
        oAppRef: oEditors,
        elPlaceHolder: "editor2",
        sSkinURI: "/admin/gd_share/script/smart/SmartEditor2Skin.html",
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
            $("#textareaDescriptionMobile").hide();
            toggleSelectionDisplay('goodsDetail');
        },
        fCreator: "createSEditor2"
    });

    $(document).ready(function () {
        $("#frmPoll").validate({
            submitHandler: function (form) {
                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                oEditors.getById["editor2"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.

                if ($('input[name="pollEndDtFl"]').prop('checked') === false ) {
                    var curDate = '<?php echo date('Y-m-d H:i'); ?>';
                    console.log(curDate);
                    if (!$('input[name="pollEndDt"]').val()) {
                        alert('설문 기간을 입력해주세요');
                        return false;
                    } else {
                        if ($('input[name="pollEndDt"]').val() < $('input[name="pollStartDt"]').val() || $('input[name="pollEndDt"]').val() == $('input[name="pollStartDt"]').val() || $('input[name="pollEndDt"]').val() < curDate) {
                            alert('진행기간을 다시 확인해주세요');
                            return false;
                        }
                    }
                }
                if ($('input[name="pollGroupFl"]:checked').val() == 'select' && $('input[name="groupSno[]"]').length <= 0) {
                    alert('설문조사에 참여할 회원등급을 선택해주세요.');
                    return false;
                }
                <?php if ($mode == 'regist') { ?>
                if ($('input[name="pollBannerFl"]:checked').val() == 'upl') {
                    if ($('input[name="pollDeviceFl"]:checked').val() != 'mobile' && !$('input[name="pollBannerImg"]').val()) {
                        alert('직접 등록할 PC용 설문배너 이미지를 선택해주세요.');
                        return false;
                    }
                    if ($('input[name="pollDeviceFl"]:checked').val() != 'pc' && !$('input[name="pollBannerImgMobile"]').val()) {
                        alert('직접 등록할 모바일용 설문배너 이미지를 선택해주세요.');
                        return false;
                    }
                }
                <?php } ?>
                if ($('input[name="pollBannerFl"]:checked').val() != 'none') {
                    if ($('input[name="pollViewPosition[]"]:checked').length <= 0) {
                        alert('설문배너가 노출될 위치를 선택해주세요.');
                        return false;
                    }
                }
                if ($('input[name="pollViewPosition[]"]:eq(1)').is(':checked') === true && $('input[name="pollViewCategory"]:checked').val() == 'select' && $('input[name="category[]"]').length <= 0) {
                    alert('설문배너가 노출될 카테고리를 선택해주세요.');
                    return false;
                }

                if ($('.question').length <= 0) {
                    alert('설문 항목은 최소 1개 이상 등록하셔야 합니다.');
                    return false;
                } else {
                    for (var i = 0; i < $('.question').length; i++) {
                        if (!$('.question input[name="itemTitle[' + i + ']"]').val()) {
                            alert('선택지가 입력되지 않은 항목이 있습니다.');
                            return false;
                        }
                        if ($('input[name^="itemAnswer[' + i + '][]"]').length > 0) {
                            for (var j = 0; j < $('input[name^="itemAnswer[' + i + '][]"]').length; j++) {
                                if (!$('input[name^="itemAnswer[' + i + '][]"]').val()) {
                                    alert('선택지가 입력되지 않은 항목이 있습니다. 확인 후 입력해주세요.');
                                    return false;
                                }
                            }
                        }
                    }
                }
                form.submit();
            },
            rules: {
                'pollTitle': 'required',
                'pollStartDt': 'required',
                'pollMemberLimitFl': 'required',
                'pollMemberLimitCnt': {
                    required: function () {
                        return $('input[name="pollMemberLimitFl"]:checked').val() === 'max';
                    },
                    min: function () {
                        if ($('input[name="pollMemberLimitFl"]:checked').val() === 'max' && parseInt($('input[name="pollMemberLimitCnt"]').val()) < 1) {
                            return 1;
                        } else {
                            return false;
                        }
                    }
                }
            },
            messages: {
                'pollTitle': {
                    required: '설문 제목을 입력해주세요'
                },
                'pollStartDt': {
                    required: '설문 기간을 입력해주세요'
                },
                'pollMemberLimitFl': {
                    required: '참여 인원 제한을 선택해주세요'
                },
                'pollMemberLimitCnt': {
                    required: '참여 인원 제한을 입력해주세요',
                    min: '참여 인원 제한을 1 이상 입력해주세요'
                }
            }
        });
        $('input[name="pollMileage"]').keyup(function () {
            $(this).val( $(this).val().replace(/[^0-9]/gi,"") );
        });
        $('input[name="pollBannerFl"]').click(function () {
            var value = this.value;

            switch (value) {
                case 'def':
                    $('.category').show();
                    $('input[name^="pollBannerImg"]').prop('disabled', true);
                    break;
                case 'upl':
                    $('.category').show();
                    $('input[name^="pollBannerImg"]').prop('disabled', false);
                    break;
                case 'none':
                    $('.category').hide();
                    $('input[name^="pollBannerImg"]').prop('disabled', true);
                    break;
            }
        });
        $('input[name="pollDeviceFl"]').click(function () {
            var value = this.value;

            switch (value) {
                case 'pc':
                    $('.poll-banner-img').show();
                    $('.poll-banner-img-mobile').hide();
                    $('input[name="pollResultViewFl"]:eq(0)').prop('disabled', false);
                    break;
                case 'mobile':
                    $('.poll-banner-img').hide();
                    $('.poll-banner-img-mobile').show();
                    $('input[name="pollResultViewFl"]:eq(0)').prop('disabled', true);
                    $('input[name="pollResultViewFl"]:eq(1)').prop('checked', true);
                    break;
                default:
                    $('.poll-banner-img').show();
                    $('.poll-banner-img-mobile').show();
                    $('input[name="pollResultViewFl"]:eq(0)').prop('disabled', true);
                    $('input[name="pollResultViewFl"]:eq(1)').prop('checked', true);
                    break;
            }
        });
        $('input[name="pollGroupFl"]').click(function () {
            var value = this.value;
            if (value != 'select') {
                $('#member_selectLayer_list').removeClass('active');
                $('#member_selectLayer_list>').remove();
            }
        });
        $('input[name="pollEndDtFl"]').click(function () {
            if (this.checked === true) {
                $('input[type="text"][name^="pollEndDt"]').val('').prop('disabled', true);
            } else {
                $('input[type="text"][name^="pollEndDt"]').prop('disabled', false);
            }
        });

        $('.js-group-select, .js-view-position-select').bind('click', function () {
            $(this).closest('td').find('input[type="radio"][value="select"]').trigger('click');
        });

        $('#btnDescriptionShop, #btnDescriptionMobile').click(function () {

            if (this.id == 'btnDescriptionShop') {
                $('#btnDescriptionShop').addClass('active');
                $('#btnDescriptionMobile').removeClass('active');
                $("#textareaDescriptionShop").show();
                $("#textareaDescriptionMobile").hide();
            } else {
                if($("input[name='pollHtmlContentSameFl']").prop('checked') == false) {
                    $('#btnDescriptionShop').removeClass('active');
                    $('#btnDescriptionMobile').addClass('active');
                    $("#textareaDescriptionShop").hide();
                    $("#textareaDescriptionMobile").show();
                }
            }
            oEditors.getById["editor"].exec("CHANGE_EDITING_MODE", ["WYSIWYG"]);
            oEditors.getById["editor2"].exec("CHANGE_EDITING_MODE", ["WYSIWYG"]);

            return false;
        });

        $(':radio[name="pollHtmlContentFl"]').click(function () {
            if (this.value == 'N') {
                $('#descriptionArea').hide();
            } else {
                $('#descriptionArea iframe').css('height', '449px');
                $('#descriptionArea').show();
                oEditors.getById["editor"].exec("CHANGE_EDITING_MODE", ["WYSIWYG"]);
                oEditors.getById["editor2"].exec("CHANGE_EDITING_MODE", ["WYSIWYG"]);
            }
        });

        $('input[name="pollViewPosition[]"]').click(function () {
            if ($('input[name="pollViewPosition[]"][value="category"]').is(':checked') === true) {
                $('input[name="pollViewCategory"], .js-view-position-select').prop('disabled', false);
            } else {
                $('input[name="pollViewCategory"], .js-view-position-select').prop('disabled', true);
            }
        });

        $('input[name="pollViewCategory"]').click(function () {
            if (this.value == 'all') {
                $('#categoryRowArea').hide();
            } else {
                $('#categoryRowArea').show();
            }
        });

        $('.btn-add').click(function () {
            if (questionCount >= Number('<?php echo $questionCount; ?>')) {
                alert('최대 <?php echo $questionCount; ?>개까지만 등록 가능합니다.');
                return;
            }
            var id = this.id;
            var content = {
                'idx': questionCount,
                'num': questionCount + 1,
                'itemAnswerType': id,
                'itemResponseType': 'radio',
                'itemTitle': '',
                'itemRequired': '',
                'itemAnswer': ['',''],
                'itemLastAnswer': 0
            };
            setItemHtml(id, content);
        });

        $(document).on('click', '.btn-ans-add', function () {
            var ansCount = $(this).closest('td').find('.form-inline').not('.ans-etc').length;
            if (ansCount >= Number('<?php echo $answerCount; ?>')) {
                alert('선택지는 최대 <?php echo $answerCount; ?>개까지만 등록 가능합니다.');
                return;
            }
            var type = $(this).closest('.question').find('input[name^="itemResponseType"]:checked').val();
            var idx = $(this).closest('.question').find('input[name="idx-value[]"]').val();
            var content = {
                'idx': idx,
                'itemResponseType': type,
                'itemAnswer': ''
            };
            var compiled = _.template($('#itemAnswer').html());
            compiled = compiled(content);
            if ($(this).closest('td').find('.form-inline').not('.ans-etc').length > 2) {
                $(this).closest('td').find('.form-inline').not('.ans-etc').last().after(compiled);
            } else {
                $(this).closest('div').after(compiled);
            }
        });

        $(document).on('click', '.btn-ans-del', function () {
            $(this).closest('.form-inline').remove();
        });

        $(document).on('click', '.btn-ans-etc-add', function () {
            var msg = '추가';

            if ($(this).hasClass('ans-etc-add-y') === true) {
                $(this).closest('td').find('.form-inline').last().remove();
            } else {
                msg = '삭제';
                var type = $(this).closest('.question').find('input[name^="itemResponseType"]:checked').val();
                var target = $(this).closest('td');
                var idx = $(this).closest('.question').find('input[name="idx-value[]"]').val();
                var content = {
                    'idx': idx,
                    'itemResponseType': type,
                    'itemAnswer': ''
                };
                var compiled = _.template($('#itemAnswerEtc').html());
                compiled = compiled(content);
                target.append(compiled);
            }

            $(this).find('>span').html(msg);
            $(this).toggleClass('ans-etc-add-y');
        });

        $(document).on('click', 'input[name^="itemResponseType"]', function () {
            if ($(this).closest('.question').find('input[name^="itemAnswerType"]').val() == 'obj') {
                var type = this.value;
                var target = $(this).closest('tr').next().find('.form-inline');

                target.find('label input').not('[type="hidden"]').prop('type', type);
            }
        });

        $(document).on('click', '.js-moverow', function () {
            var direction = $(this).data('direction');
            var html = $(this).closest('.question').clone().wrapAll('<div/>').parent().clone();
            var idx = $(this).closest('.question').find('input[name="idx-value[]"]').val();
            var target = $(this).closest('td');

            switch (direction) {
                case 'bottom':
                    if (idx == questionCount - 1) return;
                    $(this).closest('.question').remove();
                    target.append(html);

                    setRadioStorage();
                    $('.question').each(function(e){
                        sortItem(e);
                    });
                    break;
                case 'down':
                    if (idx == questionCount - 1) return;
                    $(this).closest('.question').remove();
                    $('.question').eq(idx).after(html);

                    setRadioStorage();
                    $('.question').each(function(e){
                        sortItem(e);
                    });
                    break;
                case 'up':
                    if (idx == 0) return;
                    $(this).closest('.question').remove();
                    $('.question').eq(idx - 1).before(html);

                    setRadioStorage();
                    $('.question').each(function(e){
                        sortItem(e);
                    });
                    break;
                case 'top':
                    if (idx == 0) return;
                    $(this).closest('.question').remove();
                    target.find('>div').eq(0).after(html);

                    setRadioStorage();
                    $('.question').each(function(e){
                        sortItem(e);
                    });
                    break;
            }
            getRadioStorage();
        });

        $(document).on('click', '.btn-del', function () {
            var idx = $(this).closest('.question').find('input[name="idx-value[]"]').val();
            dialog_confirm('선택하신 설문 항목을 삭제하시겠습니까? 설문 저장 시 최종 반영됩니다.', function (result) {
                if (result) {
                    $('.question').eq(idx).remove();

                    setRadioStorage();
                    $('.question').each(function(e){
                        sortItem(e);
                    });
                    questionCount--;
                }
            });
        });

        $(document).on('click', '.btn-copy', function () {
            if (questionCount >= Number('<?php echo $questionCount; ?>')) {
                alert('최대 <?php echo $questionCount; ?>개까지만 등록 가능합니다.');
                return;
            }
            var html = $(this).closest('.question').clone().wrapAll('<div/>').parent().clone();
            var target = $(this).closest('td');

            setRadioStorage();
            target.append(html);
            $('.question').each(function(e){
                sortItem(e);
            });
            getRadioStorage();
            questionCount++;
        });

        // 참여 인원 제한 무제한시 배너 미노출 항목 비활성화
        $(document).on('click', 'input[name="pollMemberLimitFl"]', function () {
            if ($(this).val() == 'unlimited') {
                $('input[name="pollBannerLimitFl"]').prop({
                    'checked': false,
                    'disabled': true
                });
            } else {
                $('input[name="pollBannerLimitFl"]').prop('disabled', false);
            }
        });
    });

    $(window).load(function () {
        setItem('<?php echo $item; ?>');
        questionCount = $('.question').length;
    });

    function setRadioStorage()
    {
        for (var i = 0; i < $('.question').length; i++) {
            radioStorage[i] = $('.question').eq(i).find('input[name^="itemResponseType"]:checked').val();
        }
    }

    function getRadioStorage()
    {
        for (var i in radioStorage) {
            $('input[name="itemResponseType[' + i + ']"][value="' + radioStorage[i] + '"]').prop('checked', true);
        }
        radioStorage = [];
    }

    function sortItem(idx)
    {
        $('.question').eq(idx).find('.item-num').html(idx + 1);
        $('.question').eq(idx).find('input[name="idx-value[]"]').val(idx);
        for (var i = 0; i < $('.question').eq(idx).find('input').length; i++) {
            if ($('.question').eq(idx).find('input').eq(i).prop('name')) {
                var replaceName = $('.question').eq(idx).find('input').eq(i).prop('name').replace(/\[[0-9]{1,2}\]/, '['+idx+']');
                //console.log(replaceName);
                $('.question').eq(idx).find('input').eq(i).prop('name', replaceName);
            }
        }
    }

    function layer_register_category() {
        var typeStr = 'category';
        var childNm = 'category';
        var addParam = {
            mode: 'simple',
            layerFormID: childNm + "Layer",
            parentFormID: childNm + "Row",
            dataFormID: childNm + "Id",
            dataInputNm: childNm,
            childRow: $("#"+childNm + "Row tr").length
        };
        layer_add_info(typeStr, addParam);
    }

    function layer_register(parentLayerFormID, dataInputNm, dataFormID) {
        var addParam = {
            "parentFormID": parentLayerFormID,
            "dataInputNm": dataInputNm,
            "dataFormID": dataFormID,
        };
        layer_add_info('member_group', addParam);
    }

    function allDel(delRow){
        $("#"+delRow).empty();
    }
    function setItem(item){
        item = item.replace(/\\/g, '\\\\');
        var data = JSON.parse(item);

        for (var i in data.itemAnswerType) {
            i = Number(i);
            if (data.itemAnswerType[i] == 'obj') {
                var content = {
                    'idx': i,
                    'num': i + 1,
                    'itemRequired': data.itemRequired[i],
                    'itemTitle': data.itemTitle[i],
                    'itemAnswer': data.itemAnswer[i],
                    'itemResponseType': data.itemResponseType[i],
                    'itemLastAnswer': data.itemLastAnswer[i]
                };
            } else {
                var content = {
                    'idx': i,
                    'num': i + 1,
                    'itemRequired': data.itemRequired[i],
                    'itemTitle': data.itemTitle[i],
                    'itemResponseType': data.itemResponseType[i],
                    'itemLastAnswer': data.itemLastAnswer[i]
                };
            }
            setItemHtml(data.itemAnswerType[i], content);
        }
    }
    function setItemHtml(answerType, objData)
    {
        var compiled = _.template($('#' + answerType + 'TextAreaRow').html());
        var target = $('.item-area');
        compiled = compiled(objData);
        target.append(compiled);

        questionCount++;
    }
</script>

<script type="text/html" class="template" id="subTextAreaRow">
    <div class="question">
        <input type="hidden" name="itemAnswerType[<%=idx%>]" value="sub" />
        <input type="hidden" name="idx-value[]" value="<%=idx%>" />
        <div class="table-action small mgt10 mgb0">
            <div class="pull-left form-inline">
                <b><span class="item-num"><%=num%></span>번 : 주관식 문항</b>
                <span class="mgl10">
                    <label>
                        <input type="checkbox" name="itemRequired[<%=idx%>]" value="Y" <%=itemRequired == 'Y' ? 'checked' : ''%>>
                        필수응답
                    </label>
                </span>
            </div>
            <div class="pull-right">
                <div class="form-inline">
                    <div class="btn-group">
                        <button type="button" class="btn btn-white btn-icon-bottom js-moverow poll_downArrowMore" data-direction="bottom">
                            맨아래
                        </button>
                        <button type="button" class="btn btn-white btn-icon-down js-moverow poll_downArrow" data-direction="down">
                            아래
                        </button>
                        <button type="button" class="btn btn-white btn-icon-up js-moverow poll_upArrow" data-direction="up">
                            위
                        </button>

                        <button type="button" class="btn btn-white btn-icon-top js-moverow poll_upArrowMore" data-direction="top">
                            맨위
                        </button>
                    </div>
                    <input type="button" value="문항삭제" class="btn btn-white btn-del">
                    <input type="button" value="문항복사" class="btn btn-white btn-copy">
                </div>
            </div>
        </div>
        <div>

            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>응답방식</th>
                    <td>
                        <div class="form-inline">
                            <label class="radio-inline">
                                <input type="radio" name="itemResponseType[<%=idx%>]" value="short" <%= itemResponseType == 'short' ? 'checked' : ''%>>
                                단답형
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="itemResponseType[<%=idx%>]" value="descript" <%= itemResponseType == 'descript' ? 'checked' : ''%>>
                                서술형
                            </label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>내용</th>
                    <td>
                        <div>
                            <input type="text" name="itemTitle[<%=idx%>]" value="<%=itemTitle%>" class="form-control width-3xl" placeholder="질문을 입력하세요" maxlength="100">
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</script>
<script type="text/html" class="template" id="objTextAreaRow">
    <div class="question">
        <input type="hidden" name="itemAnswerType[<%=idx%>]" value="obj" />
        <input type="hidden" name="idx-value[]" value="<%=idx%>" />
        <div class="table-action small mgt10 mgb0">
            <div class="pull-left form-inline">
                <b><span class="item-num"><%=num%></span>번 : 객관식 문항</b>
                <span class="mgl10">
                    <label>
                        <input type="checkbox" name="itemRequired[<%=idx%>]" value="Y" <%=itemRequired == 'Y' ? 'checked' : ''%>>
                        필수응답
                    </label>
                </span>
            </div>
            <div class="pull-right">
                <div class="form-inline">

                    <div class="btn-group">
                        <button type="button" class="btn btn-white btn-icon-bottom js-moverow poll_downArrowMore" data-direction="bottom">
                            맨아래
                        </button>
                        <button type="button" class="btn btn-white btn-icon-down js-moverow poll_downArrow" data-direction="down">
                            아래
                        </button>
                        <button type="button" class="btn btn-white btn-icon-up js-moverow poll_upArrow" data-direction="up">
                            위
                        </button>

                        <button type="button" class="btn btn-white btn-icon-top js-moverow poll_upArrowMore" data-direction="top">
                            맨위
                        </button>
                    </div>

                    <input type="button" value="문항삭제" class="btn btn-white btn-del">
                    <input type="button" value="문항복사" class="btn btn-white btn-copy">
                </div>
            </div>
        </div>
        <div>
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>응답방식</th>
                    <td>
                        <div class="form-inline">
                            <label class="radio-inline">
                                <input type="radio" name="itemResponseType[<%=idx%>]" value="radio" <%= itemResponseType == 'radio' ? 'checked' : ''%>>
                                1개 선택
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="itemResponseType[<%=idx%>]" value="checkbox" <%= itemResponseType == 'checkbox' ? 'checked' : ''%>>
                                여러 개 선택
                            </label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>내용</th>
                    <td>
                        <div>
                            <input type="text" name="itemTitle[<%=idx%>]" value="<%=itemTitle%>" class="form-control width-3xl" placeholder="질문을 입력하세요" maxlength="100">
                        </div>
                        <%
                        for (var i in itemAnswer) {
                        if (i >= 2) break;
                        %>
                        <div class="form-inline mgt10">
                            <label class="<%=itemResponseType%>-inline">
                                <input type="<%=itemResponseType%>">
                            </label>
                            <input type="text" name="itemAnswer[<%=idx%>][]" value="<%=itemAnswer[i]%>" class="form-control" maxlength="50" style="width:70%;">
                            <% if (i > 2) { %>
                            <button type="button" class="btn btn-white btn-sm checkCopy btn-ans-del">
                                <img src="<?= PATH_ADMIN_GD_SHARE . 'img/btn_icon_minus.png' ?>" alt="" class="va-m">
                                <span class="va-m">삭제</span>
                            </button>
                            <% } %>
                        </div>
                        <% } %>

                        <div class="mgt10">
                            <button type="button" class="btn btn-white btn-sm checkCopy btn-ans-add">
                                <img src="<?= PATH_ADMIN_GD_SHARE . 'img/btn_icon_plus.png' ?>" alt="" class="va-m">
                                <span class="va-m">추가</span>
                            </button>
                            <a class="va-m hand btn-ans-etc-add<% if (itemLastAnswer == 1) {%> ans-etc-add-y <% } %>">
                                기타의견 <span><% if (itemLastAnswer == 1) {%>삭제<% } else { %>추가<% } %></span>
                            </a>
                        </div>

                        <%
                        for (var i in itemAnswer) {
                        if (i < 2) continue;
                        %>
                        <div class="form-inline mgt10">
                            <label class="<%=itemResponseType%>-inline">
                                <input type="<%=itemResponseType%>">
                            </label>
                            <input type="text" name="itemAnswer[<%=idx%>][]" value="<%=itemAnswer[i]%>" class="form-control" maxlength="50" style="width:70%;">
                            <button type="button" class="btn btn-white btn-sm checkCopy btn-ans-del">
                                <img src="<?= PATH_ADMIN_GD_SHARE . 'img/btn_icon_minus.png' ?>" alt="" class="va-m">
                                <span class="va-m">삭제</span>
                            </button>
                        </div>
                        <% } %>

                        <% if (itemLastAnswer == true) { %>
                        <div class="form-inline ans-etc mgt10">
                            <label class="<%=itemResponseType%>-inline">
                                <input type="<%=itemResponseType%>">
                                <input type="hidden" name="itemAnswerEtc[<%=idx%>]" value="ETC">
                            </label>
                            기타
                        </div>
                        <% } %>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</script>
<script type="text/html" class="template" id="itemAnswer">
    <div class="form-inline mgt10">
        <label class="<%=itemResponseType%>-inline">
            <input type="<%=itemResponseType%>">
        </label>
        <input type="text" name="itemAnswer[<%=idx%>][]" value="<%=itemAnswer%>" class="form-control" maxlength="50" style="width:70%;">
        <button type="button" class="btn btn-white btn-sm checkCopy btn-ans-del">
            <img src="<?= PATH_ADMIN_GD_SHARE . 'img/btn_icon_minus.png' ?>" alt="" class="va-m">
            <span class="va-m">삭제</span>
        </button>
    </div>
</script>
<script type="text/html" class="template" id="itemAnswerEtc">
    <div class="form-inline ans-etc mgt10">
        <label class="<%=itemResponseType%>-inline">
            <input type="<%=itemResponseType%>">
            <input type="hidden" name="itemAnswerEtc[<%=idx%>]" value="ETC">
        </label>
        기타
    </div>
</script>

<style>
    #categoryRow td:nth-child(1),#categoryRow td:nth-child(3) { width:10%;height: 43px;}
    .table-cols > tr > td{
        padding: 8px 15px;
        font-size: 12px;
        height: 43px;
        border-bottom: 1px solid #E6E6E6;
    }
</style>
