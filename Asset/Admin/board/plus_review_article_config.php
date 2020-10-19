<style>
    .plus_review_config .js-number {
        width: 70px !important;
    }

    .plus_review_config ul li {
        margin-bottom: 5px;
    }

    .plus_review_config #addFormTable tr.placeholder {
        position: relative;
        background-color: red;
        /** More li styles **/
    }

</style>

<script type="text/javascript">
    <!--
    var mileageUseConfig = '<?=$mileageUseFl;?>';
    var mileageAlertUseFl = false;
    function layer_register(parentLayerFormID, dataInputNm, dataFormID) {
        var addParam = {
            "parentFormID": parentLayerFormID,
            "dataInputNm": dataInputNm,
            "dataFormID": dataFormID,
        };
        layer_add_info('member_group', addParam);
    }

    $(document).ready(function () {
        $('.js-btn-migration').bind('click',function () {
            <?php if($diskLimit){ ?>
            alert('게시판 통합에 필요한 솔루션 디스크 용량이 부족합니다. 디스크 용량 추가 후 다시 시도해주세요.');
            return true;
            <?php } ?>

            var checkMigration = '<?=$checkMigration?>';
            if(checkMigration == 'ready') {
                var msg = '상품후기 게시판에 등록된 게시글을 불러옵니다. 계속하시겠습니까? 게시글이 많을 경우 처리 시간이 많이 소요될 수 있습니다.';
            }
            else if(checkMigration == 'completed'){
                var msg = '상품후기 게시판 통합이 모두 완료된 상태입니다.';
                alert(msg);
                return;
            }
            else {
                var msg = '상품후기 게시판에 등록된 게시글이 없습니다.';
                alert(msg);
                return;
            }
            dialog_confirm(msg , function(data){
                if(!data) {
                    return false;
                }
                $.ajax({
                    method: 'post',
                    url: '../board/plus_review_ps.php',
                    data: {'mode' : 'migration'},
                    dataType: 'json'
                }).success(function (data) {
                    if(data.errorMsg) {
                        BootstrapDialog.closeAll();
                        alert(data.errorMsg);
                    }
                }).error(function (e) {
                    console.log(e);
                    alert(e);
                });

                startPprogress();
            })
        })

        $('[name=minLimitLengthFl]').bind('click', function () {
            $('[name=minContentsLength]').prop('disabled', $(this).val() == 'n');
        })

        $('[name="photoWidget[thumSizeType]"]').bind('click', function () {
            $('[name="photoWidget[thumWidth]"]').prop('disabled', $(this).val() == 'auto');
        })

        $('input[name^="photoWidget["], input[name="articleWidget[rows]"]').keyup(function (e) {
            var code = e.keyCode || e.which;
            var name = $(this).attr('name');
            if (code == '9') {
                return true;
            }
            $(this).trigger('blur');
            $(this).val($(this).val().replace(/[^0-9]/g, ""));
            $(this).trigger('focus');
            if ($(this).val() == '') {
                return false;
            }
            if (name.indexOf('thumWidth') > -1) {
                if ($(this).val() < 1) {
                    alert('1이상 설정하실 수 있습니다.');
                    $(this).val('');
                    return false;
                }
            } else {
                if ($(this).val() > 20 || $(this).val() < 1) {
                    alert('1~20까지만 설정하실 수 있습니다.');
                    $(this).val('');
                    return false;
                }
            }
        });

        $('.js-btn-widget').bind('click', function () {
            var mode = $(this).data('mode');
            var title = mode === 'article' ? '전체리뷰' : '포토리뷰';
            var layerSize = BootstrapDialog.SIZE_WIDE_XLARGE;
            var params, rows, template;
            if (_.isUndefined(mode) || _.isEmpty(mode)) {
                mode = 'photo';
            }

            switch (mode) {
                case 'photo':
                    var cols = $('[name="photoWidget[cols]"]').val();
                    rows = $('[name="photoWidget[rows]"]').val();

                    if ((cols > 20 || cols == 0 || _.isEmpty(cols)) || (rows > 20 || rows == 0 || _.isEmpty(cols))) {
                        alert('1~20까지만 설정하실 수 있습니다.');
                        $('[name="photoWidget[cols]"]').val('');
                        $('[name="photoWidget[rows]"]').val('');
                        return;
                    }

                    var thumSizeType = $('[name="photoWidget[thumSizeType]"]:checked').val();
                    var thumWidth = $('[name="photoWidget[thumWidth]"]').val();

                    if ($('[name="photoWidget[thumSizeType]"]:checked').length < 1) {
                        alert('썸네일 사이즈 타입을 선택해주세요.');
                        return;
                    }

                    if ($('[name="photoWidget[thumSizeType]"][value=menual]').is(':checked')) {
                        if (_.isEmpty(thumWidth) || thumWidth == 0) {
                            alert('수동설정 사이즈를 1이상 입력해주세요.');
                            return;
                        }
                    }
                    params = {'mode': mode, 'cols': cols, 'rows': rows, 'thumSizeType': thumSizeType, 'thumWidth': thumWidth};
                    break;
                case 'article':
                    rows = $('[name="articleWidget[rows]"]').val();
                    template = $('[name="articleWidget[template]"]:checked').val();
                    if (rows > 20 || rows == 0 || _.isEmpty(rows)) {
                        alert('1~20까지만 설정하실 수 있습니다.');
                        $('[name="articleWidget[rows]"]').val('');
                        return;
                    }
                    layerSize = BootstrapDialog.SIZE_WIDE;
                    params = {'mode': mode, 'template': template, 'rows': rows};
                    break;
            }

            $.ajax({
                method: 'get',
                url: 'plus_review_widget_preview.php',
                data: params,
                dataType: 'text'
            }).success(function (data) {
                BootstrapDialog.show({
                    title: title + ' 게시판 위젯생성',
                    size: layerSize,
                    message: data,
                    closable: true
                });
            }).error(function (e) {
                console.log(e);
                alert(e);
            });
        })

        $('.ui-sort-table').sortable();

        if ($('input[name="mileageFl"]:checked').val() === 'n') {
            mileageAlertUseFl = true;
        }

        $(':radio[name=mileageFl]').bind('click', function () {
            if ($(this).val() == 'y') {
                if (mileageAlertUseFl) {
                    $('.js-miliage-use-tr').show();
                    if (mileageUseConfig === 'n') {
                        BootstrapDialog.show({
                            title: '경고',
                            message: '마일리지 지급을 위해서는 마일리지 기본설정이 "사용함"이어야 이용 가능합니다.<br/>"회원 > 마일리지/예치금 관리 > 마일리지 기본 설정"을 "사용함"으로 설정 후 이용해 주세요.',
                            buttons: [{
                                label: '확인',
                                cssClass: 'btn-black',
                                hotkey: 13,
                                size: BootstrapDialog.SIZE_LARGE,
                                action: function (dialog) {
                                    dialog.close();
                                    $(':radio[name="mileageFl"][value="n"]').trigger('click');
                                }
                            }],
                        });
                    }
                } else {
                    mileageAlertUseFl = true;
                }
            } else {
                $('.js-miliage-use-tr').hide();
            }
        });

        $('body').on('change', 'select[name^="addForm[inputType]"]', function () { //추가정보양식 입력형태 셀렉트
            if (($(this).val() == 'select')) {
                var addBtn = '<button type="button" class="btn btn-sm btn-white btn-icon-plus js-btn-labal-value-add mgl10">추가</button>';
                $(this).closest('tr').find('[name^="addForm[labelValue]"]').data('type', 'select').attr('placeholder', 'Enter키를 이용 입력값을 연속적으로 입력하세요. ex) 160~165').after(addBtn);
            }
            else {
                $(this).closest('tr').find('[name^="addForm[labelValue]"]').data('type', 'input').attr('placeholder', 'ex) 160~165');
                $(this).closest('tr').find('.js-label-value-list>li:not(:first)').remove();
                $(this).closest('tr').find('.js-btn-labal-value-add').remove();
            }
        });

        $('body').off('keypress').on('keypress', '[name^="addForm[labelValue]"]', function (e) {
            var type = $(this).data('type');
            if (e.which == 13) {
                $(this).closest('tr').find('.js-btn-labal-value-add').trigger('click');
                $(this).closest('tr').find('[name^="addForm[labelValue]"]:last').focus();
                e.preventDefault();
                return false
            }
        })

        $('body').on('click', '.js-btn-labal-value-add', function () {  //추가정보 양식 셀렉트박스일때 입력값 추가 버튼 클릭
            var row = $(this).closest('tr').data('row');
            var removeBtn = '<button type="button" class="btn btn-sm btn-white btn-icon-minus js-btn-labal-remove-li mgl10">삭제</button>';
            var addFormRow = '<li><input type="text" class="form-control width80p" name="addForm[labelValue][' + row + '][]">' + removeBtn + '</li>';
            $(this).closest('tr').find('ul:last').append(addFormRow);
        })

        $('body').on('click', '.js-btn-form-add', function () { //추가정보양식 추가 클릭
            var templateAddForm = _.template($('#templateAddForm').html());
            if ($('#addFormTable tbody tr').length > 10) {
                alert('최대 10개까지 등록 가능합니다.');
                return;
            }

            var maxIndex = 0;
            $('#addFormTable tbody tr').each(function () {
                var index = $(this).data('row');
                if (maxIndex < index) {
                    maxIndex = index;
                }
            });

            var $labelMaxNum = $('input[name="labelMaxNum"]');
            $labelMaxNum.val( Number($labelMaxNum.val()) + 1 );
            $('#addFormTable tbody:last').append(templateAddForm({index: maxIndex + 1,maxNum: $labelMaxNum.val()}));
            $('.ui-sort-table').sortable();
        })

        $(':radio[name=addFormFl]').bind('click', function () {
            if ($(this).val() == 'y') {
                $('.js-add-form-tr').show();
                $('input[name=addFormSearchFl]').attr( 'disabled', false );
            }
            else {
                $('.js-add-form-tr').hide();
                $('input[name=addFormSearchFl]').attr( 'disabled', true );
                $("input:radio[name='addFormSearchFl']:radio[value='n']").prop('checked', true);
            }
        })

        $('body').on('click', '.js-btn-labal-remove-li', function () {
            $(this).closest('li').remove();
        })

        $('body').on('click', '.js-btn-remove-tr', function () {
            $(this).closest('tr').remove();
        })


        $('.js-group-select').bind('click', function () {
            $(this).closest('td').find('input[type="radio"][value="group"]').trigger('click');
        });

        $('input[name=useFl]').bind('click', function () {
            if ($(this).val() == 'y') {
                alert('사용함 설정 시 기존 "상품후기" 게시판과 함께 노출됩니다. <br> 상품후기 게시판을 사용안함으로 설정해주세요.');
            }
        })

        // 폼검증
        $("#frm").validate({
            ignore: ':hidden',
            dialog: false,
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                if ($(':radio[name=addFormFl]:checked').val() == 'y') {
                    if ($('input[name^="addForm[labelName]"]').length < 1) {
                        alert('추가정보 양식을 입력해주세요.');
                        return;
                    }
                }

                var minLength = 1;
                var maxLength = 2000;
                var minLimitLength = $('[name=minContentsLength]').val();
                if ($('[name=minLimitLengthFl]:checked').val() == 'y') {
                    if (minLimitLength == 0) {
                        alert('최소 1자 이상은 입력하셔야 합니다.');
                        return;
                    }

                    if (minLimitLength < minLength) {
                        alert('최소 ' + minLength + '자 이상은 입력하셔야 합니다.');
                        return;
                    }
                    if (minLimitLength > maxLength) {
                        alert('최대 ' + maxLength + '자 까지 입력가능합니다.');
                        return;
                    }
                }

                if ($('input[name="authWrite"]:checked').val() === 'group' && $('input[name="authWriteGroup[]"]').length < 1) {
                    alert('쓰기권한의 특정회원등급이 선택되지 않았습니다.');
                    return false;
                }

                if ($('input[name="authMemoWrite"]:checked').val() === 'group' && $('input[name="authMemoWriteGroup[]"]').length < 1) {
                    alert('댓글권한의 특정회원등급이 선택되지 않았습니다.');
                    return false;
                }

                form.submit();
            },
            rules: {
                minContentsLength: {
                    required: function () {
                        return $('[name=minLimitLengthFl][value=y]').is(':checked');
                    }
                },
                articlePagetCnt : {
                    min : 1,
                    number : true,
                },
                'mileageAmount[review]': {
                    required: function () {
                        return $('[name=mileageFl][value=y]').is(':checked');
                    }
                },
                'mileageAmount[photo]': {
                    required: function () {
                        return $('[name=mileageFl][value=y]').is(':checked');
                    }
                },
                'mileageAmount[first]': {
                    required: function () {
                        return $('[name=mileageFl][value=y]').is(':checked');
                    }
                },
                'mileageAmountLimit[review]': {
                    required: function () {
                        return $('[name=mileageAmountLimitFl][value=y]').is(':checked');
                    }
                },
                bdNewFl: 'required',
                uploadMaxSize: 'required'
            },
            messages: {
                minContentsLength: {
                    required: '최소 1자 이상은 입력하셔야 합니다.',
                },
                articlePagetCnt : {
                    min : '[전체리뷰 게시판 페이지당 게시물 수] 입력항목에 1 이상 입력해주시기 바랍니다.',
                },
                'mileageAmount[review]': {
                    required: '플러스리뷰 작성 마일리지 지급 설정 항목은 필수입니다.'
                },
                'mileageAmount[photo]': {
                    required: '포토리뷰 작성 마일리지 지급 설정 항목은 필수입니다.'
                },
                'mileageAmount[first]': {
                    required: '상품별 첫 리뷰 작성 마일리지 지급 설정 항목은 필수입니다.'
                },
                'mileageAmountLimit[review]': {
                    required: '최대 지급 마일리지 제한 금액을 입력해주세요.'
                },
                bdNewFl: {
                    required: 'NEW아이콘 효력 항목은 필수입니다.'
                },
                uploadMaxSize: {
                    required: '첨부이미지 최대크기 항목은 필수입니다.'
                }
            }
        });

        /**
         * 프로그레스 업데이트
         *
         * @param data
         */
        var totalMigratioCount = <?=$totalMigratioCount?>;
        function startPprogress() {
            // 복사 처리
            var compiled = _.template($('#progressBar').html());
            var isStop = false;
            BootstrapDialog.show({
                title: '파일복사 정보',
                message: compiled({total: totalMigratioCount}),
                closable: false,
                onshown: function(dialog) {
                    updateProgress(1, totalMigratioCount);
                    function upload(dialog)
                    {
                        $.ajax({
                            method: 'post',
                            url: './plus_review_ps.php',
                            async: false,
                            data: {
                                mode: 'applyMigration',
                            },
                            dataType: 'json'
                        }).success(function(data) {
                            if (data.error == 0) {
                                isStop = false;
                                updateProgress(data.migrationCount, totalMigratioCount);

                                // 전체 파일 업로드 후
                                if (data.migrationCount == 0 ) {
                                    updateProgress(totalMigratioCount, totalMigratioCount);
                                    dialog.close();
                                    alert('완료되었습니다.');
                                    self.location.reload();
                                }
                            } else if (data.error == 1) {
                                isStop = true;
                                dialog.close();
                                alert(data.message);
                            } else {
                                isStop = true;
                                dialog.close();
                                alert(data.error.message);
                            }
                        });
                    }


                    setTimeout(function(){
                        upload(dialog)
                    } , 2000);



                }
            });
        }

        /**
         * 프로그레스 업데이트
         *
         * @param data
         */
        function updateProgress(current, total) {
            var percentage = parseInt(100 * (current / total));
            if(percentage > 100) percentage = 100;
            $('.progress-bar').css('width', percentage+'%');
            $('.progress-bar .progress-current').html(current);
        }

        $(':radio[name=viewPermissionFl]').bind('click', function () {
            if ($(this).val() == 'd') {
                $('.js-review-view-tr').show();
            }
            else {
                $('.js-review-view-tr').hide();
            }
        });

        // 예외상품 노출
        $('input[name="exceptGoodsFl"]').on('click', function() {
            if ($(this).val() === 'y') {
                $('#configExceptGoods').show();
            } else {
                $('#configExceptGoods').hide();
            }
        });

        // 예외상품 삭제
        $('.js-delete-exceptGoods').on('click', function () {
            var target = $('input[name="exceptGoodsChk"]:checked');
            if ($(this).hasClass('js-all-exceptGoods')) {
                target = $('input[name="exceptGoodsChk"]');
            } else {
                if (target.length === 0) {
                    alert('선택한 상품이 없습니다');
                    return false;
                }
            }
            target.each(function() {
                $(this).closest('tr').find('input[type="button"]').trigger('click');
            });
        });

        // 템플릿 미리보기
        $('.js-btn-preview-template').mouseover(function () {
            var targetClass = $(this).data('target');
            $('.' + targetClass).show();
        }).mouseout(function () {
            var targetClass = $(this).data('target');
            $('.' + targetClass).hide();
        });

        // 특정회원등급 외 선택
        $('input[name="authWrite"], input[name="authMemoWrite"]').on('click', function () {
            if ($(this).val() !== 'group') {
                if ($(this).attr('name') === 'authWrite') {
                    if ($('#authWriteGroup').length > 0) {
                        $('#authWriteGroup').html('').removeClass('active');
                    }
                } else {
                    if ($('#authMemoWriteGroup').length > 0) {
                        $('#authMemoWriteGroup').html('').removeClass('active');
                    }
                }
            }
        });

        // 마일리지 구매 상품 가격 설정 노출 및 작성 가능 시점 선택 불가 처리
        $('input[name="authWriteExtra"]').on('click', function() {
            if ($(this).val() === 'all') {
                $('.order_goods_mileage_config').addClass('display-none');
                $('#authWriteStatus').prop('disabled', true);
                $('#authWriteStatusDuration').prop('disabled', true);
                $('input[name="authWriteStatusDurationFl"]').prop('disabled', true);
            } else {
                $('.order_goods_mileage_config').removeClass('display-none');
                $('#authWriteStatus').prop('disabled', false);
                $('#authWriteStatusDuration').prop('disabled', false);
                $('input[name="authWriteStatusDurationFl"]').prop('disabled', false);
            }
        });

        // 최대 마일리지 지급 설정 노출
        $('select[name="mileageUnit[review]"]').on('change', function() {
            if ($(this).val() === 'percent') {
                $('.mileage_unit_percent').removeClass('display-none');
            } else {
                $('.mileage_unit_percent').addClass('display-none');
            }
        });

        // 마일리지 중복 지급 제한 설정
        $('input[name="authWriteExtra"], input[name="orderDuplicateIgnoreFl"]').on('click', function() {
            if ($('input[name="authWriteExtra"]:checked').val() === 'all' && $('input[name="orderDuplicateIgnoreFl"]:checked').val() === 'n') {
                $('input:radio[name="mileageDuplicateIgnoreFl"]:input[value="n"]').prop("checked", true);
                $('input:radio[name="mileageDuplicateIgnoreFl"]:input[value="y"]').prop('disabled', true);
            } else {
                $('input:radio[name="mileageDuplicateIgnoreFl"]:input[value="y"]').prop('disabled', false);
            }
        });

        // 최대 지급 마일리지 제한 설정
        $('input[name="mileageAmountLimitFl"]').on('click', function() {
            if ($(this).is(':checked')) {
                $('input[name="mileageAmountLimit[review]"]').prop('readonly', false);
            } else {
                $('input[name="mileageAmountLimit[review]"]').prop('readonly', true);
            }
        });

        configInit();
    });

    // 설정 초기화
    function configInit() {
        $('[name=minLimitLengthFl]:checked').trigger('click');
        $('[name="photoWidget[thumSizeType]"]:checked').trigger('click');
        $(':radio[name=mileageFl]:checked').trigger('click');
        $(':radio[name=addFormFl]:checked').trigger('click');
        $(':radio[name=viewPermissionFl]:checked').trigger('click');
        $('input[name="exceptGoodsFl"]:checked').trigger('click');
        $('input[name="authWriteExtra"]:checked').trigger('click');
        $('select[name="mileageUnit[review]"]').trigger('change');
        $('input[name="authWriteExtra"]:checked, input[name="orderDuplicateIgnoreFl"]:checked').trigger('click');
        if ($('input[name="mileageAmountLimitFl"]').is(':checked')) {
            $('input[name="mileageAmountLimit[review]"]').prop('readonly', false);
        }
    }

    /**
     * 구매 상품 범위 등록 / 예외 등록 Ajax layer
     *
     * @param typeStr
     * @param modeStr
     * @param isDisabled
     */
    function layer_except_register(typeStr, modeStr, isDisabled) {
        var layerFormID = 'addPresentForm';
        var mode = 'plusReview';
        var typeStrId = typeStr.substr(0, 1).toUpperCase() + typeStr.substr(1);
        var parentFormID, dataFormID, dataInputNm, layerTitle;

        if (typeof modeStr === 'undefined') {
            parentFormID = 'present' + typeStrId;
            dataFormID = 'id' + typeStrId;
            dataInputNm = 'present' + typeStrId;
            layerTitle = '조건 - ';
        } else {
            parentFormID = 'except' + typeStrId;
            dataFormID = 'idExcept' + typeStrId;
            dataInputNm = 'except' + typeStrId;
            layerTitle = '예외 상품 선택';
        }

        var addParam = {
            "mode": mode,
            "layerFormID": layerFormID,
            "parentFormID": parentFormID,
            "dataFormID": dataFormID,
            "dataInputNm": dataInputNm,
            "layerTitle": layerTitle
        };

        // 레이어 창
        if (typeStr === 'goods') {
            addParam['scmFl'] = $('input[name="scmFl"]:checked').val();
            addParam['scmNo'] = $('input[name="scmNo"]').val();
        }

        if (!_.isUndefined(isDisabled) && isDisabled === true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr, addParam);
    }
    //-->
</script>
<div class="plus_review_config">
    <form id="frm" action="plus_review_ps.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="save">
        <div class="page-header js-affix">
            <h3><?php echo end($naviMenu->location); ?>
                <small>플러스리뷰 게시판에 대한 설정을 합니다.</small>
            </h3>
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>

        <div class="table-title">기본설정</div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>사용 여부</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="useFl"
                                                       value="y" <?= gd_isset($checked['useFl']['y']) ?> /> 사용</label>
                    <label class="radio-inline"><input type="radio" name="useFl"
                                                       value="n" <?= gd_isset($checked['useFl']['n']) ?> /> 사용안함</label>
                </td>
            </tr>
            <tr>
                <th>상품후기 게시판 통합</th>
                <td class="form-inline">
                    <button type="button" class="btn btn-gray js-btn-migration">상품후기 게시판 통합</button>
                    <?php if($migrationInfo['regDt']){?>
                    <span class="notice-danger">게시판 통합 <?=substr($migrationInfo['regDt'],0,10)?> 게시글까지 완료</span>
                    <?php }?>
                    <div class="notice-info">기존 상품후기 게시판과 데이터를 통합합니다. 단, 답변 글은 통합되지 않으며, 플러스리뷰의 추가정보들은 빈값으로 처리됩니다.
                    </div>
                    <div class="notice-info">
                        플러스리뷰에서는 비밀글을 작성할 수 없습니다. 따라서, 상품후기 게시판 통합 시 비밀글은 제외하고 게시글이 통합됩니다.
                    </div>
                    <div class="notice-danger">
                        플러스리뷰는 일반 텍스트와 첨부 이미지 중심의 리뷰 기능입니다.<br> 따라서, 게시판 통합 시 첨부된 이미지 파일만 통합되고 이미지 태그로 본문에 작성된 이미지는 통합되지 않습니다.

                    </div>
                </td>
            </tr>

            <tr>
                <th>
                    포토리뷰 게시판 주소
                </th>
                <td>
                    <div style="padding-bottom:10px;">PC : <?= $data['photoReviewUri']['front'] ?>
                        <button type="button" data-clipboard-text="<?= $data['photoReviewUri']['front'] ?>" class="js-clipboard btn btn-gray btn-sm"
                                title="<?= $data['photoReviewUri']['front']; ?>">
                            복사하기
                        </button>
                    </div>
                    <div>모바일 : <?= $data['photoReviewUri']['mobile'] ?>
                        <button type="button" data-clipboard-text="<?= $data['photoReviewUri']['mobile'] ?>" class="js-clipboard btn btn-gray btn-sm"
                                title="<?= $data['photoReviewUri']['mobile']; ?>">
                            복사하기
                        </button>
                    </div>

                    <div class="notice-info">포토리뷰만을 깔끔한 UI로 출력하는 이미지 중심의 게시판입니다.</div>
                </td>
            </tr>

            <tr>
                <th>
                    포토리뷰 게시판 페이지당 <br>게시물 수
                </th>
                <td>
                    <select name="photoPagetCnt">
                        <?php foreach ($goodsPagetCntList as $val) {?>
                            <option value="<?=$val?>" <?= $selected['photoPagetCnt'][$val] ?>><?=$val?>개</option>
                        <?php }?>
                    </select>
                    <div class="notice-info">포토리뷰 게시판의 페이지별 게시글 노출 개수를 설정합니다.</div>
                </td>
            </tr>

            <tr>
                <th>
                    포토리뷰 게시판 위젯
                </th>
                <td>
                    <table class="table table-cols">
                        <tr class="form-inline">
                            <th class="width-sm">레이아웃</th>
                            <td><input type="text" name="photoWidget[cols]" class=" form-control width-2xs" value=""> *
                                <input name="photoWidget[rows]" class="form-control width-2xs" value="">
                                <span class="notice-info">포토리뷰 위젯생성 전에 레이아웃 및 썸네일 사이즈를 입력해주세요.</span>
                            </td>
                        </tr>
                        <tr>
                            <th>썸네일 사이즈</th>
                            <td class="form-inline">
                                <label class="radio-inline"><input type="radio" name="photoWidget[thumSizeType]" value="auto">페이지에
                                    자동맞춤</label>
                                <label class="radio-inline"><input type="radio" name="photoWidget[thumSizeType]" value="menual">수동설정</label>
                                <input type="text" class="form-control width-2xs" name="photoWidget[thumWidth]" value=""> px
                                <div class="notice-info">페이지에 자동맞춤으로 설정 시 위젯이 삽입된 페이지에 맞게 썸네일 이미지 사이즈가 자동 조절 됩니다.</div>
                            </td>
                        </tr>
                    </table>
                    <div>
                        <button type="button" class="btn btn-gray js-btn-widget" data-mode="photo">위젯생성</button>
                    </div>
                    <div class="notice-info">포토리뷰 게시판 위젯을 생성하여 원하는 페이지에 삽입할 수 있습니다.
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                    전체리뷰 게시판 주소
                </th>
                <td>
                    <div style="padding-bottom:10px;">PC : <?= $data['allReviewUri']['front'] ?>
                        <button type="button" data-clipboard-text="<?= $data['allReviewUri']['front'] ?>" class="js-clipboard btn btn-gray btn-sm"
                                title="<?= $data['allReviewUri']['front']; ?>">
                            복사하기
                        </button>
                    </div>
                    <div>모바일 : <?= $data['allReviewUri']['mobile'] ?>
                        <button type="button" data-clipboard-text="<?= $data['allReviewUri']['mobile'] ?>" class="js-clipboard btn btn-gray btn-sm"
                                title="<?= $data['allReviewUri']['mobile']; ?>">
                            복사하기
                        </button>
                    </div>

                    <div class="notice-info">등록된 모든 리뷰를 리스트 형태로 모아볼 수 있는 클래식한 게시판입니다.</div>
                </td>
            </tr>
            <tr>
                <th>
                    전체리뷰 게시판<br>페이지당 게시물 수
                </th>
                <td>
                    <input type="text" class="js-number form-control" maxlength="3"   name="articlePagetCnt"  value="<?=$data['articlePagetCnt']?>">
                    <div class="notice-info">전체리뷰 게시판의 페이지별 게시글 노출 개수를 설정합니다.</div>
                </td>
            </tr>
            <tr>
                <th>전체리뷰 게시판 위젯</th>
                <td>
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width-sm">
                            <col>
                        </colgroup>
                        <tbody>
                        <tr>
                            <th>위젯 형태</th>
                            <td class="form-inline">
                                <label class="radio-inline">
                                    <input type="radio" name="articleWidget[template]" value="default" <?= gd_isset($checked['useFl']['y']) ?> /> 기본형
                                    <u class="js-btn-preview-template" data-target="layerViewArticleTemplateDefault">(미리보기)</u>
                                    <div class="layer-article-template layerViewArticleTemplateDefault"><img src="<?= PATH_ADMIN_GD_SHARE ?>image/plusreview_template_article_default.png"></div>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="articleWidget[template]" value="simple" <?= gd_isset($checked['useFl']['n']) ?> /> 간편형
                                    <u class="js-btn-preview-template" data-target="layerViewArticleTemplateSimple">(미리보기)</u>
                                    <div class="layer-article-template layerViewArticleTemplateSimple"><img src="<?= PATH_ADMIN_GD_SHARE ?>image/plusreview_template_article_simple.png"></div>
                                </label>
                                <div class="notice-info">위젯형태 기본형은 PC에서 사용하실 것을 권장드립니다.<br/>(모바일에서 사용 시 정상적으로 출력되지 않을 수 있습니다.)</div>
                            </td>
                        </tr>
                        <tr>
                            <th>출력 리뷰 개수</th>
                            <td class="form-inline">
                                <input name="articleWidget[rows]" class="form-control width-2xs js-number" value="">
                                <span class="notice-info mgl30">전체리뷰 위젯생성 전에 출력할 리뷰 개수를 입력해주세요.</span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div>
                        <button type="button" class="btn btn-gray js-btn-widget" data-mode="article">위젯생성</button>
                    </div>
                    <div class="notice-info">전체리뷰 게시판 위젯을 생성하여 원하는 페이지에 삽입할 수 있습니다.</div>
                    <div class="notice-info">위젯이 삽입된 페이지에 맞게 전체리뷰 게시판 위젯사이즈가 자동 조절 됩니다.</div>
                </td>
            </tr>
            <tr>
                <th>
                    상품기준 리뷰 게시판 주소
                </th>
                <td>
                    <div style="padding-bottom:10px;">PC : <?= $data['goodsReviewUri']['front'] ?>
                        <button type="button" data-clipboard-text="<?= $data['goodsReviewUri']['front'] ?>" class="js-clipboard btn btn-gray btn-sm"
                                title="<?= $data['goodsReviewUri']['front']; ?>">
                            복사하기
                        </button>
                    </div>
                    <div>모바일 : <?= $data['goodsReviewUri']['mobile'] ?>
                        <button type="button" data-clipboard-text="<?= $data['goodsReviewUri']['mobile'] ?>" class="js-clipboard btn btn-gray btn-sm"
                                title="<?= $data['goodsReviewUri']['mobile']; ?>">
                            복사하기
                        </button>
                    </div>
                    <div class="notice-info">리뷰가 등록된 상품 기준으로 모아볼 수 있는 게시판입니다.</div>
                </td>
            </tr>
            <tr>
                <th>
                    상품기준 리뷰 게시판<br>페이지당 게시물 수
                </th>
                <td>
                    <select name="goodsPagetCnt">
                        <?php foreach ($goodsPagetCntList as $val) {?>
                        <option value="<?=$val?>" <?= $selected['goodsPagetCnt'][$val] ?>><?=$val?>개</option>
                        <?php }?>
                    </select>
                    <div class="notice-info">상품기준 리뷰 게시판의 페이지별 게시글 노출 개수를 설정합니다.</div>
                </td>
            </tr>
            <tr>
                <th>쓰기권한 설정</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="authWrite" value="all" <?= $checked['authWrite']['all'] ?>>전체(회원+비회원)</label>
                    <label class="radio-inline"><input type="radio" name="authWrite" value="member" <?= $checked['authWrite']['member'] ?>>회원전용(비회원제외)</label>
                    <label class="radio-inline"><input type="radio" name="authWrite" value="group" <?= $checked['authWrite']['group'] ?>
                                                       onclick="layer_register('authWriteGroup','authWriteGroup','info_authWriteGroup')">특정회원등급</label>
                    <button type="button" class="btn btn-sm btn-gray js-group-select">회원등급 선택</button>
                    <div id="authWriteGroup" class="selected-btn-group <?= ($data['authWriteGroup']) ? 'active' : '' ?>">
                        <?php if (is_array($data['authWriteGroup'])) { ?>
                            <h5>선택된 회원등급</h5>
                            <?php foreach ($data['authWriteGroup'] as $k => $v) { ?>
                            <div id="info_authWriteGroup_<?= $k ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="authWriteGroup[]" value="<?= $k ?>"/>
                                <span class="btn"><?= $v ?></span>
                                <button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#info_authWriteGroup_<?= $k ?>">삭제</button>
                            </div>
                            <?php }
                        } ?>
                        <label>
                    </div>
                    <?php // } ?>
                </td>
            </tr>
            <tr>
                <th>쓰기권한 추가 기준</th>
                <td class="form-inline">
                    <div>
                        <label class="radio-inline">
                            <input type="radio" name="authWriteExtra" value="all" <?= $checked['authWriteExtra']['all'] ?>>구매 여부와 상관없이 후기 작성 가능
                        </label>
                    </div>
                    <div class="pdt5">
                        <label class="radio-inline">
                            <input type="radio" name="authWriteExtra" value="buyer" <?= $checked['authWriteExtra']['buyer'] ?>>구매 내역이 존재하는 경우에만 후기 작성 가능
                        </label>
                        ( 작성 가능 시점 : <?= gd_select_box('authWriteStatus', 'authWriteStatus', $authWriteStatus, ' 이후', $selected['authWriteStatus']); ?> )
                        <div class="pdl20">
                            <label class="checkbox-inline"><input type="checkbox" name="authWriteStatusDurationFl" value="y" <?=$checked['authWriteStatusDurationFl']['y']?>>
                                작성 가능 시점으로부터
                                <?= gd_select_box('authWriteStatusDuration', 'authWriteStatusDuration', $authWriteStatusDuration, '일', $selected['authWriteStatusDuration']); ?>
                                이내만 후기 작성 가능.
                            </label>
                        </div>
                    </div>
                    <div class="notice-info">비회원은 주문상태에 따라 체크할 경우, 주문번호로 로그인한 후에 작성이 가능합니다.</div>
                    <div class="notice-info">각 주문상태마다 관리자가 추가한 주문상태는 포함되며, 클레임 주문상태들은 제외됩니다.</div>
                </td>
            </tr>
            <tr>
                <th>중복작성 제한</th>
                <td class="form-inline">
                    <div>
                        <label class="radio-inline">
                            <input type="radio" name="orderDuplicateIgnoreFl" value="y" <?= $checked['orderDuplicateIgnoreFl']['y'] ?>>제한없음
                        </label>
                    </div>
                    <div class="pdt5">
                        <label class="radio-inline">
                            <input type="radio" name="orderDuplicateIgnoreFl" value="n" <?= $checked['orderDuplicateIgnoreFl']['n'] ?>>1회만 작성 가능하도록 제한
                        </label>
                    </div>
                    <div class="notice-info">설정에 따른 마일리지 지급 여부는 하단 안내 영역을 참고해주세요.</div>
                </td>
            </tr>
            <tr>
                <th>리뷰 작성 예외 상품</th>
                <td class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="exceptGoodsFl" value="y" <?= gd_isset($checked['exceptGoodsFl']['y']) ?> /> 사용
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="exceptGoodsFl" value="n" <?= gd_isset($checked['exceptGoodsFl']['n']) ?> /> 사용안함
                    </label>
                </td>
            </tr>
            <tr id="configExceptGoods" style="display: none;">
                <th>예외상품 설정<br/><input type="button" class="btn btn-sm btn-gray" value="상품 선택" onclick="layer_except_register('goods','except');"/></th>
                <td>
                    <table id="exceptGoodsTable" class="table table-cols width74p display-block overflow-auto max-height250">
                        <thead>
                        <tr>
                            <th class="width5p"><input type="checkbox" class="js-checkall" data-target-name="exceptGoodsChk"/></th>
                            <th class="width5p">번호</th>
                            <th class="width10p">이미지</th>
                            <th>상품명</th>
                            <th class="width8p">삭제</th>
                        </tr>
                        </thead>
                        <tbody id="exceptGoods">
                        <?php
                        if (is_array($data['exceptGoods'])) {
                            foreach ($data['exceptGoods'] as $key => $val) {
                                echo '<tr id="idExceptGoods_' . $val['goodsNo'] . '">' . chr(10);
                                echo '<td class="center">' . '<input type="checkbox" name="exceptGoodsChk"/><input type="hidden" name="exceptGoods[]" value="' . $val['goodsNo'] . '" /></td>' . chr(10);
                                echo '<td class="center"><span class="number">' . ($key + 1) . '</span></td>' . chr(10);
                                echo '<td class="center">' . gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank') . '</td>' . chr(10);
                                echo '<td><a href="../goods/goods_register.php?goodsNo=' . $val['goodsNo'] . '" target="_blank">' . $val['goodsNm'] . '</a></td>' . chr(10);
                                echo '<td class="center"><input type="button"  data-toggle="delete"  data-target="#idExceptGoods_' .$val['goodsNo'] . '" value="삭제" class="btn btn-sm btn-gray"/></td>' . chr(10);
                                echo '</tr>' . chr(10);
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                    <input type="button" class="btn btn-sm btn-gray js-delete-exceptGoods js-all-exceptGoods" value="전체삭제">
                    <input type="button" class="btn btn-sm btn-white js-delete-exceptGoods" value="선택삭제">
                </td>
            </tr>
            <tr>
                <th>댓글 기능</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="memoFl" value="y" <?= gd_isset($checked['memoFl']['y']) ?> >사용함</label>
                    <label class="radio-inline"><input type="radio" name="memoFl" value="n" <?= gd_isset($checked['memoFl']['n']) ?> >사용안함</label>
                </td>
            </tr>
            <tr>
                <th>댓글권한 설정</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="authMemoWrite" value="all" <?= $checked['authMemoWrite']['all'] ?>>전체(회원+비회원)</label>
                    <label class="radio-inline"><input type="radio" name="authMemoWrite" value="admin" <?= $checked['authMemoWrite']['admin'] ?>>관리자 전용</label>
                    <label class="radio-inline"><input type="radio" name="authMemoWrite" value="member" <?= $checked['authMemoWrite']['member'] ?>>회원전용(비회원제외)</label>
                    <label class="radio-inline"><input type="radio" name="authMemoWrite" value="group" <?= $checked['authMemoWrite']['group'] ?>
                                                       onclick="layer_register('authMemoWriteGroup','authMemoWriteGroup','info_authMemoWriteGroup')">특정회원등급</label>
                    <label>
                        <button type="button" class="btn btn-sm btn-gray js-group-select">회원등급 선택</button>
                    </label>
                    <div id="authMemoWriteGroup" class="selected-btn-group <?= ($data['authMemoWriteGroup']) ? 'active' : '' ?>">
                        <?php if (is_array($data['authMemoWriteGroup'])) { ?>
                            <h5>선택된 회원등급</h5>
                            <?php foreach ($data['authMemoWriteGroup'] as $k => $v) { ?>
                            <div id="info_authMemoWriteGroup_<?= $k ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="authMemoWriteGroup[]" value="<?= $k ?>"/>
                                <span class="btn"><?= $v ?></span>
                                <button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#info_authMemoWriteGroup_<?= $k ?>">삭제</button>
                            </div>
                            <?php }
                        } ?>
                    </div>


                </td>
            </tr>
            <tr>
                <th>
                    주문목록 리뷰등록 설정
                </th>
                <td>
                    <label class="radio-inline"><input type="radio" name="mypageFl"
                                                       value="y" <?= gd_isset($checked['mypageFl']['y']) ?> /> 노출함</label>
                    <label class="radio-inline"><input type="radio" name="mypageFl"
                                                       value="n" <?= gd_isset($checked['mypageFl']['n']) ?> /> 노출안함</label>
                    <div class="notice-info">
                        노출함으로 설정 시 주문목록/배송조회에서 플러스리뷰 등록 버튼이 노출됩니다.
                    </div>
                    <div class="notice-info">
                        플러스리뷰 등록 버튼의 노출 기준은 쓰기권한 설정을 따릅니다.
                        구매자만 리뷰등록이 가능하도록 하시려면 쓰기권한 추가기준 설정을 '구매 내역이 존재하는 경우에만 리뷰 작성 가능'으로 설정해주세요.
                    </div>

                </td>
            </tr>
            <tr>
                <th>
                    작성자 표시방법
                </th>
                <td>
                    <label class="radio-inline"><input type="radio" name="writerDisplay"
                                                       value="name" <?= gd_isset($checked['writerDisplay']['name']) ?> /> 이름표시</label>
                    <label class="radio-inline"><input type="radio" name="writerDisplay"
                                                       value="id" <?= gd_isset($checked['writerDisplay']['id']) ?> /> 아이디표시</label>
                    <label class="radio-inline"><input type="radio" name="writerDisplay"
                                                       value="nick" <?= gd_isset($checked['writerDisplay']['nick']) ?> /> 닉네임표시</label>
                </td>
            </tr>
            <tr>
                <th>
                    관리자 표시방법
                </th>
                <td>
                    <div class="notice-info">관리자는 작성자에 '관리자'로 표시됩니다.</div>
                </td>
            </tr>
            <tr>
                <th>
                    작성자 노출제한
                </th>
                <td>
                    <select name="writerDisplayLimit" class="form-control">
                        <option value="0" <?= gd_isset($selected['writerDisplayLimit'][0]) ?>>전체노출</option>
                        <option value="1" <?= gd_isset($selected['writerDisplayLimit'][1]) ?>>1글자 노출</option>
                        <option value="2" <?= gd_isset($selected['writerDisplayLimit'][2]) ?>>2글자 노출</option>
                    </select>
                </td>
            </tr>
        </table>


        <div class="table-title">리뷰작성 혜택 설정</div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>마일리지 사용유무</th>
                <td>
                    <label class="radio-inline">
                        <input name="mileageFl" type="radio" value='y' <?= gd_isset($checked['mileageFl']['y']) ?> />사용함
                    </label>
                    <label class="radio-inline">
                        <input name="mileageFl" type="radio" value='n' <?= gd_isset($checked['mileageFl']['n']) ?> />사용안함
                    </label>
                </td>
            </tr>
            <tr>
                <th>리뷰혜택 안내문구</th>
                <td class="form-inline">
                <textarea rows="5" class="width100p form-control  js-maxlength" maxlength="250" name="reviewBenefitInfo"
                          placeholder="리뷰 작성 시 혜택 등을 구매자에게 안내해주세요."><?= $data['reviewBenefitInfo'] ?></textarea>
                    <div class="notice-info">리뷰 작성 시 혜택에 대한 안내문구를 입력해주세요. 리뷰 작성 영역에 노출됩니다.</div>
                </td>
            </tr>
            <tr class="js-miliage-use-tr">
                <th>마일리지 혜택 안내문구</th>
                <td class="form-inline">
                    <div style="padding-bottom:10px">
                        <input type="text" name="mileageAddGuid" class="width-3xl form-control" value="<?= $data['mileageAddGuid'] ?>" placeholder="리뷰를 등록하시겠습니까? 00자 이상 등록하시면 마일리지 혜택을 드립니다.">
                    </div>
                    <div class="notice-info">리뷰 등록 시 마일리지 지급 최소 글자수 충족이 되지 않을 경우 출력될 문구를 설정합니다. 입력하지 않을 경우 출력되지 않습니다.</div>
                </td>
            </tr>
            <tr class="js-miliage-use-tr">
                <th>마일리지 지급 방법</th>
                <td class="form-inline">
                    <label class="radio-inline">
                        <input name="mileageAddFl" type="radio" value='a' <?= gd_isset($checked['mileageAddFl']['a']) ?> />리뷰 등록 시 지급
                        ( 지급 시점 설정 : <?= gd_select_box('mileageAddDuration', 'mileageAddDuration', $mileageAddDuration, '일', $selected['mileageAddDuration'], '작성 즉시'); ?> 이후 지급 )
                    </label>
                    <label class="radio-inline">
                        <input name="mileageAddFl" type="radio" value='m' <?= gd_isset($checked['mileageAddFl']['m']) ?> />수동지급
                    </label>
                    <div class="notice-info">‘수동 지급’ 설정 시 플러스리뷰 게시글 관리 메뉴에서 마일리지 지급 처리가 가능합니다.</div>
                    <div class="notice-info">지급 시점 변경 시, 지급 신청 변경 처리 후 등록되는 리뷰 건부터 적용됩니다.</div>
                </td>
            </tr>
            <tr class="js-miliage-use-tr">
                <th>마일리지 지급 예외 설정</th>
                <td class="form-inline">
                    <div>
                        리뷰 작성 글자수 최소 <input type="text" name="mileageAddminLimit" class="js-number form-control" value="<?= $data['mileageAddminLimit'] ?>" maxlength="3"> 자 이상 입력 시에만 지급
                    </div>
                    <div class="pdt10 pdb10 order_goods_mileage_config">
                        구매 상품 가격 <input type="text" name="mileageAddLimitGoodsPrice" class="js-number form-control" value="<?= $data['mileageAddLimitGoodsPrice'] ?>">원 이상인 상품 리뷰 등록 시 마일리지 지급
                    </div>
                    <div class="notice-info order_goods_mileage_config">
                        구매상품에 대한 마일리지 지급 기준은 <a href="../member/member_mileage_basic.php" class="btn-link" target="_blank">회원 > 마일리지/예치금 관리 > 마일리지 기본 설정</a> 에 따릅니다<br/>
                        마일리지 기본 설정에서 사용 설정을 사용함으로 설정해주세요.
                    </div>
                    <div class="notice-info">중복작성 제한이 '제한없음'으로 설정된 경우에만 설정 가능합니다.</div>
                    <div class="notice-info">1회만 제공 선택 시, 동일상품/동일옵션으로 등록된 최초 리뷰에 대해 마일리지 지급됩니다.</div>
                </td>
            </tr>
            <tr class="js-miliage-use-tr">
                <th>마일리지 지급 설정</th>
                <td class="form-inline">
                    <div class="pdb10">
                        플러스리뷰 작성 시
                        <input type="text" name="mileageAmount[review]" class="js-number form-control" value="<?= $data['mileageAmount']['review'] ?>" required>
                        <select name="mileageUnit[review]" class="form-control">
                            <option value="default" <?= $selected['mileageUnit']['review']['default'] ?>>원</option>
                            <option value="percent" <?= $selected['mileageUnit']['review']['percent'] ?>>%</option>
                        </select>
                        지급
                        <div class="mileage_unit_percent display-none">
                            <div class="pdt10 pdl15">
                                <label class="checkbox-inline"><input type="checkbox" name="mileageAmountLimitFl" value="y" <?=$checked['mileageAmountLimitFl']['y']?>>
                                    최대 지급 마일리지 제한
                                    <input type="text" name="mileageAmountLimit[review]" class="js-number form-control" value="<?= $data['mileageAmountLimit']['review'] ?>" readonly>
                                    원
                                </label>
                            </div>
                            <div class="notice-info">
                                마일리지 지급 단위를 %로 설정한 경우의 마일리지 지급 기준은 <a href="../member/member_mileage_basic.php" class="btn-link" target="_blank">회원 > 마일리지/예치금 관리 > 마일리지 기본 설정</a>에 따릅니다.<br/>
                                절사 금액 기준은 <a href="../policy/base_currency_unit.php" class="btn-link" target="_blank">기본설정 > 기본정책 > 금액/단위 기준 설정</a>에서 설정 가능합니다.
                            </div>
                            <div class="notice-info">
                                최대 지급 마일리지 제한 설정 시, 1회 리뷰 등록에 대해 설정된 가격까지만 마일리지가 지급됩니다.<br/>
                                (포토리뷰/상품별 첫 리뷰 작성으로 인해 추가 지급되는 마일리지는 포함되지 않습니다.)
                            </div>
                        </div>
                    </div>
                    <div class="pdb10">
                        포토리뷰 작성 시
                        <input type="text" name="mileageAmount[photo]" class="js-number form-control" value="<?= $data['mileageAmount']['photo'] ?>" required>
                        원 추가지급
                    </div>
                    <div>
                        상품별 첫 리뷰 작성 시
                        <input type="text" name="mileageAmount[first]" class="js-number form-control" value="<?= $data['mileageAmount']['first'] ?>" required>
                        원 추가지급
                    </div>
                </td>
            </tr>
            <tr class="js-miliage-use-tr">
                <th>마일리지 중복 지급 제한</th>
                <td class="form-inline">
                    <div>
                        <label class="radio-inline">
                            <input type="radio" name="mileageDuplicateIgnoreFl" value="y" <?= $checked['mileageDuplicateIgnoreFl']['y'] ?>> 제한없음
                        </label>
                    </div>
                    <div class="pdt5">
                        <label class="radio-inline">
                            <input type="radio" name="mileageDuplicateIgnoreFl" value="n" <?= $checked['mileageDuplicateIgnoreFl']['n'] ?>> 최초 1회만 마일리지 지급
                        </label>
                    </div>
                    <div class="notice-info">설정에 따른 마일리지 지급 여부는 하단 안내 영역을 참고해주세요.</div>
                </td>
            </tr>
            <tr class="js-miliage-use-tr">
                <th>게시글 삭제 시 <br/> 마일리지 차감</th>
                <td>
                    <label class="radio-inline">
                        <input name="mileageDeleteFl" type="radio"
                               value='y' <?= gd_isset($checked['mileageDeleteFl']['y']) ?> />
                        사용함
                    </label>
                    <label class="radio-inline">
                        <input name="mileageDeleteFl" type="radio"
                               value='n' <?= gd_isset($checked['mileageDeleteFl']['n']) ?> />
                        사용안함
                    </label>
                </td>
            </tr>
            <tr class="js-miliage-use-tr">
                <th>차감 마일리지 <br/>부족 시 처리방법</th>
                <td>
                    <label class="radio-inline">
                        <input name="mileageLackAction" type="radio"
                               value='delete' <?= gd_isset($checked['mileageLackAction']['delete']) ?> />
                        마이너스 차감 후 게시글 삭제
                    </label>
                    <label class="radio-inline">
                        <input name="mileageLackAction" type="radio"
                               value='noDelete' <?= gd_isset($checked['mileageLackAction']['noDelete']) ?> />
                        게시글 삭제 불가
                    </label>
                </td>
            </tr>
        </table>

        <div class="table-title">기능설정</div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>추가정보 입력</th>
                <td>
                    <label class="radio-inline"><input name="addFormFl" type="radio" value='y' <?= gd_isset($checked['addFormFl']['y']) ?> />
                        사용함</label>
                    <label class="radio-inline">
                        <input name="addFormFl" type="radio"
                               value='n' <?= gd_isset($checked['addFormFl']['n']) ?> />
                        사용안함
                    </label>
                    <div class="notice-info">리뷰등록 시 추가로 입력할 옵션을 설정합니다.</div>
                </td>
            </tr>
            <tr class="js-add-form-tr">
                <th>추가정보 양식 설정</th>
                <td>
                    <div class="notice-info">추가정보 양식 설정 후 추가 시 구매자가 리뷰 작성을 할 때 아래와 같이 노출됩니다.</div>
                    <div class="notice-info">최대 10까지 등록 가능합니다.</div>
                    <div class="notice-info">추가정보 항목은 마우스 드래그 앤 드롭으로 순서 조정이 가능합니다.</div>
                    <button type="button" class="btn btn-sm btn-white btn-icon-plus js-btn-form-add mgb10">추가</button>

                    <table class="table table-cols width80p" id="addFormTable">
                        <tr>
                            <th class="width-md">항목명</th>
                            <th class="width-md">입력형태</th>
                            <th>입력값</th>
                            <th class="width-2xs center">사용</th>
                            <th class="width-2xs center">필수</th>
                            <th class="width-xs center">삭제</th>
                        </tr>
                        <input type="hidden" name="labelMaxNum" value="<?=$data['labelMaxNum']?>">
                        <tbody class="ui-sort-table">
                        <?php
                        $addForm = $data['addForm'];
                        foreach ($addForm['labelName'] as $k => $v) { ?>
                            <tr data-row="<?= $k ?>">
                                <td>
                                    <input type="text" name="addForm[labelName][<?= $k ?>]" value="<?= $addForm['labelName'][$k] ?>" class="form-control width-lg">
                                    <input type="hidden" name="addForm[labelNumber][<?= $k ?>]" value="<?= $addForm['labelNumber'][$k] ?>">
                                </td>
                                <td>
                                    <select name="addForm[inputType][<?= $k ?>]">
                                        <option value="text" <?php if ($addForm['inputType'][$k] == 'text') echo 'selected' ?>>텍스트 입력</option>
                                        <option value="select" <?php if ($addForm['inputType'][$k] == 'select') echo 'selected' ?>>셀렉트 박스</option>
                                    </select>
                                </td>
                                <td class="form-inline">
                                    <ul class="js-label-value-list">
                                        <?php if ($addForm['inputType'][$k] == 'select') {
                                            foreach ($addForm['labelValue'][$k] as $_k => $_v) {
                                                ?>
                                                <li><input type="text" class="form-control width80p" name="addForm[labelValue][<?= $k ?>][]" value="<?= $_v ?>">
                                                <?php if ($_k == 0) { ?>
                                                    <button type="button" class="btn btn-sm btn-white btn-icon-plus js-btn-labal-value-add mgl10">추가</button></li>
                                                <?php } else { ?>
                                                    <button type="button" class="btn btn-sm btn-white btn-icon-minus js-btn-labal-remove-li mgl10">삭제</button></li>
                                                    <?php
                                                } ?>


                                                <?php
                                            }
                                        } else { ?>
                                            <li><input type="text" class="form-control width-lg" name="addForm[labelValue][<?= $k ?>][]"
                                                       value="<?= $addForm['labelValue'][$k][0] ?>">
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </td>
                                <td class="center">
                                    <label class="checkbox-inline"><input type="checkbox" name="addForm[useFl][<?= $k ?>]"
                                                                          value="y" <?php if ($addForm['useFl'][$k] == 'y') echo 'checked' ?>></label>
                                </td>
                                <td class="center"><label class="checkbox-inline"><input type="checkbox" name="addForm[requireFl][<?= $k ?>]"
                                                                                         value="y" <?php if ($addForm['requireFl'][$k] == 'y') echo 'checked' ?>></label></td>
                                <td class="center">
                                    <button type="button" class="btn btn-sm btn-white btn-icon-minus js-btn-remove-tr">삭제</button>
                                </td>
                            </tr>
                            <?php
                        } ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <th>추가정보 검색 기능</th>
                <td class="form-inline">
                    <label class="radio-inline">
                        <input name="addFormSearchFl" type="radio"
                               value='y' <?= gd_isset($checked['addFormSearchFl']['y']) ?> />
                        사용함
                    </label>
                    <label class="radio-inline">
                        <input name="addFormSearchFl" type="radio"
                               value='n' <?= gd_isset($checked['addFormSearchFl']['n']) ?> />
                        사용안함
                    </label>
                    <div class="notice-info">리뷰 작성 시 선택된 추가정보 값을 기준으로 리뷰를 검색할 수 있는 기능을 PC쇼핑몰 및 모바일쇼핑몰에 출력합니다.</div>
                </td>
            </tr>
            <tr>
                <th>추가정보 검색 기능 타이틀 설정</th>
                <td class="form-inline">
                <input type="text" class="form-control width-xl js-maxlength" maxlength="20" name="addFormSearchTitle" value="<?= $data['addFormSearchTitle']?>" placeholder="ex) 나와 비슷한 회원 리뷰 보기" />
                    <div class="notice-info">추가정보 검색 기능의 타이틀을 설정합니다.</div>
                </td>
            </tr>
            <tr>
                <th>리뷰작성 안내 문구 설정</th>
                <td class="form-inline">
                    <textarea rows="5" class="width100p form-contro" name="reviewPlaceHolder"><?= $data['reviewPlaceHolder'] ?></textarea>
                    <div class="notice-info">리뷰를 작성하는 구매자들에게 표시될 안내문구를 설정합니다.</div>
                </td>
            </tr>
            <tr>
                <th>구매한 상품 옵션 노출</th>
                <td>
                    <label class="radio-inline">
                        <input name="displayOptionFl" type="radio"
                               value='y' <?= gd_isset($checked['displayOptionFl']['y']) ?> />
                        사용함
                    </label>
                    <label class="radio-inline">
                        <input name="displayOptionFl" type="radio"
                               value='n' <?= gd_isset($checked['displayOptionFl']['n']) ?> />
                        사용안함
                    </label>
                </td>
            </tr>
            <tr>
                <th>게시글 작성 시 평가</th>
                <td>
                    <span class="notice-info">플러스리뷰는 사용자 평가 기반의 기능이므로 평가 항목은 필수입니다.</span>
                </td>
            </tr>
            <tr>
                <th>게시글 추천</th>
                <td>
                    <label class="radio-inline">
                        <input name="recommendFl" type="radio"
                               value='y' <?= gd_isset($checked['recommendFl']['y']) ?> />
                        사용함
                    </label>
                    <label class="radio-inline">
                        <input name="recommendFl" type="radio"
                               value='n' <?= gd_isset($checked['recommendFl']['n']) ?> />
                        사용안함
                    </label>
                </td>
            </tr>
            <tr>
                <th>
                    NEW아이콘 효력
                </th>
                <td class="form-inline">
                    <input type="text" name="bdNewFl" id="newIconHour" size="5" class="form-control js-number wd-sm2" required
                           value="<?= gd_isset($data['newIconHour']) ?>"/> 시간
                </td>
            </tr>
        </table>

        <div class="table-title">리뷰작성 설정</div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>최소 글자수 제한</th>
                <td class="form-inline">
                    <div>
                        <label class="radio-inline"><input type="radio" name="minLimitLengthFl" value="n" <?= $checked['minLimitLengthFl']['n'] ?>> 제한없음</label>
                    </div>
                    <div>
                        <label class="radio-inline"><input type="radio" name="minLimitLengthFl" value="y" <?= $checked['minLimitLengthFl']['y'] ?>>
                             리뷰 작성 최소 글자수<input type="text" name="minContentsLength" value="<?= $data['minContentsLength'] ?>" class="form-control js-number"> 자
                        </label>
                        <label class="radio-inline">( <input type="checkbox" name="unMinLimitLengthFl" value="y" <?= $checked['unMinLimitLengthFl']['y'] ?>>
                            최소 글자수만 표기하고 등록 가능하도록 설정 )
                        </label>
                    </div>

                </td>
            </tr>
            <tr>
                <th>플러스리뷰<br/>금칙어 설정 사용여부</th>
                <td class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="prohibitedWordsFl" value="y" <?= gd_isset($checked['prohibitedWordsFl']['y']) ?> /> 사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="prohibitedWordsFl" value="n" <?= gd_isset($checked['prohibitedWordsFl']['n']) ?> /> 사용안함
                    </label>
                    <div class="notice-info">
                        금칙어 설정은 <a href="../board/board_forbidden.php" class="btn-link" target="_blank">게시판 > 게시판관리 > 게시판 금칙어 관리</a> 에서 설정 가능합니다.<br/>
                        사용여부를 "사용함"으로 설정한 경우 게시판 금칙어 관리 메뉴에서 금칙어를 설정해 주세요.
                    </div>
                </td>
            </tr>
            <tr>
                <th>첨부이미지 최대크기</th>
                <td class="form-inline">
                    <input type="text" required name="uploadMaxSize" value="<?= $data['uploadMaxSize'] ?>" class="form-control js-number">Mbyte(s)
                </td>
            </tr>
            <tr>
                <th>첨부이미지 등록 설정</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="uploadRequiredFl" value="y" <?= $checked['uploadRequiredFl']['y'] ?>>필수</label>
                    <label class="radio-inline"><input type="radio" name="uploadRequiredFl" value="n" <?= $checked['uploadRequiredFl']['n'] ?>>선택</label>
                </td>
            </tr>
            <tr>
                <th>첨부이미지 파일 개수
                </th>
                <td>
                    <select name="uploadMaxCount">
                        <?php for ($i = 4; $i >= 1; $i--) { ?>
                            <option value="<?= $i ?>" <?= $selected['uploadMaxCount'][$i] ?>><?= $i ?>개</option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>리뷰 승인 설정
                </th>
                <td>
                    <label class="radio-inline">
                        <input name="viewPermissionFl" type="radio"
                               value='a' <?= gd_isset($checked['viewPermissionFl']['a']) ?> />
                        등록 시 자동 승인
                    </label>
                    <label class="radio-inline">
                        <input name="viewPermissionFl" type="radio"
                               value='d' <?= gd_isset($checked['viewPermissionFl']['d']) ?> />
                        직접 승인
                    </label>
                    &nbsp;<span class="notice-info">‘직접 승인’ 설정 시 플러스리뷰 게시글 관리 메뉴에서 리뷰 승인 처리가 가능합니다.</span>
                </td>
            </tr>
            <tr class="js-review-view-tr">
                <th>미승인 리뷰 노출 설정
                </th>
                <td>
                    <label class="radio-inline">
                        <input name="viewAllowFl" type="radio"
                               value='y' <?= gd_isset($checked['viewAllowFl']['y']) ?> />
                        미승인 시 작성자에게만 노출
                    </label>
                    <label class="radio-inline">
                        <input name="viewAllowFl" type="radio"
                               value='n' <?= gd_isset($checked['viewAllowFl']['n']) ?> />
                        미승인 시 노출안함
                    </label>
                </td>
            </tr>
            <tr class="js-plusReview-view-tr">
                <th>리뷰 수정/삭제 제한
                </th>
                <td>
                    <label class="radio-inline">
                        <input name="modifyDelPermissionFl" type="radio"
                               value='n' <?= gd_isset($checked['modifyDelPermissionFl']['n']) ?> />
                        제한없음
                    </label>
                    <label class="radio-inline">
                        <input name="modifyDelPermissionFl" type="radio"
                               value='y' <?= gd_isset($checked['modifyDelPermissionFl']['y']) ?> />
                        수정/삭제 제한
                    </label>
                    <label class="radio-inline">
                        <input name="modifyDelPermissionFl" type="radio"
                               value='ma' <?= gd_isset($checked['modifyDelPermissionFl']['ma']) ?> />
                        혜택 지급 시 수정/삭제 제한
                    </label>
                </td>
            </tr>
        </table>
    </form>
    <script id="templateAddForm" type="text/html">
        <tr data-row="<%=index%>">
            <td>
                <input type="text" name="addForm[labelName][<%=index%>]" class="form-control width-lg" placeholder="ex)키">
                <input type="hidden" name="addForm[labelNumber][<%=index%>]" value="<%=maxNum%>">
            </td>
            <td>
                <select name="addForm[inputType][<%=index%>]">
                    <option value="text">텍스트 입력</option>
                    <option value="select">셀렉트 박스</option>
                </select>
            </td>
            <td class="form-inline">
                <ul class="js-label-value-list">
                    <li><input type="text" class="form-control width80p" placeholder="ex) 160~165" data-type="text" name="addForm[labelValue][<%=index%>][]"></li>
                </ul>
            </td>
            <td class="center"><label class="checkbox-inline"><input type="checkbox" name="addForm[useFl][<%=index%>]" checked value="y"></label></td>
            <td class="center"><label class="checkbox-inline"><input type="checkbox" name="addFormp[requireFl][<%=index%>]" value="y"></label></td>
            <td class="center">
                <button type="button" class="btn btn-sm btn-white btn-icon-minus js-btn-remove-tr">삭제</button>
            </td>
        </tr>
    </script>
</div>
<script id="progressBar" type="text/template">
    <div class="progress">
        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
            <span class="progress-current">1</span>/<%=total%>
        </div>
    </div>
    <div>
        상품후기게시판 통합중입니다..<br>
        복사 중 브라우저를 닫거나 컴퓨터를 종료하면 파일이 정상적으로 복사되지 않아<br>
        오류가 발생할 수 있으니 주의 바랍니다.
    </div>
</script>
<div class="information ">
    <h4>안내</h4>
    <div class="content">
        <div style="color: rgb(51, 51, 51); line-height: 22px; padding-top: 4px; font-size: 12px;">
            <strong>[중복작성 제한],[마일리지 중복 지급 제한] 설정은 어떻게 제공 되나요?</strong></div>
        <div style="color: rgb(136, 136, 136); line-height: 20px; font-size: 11px;">ㆍ쓰기권한 추가 기준 / 중복작성 제한 / 마일리지 중복 지급 제한 항목의 설정에 따라, 리뷰 작성 및 마일리지 지급 여부가 달라 집니다.<br>
            ㆍ구매여부 상관없이 작성 가능하며, 1회만 작성 가능하도록 제한 시, 마일리지 중복 지급 제한은 1회만 마일리지 지급으로 자동 설정 됩니다.</div>

        <table class="content_table text-center mgt10">
            <colgroup>
                <col class="width-lg"><col class="width-lg"><col class="width-lg"><col>
            </colgroup>
            <thead>
            <tr>
                <th class="text-center">쓰기권한 추가 기준</th>
                <th class="text-center">중복작성 제한</th>
                <th class="text-center">마일리지 중복 지급 제한</th>
                <th class="text-center">리뷰 작성 및 마일리지 지급 여부</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th class="text-center" rowspan="3">구매여부 상관 없이 작성 가능</th>
                <td rowspan="2">제한 없음</td>
                <td>제한없음</td>
                <td class="text-left">
                    ㆍ리뷰작성 : 동일 상품에 계속 작성 가능<br>
                    ㆍ마일리지 : 리뷰 작성 시 마다 지급
                </td>
            </tr>
            <tr>
                <td>1회만 마일리지 지급</td>
                <td class="text-left">
                    ㆍ리뷰작성 : 동일 상품에 계속 작성 가능<br>
                    ㆍ마일리지 : 동일 상품에 대해 최초 1회만 지급
                </td>
            </tr>
            <tr>
                <td>1회만 작성 가능하도록 제한</td>
                <td>1회만 마일리지 지급</td>
                <td class="text-left">
                    ㆍ리뷰작성 : 동일 상품에 1회만 작성 가능<br>
                    ㆍ마일리지 : 동일 상품에 대해 최초 1회만 지급
                </td>
            </tr>
            <tr>
                <th class="text-center" rowspan="4">구매 내역이 존재하는 경우에만<br>후기 작성 가능</th>
                <td rowspan="2">제한 없음</td>
                <td>제한없음</td>
                <td class="text-left">
                    ㆍ리뷰작성 : 주문 번호 내 상품에 대해 중복 리뷰 작성 가능<br>
                    ㆍ마일리지 : 리뷰 작성 시 마다 지급
                </td>
            </tr>
            <tr>
                <td>1회만 마일리지 지급</td>
                <td class="text-left">
                    ㆍ리뷰작성 : 주문 번호 내 상품에 대해 중복 리뷰 작성 가능<br>
                    ㆍ마일리지 : 동일 상품에 대해 최초 1회만 지급
                </td>
            </tr>
            <tr>
                <td rowspan="2">1회만 작성 가능하도록 제한</td>
                <td>제한 없음</td>
                <td class="text-left">
                    ㆍ리뷰작성 : 주문번호 내 상품에 대해 최초 1회만 리뷰 작성 가능<br><span style="padding: 0 0 0 63px;">(동일상품 재주문 시 리뷰 작성 가능)</span><br>
                    ㆍ마일리지 : 리뷰 작성 시 마다 지급
                </td>
            </tr>
            <tr>
                <td>1회만 마일리지 지급</td>
                <td class="text-left">
                    ㆍ리뷰작성 : 주문번호 내 상품에 대해 최초 1회만 리뷰 작성 가능<br><span style="padding: 0 0 0 63px;">(동일상품 재주문 시 리뷰 작성 가능)</span><br>
                    ㆍ마일리지 : 동일 상품에 대해 최초 1회만 지급
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>