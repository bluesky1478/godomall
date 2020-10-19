<form id="frmRegister" name="frmRegister" action="./order_ps.php" method="post">
    <input type="hidden" name="mode" value="register_frequency" />
    <input type="hidden" name="sno" value="<?= gd_isset($data['sno']); ?>" />
    <input type="hidden" name="groupNm" value="<?= gd_isset($data['groupNm']); ?>" />
    <table class="table table-cols no-title-line">
        <colgroup>
            <col class="width-md"/>
            <col class="width-md"/>
        </colgroup>
        <tbody>
        <tr>
            <th class="require">그룹</th>
            <td colspan="3">
                <div class="radio">
                    <label>
                        <input type="radio" name="tmpCheckedGroup" value="old" class="js-order-same" /> 기존그룹
                    </label>
                    <label>
                        <?= gd_select_box('selectGroupNm', 'selectGroupNm', $groups, null, $data['groupNm'], null);?>
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="tmpCheckedGroup" value="new" class="js-order-same" /> 신규그룹
                    </label>
                    <label>
                        <input type="text" name="inputGroupNm" value="" class="form-control" />
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">이름</th>
            <td colspan="3">
                <div class="form-inline">
                    <input type="text" name="name" value="<?= gd_isset($data['name']); ?>" class="form-control"/>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">주소</th>
            <td colspan="3">
                <div class="form-inline">
                    <input type="text" name="zonecode" value="<?= gd_isset($data['zonecode']); ?>" size="5" class="form-control"/>
                    <input type="hidden" name="zipcode" value="<?= gd_isset($data['zipcode']); ?>"/>
                    <span id="zipcodeText" class="number <?php if (strlen($data['zipcode']) != 7) { echo 'display-none';} ?>">(<?php echo $data['zipcode'];?>)</span>
                    <!--<input type="button" onclick="postcode_search('zonecode', 'address', 'zipcode');" value="우편번호찾기" class="btn btn-sm btn-gray"/>-->
                    <input type="button" onclick="postcode('basic');" value="우편번호찾기" class="btn btn-sm btn-gray"/>
                </div>
                <div class="mgt5">
                    <input type="text" name="address" value="<?= gd_isset($data['address']); ?>" class="form-control"/>
                </div>
                <div class="mgt5">
                    <input type="text" name="addressSub" value="<?= gd_isset($data['addressSub']); ?>" class="form-control"/>
                </div>
            </td>
        </tr>
        <tr>
            <th>이메일</th>
            <td colspan="3">
                <div class="form-inline js-email-select" data-target-name="email[]" data-origin-data="<?= $data['email'][1] ?>">
                    <input type="text" name="email[]" value="<?= $data['email'][0] ?>" class="form-control">
                    <label class="control-label">@</label>
                    <input type="text" name="email[]" value="<?= $data['email'][1] ?>" class="form-control">
                    <?= gd_select_box_by_mail_domain(null, null, null, $data['email'][1], '직접입력'); ?>
                </div>
            </td>
        </tr>
        <tr>
            <th>전화번호</th>
            <td colspan="3">
                <div class="form-inline">
                    <input type="text" name="phone" value="<?= implode("-",$data['phone']) ?>" maxlength="12" class="form-control js-number-only"/>
                </div>
            </td>
        </tr>
        <tr>
            <th>휴대폰번호</th>
            <td colspan="3">
                <div class="form-inline">
                    <input type="text" name="cellPhone" value="<?= implode("-",$data['cellPhone']) ?>" maxlength="12" class="form-control js-number-only"/>
                </div>
            </td>
        </tr>
        <tr>
            <th>메모</th>
            <td colspan="3">
                <div class="form-inline">
                    <textarea name="memo" rows="3" class="form-control"><?= gd_isset($data['memo']); ?></textarea>
                </div>
            </td>
        </tr>
        </tbody>
        <!-- 사업자정보 start -->
        <tbody class="js-search-detail" style="display: none;">
        <tr>
            <th>사업자번호</th>
            <td colspan="3">
                <div class="form-inline">
                    <input type="text" name="businessNo[]" value="<?php echo $data['businessNo'][0]; ?>" maxlength="3" class="form-control js-number-only width-3xs"/> -
                    <input type="text" name="businessNo[]" value="<?php echo $data['businessNo'][1]; ?>" maxlength="2" class="form-control js-number-only width-3xs"/> -
                    <input type="text" name="businessNo[]" value="<?php echo $data['businessNo'][2]; ?>" maxlength="5" class="form-control js-number-only width-3xs"/>
                </div>
            </td>
        </tr>
        <tr>
            <th>회사명</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="bCompanyNm" value="<?php echo $data['bCompanyNm']; ?>" class="form-control"/>
                </div>
            </td>
            <th>대표자명</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="bCeoNm" value="<?php echo $data['bCeoNm']; ?>" class="form-control"/>
                </div>
            </td>
        </tr>
        <tr>
            <th>업태</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="bService" value="<?php echo $data['bService']; ?>" class="form-control"/>
                </div>
            </td>
            <th>종목</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="bItem" value="<?php echo $data['bItem']; ?>" class="form-control"/>
                </div>
            </td>
        </tr>
        <tr>
            <th>사업장 주소</th>
            <td colspan="3">
                <div class="form-inline mgb5">
                    <input type="text" name="bZonecode" value="<?php echo $data['bZonecode']; ?>" maxlength="5" class="form-control width-2xs"/>
                    <input type="hidden" name="bZipcode" value="<?php echo $data['bZipcode']; ?>"/>
                    <span id="bZipcodeText" class="number <?php if (strlen($data['bZipcode']) != 7) { echo 'display-none'; } ?>">(<?php echo $data['bZipcode']; ?>)</span>
                    <input type="button" onclick="postcode('business');" value="우편번호찾기" class="btn btn-sm btn-gray"/>
                </div>
                <div class="mgt5">
                    <input type="text" name="bAddress" value="<?php echo $data['bAddress']; ?>" class="form-control width-2xl"/>
                </div>
                <div class="mgt5">
                    <input type="text" name="bAddressSub" value="<?php echo $data['bAddressSub']; ?>" class="form-control width-2xl"/>
                </div>
            </td>
        </tr>
        <tr>
            <th>발행 이메일</th>
            <td colspan="3">
                <div class="form-inline js-email-select" data-target-name="bEmail[]" data-origin-data="<?= $data['bEmail'][1] ?>">
                    <input type="text" name="bEmail[]" value="<?php echo $data['bEmail'][0]; ?>"class="form-control width-lg" placeholder="미입력 시 주문자의 이메일로 발행"/> @
                    <input type="text" id="bEmail" name="bEmail[]" value="<?php echo $data['bEmail'][1]; ?>" class="form-control width-md js-email-domain"/>
                    <?= gd_select_box_by_mail_domain(null, null, null, $data['bEmail'][1], '직접입력'); ?>
                </div>
            </td>
        </tr>
        </tbody>
        <!-- 사업자정보 end -->
    </table>
    <button type="button" class="btn btn-sm btn-link js-search-toggle bold">사업자정보 입력<span>∧</span></button>
    <div class="table-btn">
        <button type="button" class="btn btn-white btn-lg js-layer-close">취소</button>
        <input type="submit" class="btn btn-lg btn-black" value="저장" />
    </div>
</form>

<script type="text/javascript">
    <!--
    $(function() {
        // 폼체크
        $('#frmRegister').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
                layer_close();
            },
            rules: {
                tmpCheckedGroup: 'required',
                groupNm: 'required',
                name: 'required',
                address: 'required',
                addressSub: 'required',
                'phone[]': {
                    number: true,
                },
            },
            messages: {
                tmpCheckedGroup: '그룹을 선택해주세요.',
                groupNm: '그룹을 선택 혹은 입력해주세요.',
                name: '이름을 입력해주세요.',
                address: '주소를 입력해주세요.',
                addressSub: '주소를 입력해주세요.',
                'phone[]': {
                    number: '숫자만 입력해주세요.'
                },
            }
        });

        // 그룹선택 라디오
        $('input[name=tmpCheckedGroup]').click(function(e){
            $('input[name=groupNm]').val('<?=gd_isset($data['groupNm'])?>');
            if ($(this).val() == 'old') {
                $('select[name=selectGroupNm]').prop('disabled', false);
                $('input[name=inputGroupNm]').prop('disabled', true);
                $('select[name=selectGroupNm]').trigger('change');
            } if ($(this).val() == 'new') {
                $('select[name=selectGroupNm]').prop('disabled', true);
                $('input[name=inputGroupNm]').prop('disabled', false);
            }
        });
        $('input[name=tmpCheckedGroup]').eq(0).trigger('click');

        // 기존그룹 선택시 데이터 할당
        $('select[name=selectGroupNm]').change(function(e){
            var value = '';
            if ($(this).val() != '0') {
                value = $(this).val();
            }
            $('input[name=groupNm]').val(value);
        });

        // 신규그룹 글 작성 후
        $('input[name=inputGroupNm]').blur(function(e){
            var value = $(this).val();
            $('input[name=groupNm]').val(value);
        });

        // 자주쓰는 주소 삭제
        $('.js-btn-delete').click(function (e) {
            $.validator.setDefaults({dialog: false});
            if ($('input[name="sno"]:checked').length > 0) {
                BootstrapDialog.confirm({
                    type: BootstrapDialog.TYPE_DANGER,
                    title: '주문삭제',
                    message: '선택된 ' + $('input[name="sno[]"]:checked').length + '개의 자주쓰는 주소를 정말로 삭제 하시겠습니까?<br />삭제 시 정보는 복구 되지 않습니다.',
                    closable: false,
                    callback: function(result) {
                        if (result) {
                            $('#frmList').submit();
                        }
                    }
                });
            } else {
                $('#frmList').submit();
            }
        });

        // 자주쓰는 주소 수정 시 사업자정보 입력되어있다면 display block처리
        /*var layerFrm_sno = $('#frmRegister input[name=sno]').val();
        var layerFrm_cNm = $('#frmRegister input[name=bCompanyNm]').val();

        if( layerFrm_sno != null && layerFrm_cNm != null ){
            $("#frmRegister .js-search-detail").css("display", "block");
            clickChk = 0;
        }*/
    });
    // 숫자만 입력하기 원하는 경우 ,(쉼표) .(콤마) -(마이너스) 입력안됨
    if ($('input.js-number-only').length > 0) {
        $('input.js-number-only').each(function () {
            $(this).number_only("d");
        });
    }

    var clickChk = 0;
    //$('.js-search-toggle').on('click', function (e) {
    $('.js-search-toggle').click(function(){
        var form = $(this).closest('form');
        var tbodyObj = form.find('.js-search-detail');

        if (clickChk == 0) {
            tbodyObj.hide();
            $(this).find('span').text('∨');
            $(this).removeClass('opened');
            clickChk++;

            // input val 초기화(등록 시)
            if ($('input[name="sno[]"]').val() == null){
                $('input[name="businessNo[]"]').val('');
                $('input[name="bCompanyNm"]').val('');
                $('input[name="bCeoNm"]').val('');
                $('input[name="bService"]').val('');
                $('input[name="bItem"]').val('');
                $('input[name="bZonecode"]').val('');
                $('input[name="bZipcode"]').val('');
                $('input[name="bAddress"]').val('');
                $('input[name="bAddressSub"]').val('');
                $('input[name="bEmail[]"]').val('');
            }
        } else {
            tbodyObj.show();
            $(this).find('span').text('∧');
            $(this).addClass('opened');
            clickChk = 0;
        }
    });
    $('.js-search-toggle').trigger('click');

    function postcode(mode){
        if(mode == 'basic'){    // 기본 주소
            postcode_search('zonecode', 'address', 'zipcode');
        }else if(mode == 'business'){   // 사업장 주소
            postcode_search('bZonecode', 'bAddress', 'bZipcode');
        }
    }
    //-->
</script>
