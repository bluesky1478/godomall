<?php
use Component\Board\Board;
use Component\Storage\Storage;

$boardPath = UserFilePath::data('board');

?>
<script id="templateBdAllowDomain" type="text/html">
    <li class="mgb5">
        <input type="text" name="bdAllowDomain[]" class="form-control" size="40"/>
        <input type="text" name="bdAllowDomain[]" class="form-control" size="40"/>
        <button type="button" class="btn btn-sm btn-white btn-icon-minus js-allow-domain-minus">삭제</button>
    </li>
</script>
<script type="text/javascript">
    <!--
    // 테마별 리스트형태
    var themes = <?=json_encode(gd_isset($themes, array()));?>;

    // 테마수정으로 이동
    function addSkin() {
        window.open("board_theme_register.php");
    }

    function layer_register(parentLayerFormID, dataInputNm, dataFormID) {
        var addParam = {
            "parentFormID": parentLayerFormID,
            "dataInputNm": dataInputNm,
            "dataFormID": dataFormID,
        };

        if (dataInputNm == 'bdAuthReplyGroup') {
            if ($('input[name=bdReplyFl]:checked').val() == 'n') {
                return;
            }
        }
        else if (dataInputNm == 'bdAuthMemoGroup') {
            if ($('input[name=bdMemoFl]:checked').val() == 'n') {
                return;
            }
        }
        else if (dataInputNm == 'bdAuthWriteGroup') {
            if ($('input[name=bdAuthWrite][value=group]').is(':disabled')) {
                return;
            }
        }

        layer_add_info('member_group', addParam);
    }

    /**
     * 구매 상품 범위 등록 / 예외 등록 Ajax layer
     *
     * @param string typeStr 타입
     * @param string modeStr 예외 여부
     */
    function layer_except_register(typeStr, modeStr, isDisabled) {
        var layerFormID = 'addPresentForm';

        typeStrId = typeStr.substr(0, 1).toUpperCase() + typeStr.substr(1);

        if (typeof modeStr == 'undefined') {
            var parentFormID = 'present' + typeStrId;
            var dataFormID = 'id' + typeStrId;
            var dataInputNm = 'present' + typeStrId;
            var layerTitle = '조건 - ';
        } else {
            var parentFormID = 'except' + typeStrId;
            var dataFormID = 'idExcept' + typeStrId;
            var dataInputNm = 'except' + typeStrId;
            var layerTitle = '예외 조건 - ';
        }

        // 레이어 창
        if (typeStr == 'goods') {
            var layerTitle = layerTitle + '상품';
            var mode = 'simple';

            $("#" + parentFormID + "Table thead").show();
            $("#" + parentFormID + "Table tfoot").show();
        }
        if (typeStr == 'category') {
            var layerTitle = layerTitle + '카테고리';
            var mode = 'simple';

            $("#" + parentFormID + "Table thead").show();
            $("#" + parentFormID + "Table tfoot").show();
        }
        if (typeStr == 'brand') {
            var layerTitle = layerTitle + '브랜드';
            var mode = 'simple';

            $("#" + parentFormID + "Table thead").show();
            $("#" + parentFormID + "Table tfoot").show();
        }
        if (typeStr == 'scm') {
            var layerTitle = '공급사';
            var dataInputNm = typeStr + "No";
            var parentFormID = typeStr + 'Layer';
            var dataFormID = 'info_scm_';

            $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);

            var mode = 'radio';
        }

        if (typeStr == 'member_group') {
            var mode = 'search';
            var layerTitle = '회원등급 선택';
            var dataInputNm = "memberGroupNo";
            var parentFormID = "member_groupLayer";
            var dataFormID = "info_member_group";
        }

        var addParam = {
            "mode": mode,
            "layerFormID": layerFormID,
            "parentFormID": parentFormID,
            "dataFormID": dataFormID,
            "dataInputNm": dataInputNm,
            "layerTitle": layerTitle,
        };
        console.log(addParam);

        if (typeStr == 'goods') {
            addParam['scmFl'] = $('input[name="scmFl"]:checked').val();
            addParam['scmNo'] = $('input[name="scmNo"]').val();
        }


        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr, addParam);
    }

    $(document).ready(function () {
        var mode = $('#frmBoard>input[name=mode]').val();
        var bdId = $('#frmBoard>input[name=bdId]').val();
        var arrOnlyGoodsBdId = [];
        <?php foreach ($data['onlyGoodsBdId'] as $val) {?>
        arrOnlyGoodsBdId.push('<?=$val?>');
        <?php }?>

        $('[name=bdReviewAuthWrite]').bind('click',function(){
            $('[name=bdGoodsType][value=order]').prop('disabled',false);
            $('[name=bdGoodsType][value=goods]').prop('disabled',false);
            $('[name=bdReviewPeriodFl]').prop('disabled',false);
            if($(this).val() == 'all') {
                $('[name=bdGoodsType][value=goods]').prop('checked',true);
                $('[name=bdGoodsType][value=order]').prop('disabled',true);
                $('[name=bdReviewPeriodFl]').prop('disabled',true);
            }
            else {
                $('[name=bdGoodsType][value=order]').prop('checked',true);
                $('[name=bdGoodsType][value=goods]').prop('disabled',true);
            }
        })

        function list_image_target_notice_toggle() {
            var is_show = true;
            $('label[class*="js-bdListImageTarget-"]').each(function (idx, item) {
                if (item.style.display != 'none') {
                    is_show = false;
                    return false;
                }
            });
            $('.js-bdListImageTarget-notice').hide();
            if (is_show) {
                $('.js-bdListImageTarget-notice').show();
            }
        }

        $('input[name=bdUploadFl]').bind('click', function () {
            var $target = $('.js-bdListImageTarget-upload');
            $target.hide();
            if ($(this).val() == 'y') {
                $target.show();
            } else {
                $target.find(':radio').prop('checked', false);
            }
            list_image_target_notice_toggle();
        });

        $('input[name=bdEditorFl]').bind('click', function () {
            var $target = $('.js-bdListImageTarget-editor');
            $target.hide();
            if ($(this).val() == 'y') {
                $target.show();
            } else {
                $target.find(':radio').prop('checked', false);
            }
            list_image_target_notice_toggle();
        });

        $('input[name=bdGoodsFl]').bind('click', function () {
            $('input[name=bdGoodsType]').prop('disabled', $(this).val() == 'n');    //상품연동 사용안함이면 상품연동타입 비활성화
            $('input[name=bdGoodsTypeOrderDuplication]').prop('disabled', $(this).val() == 'n');
            var $target = $('.js-bdListImageTarget-goods');
            $target.hide();
            if ($(this).val() == 'y') {
                $target.show();
                $('input[name=bdGoodsType]:checked').trigger('click');
            } else {
                $target.find(':radio').prop('checked', false);
            }
            list_image_target_notice_toggle();
        });

        // 말머리 기능 > 말머리 입력 > 삭제 버튼 클릭 시
        $('body').on('click', '.js-allow-category-minus', function () {
            $(this).closest('tr').remove();
        })

        // 말머리 기능 > 말머리 입력 > 말머리 명에서 엔터키 입력 시
        $(document).on('keypress', '.js-add-field-category', function (e) {
            if(e.which == 13) {
                add_category();
            }
        })

        $('input[name=bdGoodsType]').bind('click', function () {
            $('.js-bdGoodsTypeOrderDuplication').hide();
            if ($(this).val() == 'order') {
                $('.js-bdGoodsTypeOrderDuplication').show();
            }
        })
        $('input[name=bdGoodsType]:checked').trigger('click')
        $('input[name=bdGoodsFl]:checked').trigger('click');
        $('[name=bdReviewAuthWrite]:checked').trigger('click');

        if (arrOnlyGoodsBdId.indexOf(bdId) != -1) {
            $('input[name=bdGoodsFl][value=n]').prop('disabled', true);
//            $('input[name=bdGoodsType][value=order]').prop('disabled', true);
            $('input[name=bdGoodsType][value=bdGoodsTypeOrderDuplication]').prop('disabled', true);
        }

        $('input[name=bdAttachImageDisplayFl]').bind('click', function () {
            if ($(this).val() == 'y') {
                $('.bdAttachImageRow').show();
            }
            else {
                $('.bdAttachImageRow').hide();
            }
        })
        $('input[name=bdMemoFl]').bind('click', function () {
            if ($(this).val() == 'y') {
                $('.bdSecretReply').show();
                $('.bdSecretReplyTitle').show();
            }
            else {
                $('.bdSecretReply').hide();
                $('.bdSecretReplyTitle').hide();
            }
        })

        $('input[name=bdAttachImageDisplayFl]:checked').trigger('click');

        $('.js-allow-domain-add').bind('click', function () {
            var templateBdAllowDomain = _.template($('#templateBdAllowDomain').html())
            if ($('#domain-allow-box>li').length == 10) {
                alert('허용도메인은 최대 20개까지 등록가능합니다.');
                return;
            }
            $('#domain-allow-box>li:last').after(templateBdAllowDomain());
        })

        $('body').on('click', '.js-allow-domain-minus', function () {
            $(this).closest('li').remove();
        })


        $('input[name=bdReplyFl]').bind('click', function () {
            $('input[name=bdAuthReply]').prop('disabled', $(this).val() == 'n');

            // 답변기능 사용안함일때
            if($(this).val() == 'n'){
                $(':checkbox[name="bdAnswerStatusFl"]').closest('label').addClass('display-none');
                $('.js-answer-status').addClass('display-none');
                $("input:checkbox[name='bdAnswerStatusFl']").prop("checked", false);
                $('.bdReplyFl-area').addClass('display-none');
                $('.bdReplyMileageFl-area').addClass('display-none');
                $('input:radio[name=bdReplyDelFl][value=applicable]').prop('checked',true);
                $("input:checkbox[name='bdReplyMileageFl']").prop("checked", false);
            }else if($(this).val() == 'y'){
                var checkedKind = $("input:radio[name=bdKind]:checked").val();

                if(checkedKind == 'default' || checkedKind == 'gallery') {
                    $(':checkbox[name="bdAnswerStatusFl"]').closest('label').removeClass('display-none');
                    $('.js-answer-status').removeClass('display-none');
                    $('.bdReplyFl-area').removeClass('display-none');
                    $('.bdReplyMileageFl-area').removeClass('display-none');
                }
            }
        })
        $('input[name=bdReplyFl]:checked').trigger('click');


        $('input[name=bdMemoFl]').bind('click', function () {
            $('input[name=bdAuthMemo]').prop('disabled', $(this).val() == 'n');
        })
        $('input[name=bdMemoFl]:checked').trigger('click');

        $('input[type=radio][name^=bdAuth]').bind('click', function () {
            if ($(this).val() != 'group') {
                $(this).closest('td').find('.selected-btn-group').html('');
            }
        })


        $('.js-group-select').bind('click', function () {
            $(this).closest('td').find('input[type="radio"][value="group"]').trigger('click');
        })

        //게시판유형
        var oldCheckedKind = $("input:radio[name=bdKind]:checked").val();
        $("input:radio[name=bdKind]").bind('change', function (event, isLoad) {
            var val = $(this).val();
            if( oldCheckedKind == 'event' && val!= 'event' ){  //유형을 이벤트에서 다른유형으로 수정하는경우 쓰기권한 전체로 변경
                $('input:radio[name=bdAuthWrite][value=all]').prop('checked',true);
            }
            if (val == 'default') {
                $(':radio[name="bdListImageFl"][value="n"]').closest('label').removeClass('display-none');
            } else if (val == 'gallery' || val == 'event') {
                $(':radio[name="bdListImageFl"][value="n"]').closest('label').addClass('display-none');
            }

            // 게시판 유형에 따른 답변관리 기능 사용 노출 처리
            if(val == 'default' || val == 'gallery' || val == 'qa'){
                // 게시판 등록시 유형 변경되었을경우 디폴트값 변동 없도록
                if(mode == 'regist'){
                    $('input:radio[name=bdReplyFl][value=y]').prop('checked',true);
                }

                if(val == 'default' || val == 'gallery') {
                    $(':checkbox[name="bdAnswerStatusFl"]').closest('label').removeClass('display-none');
                    $('.js-answer-status').removeClass('display-none');
                    if ($('input[name=bdReplyFl]:checked').val() == 'y') {
                        $('.bdReplyMileageFl-area').removeClass('display-none');
                        $('.bdReplyFl-area').removeClass('display-none');
                    }
                }else{
                    $(':checkbox[name="bdAnswerStatusFl"]').closest('label').addClass('display-none');
                    $('.js-answer-status').addClass('display-none');
                    $('.bdReplyMileageFl-area').addClass('display-none');
                    $("input:checkbox[name='bdReplyMileageFl']").prop("checked", false);
                    $('.bdReplyFl-area').addClass('display-none');
                }
            }else{
                $(':checkbox[name="bdAnswerStatusFl"]').closest('label').addClass('display-none');
                $('.js-answer-status').addClass('display-none');
                $('.bdReplyMileageFl-area').addClass('display-none');
                $("input:checkbox[name='bdReplyMileageFl']").prop("checked", false);
            }

            oldCheckedKind = val;

            if (isLoad !== true) {
                <?php if ($gGlobal['isUse']) {  //글로벌 사용중   ?>
                <?php foreach ($gGlobal['useMallList'] as $key => $val) {?>
                loadSkinSelectBox(val, 'n', '<?=$val['skin']['frontLive']?>', '<?=$val['domainFl']?>');
                loadSkinSelectBox(val, 'y', '<?=$val['skin']['mobileLive']?>', '<?=$val['domainFl']?>');
                <?php }?>
                <?php }
                else {?>
                loadSkinSelectBox(val, 'n');
                loadSkinSelectBox(val, 'y');
                <?php  }?>
            }


            var isCondition = function (data, val, flag) {
                var _tmp = data.split('_');

                if (flag == 'is') { //or
                    for (var i = 0; i < _tmp.length; i++) {
                        if (_tmp[i] == val) {
                            return true;
                        }
                    }
                    return false;
                }
                else {
                    for (var i = 0; i < _tmp.length; i++) {
                        if (_tmp[i] != val) {
                            return true;
                        }
                    }
                    return false;
                }
            }

            $('[class^="if-"]').each(function () {
                className = $(this).attr('class');
                _className = className.split('-');
                condition = _className[1];
                kind = _className[2];
                actionName = _className[3];
                arrayAction = actionName.split('_');
                thisClassEl = $('.' + className);
                // console.log(className, _className, condition, kind, actionName, arrayAction, thisClassEl);
                for (var i = 0; i < arrayAction.length; i++) {
                    action = arrayAction[i];
                    if (isCondition(kind, val, condition) == true) {
                        if (action == 'show') {
                            thisClassEl.show();
                        }
                        else if (action == 'hide') {
                            thisClassEl.hide();
                        }
                        else if (action == 'disabled') {
                            thisClassEl.attr('disabled', true);
                        }
                        else if (action == 'checked') {
                            //thisClassEl.prop('checked', true);
                            thisClassEl.trigger('click');
                        }
                    }
                    else {
                        if (action == 'show') {
                            thisClassEl.hide();
                        }
                        else if (action == 'hide') {
                            thisClassEl.show();
                        }
                        else if (action == 'disabled') {
                            thisClassEl.removeAttr('disabled');
                        }
                        else if (action == 'checked') {
                        }
                    }
                }
            });

            var defaultListImageSizeWidth = '<?=$data['bdListImageSizeWidth']?>';
            var defaultListImageSizeHeight = '<?=$data['bdListImageSizeHeight']?>';
            var defaultEventListImageSizeWidth = '<?=$data['bdEventListImageSizeWidth']?>';
            var defaultEventListImageSizeHeight = '<?=$data['bdEventListImageSizeHeight']?>';

            if (mode == 'regist') {
                switch (val) {
                    case 'default' :
                    case 'gallery' :
                        $('input[name="bdListImageSize[width]"]').val(defaultListImageSizeWidth);
                        $('input[name="bdListImageSize[height]"]').val(defaultListImageSizeHeight);
                        break;
                    case 'qa' :
                        break;
                    case 'event' :
                        $('input[name="bdListImageSize[width]"]').val(defaultEventListImageSizeWidth);
                        $('input[name="bdListImageSize[height]"]').val(defaultEventListImageSizeHeight);
                        break;
                }
            }
        });

        $("input:radio[name=bdKind]:checked").trigger('change', [true]);

        //파일 업로드
        $("input:radio[name=bdUploadFl]").bind('change', function () {
            $('input[name=bdUploadMaxSize]').attr('disabled', $(this).val() == 'n');
        });
        $("input:radio[name=bdUploadFl]:checked").trigger('change');

        //아이디중복체크
        $('#overlap_bdId').bind('click', function () {
            var bdId = $('input[name=bdId]').val();
            if (bdId.length < 2 || bdId.length > 30) {
                alert('2~30자리까지 입력가능합니다.');
                return false;
            }

            if (!validId(bdId)) {
                alert('영문으로 시작해야하며 특수문자와 한글은 사용하실 수 없습니다.(2~30자)');
                return false;
            }
            $.post('board_ps.php', {'bdId': bdId, 'mode': 'overlapBdId'},
                function (data) {
                    alert(data['msg']);
                    if (data['result'] == 'ok') {
                        $('#chkbdId').val(bdId);
                    }
                })
        })

        //스토리지 경로 변경
        $("input[name=bdId]").bind('keyup', function () {
            $(this).val($(this).val().replace(/[^a-z0-9]*/gi, ''));
            var val = valThumb = $(this).val();
            if (val != "") {
                val += "/";
                valThumb = val + "t/";
            }
            $("#bdUploadPath").val('upload/' + val);
            $("#bdUploadThumbPath").val('upload/' + valThumb);
        });

        //말머리
        $('input[name=bdCategoryFl]').bind('change', function () {
            $('.categoryWrite').hide();
            if ($(this).is(':checked')) {
                $('.categoryWrite').show();
            }
        });
        $('input[name=bdCategoryFl]').trigger('change');

        //저장소
        $("#bdUploadStorage").bind('change', function () {
            var storageName = $(this).val();
            $.get("board_ps.php", {mode: "getStorage", storage: storageName, pathCode: '<?=Storage::PATH_CODE_BOARD?>'})
                .done(function (data) {
                    $("#spanFileStorage").html(data);
                    $("#spanFileThumbStorage").html(data);
                });
        });
        $('#bdUploadStorage').trigger('change');

        //마일리지
        $('input[name=bdMileageFl]').bind('click', function () {
            $('.bdMileageFl-area').hide();
            if ($(this).val() == 'y') {
                $('.bdMileageFl-area').show();
            }
        });
        $('input[name=bdMileageFl]:checked').trigger('click');


        $('input[name=bdMileageFl]').bind('click', function () {
            $('input[name=bdMileageAmount]').attr('disabled', $(this).val() == 'n');
        });
        $("input:radio[name=bdMileageFl]:checked").trigger('click');


        //비밀번호
        $("input:radio[name=bdSecretTitleFl]").bind('click', function () {
            $("input[name=bdSecretTitleTxt]").attr('disabled', true);
            if ($(this).val() == 1) {
                $("input[name=bdSecretTitleTxt]").attr('disabled', false);
            }
        });
        $("input:radio[name=bdSecretTitleFl]:checked").trigger('click');

        //비밀댓글 제목설정
        $("input:radio[name=bdSecretReplyTitleFl]").bind('click', function () {
            $("input[name=bdSecretReplyTitle]").attr('disabled', true);
            if ($(this).val() == 1) {
                $("input[name=bdSecretReplyTitle]").attr('disabled', false);
            }
        });
        $("input:radio[name=bdSecretReplyTitleFl]:checked").trigger('click');

        var validId = function (id) {
            var regExp = /^[A-za-z][A-za-z0-9]{2,29}$/g;
            return regExp.test(id);
        }
        //이벤트
        $("input:radio[name=bdEndEventType]").bind('click', function () {
            $("input[name=bdEndEventMsg]").attr('disabled', false);
            if ($(this).val() == 'read') {
                $("input[name=bdEndEventMsg]").attr('disabled', true);
            }
        });
        $("input:radio[name=bdEndEventType]:checked").trigger('click');

        $(':radio[name="bdListImageFl"]').click(function () {
            var row = $('#trListImageSize');
            var noticeImage = $("#trListNoticeImage");

            var bdKind = $("input:radio[name=bdKind]:checked").val();

            if ($(this).val() == 'y') {
                row.show();
                if(bdKind === 'default' || bdKind === 'qa'){
                    noticeImage.show();
                }
            } else {
                row.hide();
                if(bdKind === 'default' || bdKind === 'qa'){
                    noticeImage.hide();
                }
            }
        });
        $(':radio[name="bdListImageFl"]:checked').trigger('click');

        $('input[name=bdIncludeReplayInSearchFl]').bind('click',function () {
            $('.js-bdIncludeReplayInSearchFl-show').hide()
            if($(this).val() == 'y'){
                $('.js-bdIncludeReplayInSearchFl-show').show();
            }
        })

        $(':radio[name=bdIncludeReplayInSearchFl]:checked').trigger('click')

        $.validator.addMethod("regx", function (value, element, fl) {
            return validId(value);
        }, "영문으로 시작해야하며 특수문자와 한글은 사용하실 수 없습니다.(2~30자)");
        // 폼검증
        $("#frmBoard").validate({
            ignore: ':hidden',
            submitHandler: function (form) {
                if ($(':radio[name="bdListImageFl"][value="y"]').prop('checked') && $('.js-bdListImageTarget-notice').css('display') != 'none') {
                    alert(this.settings.messages.bdListImageTarget.required);
                    return false;
                }
                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                oEditors.getById["editor2"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                form.target = 'ifrmProcess';
                form.submit();
            },

            // onclick: false, // <-- add this option
            rules: {
                bdId: {
                    required: true,
                    minlength: 2,
                    maxlength: 30,
                    regx: true,
                    equalTo: '#chkbdId',
                },
                bdUsePcFl: "required",
                bdUseMobileFl: "required",
                bdKind: "required",
                bdNm: 'required',
                themeSno: 'required',
                mobileThemeSno: 'required',
                bdAuthMemo: {
                    required: function () {
                        return $('input[name=bdMemoFl][value=y]').is(':checked');
                    }
                },
                bdAuthReply: {
                    required: function () {
                        return $('input[name=bdReplyFl][value=y]').is(':checked');
                    }
                },
                bdUploadMaxSize: {
                    required: "input:radio[name=bdUploadFl][value=y]:checked"
                },
                bdCategoryTitle: {
                    required: "input:checkbox[name=bdCategoryFl]:checked"
                },
                'bdCategory[]': {
                    required: "input:checkbox[name=bdCategoryFl]:checked"
                },
                bdSubjectLength: {
                    max: 255,
                    number: true,
                },
                bdGoodsFl: 'required',
                bdGoodsType: {
                    required: "input:radio[name=bdGoodsFl][value=y]:checked"
                },
                bdMileageAmount: {
                    required: "input:checkbox[name=bdMileageFl][value=y]:checked",
                },
                bdAttachImageMaxSize: {
                    required: "input[name=bdAttachImageDisplayFl][value=y]:checked",
                    number: "input[name=bdAttachImageDisplayFl][value=y]:checked",
                },
                bdListCount: {
                    required: true,
                    number: true,
                },
                bdListImageTarget: {
                    required: ":radio[name=bdListImageFl][value=y]:checked"
                },
                bdGoodsPageCountPc: {
                    required: true,
                    number: true,
                    min: 1,
                },
                bdGoodsPageCountMobile: {
                    required: true,
                    number: true,
                    min: 1,
                }
            },
            messages: {
                themeSno: {
                    required: "PC 쇼핑몰 스킨을 선택해주세요.",
                },
                mobileThemeSno: {
                    required: "모바일 쇼핑몰 스킨을 선택해주세요.",
                },
                bdAuthMemo: {
                    required: "댓글권한설정을 선택해주세요.",
                },
                bdAuthReply: {
                    required: "답변권한설정을 선택해주세요.",
                },
                bdKind: {
                    required: "유형을 선택하세요",
                },
                bdId: {
                    required: "아이디를 입력하세요.",
                    equalTo: '아이디 중복체크를 해주세요.',
                    minlength: '{0}자 이상입력해주세요.',
                    maxlength: '{0}자 이하로 입력해주세요.',
                },
                bdGoodsFl: {
                    required: "상품연동을 사용여부를 체크해주세요",
                },
                bdGoodsType: {
                    required: "상품/주문연동을 체크해주세요",
                },
                bdUsePcFl: {
                    required: "PC쇼핑몰 사용여부를 체크해주세요."
                },
                bdUseMobileFl: {
                    required: "모바일쇼핑몰 사용여부를 체크해주세요."
                },
                bdNm: {
                    required: "이름을 입력해주세요."
                },
                bdUploadMaxSize: {
                    required: "업로드 사이즈를 입려해주세요."
                },
                bdCategoryTitle: {
                    required: "말머리 타이틀을 입력해주세요."
                },
                'bdCategory[]': {
                    required: "말머리명을 입력해주세요."
                },
                bdSubjectLength: {
                    max: '{0} 까지 제한가능'
                },
                bdAttachImageMaxSize: {
                    required: '이미지 리사이즈값을 입력해주세요.',
                    number: '숫자를 입력해주세요.'
                },
                bdListImageTarget: {
                    required: "대표 이미지 설정이 상품연동/업로드파일사용/에디터 사용 중 1개 이상을 사용함으로 설정해야합니다."
                },
                bdGoodsPageCountPc: {
                    required: '상품상세 페이지 내 페이지별 게시물 수(PC)를 입력해 주세요.',
                    number: '상품상세 페이지 내 페이지별 게시물 수(PC)는 숫자로 입력해 주세요.',
                    min: '상품상세 페이지 내 페이지별 게시물 수(PC)는 0 이상 입력해 주세요.',
                },
                bdGoodsPageCountMobile: {
                    required: '상품상세 페이지 내 페이지별 게시물 수(모바일)를 입력해 주세요.',
                    number: '상품상세 페이지 내 페이지별 게시물 수(모바일)는 숫자로 입력해 주세요.',
                    min: '상품상세 페이지 내 페이지별 게시물 수(모바일)는 0 이상 입력해 주세요.',
                }
            },
        });

        $('.js-template-register').bind('click', function () {
            window.open('template_write.php?mode=popup&templateType=front', 'template', 'width=850,height=600');
        })

        // 답변관리 기능 사용 디폴트값은 체크해제(등록일경우)
        if(mode == 'regist'){
            $('input[name="bdKind"]').click(function () {
                $("input:checkbox[name='bdAnswerStatusFl']").prop("checked", false);
            });
        }
    });

    function add_category() {
        var select = $("#bdTemplateSno").html();
        var html = '<tr>' +
            '<td class="form-inline">' +
            '<input type="text" name="bdCategory[]" class="form-control js-add-field-category" size="40" value=""> ' +
            '<button type="button" class="btn btn-sm btn-white btn-icon-minus js-allow-category-minus">삭제</button>' +
            '</td>' +
            '<td>' +
            '<select class="form-control" id="bdCategoryTemplateSno[]" name="bdCategoryTemplateSno[]">' +
            select +
            '</select>' +
            '</td>' +
            '</tr>';
        $('.categoryTable').append(html);
        $("input[name='bdCategory[]']:last").focus();
    }

    function loadSkinSelectBox(bdKind, isMobile, liveSkin, domainFl) {
        $.ajax({
            method: 'get',
            url: 'board_ps.php',
            data: {'mode': 'selectListTheme', 'bdKind': bdKind, 'mobileFl': isMobile, 'liveSkin': liveSkin},
            dataType: 'json'
        }).success(function (data) {
            if (typeof domainFl != 'undefined') {
                var domainPostfix = (domainFl == 'kr') ? '' : domainFl;
                if (domainFl != '') {
                    domainPostfix = domainPostfix.substring(0, 1).toUpperCase() + domainPostfix.substring(1, domainPostfix.length).toLowerCase()
                }
            }
            else {
                domainPostfix = '';
            }

            var $themeSnoSelector = isMobile == 'y' ? $('select[name=mobileTheme' + domainPostfix + 'Sno]') : $('select[name=theme' + domainPostfix + 'Sno]');

            $themeSnoSelector.empty();
            $themeSnoSelector.append($('<option>', {value: 0, text: '스킨을 선택하세요.'}));
            for (var i = 0; i < data.list.length; i++) {
                $themeSnoSelector.append($('<option>', {value: data.list[i].sno, text: data.list[i].themeNm}));
            }
            if (data.selected == null) {
                $themeSnoSelector.index(0);
            }
            else {
                $themeSnoSelector.val(data.selected);
            }
        }).error(function (e) {
            console.log(e);
            alert(e);
        });

        /*$.ajax({
         method: 'get',
         url: 'board_ps.php',
         data: {'mode': 'selectListTheme', 'bdKind': val, 'mobileFl': 'n'},
         dataType: 'json'
         }).success(function (data) {
         $('select[name=themeSno]').empty();
         $('select[name=themeSno]').append($('<option>', {value: 0, text: '스킨을 선택하세요.'}));
         for (var i = 0; i < data.list.length; i++) {
         $('select[name=themeSno]').append($('<option>', {value: data.list[i].sno, text: data.list[i].themeNm}));
         }
         $('select[name=themeSno]').val(data.selected);
         }).error(function (e) {
         console.log(e);
         alert(e);
         });

         $.ajax({
         method: 'get',
         url: 'board_ps.php',
         data: {'mode': 'selectListTheme', 'bdKind': val, 'mobileFl': 'y'},
         dataType: 'json'
         }).success(function (data) {
         $('select[name=mobileThemeSno]').empty();
         $('select[name=mobileThemeSno]').append($('<option>', {value: 0, text: '스킨을 선택하세요.'}));
         for (var i = 0; i < data.list.length; i++) {
         $('select[name=mobileThemeSno]').append($('<option>', {value: data.list[i].sno, text: data.list[i].themeNm}));
         }
         $('select[name=mobileThemeSno]').val(data.selected);
         }).error(function (e) {
         console.log(e);
         alert(e);
         });*/
    }

    function presentExcept_conf(thisValue) {
        if ($('#presentFlExcept_' + thisValue + "_tbl").is(':hidden')) $('#presentFlExcept_' + thisValue + "_tbl").show();
        else  $('#presentFlExcept_' + thisValue + "_tbl").hide();
    }
    //-->
</script>
<div class="board-register">
    <form id="frmBoard" action="board_ps.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="mode" id="mode" value="<?= gd_isset($mode) ?>"/>
        <input type="hidden" name="sno" id="sno" value="<?= gd_isset($data['sno']) ?>"/>
        <?php if ($mode == 'modify') { ?>
            <input type="hidden" name="bdId" id="bdId" value="<?= gd_isset($data['bdId']) ?>"/>
        <?php } ?>
        <div class="page-header js-affix">
            <h3><?php echo end($naviMenu->location); ?>
                <small>커뮤니티 메뉴에서 서비스하는 게시판을 <?= gd_isset($modeTxt); ?>합니다.</small>
            </h3>
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>

        <div class="table-title gd-help-manual">기본설정</div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th class="require">PC쇼핑몰 사용여부</th>
                <td class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="bdUsePcFl" value="y" <?= $checked['bdUsePcFl']['y'] ?>/>사용
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="bdUsePcFl" value="n" <?= $checked['bdUsePcFl']['n'] ?> />사용안함
                    </label>
                    <div class="notice-info">
                        “사용 안함” 설정 시 쇼핑몰 회원(비회원포함)접근을 하지 못하도록 설정합니다.<br>
                        관리자에서 접근 시 사용여부 설정과 상관없이 접속이 가능합니다.
                    </div>
                </td>
            </tr>
            <tr>
                <th class="require">모바일쇼핑몰<br> 사용여부</th>
                <td class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="bdUseMobileFl" value="y" <?= $checked['bdUseMobileFl']['y'] ?>/>사용
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="bdUseMobileFl" value="n" <?= $checked['bdUseMobileFl']['n'] ?> />사용안함
                    </label>
                    <div class="notice-info">
                        “사용 안함” 설정 시 쇼핑몰 회원(비회원포함)접근을 하지 못하도록 설정합니다.<br>
                        관리자에서 접근 시 사용여부 설정과 상관없이 접속이 가능합니다.

                    </div>
                </td>
            </tr>
            <tr>
                <th class="require">유형</th>
                <td class="form-inline">
                    <?php
                    if ($data['bdKind'] == 'qa') {
                        unset($bdKindList['default'], $bdKindList['gallery'], $bdKindList['event']);
                    } elseif ($mode == 'modify') {
                        unset($bdKindList['qa']);
                    }
                    foreach ($bdKindList as $key => $val) {
                        ?>
                        <div class="pull-left" style="padding-right:15px">
                            <label class="radio-inline">
                                <input type='radio' name="bdKind"
                                       value="<?= $key ?>" <?= $checked['bdKind'][$key] ?> /><?= $val ?>
                                <div class="mgt10"><img src="<?= PATH_ADMIN_GD_SHARE ?>img/board/type_<?= $key ?>.png"></div>
                            </label>
                        </div>
                    <?php }

                    ?>
                </td>
            </tr>
            <tr>
                <th class="require">아이디</th>
                <td class="form-inline">
                    <input type="hidden" name="chkbdId" id="chkbdId" value="<?= gd_isset($data['bdId']); ?>"/>
                    <?php if ($mode == 'regist') { ?>
                        <input type="text" name="bdId" id="bdId" class="form-control" size="31" style="ime-mode:disabled"/>
                        <button type="button" id="overlap_bdId" class="btn btn-white btn-sm">중복확인</button>
                    <?php } else { ?>
                        <b><?= gd_isset($data['bdId']); ?></b>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th class="require">게시판명</th>
                <td class="form-inline">
                    <input type="text" name="bdNm" value="<?= gd_isset($data['bdNm']); ?>"
                           class="form-control" size="31" title="이름"/>
                </td>
            </tr>
            <?php if ($mode == 'modify') { ?>
                <tr>
                    <th>PC게시판 주소</th>
                    <td class="form-inline">
                        (쇼핑몰 주소) <?= $data['pageUrl'] ?>&nbsp;&nbsp;
                        <button type="button" data-clipboard-text="<?= $data['pageUrl'] ?>" class="js-clipboard btn btn-gray btn-sm" title="<?= $data['bdNm']; ?>">
                            복사하기
                        </button>
                    </td>
                </tr>
                <tr>
                    <th>모바일게시판 주소</th>
                    <td class="form-inline">
                        (쇼핑몰 주소) <?= $data['pageMobileUrl'] ?>&nbsp;&nbsp;
                        <button type="button" data-clipboard-text="<?= $data['pageMobileUrl'] ?>" class="js-clipboard btn btn-gray btn-sm"
                                title="<?= $data['bdNm']; ?>">
                            복사하기
                        </button>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th class="require">게시판 스킨</th>
                <td class="form-inline">

                    <table class="table table-cols">
                        <colgroup>
                            <col class="width10p">
                            <col class="width45p">
                            <col class="width45p">
                        </colgroup>
                        <tr>
                            <th>구분</th>
                            <th>사용중인 디자인 스킨</th>
                            <th>게시판 디자인 스킨 선택</th>
                        </tr>
                        <?php if (Globals::get('gSkin.frontSkinName')) { ?>
                            <?php if (\Globals::get('gGlobal.isUse')) { ?>

                                <?php foreach (\Globals::get('gGlobal.useMallList') as $key => $val) {
                                    $domainPostfix = $val['domainFl'] == 'kr' ? '' : ucfirst($val['domainFl']);
                                    ?>
                                    <tr>
                                        <?php if ($val['sno'] == 1) { ?>
                                            <th rowspan="<?= count(\Globals::get('gGlobal.useMallList')) ?>">PC 쇼핑몰</th>
                                        <?php } ?>
                                        <td><span class="flag flag-16 flag-<?= $val['domainFl'] ?>"></span> <?= $val['skin']['frontLive'] ?></td>
                                        <td>
                                            <select name="theme<?= $domainPostfix ?>Sno" id="theme<?= $domainPostfix ?>Sno" class="form-control">
                                                <option value="">선택해주세요</option>
                                                <?php //if ($mode == 'modify') {
                                                foreach ($selected['frontThemeList'][$val['domainFl']] as $row) {
                                                    ?>
                                                    <option value="<?= $row['sno'] ?>"
                                                            data-basic="<?= $row['bdBasicFl'] ?>" <?php if ($row['sno'] == $data['theme' . $domainPostfix . 'Sno']) echo 'selected' ?>><?= $row['themeNm'] ?>
                                                        (<?= $row['themeId'] ?>)
                                                    </option>
                                                <?php }
                                                //  } ?>
                                            </select>

                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <th>PC 쇼핑몰</th>
                                    <td>
                                        <?= Globals::get('gSkin.frontSkinName') ?>
                                    </td>
                                    <td>
                                        <select name="themeSno" id="themeSno" class="form-control">
                                            <option value="">선택해주세요</option>
                                            <?php //if ($mode == 'modify') {
                                            foreach ($selected['frontThemeList'] as $row) { ?>
                                                <option data-basic="<?= $row['bdBasicFl'] ?>"
                                                        value="<?= $row['sno'] ?>" <?php if ($row['sno'] == $data['themeSno']) echo 'selected' ?>><?= $row['themeNm'] ?>
                                                    (<?= $row['themeId'] ?>)
                                                </option>
                                            <?php }
                                            //    } ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                        <?php if (Globals::get('gSkin.mobileSkinName')) { ?>
                            <?php if (\Globals::get('gGlobal.isUse')) { ?>

                                <?php foreach (\Globals::get('gGlobal.useMallList') as $key => $val) {
                                    $domainPostfix = $val['domainFl'] == 'kr' ? '' : ucfirst($val['domainFl']);
                                    ?>
                                    <tr>
                                        <?php if ($val['sno'] == 1) { ?>
                                            <th rowspan="<?= count(\Globals::get('gGlobal.useMallList')) ?>">모바일 쇼핑몰</th>
                                        <?php } ?>
                                        <td><span class="flag flag-16 flag-<?= $val['domainFl'] ?>"></span> <?= $val['skin']['mobileLive'] ?></td>
                                        <td>
                                            <select name="mobileTheme<?= $domainPostfix ?>Sno" id="mobileTheme<?= $domainPostfix ?>Sno" class="form-control">
                                                <option value="">선택해주세요</option>
                                                <?php //if ($mode == 'modify') {
                                                foreach ($selected['mobileThemeList'][$val['domainFl']] as $row) { ?>
                                                    <option value="<?= $row['sno'] ?>" <?php if ($row['sno'] == $data['mobileTheme' . $domainPostfix . 'Sno']) echo 'selected' ?>><?= $row['themeNm'] ?>
                                                        (<?= $row['themeId'] ?>)
                                                    </option>
                                                <?php }
                                                //   } ?>
                                            </select>

                                        </td>
                                    </tr>
                                <?php } ?>


                            <?php } else { ?>
                                <tr>
                                    <th>모바일 쇼핑몰</th>
                                    <td>
                                        <?= Globals::get('gSkin.mobileSkinName') ?>
                                    </td>
                                    <td>
                                        <select name="mobileThemeSno" id="mobileThemeSno" class="form-control">
                                            <option value="">선택해주세요</option>
                                            <?php //if ($mode == 'modify') {
                                            foreach ($selected['mobileThemeList'] as $row) { ?>
                                                <option value="<?= $row['sno'] ?>" <?php if ($row['sno'] == $data['mobileThemeSno']) echo 'selected' ?>><?= $row['themeNm'] ?>
                                                    (<?= $row['themeId'] ?>)
                                                </option>
                                            <?php }
                                            //  } ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </table>

                    <button class="btn btn-white btn-sm pull-right" onclick="addSkin()" type="button">게시판 스킨등록</button>
                    <div class="notice-info">
                        ※ 쇼핑몰 디자인 사용스킨 변경 시 이전에 생성한 게시판 스킨은 사용할 수 없습니다.<br>
                        사용 스킨 변경 시 게시판 스킨을 다시한번 확인하시기 바랍니다.
                    </div>
                </td>
            </tr>

            <tr>
                <th>리스트권한 설정</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="bdAuthList" value="all" <?= $checked['bdAuthList']['all'] ?>>전체(회원+비회원)</label>
                    <label class="radio-inline"><input type="radio" name="bdAuthList" value="admin" <?= $checked['bdAuthList']['admin'] ?>>관리자 전용</label>
                    <label class="radio-inline"><input type="radio" name="bdAuthList" value="member" <?= $checked['bdAuthList']['member'] ?>>회원전용(비회원제외)</label>
                    <label class="radio-inline"><input type="radio" name="bdAuthList" value="group" <?= $checked['bdAuthList']['group'] ?>
                                                       onclick="layer_register('member_groupLayer_list','bdAuthListGroup','info_member_list_group')">특정회원등급</label>
                    <label>
                        <button type="button" class="btn btn-sm btn-gray js-group-select">회원등급 선택</button>
                    </label>
                    <div id="member_groupLayer_list" class="selected-btn-group <?= is_array($data['bdAuthListGroup']) ? 'active' : '' ?>">
                        <?php if (is_array($data['bdAuthListGroup'])) { ?>
                            <h5>선택된 회원등급</h5>
                            <?php foreach ($data['bdAuthListGroup'] as $k => $v) { ?>
                                <span id="info_member_list_group_<?= $k ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="bdAuthListGroup[]" value="<?= $k ?>"/>
                                <span class="btn"><?= $v ?></span>
                                <button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#info_member_list_group_<?= $k ?>">삭제</button>
                            </span>
                            <?php }
                        } ?>
                    </div>
                </td>
            </tr>

            <tr>
                <th>읽기권한 설정</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="bdAuthRead" value="all" <?= $checked['bdAuthRead']['all'] ?>>전체(회원+비회원)</label>
                    <label class="radio-inline"><input type="radio" name="bdAuthRead" value="admin" <?= $checked['bdAuthRead']['admin'] ?>>관리자 전용</label>
                    <label class="radio-inline"><input type="radio" name="bdAuthRead" value="member" <?= $checked['bdAuthRead']['member'] ?>>회원전용(비회원제외)</label>
                    <label class="radio-inline"><input type="radio" name="bdAuthRead" value="group" <?= $checked['bdAuthRead']['group'] ?>
                                                       onclick="layer_register('member_groupLayer_read','bdAuthReadGroup','info_member_read_group')">특정회원등급</label>
                    <label>
                        <button type="button" class="btn btn-sm btn-gray js-group-select">회원등급 선택</button>
                    </label>
                    <div id="member_groupLayer_read" class="selected-btn-group <?= is_array($data['bdAuthReadGroup']) ? 'active' : '' ?>">
                        <?php if (is_array($data['bdAuthReadGroup'])) { ?>
                            <h5>선택된 회원등급</h5>
                            <?php foreach ($data['bdAuthReadGroup'] as $k => $v) { ?>
                                <span id="info_member_read_group_<?= $k ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="bdAuthReadGroup[]" value="<?= $k ?>"/>
                                <span class="btn"><?= $v ?></span>
                                <button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#info_member_read_group_<?= $k ?>">삭제</button>
                            </span>
                            <?php }
                        } ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>쓰기권한 설정</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="bdAuthWrite" value="all"
                                                       class="if-is-event-disabled" <?= $checked['bdAuthWrite']['all'] ?>>전체(회원+비회원)</label>
                    <label class="radio-inline"><input type="radio" name="bdAuthWrite" value="admin" class="if-is-event-checked" <?= $checked['bdAuthWrite']['admin'] ?>>관리자
                        전용</label>
                    <label class="radio-inline"><input type="radio" name="bdAuthWrite" value="member" class="if-is-event-disabled" <?= $checked['bdAuthWrite']['member'] ?>>회원전용(비회원제외)</label>
                    <label class="radio-inline"><input type="radio" name="bdAuthWrite" value="group" class="if-is-event-disabled" <?= $checked['bdAuthWrite']['group'] ?>
                                                       onclick="layer_register('member_groupLayer_write','bdAuthWriteGroup','info_member_write_group')">특정회원등급</label>
                    <label>
                        <button type="button" class="btn btn-sm btn-gray js-group-select">회원등급 선택</button>
                    </label>
                    <div id="member_groupLayer_write" class="selected-btn-group <?= is_array($data['bdAuthWriteGroup']) ? 'active' : '' ?>">
                        <?php if (is_array($data['bdAuthWriteGroup'])) { ?>
                            <h5>선택된 회원등급</h5>
                            <?php foreach ($data['bdAuthWriteGroup'] as $k => $v) { ?>
                                <span id="info_member_write_group_<?= $k ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="bdAuthWriteGroup[]" value="<?= $k ?>"/>
                                <span class="btn"><?= $v ?></span>
                                <button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#info_member_write_group_<?= $k ?>">삭제</button>
                            </span>
                            <?php }
                        } ?>
                    </div>
                </td>
            </tr>
            <?php if($isGoodsReview) {?>
                <tr>
                    <th>쓰기권한 추가 기준</th>
                    <td >
                        <div>
                            <label>
                                <input type="radio" name="bdReviewAuthWrite" value="all" <?= $checked['bdReviewAuthWrite']['all'] ?>>구매 여부와 상관없이 후기 작성 가능
                            </label>
                        </div>
                        <div class="pdt10">
                            <label >
                                <input type="radio" name="bdReviewAuthWrite" value="buyer" <?= $checked['bdReviewAuthWrite']['buyer'] ?>>구매 내역이 존재하는 경우에만 후기 작성 가능
                            </label>
                            ( 작성 가능 시점 :
                            <select name="bdReviewOrderStatus" style="width:120px !important;">
                                <option value="p1" <?=$selected['bdReviewOrderStatus']['p1']?>>결제 완료 이후</option>
                                <option value="d1" <?=$selected['bdReviewOrderStatus']['d1']?>>배송중 이후</option>
                                <option value="d2" <?=$selected['bdReviewOrderStatus']['d2']?>>배송완료 이후</option>
                                <option value="s1" <?=$selected['bdReviewOrderStatus']['s1']?>>구매확정 이후</option>
                            </select>
                            )

                            <div style="padding-left:20px">
                                <label><input type="checkbox" name="bdReviewPeriodFl" value="y" <?=$checked['bdReviewPeriodFl']?>>
                                    작성 가능 시점으로부터
                                    <select name="bdReviewPeriod" style="width:50px !important;">
                                        <?php foreach($periodDay as $val) {?>
                                            <option value="<?=$val?>" <?=$selected['bdReviewPeriod'][$val]?>><?=$val?>일</option>
                                        <?php }?>
                                    </select>
                                    이내만 후기 작성 가능.</div>
                            </label>
                        </div>
                        <div class="notice-info"> 설정에 따른 게시글 작성 가능 여부는 하단 안내 영역을 참고해주세요.</div>
                    </td>
                </tr>
            <?php }?>
            <tr class="if-is-event-hide">
                <th>답변 기능</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="bdReplyFl" value="y" <?= gd_isset($checked['bdReplyFl']['y']) ?> class="if-is-event-disabled">사용</label>
                    <label class="checkbox-inline">(<input type="checkbox" name="bdAnswerStatusFl" value="y" <?=$checked['bdAnswerStatusFl']?>>답변관리 기능 사용)</label>
                    <label class="radio-inline"><input type="radio" name="bdReplyFl" value="n" <?= gd_isset($checked['bdReplyFl']['n']) ?> class="if-is-event-checked">사용안함</label>
                    <div class="notice-info js-answer-status">답변관리 기능 사용 시 운영자 작성 답변글은 답변완료인 글만 쇼핑몰에 노출되며, 사용 전에 작성된 답변글은 답변완료로 처리 시 노출됩니다.</div>
                </td>
            </tr>
            <tr class="if-is-event-hide">
                <th>답변권한 설정</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="bdAuthReply" value="all" <?= $checked['bdAuthReply']['all'] ?>>전체(회원+비회원)</label>
                    <label class="radio-inline"><input type="radio" name="bdAuthReply" value="admin" <?= $checked['bdAuthReply']['admin'] ?>>관리자 전용</label>
                    <label class="radio-inline"><input type="radio" name="bdAuthReply" value="member" <?= $checked['bdAuthReply']['member'] ?>>회원전용(비회원제외)</label>
                    <label class="radio-inline"><input type="radio" name="bdAuthReply" value="group" <?= $checked['bdAuthReply']['group'] ?>
                                                       onclick="layer_register('member_groupLayer_reply','bdAuthReplyGroup','info_member_reply_group')">특정회원등급</label>
                    <label>
                        <button type="button" class="btn btn-sm btn-gray js-group-select">회원등급 선택</button>
                    </label>
                    <div id="member_groupLayer_reply" class="selected-btn-group <?= is_array($data['bdAuthReplyGroup']) ? 'active' : '' ?>">
                        <?php if (is_array($data['bdAuthReplyGroup'])) { ?>
                            <h5>선택된 회원등급</h5>
                            <?php foreach ($data['bdAuthReplyGroup'] as $k => $v) { ?>
                                <span id="info_member_reply_group_<?= $k ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="bdAuthReplyGroup[]" value="<?= $k ?>"/>
                                <span class="btn"><?= $v ?></span>
                                <button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#info_member_reaply_group_<?= $k ?>">삭제</button>
                            </span>
                            <?php }
                        } ?>
                    </div>
                </td>
            </tr>
            <tr class="if-is-qa-hide">
                <th>댓글 기능</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="bdMemoFl" id="bdMemoFl" class="if-is-qa-disabled"
                                                       value="y" <?= gd_isset($checked['bdMemoFl']['y']) ?> /> 사용</label>
                    <label class="radio-inline"><input type="radio" name="bdMemoFl" id="bdMemoFl" class="if-is-qa-disabled"
                                                       value="n" <?= gd_isset($checked['bdMemoFl']['n']) ?> /> 사용안함</label>
                </td>
            </tr>
            <tr class="if-is-qa-hide">
                <th>댓글권한 설정</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="bdAuthMemo" value="all" <?= $checked['bdAuthMemo']['all'] ?>>전체(회원+비회원)</label>
                    <label class="radio-inline"><input type="radio" name="bdAuthMemo" value="admin" <?= $checked['bdAuthMemo']['admin'] ?>>관리자 전용</label>
                    <label class="radio-inline"><input type="radio" name="bdAuthMemo" value="member" <?= $checked['bdAuthMemo']['member'] ?>>회원전용(비회원제외)</label>
                    <label class="radio-inline"><input type="radio" name="bdAuthMemo" value="group" <?= $checked['bdAuthMemo']['group'] ?>
                                                       onclick="layer_register('member_groupLayer_memo','bdAuthMemoGroup','info_member_memo_group')">특정회원등급</label>
                    <label>
                        <button type="button" class="btn btn-sm btn-gray js-group-select">회원등급 선택</button>
                    </label>
                    <div id="member_groupLayer_memo" class="selected-btn-group <?= is_array($data['bdAuthMemoGroup']) ? 'active' : '' ?>">
                        <?php if (is_array($data['bdAuthMemoGroup'])) { ?>
                            <h5>선택된 회원등급</h5>
                            <?php foreach ($data['bdAuthMemoGroup'] as $k => $v) { ?>
                                <span id="info_member_memo_group_<?= $k ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="bdAuthMemoGroup[]" value="<?= $k ?>"/>
                                <span class="btn"><?= $v ?></span>
                                <button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#info_member_memo_group_<?= $k ?>">삭제</button>
                            </span>
                            <?php }
                        } ?>
                    </div>
                </td>
            </tr>

            <tr>
                <th>
                    작성자 표시방법
                </th>
                <td>
                    <label class="radio-inline"><input type="radio" name="bdUserDsp"
                                                       value="name" <?= gd_isset($checked['bdUserDsp']['name']) ?> /> 이름표시</label>
                    <label class="radio-inline"><input type="radio" name="bdUserDsp"
                                                       value="nick" <?= gd_isset($checked['bdUserDsp']['nick']) ?> /> 닉네임표시</label>
                    <label class="radio-inline"><input type="radio" name="bdUserDsp"
                                                       value="id" <?= gd_isset($checked['bdUserDsp']['id']) ?> /> 아이디표시</label>
                </td>
            </tr>

            <tr>
                <th>
                    작성자 노출제한
                </th>
                <td>
                    <select name="bdUserLimitDsp" class="form-control">
                        <option value="0" <?= gd_isset($selected['bdUserLimitDsp'][0]) ?>>전체노출</option>
                        <option value="1" <?= gd_isset($selected['bdUserLimitDsp'][1]) ?>>1글자 노출</option>
                        <option value="2" <?= gd_isset($selected['bdUserLimitDsp'][2]) ?>>2글자 노출</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th>운영자 표시방법</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="bdAdminDsp"
                               value="nick" <?= gd_isset($checked['bdAdminDsp']['nick']) ?> /> 닉네임표시</label>
                    <label class="radio-inline">
                        <input type="radio" name="bdAdminDsp"
                               value="image" <?= gd_isset($checked['bdAdminDsp']['image']) ?> /> 이미지표시
                    </label>
                </td>
            </tr>
            <?php if (gd_use_provider() === true) { ?>
                <tr>
                    <th>공급사 표시방법</th>
                    <td>
                        <label class="radio-inline"><input type="radio" name="bdSupplyDsp"
                                                           value="nick" <?= gd_isset($checked['bdSupplyDsp']['nick']) ?> /> 닉네임표시</label>
                        <label class="radio-inline"><input type="radio" name="bdSupplyDsp"
                                                           value="image" <?= gd_isset($checked['bdSupplyDsp']['image']) ?> /> 이미지표시
                        </label>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th>저장 위치</th>
                <td class="form-inline">
                    <select name="bdUploadStorage" id="bdUploadStorage" class="form-control">
                        <?php
                        foreach ($storageBox as $key => $val) {
                            $selected = '';
                            if ($key == gd_isset($data['bdUploadStorage'])) {
                                $selected = ' selected="selected" ';
                            }
                            echo '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>';
                        }
                        ?>
                    </select>

                    <div class="mgt5">
                        파일 저장 위치 :
                        <span id="spanFileStorage">

                        </span>

                        <input type="text" name="bdUploadPath" id="bdUploadPath" size="30"
                               value="<?= gd_isset($data['bdUploadPath']) ?>" readonly="readonly"
                               class="form-control"/>
                    </div>
                    <div class="mgt5">
                        썸네일 저장위치
                        :
                        <span id="spanFileThumbStorage">
                        </span>

                        <input type="text" name="bdUploadThumbPath" id="bdUploadThumbPath" size="30"
                               value="<?= gd_isset($data['bdUploadThumbPath']) ?>" readonly="readonly"
                               class="form-control"/>
                    </div>
                </td>
            </tr>

            <tr class="bdReplyFl-area <?php if(gd_isset($checked['bdReplyFl']['n']) || $data['bdKind'] === 'qa'){ echo 'display-none'; } ?>">
                <th>
                    게시글 삭제 설정
                </th>
                <td>
                    <label class="radio-inline"><input type="radio" name="bdReplyDelFl"
                                                       value="applicable" <?= gd_isset($checked['bdReplyDelFl']['applicable']) ?> /> 답변글이 있는 게시글 삭제시, 해당 글만 삭제</label><br>
                    <label class="radio-inline"><input type="radio" name="bdReplyDelFl"
                                                       value="reply" <?= gd_isset($checked['bdReplyDelFl']['reply']) ?> /> 답변글이 있는 게시글 삭제시, 답변글도 함께 삭제</label><br>
                    <div class="notice-info">답변글이 있는 게시글 삭제시, 삭제하고자 하는 게시글 하위의 모든 답변글이 함께 삭제됩니다.</div>
                </td>
            </tr>


            <tr>
                <th>마일리지 사용유무</th>
                <td>
                    <label class="radio-inline"><input name="bdMileageFl" type="radio"
                                                       value='y' <?= gd_isset($checked['bdMileageFl']['y']) ?> />
                        사용</label>
                    <label class="radio-inline">
                        <input name="bdMileageFl" type="radio"
                               value='n' <?= gd_isset($checked['bdMileageFl']['n']) ?> />
                        사용안함
                    </label>
                </td>
            </tr>

            <tr class="bdMileageFl-area">
                <th>마일리지 지급</th>
                <td class="form-inline">
                    게시글 작성 시
                    <input type="text" class="form-control js-number" name="bdMileageAmount"
                           value="<?= gd_isset($data['bdMileageAmount']) ?>" maxlength="8"/>원 지급
                    <div style="padding-top: 10px;" class="bdReplyMileageFl-area <?php if(gd_isset($checked['bdReplyFl']['n']) || $data['bdKind'] === 'event' || $data['bdKind'] === 'qa'){ echo 'display-none'; } ?>">
                        <label class="checkbox-inline"><input type="checkbox" name="bdReplyMileageFl"
                                                              value='y' <?= gd_isset($checked['bdReplyMileageFl']['y']) ?> /> 답변글 작성 시에도 마일리지 지급
                        </label>
                        <div class="notice-info">해당 기능 사용시 답변의 답변글 작성 시에도 마일리지가 지급됩니다.</div>
                    </div>
                </td>
            </tr>
            <tr class="bdMileageFl-area">
                <th>게시글 삭제 시 <br/> 마일리지 차감</th>
                <td>
                    <label class="radio-inline">
                        <input name="bdMileageDeleteFl" type="radio"
                               value='y' <?= gd_isset($checked['bdMileageDeleteFl']['y']) ?> />
                        사용함
                    </label>
                    <label class="radio-inline">
                        <input name="bdMileageDeleteFl" type="radio"
                               value='n' <?= gd_isset($checked['bdMileageDeleteFl']['n']) ?> />
                        사용안함
                    </label>
                    <div class="notice-info">해당 기능은 삭제한 게시글에 대한 마일리지만을 차감하는 기능이며, 답변글에 지급된 마일리지는 수기로 관리하셔야 합니다.</div>
                </td>
            </tr>
            <tr class="bdMileageFl-area">
                <th>차감 마일리지 <br/>부족 시 처리방법</th>
                <td>
                    <label class="radio-inline">
                        <input name="bdMileageLackAction" type="radio"
                               value='delete' <?= gd_isset($checked['bdMileageLackAction']['delete']) ?> />
                        마이너스 차감 후 게시글 삭제
                    </label>
                    <label class="radio-inline">
                        <input name="bdMileageLackAction" type="radio"
                               value='nodelete' <?= gd_isset($checked['bdMileageLackAction']['nodelete']) ?> />
                        게시글 삭제 불가
                    </label>
                </td>
            </tr>
        </table>


        <div class="table-title gd-help-manual">기능설정</div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>
                    상품 연동
                </th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="bdGoodsFl" value="y" <?= gd_isset($checked['bdGoodsFl']['y']) ?> /> 사용</label>
                    <?php if ($goodsBoard != 'y') { ?>
                        <label class="radio-inline">
                            <input type="radio" name="bdGoodsFl" value="n" <?= gd_isset($checked['bdGoodsFl']['n']) ?> <?= gd_isset($disabled['bdGoodsFl']['n']) ?>/> 사용안함</label>
                    <?php } ?>
                    <table class="table table-cols mgt10">
                        <tr>
                            <th class="width-sm">상품/주문연동</th>
                            <td>
                                <label class="radio-inline">
                                    <input type="radio" name="bdGoodsType"
                                           value="goods" <?= gd_isset($checked['bdGoodsType']['goods']) ?>  <?=$disabled['bdGoodsType']['goods']?> /> 상품
                                </label>
                                <?php if(!$isGoodsQa) {?>
                                    <label class="radio-inline">
                                        <input type="radio" name="bdGoodsType"
                                               value="order" <?= gd_isset($checked['bdGoodsType']['order']) ?> <?= gd_isset($disabled['bdGoodsType']['order']) ?>/> 주문상품
                                    </label>
                                <?php }?>
                                <?php if($isGoodsReview === false) {?>
                                    <label class="checkbox-inline js-bdGoodsTypeOrderDuplication display-none">
                                        <input type="checkbox" name="bdGoodsTypeOrderDuplication"
                                               value="y" <?= gd_isset($checked['bdGoodsType']['orderDuplication']) ?> <?= gd_isset($disabled['bdGoodsTypeOrderDuplication']) ?>/> 주문내역
                                        중복 허용
                                    </label>
                                    <br><br>
                                <?php }?>
                                <div class="notice-info">
                                    ※ 상품 / 주문상품 연동 사용 시 등록된 상품 또는 주문상품 중 1개를 선택하여 게시글을 작성 할 수 있습니다. (단 주문상품에서 입금대기는 제외됩니다.)
                                </div>
                                <div class="notice-info">
                                    ※ 주문내역 중복 사용 체크 시 다른 게시글에 연동하여 등록한 주문 상품을 신규 게시글 등록 시 다시 선택할 수 있습니다.<br>
                                    &nbsp&nbsp&nbsp미체크 시 해당 게시판에 주문상품 연동은 1회로 제한됩니다.
                                </div>
                                <div class="notice-info">
                                    ※ 상품문의 게시판은 상품만 설정 가능합니다.
                                </div>
                                <div class="notice-info">
                                    ※ 상품후기 게시판은 쓰기권한 추가 기준의 설정값에 따라 "상품", "주문상품" 설정값이 결정됩니다.
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <?php if($isGoodsReview) {?>
                <tr>
                    <th>
                        중복작성 제한
                    </th>
                    <td>
                        <div>
                            <label>
                                <input type="radio" name="bdReviewDuplicateLimit" value="free" <?= $checked['bdReviewDuplicateLimit']['free'] ?>>제한없음
                            </label>
                        </div>
                        <div class="pdt10">
                            <label >
                                <input type="radio" name="bdReviewDuplicateLimit" value="one" <?= $checked['bdReviewDuplicateLimit']['one'] ?>>
                                1회만 작성 가능하도록 제한
                            </label>
                        </div>
                        <div class="notice-info">설정에 따른 게시글 작성 가능 여부는 하단 안내 영역을 참고해주세요.</div>
                    </td>
                </tr>
                <tr>
                    <th>
                        후기작성 예외상품
                    </th>
                    <td>
                     <span id="presentFlExcept_goods">
                         <label class="checkbox-inline"><input type="checkbox" <?=$checked['bdReviewExceptGoodsData']?> name="presentExceptFl" value="goods" onclick="presentExcept_conf(this.value)">예외
                    상품</label></span>

                    </td>
                </tr>
                <tr id="presentFlExcept_goods_tbl" <?php if(empty($data['bdReviewExceptGoodsData'])){?> style="display:none" <?php }?>>
                    <th>예외 상품
                        <div><input type="button" class="btn btn-sm btn-gray" value="상품 선택" onclick="layer_except_register('goods','except');"/></div>
                    </th>
                    <td>

                        <table id="exceptGoodsTable" class="table table-cols" style="width:80%">
                            <thead <?php if (is_array($data['bdReviewExceptGoodsData']) == false) {
                                echo "style='display:none'";
                            } ?>>
                            <tr>
                                <th class="width5p">번호</th>
                                <th class="width10p">이미지</th>
                                <th>상품명</th>
                                <th class="width8p">삭제</th>
                            </tr>
                            </thead>
                            <tbody id="exceptGoods">
                            <?php
                            if (is_array($data['bdReviewExceptGoodsData'])) {
                                foreach ($data['bdReviewExceptGoodsData'] as $key => $val) {
                                    echo '<tr id="idExceptGoods_' . $val['goodsNo'] . '">' . chr(10);
                                    echo '<td>' . ($key + 1) . '<input type="hidden" name="exceptGoods[]" value="' . $val['goodsNo'] . '" /></td>' . chr(10);
                                    echo '<td>' . gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank') . '</td>' . chr(10);
                                    echo '<td><a href="../goods/goods_register.php?goodsNo=' . $val['goodsNo'] . '" target="_blank">' . $val['goodsNm'] . '</a></td>' . chr(10);
                                    echo '<td><input type="button" class="btn btn-gray btn-sm" onclick="field_remove(\'idExceptGoods_' . $val['goodsNo'] . '\');" value="삭제" /></td>' . chr(10);
                                    echo '</tr>' . chr(10);
                                }
                            }
                            ?>
                            </tbody>
                            <tfoot <?php if (is_array($data['bdReviewExceptGoodsData']) == false) {
                                echo "style='display:none'";
                            } ?>>
                            <tr>
                                <td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptGoods').html('');"></td>
                            </tr>
                            </tfoot>
                        </table>

                    </td>
                </tr>
            <?php }?>
            <tr class="if-is-gallery_default-show">
                <th>
                    게시글 작성 시 별점
                </th>
                <td>
                    <label class="radio-inline"><input type="radio" name="bdGoodsPtFl"
                                                       value="y" <?= gd_isset($checked['bdGoodsPtFl']['y']) ?> /> 사용</label>
                    <label class="radio-inline"><input type="radio" name="bdGoodsPtFl"
                                                       value="n" <?= gd_isset($checked['bdGoodsPtFl']['n']) ?> /> 사용안함</label>
                </td>
            </tr>
            <tr>
                <th>
                    게시글 추천
                </th>
                <td>
                    <label class="radio-inline"><input type="radio" name="bdRecommendFl"
                                                       value="y" <?= gd_isset($checked['bdRecommendFl']['y']) ?> /> 사용</label>
                    <label class="radio-inline"><input type="radio" name="bdRecommendFl"
                                                       value="n" <?= gd_isset($checked['bdRecommendFl']['n']) ?> /> 사용안함</label>
                </td>
            </tr>

            <tr>
                <th>기본 게시글 양식 설정</th>
                <td class="form-inline">
                    <?= gd_select_box('bdTemplateSno', 'bdTemplateSno', $templateList, null, $data['bdTemplateSno']); ?>
                    <input type="button" value="게시글 양식 등록" class="btn btn-black js-template-register">
                    <div class="notice-info">에디터 미사용 시 쇼핑몰 게시글 양식은 텍스트만 노출됩니다.</div>

                </td>
            </tr>

            <tr>
                <th>말머리 기능</th>
                <td>
                    <div class="useCateLbl">
                        <div class="checkbox">
                            <label class="checkbox-inline"><input type="checkbox" name="bdCategoryFl"
                                                                  value='y' <?= gd_isset($checked['bdCategoryFl']) ?> /> 말머리 사용
                                <a class="btn-link">글작성시 제목앞에 특정단어를 넣는 기능입니다</a>
                            </label>
                            <div class="notice-info">말머리 게시글 양식 설정: 말머리 선택에 따른 게시글 양식을 설정할 수 있습니다. 기본 게시글 양식과 중복 사용이 가능하되, 말머리에 설정한
                                게시글 양식이 있는 경우 말머리 게시글 양식이 우선으로 노출됩니다.
                            </div>
                        </div>
                        <div class="categoryWrite" style="display:none">
                            <table class="table table-cols">
                                <colgroup>
                                    <col class="width-sm"/>
                                    <col/>
                                </colgroup>
                                <tr>
                                    <th>말머리 타이틀</th>
                                    <td>
                                        <input type="text" name="bdCategoryTitle" class="form-control" value="<?= gd_isset($data['bdCategoryTitle']) ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        말머리 입력
                                    </th>
                                    <td>
                                        <table class="table table-cols categoryTable">
                                            <colgroup>
                                                <col class="width-2xl"/>
                                                <col/>
                                            </colgroup>
                                            <tr>
                                                <th>말머리명</th>
                                                <th>말머리 게시글 양식 설정</th>
                                            </tr>
                                            <?php
                                            $bdCategory = explode(STR_DIVISION, gd_isset($data['bdCategory']));
                                            $bdCategoryTemplateSno = explode(STR_DIVISION, gd_isset($data['bdCategoryTemplateSno']));
                                            for ($i = 0; $i < $data['bdCategoryCount']; $i++) {
                                                ?>
                                                <tr>
                                                    <td class="form-inline">
                                                        <input type="text" name="bdCategory[]" class="form-control js-add-field-category" size="40" value="<?= $bdCategory[$i] ?>"/>
                                                        <?php if($i == 0) { ?>
                                                            <button type="button" class="btn btn-sm btn-white btn-icon-plus" onclick="add_category();">추가</button>
                                                        <?php } else { ?>
                                                            <button type="button" class="btn btn-sm btn-white btn-icon-minus js-allow-category-minus">삭제</button>
                                                        <?php } ?>
                                                    </td>
                                                    <td>
                                                        <?= gd_select_box('bdCategoryTemplateSno[]', 'bdCategoryTemplateSno[]', $templateList, null, $bdCategoryTemplateSno[$i]); ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>조회수 표시 설정</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="checkbox" name="bdPcHitFl" value="y" <?= gd_isset($checked['bdPcHitFl']['y']) ?>> PC쇼핑몰</label>
                    <label class="radio-inline"><input type="checkbox" name="bdMobileHitFl" value="y" <?= gd_isset($checked['bdMobileHitFl']['y']) ?> <?=gd_isset($disabled['bdMobileShopFl']['y'])?>> 모바일쇼핑몰</label>
                    <div class="notice-info">
                        PC쇼핑몰, 모바일쇼핑몰 전체 체크 시, PC 및 모바일쇼핑몰의 리스트 및 상세 영역의 조회수가 노출됩니다.
                    </div>
                </td>
            </tr>
            <tr>
                <th class="require">조회당 Hit증가수</th>
                <td class="form-inline">
                    <input type="text" name="bdHitPerCnt" size="6" value="<?= gd_isset($data['bdHitPerCnt']) ?>"
                           class="form-control js-number wd-sm2"/> 개
                    <label class="checkbox-inline mgl8">
                        <input type="checkbox" name="bdHitIPCheck" value="y" <?= gd_isset($checked['bdHitIPCheck']['y']) ?> />IP 중복제한
                    </label>
                </td>
            </tr>
            <tr>
                <th>비밀글 설정</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="bdSecretFl"
                                                       value="0" <?= gd_isset($checked['bdSecretFl'][0]) ?> />
                        작성시 기본 일반글</label>
                    <label class="radio-inline"><input type="radio" name="bdSecretFl"
                                                       value="1" <?= gd_isset($checked['bdSecretFl'][1]) ?> />
                        작성시 기본 비밀글</label>
                    <label class="radio-inline"><input type="radio" name="bdSecretFl"
                                                       value="2" <?= gd_isset($checked['bdSecretFl'][2]) ?> />
                        무조건 일반글</label>
                    <label class="radio-inline"><input type="radio" name="bdSecretFl"
                                                       value="3" <?= gd_isset($checked['bdSecretFl'][3]) ?> />
                        무조건 비밀글</label>
                </td>
            </tr>
            <tr>
                <th class="require">비밀글 제목설정</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="bdSecretTitleFl"
                                                       value="0" <?= gd_isset($checked['bdSecretTitleFl'][0]) ?> /> 제목 노출</label>
                    <label class="radio-inline"><input type="radio" name="bdSecretTitleFl"
                                                       value="1" <?= gd_isset($checked['bdSecretTitleFl'][1]) ?> /> 제목 지정</label>
                    <input type="text" name="bdSecretTitleTxt" value="<?= gd_isset($data['bdSecretTitleTxt']) ?>"
                           class="form-control" size="40"/>
                </td>
            </tr>
            <tr class="bdSecretReply if-is-qa-hide">
                <th class="require">비밀댓글 설정</th>
                <td class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="bdSecretReplyFl" value="0" <?= gd_isset($checked['bdSecretReplyFl'][0]) ?>/> 작성시 기본 일반댓글
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="bdSecretReplyFl" value="1" <?= gd_isset($checked['bdSecretReplyFl'][1]) ?>/> 작성시 기본 비밀댓글
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="bdSecretReplyFl" value="2" <?= gd_isset($checked['bdSecretReplyFl'][2]) ?>/> 무조건 일반댓글
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="bdSecretReplyFl" value="3" <?= gd_isset($checked['bdSecretReplyFl'][3]) ?>/> 무조건 비밀댓글
                    </label>
                </td>
            </tr>
            <tr class="bdSecretReplyTitle if-is-qa-hide">
                <th class="require">비밀댓글 제목설정</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="bdSecretReplyTitleFl"
                                                       value="0" <?= gd_isset($checked['bdSecretReplyTitleFl'][0]) ?> /> 제목 노출</label>
                    <label class="radio-inline"><input type="radio" name="bdSecretReplyTitleFl"
                                                       value="1" <?= gd_isset($checked['bdSecretReplyTitleFl'][1]) ?> /> 제목 지정</label>
                    <input type="text" name="bdSecretReplyTitle" value="<?= gd_isset($data['bdSecretReplyTitle']) ?>"
                           class="form-control" size="40"/>
                </td>
            </tr>
            <tr>
                <th class="require">게시물 시작번호</th>
                <td>
                    <input type="text" name="bdStartNum" size="6" value="<?= gd_isset($data['bdStartNum']) ?>"
                           class="form-control js-number wd-sm2"/>
                </td>
            </tr>
            <tr>
                <th class="require">
                    NEW아이콘 효력
                </th>
                <td class="form-inline">
                    <input type="text" name="bdNewFl" id="bdNewFl" size="5" class="form-control js-number wd-sm2"
                           value="<?= gd_isset($data['bdNewFl']) ?>"/> 시간
                </td>
            </tr>
            <tr>
                <th class="require">
                    HOT아이콘 조건
                </th>
                <td class="form-inline">
                    조회수 <input type="text" name="bdHotFl" id="bdHotFl" class="form-control js-number wd-sm2" size="5"
                               value="<?= gd_isset($data['bdHotFl']) ?>"/> 회 이상 게시글
                </td>
            </tr>
        </table>

        <div class="table-title gd-help-manual">스팸방지 설정</div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>

            <tr>
                <th>허용 태그</th>
                <td>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="bdAllowTags[]" value="iframe" <?= gd_isset($checked['bdAllowTags']['iframe']) ?> />iframe
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="bdAllowTags[]" value="embed" <?= gd_isset($checked['bdAllowTags']['embed']) ?> />embed
                    </label>

                </td>
            </tr>
            <tr>
                <th>허용 도메인</th>
                <td class="form-inline">
                    <ul class="pd0" id="domain-allow-box">
                        <?php
                        for ($i = 0; $i < $data['bdAllowDomainCount']; $i++) {
                            ?>
                            <?php if ($i % 2 == 0) { ?>
                                <li class="mgb5">
                                <input type="text" name="bdAllowDomain[]" class="form-control" maxlength="60"" size="40" value="<?= gd_isset($data['arrBdAllowDomain'][$i]) ?>">
                            <?php } else if ($i % 2 == 1) { ?>
                                <input type="text" name="bdAllowDomain[]" class="form-control" size="40" value="<?= gd_isset($data['arrBdAllowDomain'][$i]) ?>">
                                <?php if ($i == 1) { ?>
                                    <button type="button" class="btn btn-sm btn-white btn-icon-plus js-allow-domain-add">추가</button>
                                <?php } else {
                                    ?>
                                    <button type="button" class="btn btn-sm btn-white btn-icon-minus js-allow-domain-minus">삭제</button>
                                <?php } ?>
                                </li>
                            <?php }
                        }
                        ?>
                        <?php if ($data['bdAllowDomainCount'] % 2 == 1) { ?>
                            <input type="text" name="bdAllowDomain[]" class="form-control" size="40" value="">
                            <button type="button" class="btn btn-sm btn-white btn-icon-minus js-allow-domain-minus">삭제</button>
                        <?php } ?>

                    </ul>
                    <div class="notice-info">허용된 도메인의 컨텐츠에 대해 허용태그 사용이 가능합니다. 예) youtube.com</div>
                </td>
            </tr>

            <tr class="if-is-qa-hide">
                <th>댓글 스팸방지</th>
                <td>
                    <label class="checkbox-inline"><input type="checkbox" name="bdSpamMemoFl[]"
                                                          value="1" <?= gd_isset($checked['bdSpamMemoFl'][1]) ?> /> 외부유입차단</label>
                    <label class="checkbox-inline"><input type="checkbox" name="bdPasswordMemoFl"
                                                          value="y" <?= gd_isset($checked['bdPasswordMemoFl']['y']) ?> /> 비밀댓글 암호보안</label>

                </td>
            </tr>
            <tr>
                <th>게시글 스팸방지</th>
                <td>
                    <label class="checkbox-inline"><input type="checkbox" name="bdSpamBoardFl[]"
                                                          value="1" <?= gd_isset($checked['bdSpamBoardFl'][1]) ?> /> 외부유입차단 </label>
                    <label class="checkbox-inline"><input type="checkbox" name="bdSpamBoardFl[]"
                                                          value="2" <?= gd_isset($checked['bdSpamBoardFl'][2]) ?> />
                        자동등록방지문자</label>
                </td>
            </tr>
        </table>


        <div class="table-title gd-help-manual">리스트화면 설정</div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>

            <tr>
                <th class="require">공지사항 노출설정</th>
                <td class="form-inline">
                    <input type="text" name="bdNoticeCount" value="<?= gd_isset($data['bdNoticeCount']) ?>"
                           class="form-control js-number" size="5" maxlength="5"/>개&nbsp;&nbsp;&nbsp;&nbsp;
                    <label class="radio-inline"><input type="checkbox" name="bdListInNotice" value="y" <?= gd_isset($checked['bdListInNotice']['y']) ?>> 리스트 내 노출</label>
                    <label class="radio-inline"><input type="checkbox" name="bdOnlyMainNotice" value="y" <?= gd_isset($checked['bdOnlyMainNotice']['y']) ?>>첫페이지만 노출</label>
                    <div class="notice-info">
                        리스트 내 노출 설정 시 공지사항 글이 리스트 상단 이외에 본문리스트에 노출됩니다.
                    </div>
                    <div class="notice-info">
                        첫페이지만 노출 설정 시 공지사항 글이 첫페이지만 노출됩니다.
                    </div>

                </td>
            </tr>

            <tr>
                <th class="require">제목글 제한</th>
                <td class="form-inline">
                    <input type="text" name="bdSubjectLength" value="<?= gd_isset($data['bdSubjectLength']) ?>"
                           class="form-control js-number" size="5" maxlength="5"/> 자
                </td>
            </tr>

            <tr class="if-is-gallery-hide">
                <th class="require">페이지당 게시물수</th>
                <td>
                    <input type="text" name="bdListCount" value="<?= gd_isset($data['bdListCount']) ?>"
                           class="form-control js-number wd-sm2" size="5" maxlength="5"/>
                    <div class="notice-info">
                        게시판 전체보기의 페이지별 게시글 노출 개수를 설정합니다.
                    </div>
                </td>
            </tr>

            <tr class="if-is-gallery-show">
                <th class="require">페이지당 노출 수</th>
                <td class="form-inline">
                    <input type="text" name="bdListColsCount" value="<?= gd_isset($data['bdListColsCount']) ?>"
                           class="form-control js-number" size="5" maxlength="5"/> *
                    <input type="text" name="bdListRowsCount" value="<?= gd_isset($data['bdListRowsCount']) ?>"
                           class="form-control js-number" size="5" maxlength="5"/>
                </td>
            </tr>

            <tr <?php if($data['bdId'] !== 'goodsreview' && $data['bdId'] !== 'goodsqa'){ ?> class="display-none" <?php } ?>>
                <th class="require">상품상세 페이지 내<br />페이지별 게시물 수</th>
                <td class="form-inline">
                    <table>
                        <tr>
                            <td>PC</td>
                            <td style="padding:0 0 3px 3px;"><input type="text" name="bdGoodsPageCountPc" value="<?= gd_isset($data['bdGoodsPageCountPc']) ?>" class="form-control js-number wd-sm2" maxlength="3"/> 개</td>
                        </tr>
                        <tr>
                            <td>모바일</td>
                            <td style="padding:0 0 0 3px;"><input type="text" name="bdGoodsPageCountMobile" value="<?= gd_isset($data['bdGoodsPageCountMobile']) ?>" class="form-control js-number wd-sm2" maxlength="3"/> 개</td>
                        </tr>
                    </table>
                    <div class="notice-info">
                        상품 상세화면 하단 영역에 노출되는 게시글 노출 개수를 설정합니다.
                    </div>
                </td>
            </tr>

            <tr class="if-is-gallery_event_qa_default-show">
                <th>대표 이미지 노출 여부</th>
                <td class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="bdListImageFl" value="y" <?= gd_isset($checked['bdListImageFl']['y']) ?> /> 사용</label>
                    <label class="radio-inline <?php if($data['bdKind'] !== 'default' && $data['bdKind'] !== 'qa'){ echo 'display-none'; } ?> ?>">
                        <input type="radio" name="bdListImageFl" value="n" <?= gd_isset($checked['bdListImageFl']['n']) ?> /> 사용안함</label>
                    <table class="table table-cols mgt10">
                        <tbody>
                        <tr>
                            <th class="width-sm">대표 이미지 설정</th>
                            <td>
                                <label class="radio-inline js-bdListImageTarget-goods">
                                    <input type="radio" name="bdListImageTarget"
                                           value="goods" <?= gd_isset($checked['bdListImageTarget']['goods']) ?> />상품 이미지
                                </label>
                                <label class="radio-inline js-bdListImageTarget-upload">
                                    <input type="radio" name="bdListImageTarget"
                                           value="upload" <?= gd_isset($checked['bdListImageTarget']['upload']) ?> />업로드
                                    이미지
                                </label>
                                <label class="radio-inline js-bdListImageTarget-editor">
                                    <input type="radio" name="bdListImageTarget"
                                           value="editor" <?= gd_isset($checked['bdListImageTarget']['editor']) ?> />에디터 이미지
                                </label>
                                <div class="notice-info js-bdListImageTarget-notice display-none">
                                    ※ '상품연동 / 업로드 파일 사용 / 에디터 사용' 중 1개 이상을 사용함으로 설정해야 합니다.
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr class="if-is-gallery_event_qa_default-show" id="trListImageSize">
                <th class="require">리스트 이미지 크기</th>
                <td class="form-inline">
                    <input type="text" name="bdListImageSize[width]" class="form-control js-number"
                           value="<?= $data['bdListImageSizeWidth'] ?>"> *
                    <input type="text" name="bdListImageSize[height]" class="form-control js-number"
                           value="<?= $data['bdListImageSizeHeight'] ?>">
                    <div class="notice-info">
                        ※ 모바일에서는 리스트 이미지 크기는 화면 비율에 따라 자동 적용 됩니다.
                    </div>
                </td>
            </tr>
            <tr class="if-is-qa_default-show" id="trListNoticeImage">
                <th>공지글 이미지 노출 여부</th>
                <td class="form-inline">
                    <label class="checkbox-inline">
                        <input name="bdListNoticeImageDisplayPc" type="checkbox" value='y' <?= gd_isset($checked['bdListNoticeImageDisplayPc']['y']) ?> />
                        PC 쇼핑몰
                    </label>
                    <label class="checkbox-inline">
                        <input name="bdListNoticeImageDisplayMobile" type="checkbox" value='y' <?= gd_isset($checked['bdListNoticeImageDisplayMobile']['y']) ?> />
                        모바일 쇼핑몰
                    </label>

                    <div class="notice-info">
                        리스트에 노출되는 공지글에 대하여 이미지를 노출시킬지에 대한 여부를 설정 하실 수 있습니다.
                    </div>
                </td>
            </tr>
            <tr>
                <th>검색 시 답변글 노출여부</th>
                <td class="form-inline">
                    <label class="radio-inline">
                        <input name="bdIncludeReplayInSearchFl" type="radio"
                               value='y' <?= gd_isset($checked['bdIncludeReplayInSearchFl']['y']) ?> />
                        사용함
                    </label>
                    <label class="radio-inline">
                        <input name="bdIncludeReplayInSearchFl" type="radio"
                               value='n' <?= gd_isset($checked['bdIncludeReplayInSearchFl']['n']) ?> />
                        사용안함
                    </label>

                    <div class="js-bdIncludeReplayInSearchFl-show" style="display:none">
                        <label class="checkbox-inline">
                            <input name="bdIncludeReplayInSearchType[]" type="checkbox"
                                   value='1' <?= gd_isset($checked['bdIncludeReplayInSearchType']['front']['y']) ?> />
                            쇼핑몰 화면 적용
                        </label>
                        <label class="checkbox-inline">
                            <input name="bdIncludeReplayInSearchType[]" type="checkbox"
                                   value='2' <?= gd_isset($checked['bdIncludeReplayInSearchType']['admin']['y']) ?> />
                            관리자 화면 적용
                        </label>
                    </div>

                    <div class="notice-info">
                        검색 시 원본글에 등록된 답변글도 검색결과에 노출시킬지 여부를 설정하실 수 있습니다.
                    </div>
                    <div class="notice-info">
                        검색결과 때 노출되는 답변글은 페이지당 게시물 수에 포함되지 않습니다.
                    </div>
                </td>
            </tr>
            <tr class="if-is-event-show">
                <th>종료된 이벤트</th>
                <td class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="bdEndEventType"
                               value="read" <?= gd_isset($checked['bdEndEventType']['read']) ?> />읽기가능
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="bdEndEventType"
                               value="msg" <?= gd_isset($checked['bdEndEventType']['msg']) ?>/>
                        접속제한 알럿메세지
                    </label>
                    <input type="text" name="bdEndEventMsg" class="form-control"
                           value="<?= gd_isset($data['bdEndEventMsg']) ?>"/>
                </td>
            </tr>
        </table>

        <div class="table-title gd-help-manual">작성자 화면 설정</div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>

            <tr class="if-is-event-show">
                <th>부가설명</th>
                <td class="form-inline">
                    <label class="radio-inline">
                        <input name="bdSubSubjectFl" type="radio"
                               value='y' <?= gd_isset($checked['bdSubSubjectFl']['y']) ?> /> 사용함
                    </label>
                    <label class="radio-inline">
                        <input name="bdSubSubjectFl" type="radio"
                               value='n' <?= gd_isset($checked['bdSubSubjectFl']['n']) ?> /> 사용안함
                    </label>
                </td>
            </tr>

            <tr>
                <th>에디터 사용</th>
                <td>
                    <label class="radio-inline">
                        <input name="bdEditorFl" type="radio"
                               value='y' <?= gd_isset($checked['bdEditorFl']['y']) ?> /> 사용함
                    </label>
                    <label class="radio-inline">
                        <input name="bdEditorFl" type="radio"
                               value='n' <?= gd_isset($checked['bdEditorFl']['n']) ?> /> 사용안함
                    </label>
                </td>
            </tr>

            <tr>
                <th>휴대폰 작성</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="bdMobileFl"
                                                       value="y" <?= gd_isset($checked['bdMobileFl']['y']) ?> /> 사용함</label>
                    <label class="radio-inline"><input type="radio" name="bdMobileFl"
                                                       value="n" <?= gd_isset($checked['bdMobileFl']['n']) ?> /> 사용안함</label>
                </td>
            </tr>
            <tr>
                <th>이메일 작성</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="bdEmailFl"
                                                       value="y" <?= gd_isset($checked['bdEmailFl']['y']) ?> /> 사용함</label>
                    <label class="radio-inline"><input type="radio" name="bdEmailFl"
                                                       value="n" <?= gd_isset($checked['bdEmailFl']['n']) ?> /> 사용안함</label>
                </td>
            </tr>

            <tr>
                <th>업로드 파일 사용</th>
                <td>
                    <label class="radio-inline">
                        <input name="bdUploadFl" type="radio"
                               value='y' <?= gd_isset($checked['bdUploadFl']['y']) ?> />
                        사용함
                    </label>
                    <label class="radio-inline">
                        <input name="bdUploadFl" type="radio"
                               value='n' <?= gd_isset($checked['bdUploadFl']['n']) ?> />
                        사용안함
                    </label>
                </td>
            </tr>

            <tr id="bdUploadMaxSize_tr">
                <th class="require">업로드파일 최대크기</th>
                <td class="form-inline"><input type="text" name="bdUploadMaxSize" id="bdUploadMaxSize" class="form-control js-number wd-sm2"
                                               value="<?= gd_isset($data['bdUploadMaxSize']) ?>"/> MByte(s)
                </td>
            </tr>

            <tr>
                <th>링크</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="bdLinkFl"
                                                       value="y" <?= gd_isset($checked['bdLinkFl']['y']) ?> /> 사용함</label>
                    <label class="radio-inline"><input type="radio" name="bdLinkFl"
                                                       value="n" <?= gd_isset($checked['bdLinkFl']['n']) ?> /> 사용안함</label>
                </td>
            </tr>
        </table>
        <div>
            <div class="table-title gd-help-manual">게시글 내용 화면설정</div>
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>첨부파일 이미지 표시</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="bdAttachImageDisplayFl" value="y" <?= gd_isset($checked['bdAttachImageDisplayFl']['y']) ?> />사용함
                        </label>
                        <label class=R"radio-inline">
                            <input type="radio" name="bdAttachImageDisplayFl" value="n" <?= gd_isset($checked['bdAttachImageDisplayFl']['n']) ?> />사용안함
                        </label>
                        <div class="notice-info">첨부파일로 등록된 이미지를 게시글 본문 상단에 노출할 수 있습니다.</div>
                    </td>
                </tr>
                <tr class="bdAttachImageRow">
                    <th>이미지 리사이즈</th>
                    <td class="form-inline">
                        <input typ="text" class="form-control js-number wd-sm2" name="bdAttachImageMaxSize" value="<?= $data['bdAttachImageMaxSize'] ?>" size="5">px
                        <div class="notice-info">첨부파일 이미지 업로드 시 제한된 값보다 이미지 넓이가 클 경우 설정된 값으로 리사이즈하여 노출합니다.</div>
                        <div class="notice-info">모바일은 디바이스 해상도보다 클 경우 모바일 해상도로 리사이즈하여 노출됩니다.</div>
                    </td>
                </tr>
                <tr class="bdAttachImageRow">
                    <th>노출 위치</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="bdAttachImagePosition" value="top" <?= gd_isset($checked['bdAttachImagePosition']['top']) ?> />본문상단
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="bdAttachImagePosition" value="bottom" <?= gd_isset($checked['bdAttachImagePosition']['bottom']) ?> />본문하단
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>리스트화면 노출</th>
                    <td>
                        <label class="radio-inline"><input type="radio" name="bdListInView"
                                                           value="y" <?= gd_isset($checked['bdListInView']['y']) ?> />사용함</label>
                        <label class="radio-inline"><input type="radio" name="bdListInView"
                                                           value="n" <?= gd_isset($checked['bdListInView']['n']) ?> />사용안함</label>
                    </td>
                </tr>
                <tr>
                    <th>IP 노출</th>
                    <td>
                        <label class="checkbox-inline"><input type="checkbox" name="bdIpFl"
                                                              value="y" <?= gd_isset($checked['bdIpFl']) ?>
                                                              onclick="$('#bdIpFilterFl').prop('disabled', !this.checked)"/>
                            글쓴이의 IP를 보여줍니다</label>
                        <label class="checkbox-inline"><input type="checkbox" name="bdIpFilterFl" id="bdIpFilterFl"
                                                              value="y" <?= gd_isset($checked['bdIpFilterFl']) ?> <?= gd_isset($disabled['bdIpFilterFl']) ?> />
                            IP 끝자리 암호화표기
                        </label>
                    </td>
                </tr>
            </table>

            <div class="table-title gd-help-manual">상단 하단 꾸미기</div>
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>상단디자인<br>(Header)</th>
                    <td>
                        <!-- mini editor -->
                        <textarea name="bdHeader" style="display:none" id="editor"
                                  label="상단디자인"><?= gd_isset($data['bdHeader']); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th>하단디자인<br>(Footer)</th>
                    <td>
                        <!-- mini editor -->
                        <textarea name="bdFooter" style="display:none" id="editor2"
                                  label="하단디자인"><?= gd_isset($data['bdFooter']); ?></textarea>
                    </td>
                </tr>
            </table>

            <div class="table-title gd-help-manual mgt35">
                게시판 개별 SEO 태그 설정
                <div class="pull-right form-inline">
                    <button type="button" class="btn btn-sm btn-gray js-code-view" data-target="board">치환코드 보기</button>
                </div>
            </div>
            <?php include($seoTagFrm); ?>

        </div>
    </form>
</div>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js" charset="utf-8"></script>
<script type="text/javascript">
    nhn.husky.EZCreator.createInIFrame({
        oAppRef: oEditors,
        elPlaceHolder: "editor2",
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

</script>
