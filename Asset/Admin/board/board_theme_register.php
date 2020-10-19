<form id="frmTheme" name="frmTheme" action="board_theme_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?= $data['mode']; ?>"/>
    <input type="hidden" name="sno" value="<?= $data['sno']; ?>"/>

    <div class="page-header js-affix">
        <h3>게시판 스킨 <?= ($data['mode'] == 'theme_register') ? '등록' : '수정'; ?></h3>
        <input type="submit" value="<?= ($data['mode'] == 'theme_register') ? '저장' : '수정'; ?>" class="btn btn-red"/>
    </div>

    <div class="table-title gd-help-manual">
        기본 정보
    </div>
    <div>
        <table class="table table-cols">
            <colgroup>
                <col class="width10p"/>
                <col/>
            </colgroup>
            <?php if ($gGlobal['isUse']) { ?>
                <tr>
                    <th class="require">적용 디자인스킨</th>
                    <td>
                        <?php if ($data['mode'] == 'theme_modify') { ?>
                            <b><?= $data['liveSkinName'] ?>(<?= $data['liveSkin'] ?>)</b>
                        <?php } else { ?>
                            <?php foreach ($gGlobal['useMallList'] as $val) {
                                ?>
                                <label class="radio-inline"><input type="radio" name="liveSkin"
                                                                   value="<?= $val['skin']['frontLive'] . STR_DIVISION . $val['skin']['mobileLive'] ?>">
                                    <span class="flag flag-16 flag-<?= $val['domainFl'] ?>"></span>
                                    <?= $val['mallName'] ?></label>
                            <?php } ?>
                        <?php } ?>
                        <div class="notice-info">게시판 스킨을 생성하기 위해서는 현재 사용스킨으로 설정된 디자인스킨에서만 신규 등록이 가능합니다.</div>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th>구분</th>
                <td>
                    <?php if ($data['mode'] == 'theme_register') { ?>
                        <label class="radio-inline"><input type="radio" name="bdMobileFl" value="n" <?= $checked['bdMobileFl']['n'] ?>>PC쇼핑몰</label>
                        <label class="radio-inline"><input type="radio" name="bdMobileFl" value="y" <?= $checked['bdMobileFl']['y'] ?>>모바일쇼핑몰</label>
                    <?php } else { ?>
                        <b> <?= $data['deviceTypeText'] ?></b>
                        <input type="hidden" name="bdMobileFl" value="<?= $data['bdMobileFl'] ?>">
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th>유형</th>
                <td>
                    <?php
                    if ($data['mode'] == 'theme_register') {
                        $tmpChecked = 'checked="checked"';
                        foreach ($bdKind as $k => $v) {
                            ?>
                            <div class="pull-left" style="padding-right:15px">
                                <label class="radio-inline"><input type="radio" name="bdKind" value="<?= $k; ?>" <?= $tmpChecked ?> /><?= $v; ?>
                                    <div class="mgt10"><img src="<?= PATH_ADMIN_GD_SHARE ?>img/board/type_<?= $k ?>.png"></div>
                                </label>
                            </div>
                            <?php
                            $tmpChecked = '';
                        }
                    } else {
                        ?>
                        <input type="hidden" name="bdKind" value="<?= gd_isset($data['bdKind']); ?>"/>
                        <strong><?= gd_isset($bdKind[$data['bdKind']]); ?></strong>
                    <?php } ?>

                    <span id="bdKind_msg" class="input_error_msg"></span>
                </td>
            </tr>
            <tr>
                <th class="require">스킨코드</th>
                <td class="form-inline">
                    <input type="hidden" name="chkThemeId" id="chkThemeId" value="<?= $data['themeId']; ?>"/>
                    <?php if ($data['mode'] == 'theme_register') { ?>
                        <input type="text" name="themeId" id="themeId" value="" class="form-control width_sm"
                               style="ime-mode:disabled; text-transform:lowercase;"/>
                        <button type="button" class="btn btn-gray btn-sm" id="overlap_themeId">중복확인</button>
                        <span id="themeId_msg" class="input_error_msg"></span>
                    <?php } else { ?>
                        <strong><?= gd_isset($data['themeId']); ?></strong>
                        <input type="hidden" name="themeId" value="<?= $data['themeId'] ?>">
                    <?php } ?>
                    <div class="notice-info">
                        스킨코드는 현재 작업 중인 쇼핑몰 디자인 스킨에 ‘게시판(board)/skin/(스킨코드)/’로 게시판 스킨 폴더가 생성됩니다.

                    </div>
                </td>
            </tr>
            <tr>
                <th class="require">게시판 스킨명</th>
                <td>
                    <input type="text" name="themeNm" value="<?= $data['themeNm']; ?>"
                           class="form-control"/>
                    <span id="themeNm_msg" class="input_error_msg"></span>
                </td>
            </tr>
            <tr class="js-mobile-hide">
                <th>게시판 위치</th>
                <td>
                    <div>
                        <div class="pull-left" style="padding-right:15px">
                            <label class="radio-inline">
                                <input type="radio" name="bdAlign" value="left" <?= $checked['bdAlign']['left'] ?>>좌측 정렬
                                <div class="mgt10"><img src="<?= PATH_ADMIN_GD_SHARE ?>img/board/align_left.png"></div>
                            </label>
                        </div>

                        <div class="pull-left" style="padding-right:15px">
                            <label class="radio-inline">
                                <input type="radio" name="bdAlign" value="center" <?= $checked['bdAlign']['center'] ?>>센터 정렬
                                <div class="mgt10"><img src="<?= PATH_ADMIN_GD_SHARE ?>img/board/align_center.png"></div>
                            </label>
                        </div>

                        <div class="pull-left" style="padding-right:15px">
                            <label class="radio-inline">
                                <input type="radio" name="bdAlign" value="right" <?= $checked['bdAlign']['right'] ?>>우측 정렬
                                <div class="mgt10"><img src="<?= PATH_ADMIN_GD_SHARE ?>img/board/align_right.png"></div>
                            </label>
                        </div>
                    </div>
                </td>
            </tr>

            <tr class="js-mobile-hide">
                <th class="require">게시판 넒이</th>
                <td class="form-inline">
                    <input type="text" name="bdWidth" value="<?= gd_isset($data['bdWidth']) ?>"
                           class="form-control js-number"/>
                    <?= gd_select_box('bdWidthUnit', 'bdWidthUnit', array('%' => '%', 'px' => 'pixel'), null, gd_isset($data['bdWidthUnit'])); ?>
                    <img src="<?= PATH_ADMIN_GD_SHARE ?>/img/board/board-width.png" style="margin-left:20px">
                </td>
            </tr>
            <tr class="js-pc-show" style="display:none">
                <th>pc아이콘 관리</th>
                <td>
                    <table class="table table-rows table-fixed">
                        <colgroup>
                            <col width="100"/>
                            <col width="100"/>
                            <col width="350"/>
                            <col/>
                        </colgroup>
                        <thead>
                        <tr>
                            <th>아이콘 종류</th>
                            <th>아이콘 미리보기</th>
                            <th>아이콘 등록</th>
                            <th>아이콘 예시 화면</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $i = 0;
                        foreach ($iconTypeList as $key => $val) {
                            ?>
                            <tr height="50">
                                <td align="center"><?= $key ?></td>
                                <td align="center"><img src='<?= $data['iconImage'][$val]['url'] ?>'/></td>
                                <td>
                                    <div class="pull-left pdl15" style="width:280px">
                                        <input type="file" name="boardIcon[<?= $val ?>]"/>
                                    </div>
                                    <div class="pull-left mgl10">
                                        <?php if ($data['mode'] == 'theme_modify' && $data['iconImage'][$val]['userModify'] == true) { ?>
                                            <button type="button" class="btn btn-white btn-sm"
                                                    onclick="deleteIcon('<?= $data['themeId'] ?>','<?= $val ?>','pc')">
                                                삭제
                                            </button>
                                        <?php } ?>
                                    </div>
                                </td>
                                <?php if ($i == 0) { ?>
                                    <td rowspan="7" align="center">
                                        <img src="<?= PATH_ADMIN_GD_SHARE ?>img/board/board_sample.png" class="pdl15">
                                    </td>
                                <?php } ?>
                            </tr>
                            <?php
                            $i++;
                        } ?>
                        </tbody>
                    </table>

                </td>
            </tr>
            <tr class="js-mobile-show" style="display:none">
                <th>모바일아이콘 관리</th>
                <td>
                    <table class="table table-rows table-fixed">
                        <colgroup>
                            <col width="100"/>
                            <col width="100"/>
                            <col width="350"/>
                            <col/>
                        </colgroup>
                        <thead>
                        <tr>
                            <th>아이콘 종류</th>
                            <th>아이콘 미리보기</th>
                            <th>아이콘 등록</th>
                            <th>아이콘 예시 화면</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $i = 0;
                        foreach ($iconTypeList as $key => $val) {
                            ?>
                            <tr height="50">
                                <td align="center"><?= $key ?></td>
                                <td align="center"><img src='<?= $data['mobileIconImage'][$val]['url'] ?>'/></td>
                                <td>
                                    <div class="pull-left pdl15" style="width:280px">
                                        <input type="file" name="boardIconMobile[<?= $val ?>]"/>
                                    </div>
                                    <div class="pull-left mgl10">
                                        <?php if ($data['mode'] == 'theme_modify' && $data['mobileIconImage'][$val]['userModify'] == true) { ?>
                                            <button type="button" class="btn btn-white btn-sm"
                                                    onclick="deleteIcon('<?= $data['themeId'] ?>','<?= $val ?>','mobile')">
                                                삭제
                                            </button>
                                        <?php } ?>
                                    </div>
                                </td>
                                <?php if ($i == 0) { ?>
                                    <td rowspan="7" align="center">
                                        <img src="<?= PATH_ADMIN_GD_SHARE ?>img/board/board_sample.png" class="pdl15">
                                    </td>
                                <?php } ?>
                            </tr>
                            <?php
                            $i++;
                        } ?>
                        </tbody>
                    </table>

                </td>
            </tr>
            <tr class="js-mobile-hide">
                <th class="require">게시글 줄높이</th>
                <td class="form-inline">
                    <input type="text" name="bdListLineSpacing"
                           value="<?= gd_isset($data['bdListLineSpacing']) ?>" class="form-control js-number"/>
                    pixel

                    <img src="<?= PATH_ADMIN_GD_SHARE ?>img/board/board_line-height.png" style="margin-left:20px">
                </td>
            </tr>
            <?php if ($data['mode'] == 'theme_modify' && !$gGlobal['isUse']) { ?>
                <tr>
                    <th>디자인 수정</th>
                    <td class="form-inline">
                        <table class="table table-rows table-fixed">
                            <colgroup>
                                <col width="400"/>
                                <col width="400"/>
                                <col width="400"/>
                            </colgroup>
                            <thead>
                            <tr>
                                <th>목록 화면</th>
                                <th>상세보기 화면</th>
                                <th>작성 화면</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr align="center">
                                <td>게시판{board}/skin/{스킨코드}/list.html
                                    <br>
                                    <button type="button" class="btn btn-gray btn-sm mgt10" onclick="skin_modify_link('list')">바로가기</button>
                                </td>
                                <td>게시판{board}/skin/{스킨코드}/view.html
                                    <br>
                                    <button type="button" class="btn btn-gray btn-sm mgt10" onclick="skin_modify_link('view')">바로가기</button>
                                </td>
                                <td>게시판{board}/skin/{스킨코드}/write.html
                                    <br>
                                    <button type="button" class="btn btn-gray btn-sm mgt10" onclick="skin_modify_link('write')">바로가기</button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="notice-danger">※ 현재 작업 중인 쇼핑몰 디자인 스킨에 게시판(board)/skin/(스킨코드)/ 안에 목록(list.htm), 상세보기(view.htm), 작성(write.htm) 화면으로
                            이동하여 개별 디자인 수정이 가능합니다.
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</form>

<script type="text/javascript">
    <!--
    var bdKind = '<?=gd_isset($data['bdKind']); ?>';
    var themeId = '<?=gd_isset($data['themeId']); ?>';
    var bdMobileFl = '<?=gd_isset($data['bdMobileFl']); ?>';
    function deleteIcon(themeId, iconType, device) {
        ifrmProcess.location.href = 'board_theme_ps.php?mode=deleteIcon&themeId=' + themeId + '&iconType=' + iconType + '&device=' + device;
    }
    $(document).ready(function () {

        $('input[name=bdMobileFl]').bind('click', function () {
            $('.js-pc-show').hide();
            $('.js-mobile-show').hide();
            if ($(this).val() == 'y') {
                $('.js-mobile-show').show();
                $('.js-mobile-hide').hide();
            }
            else {
                $('.js-pc-show').show();
                $('.js-mobile-hide').show();
            }
        });

        if (bdMobileFl == 'y') {
            $('.js-mobile-show').show();
        }
        else if (bdMobileFl == 'n') {
            $('.js-pc-show').show();
        }

        $('input[name=bdMobileFl]:checked').trigger('click');

        skin_modify_link = function (type) {
            if (bdMobileFl == 'y') {
                window.open('/mobile/design_page_edit.php?designPageId=board/skin/' + themeId + '/' + type + '.html');
            }
            else {
                window.open('/design/design_page_edit.php?designPageId=board/skin/' + themeId + '/' + type + '.html');
            }
        }
        var validId = function (id) {
            var regExp = /^[A-za-z][A-za-z0-9]{1,29}$/g;
            return regExp.test(id);
        }

        $.validator.addMethod("regx", function (value, element, fl) {
            return validId(value);
        }, "영문으로 시작해야하며 특수문자와 한글은 사용하실 수 없습니다.(2~30자)");

        $('#frmTheme').validate({
            debug: true,
            onclick: false,
            onfocusout: false,
            onkeyup: false,
            ignore: [],
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                liveSkin: 'required',
                themeId: {
                    required: true,
                    minlength: 2,
                    maxlength: 30,
                    regx: true,
                    equalTo: '#chkThemeId',
                },
                themeNm: 'required',
                bdWidth: {
                    required: true,
                    number: true,
                },
                bdListLineSpacing: {
                    required: true,
                    number: true,
                }
            },
            messages: {
                themeId: {
                    required: '스킨코드를 입력해주세요.',
                    equalTo: '스킨코드 중복확인을 해주세요.',
                    minlength: '2~30자리까지 입력가능합니다.',
                    maxlength: '2~30자리까지 입력가능합니다.',
                },
                liveSkin: '적용 디자인 스킨을 선택해주세요.',
                themeNm: '스킨명을 입력해주세요.',
            }
        });

        $('[class^=input_int]').number_only('d');
        $(".display-none").find("*").prop("disabled", true);


        $('#overlap_themeId').bind('click', function () {
            var themeId = $('#themeId').val();

            if (!validId(themeId)) {
                alert('영문으로 시작해야하며 특수문자와 한글은 사용하실 수 없습니다.(2~30자)');
                return false;
            }

            var liveSkin = '';
            <?php if($gGlobal['isUse']){?>
            liveSkin = $('input[name=liveSkin]:checked').val();
            <?php }?>

            $.post('board_theme_ps.php', {'themeId': themeId, 'isMobile': $('input[name=bdMobileFl]:checked').val(), 'mode': 'overlapThemeId', 'liveSkin': liveSkin},
                function (data) {
                    alert(data['msg']);
                    if (data['result'] == 'ok') {
                        $('#themeId_msg').hide();
                        $('#chkThemeId').val(themeId);
                    }
                })
        })
    });
    //-->
</script>
