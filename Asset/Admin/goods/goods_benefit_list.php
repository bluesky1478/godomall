<script type="text/javascript">
    <!--
    $(document).ready(function () {

        $('.benefitConfig').click(function(){
            if($('input[name="goodsBenefitUse"]:checked').val() == 'n') {
                dialog_confirm('상품 혜택 관리 기능을 사용하지 않으시겠습니까? <br>혜택이 적용된 모든 상품의 상품 할인/혜택 설정은 초기화 됩니다.', function (result) {
                    if (result) {
                        $("#frmBenfitConfig").submit();
                    }
                });
            }else{
                $("#frmBenfitConfig").submit();
            }
        });

        $("#frmBenfitConfig").validate({
            submitHandler: function (form) {
                form.target='ifrmProcess';
                form.submit();
            },
        });

        // 삭제
        $('button.checkDelete').click(function () {
            var chkCnt = $('input[name*="sno["]:checkbox:checked').length;
            if (chkCnt == 0) {
                alert('선택된 혜택이 없습니다.');
                return;
            }

            dialog_confirm('선택한 ' + chkCnt + '개의 상품 혜택을 삭제하시겠습니까?<br/>삭제시 해당 혜택이 적용된 상품의 상품 할인/혜택 설정은 초기화 됩니다.', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('delete');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('action', './goods_benefit_ps.php');
                    $('#frmList').submit();
                }
            });
        });

        // 등록
        $('#checkRegister').click(function () {
            location.href = './goods_benefit_register.php';
        });


        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchBase').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchBase').submit();
        });

        $('.js-member-group-benefit').click(function(){
            var sno = $(this).data('sno');

            var title = "할인 혜택 상세보기";
            $.get('../goods/layer_benefit_detail.php',{ sno : sno }, function(data){

                data = '<div id="viewInfoForm">'+data+'</div>';

                var layerForm = data;

                BootstrapDialog.show({
                    title:title,
                    size: get_layer_size('normal'),
                    message: $(layerForm),
                    closable: true
                });
            });
        });
    });

    //-->
</script>

<form id="frmBenfitConfig" name="frmBenfitConfig" action="./goods_benefit_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="config">
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <?php
            if($goodsBenefitUse == 'y') {
            ?>
            <input type="button" id="checkRegister" value="상품 혜택 등록" class="btn btn-red-line"/>
                <?php
            }
            ?>
        </div>
    </div>
    <div class="table-title gd-help-manual">
        기본설정
        <span class="depth-toggle pdb5"><button type="button" class="btn btn-red benefitConfig">저장</button></span>
    </div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-lg">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th>상품 혜택 관리<br>
                사용 설정
            </th>
            <td>
                <label class="radio-inline"><input type="radio" name="goodsBenefitUse" value="n" <?php if($goodsBenefitUse == 'n') echo "checked" ?>>사용안함</label>
                <label class="radio-inline"><input type="radio" name="goodsBenefitUse" value="y" <?php if($goodsBenefitUse == 'y') echo "checked" ?>>사용함</label>
                <p class="notice-info">
                    상품 혜택 관리 기능 사용 시 쇼핑몰 이용 속도가 느려질 수 있습니다.
                </p>
            </td>
        </tr>
    </table>
</form>

<?php
if($goodsBenefitUse == 'y') {
?>
    <form id="frmSearchBase" name="frmSearchBase" method="get" class="js-form-enter-submit">
        <div class="table-title gd-help-manual">
            상품 혜택 검색
        </div>
        <div class="search-detail-box">
            <table class="table table-cols">
                <colgroup>
                    <col class="width-sm"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>혜택명</th>
                    <td colspan="3">
                        <input type="text" name="benefitNm" value="<?= $search['benefitNm']; ?>"
                               class="form-control width-lg"/>
                    </td>
                </tr>
                <tr>
                    <th>기간검색</th>
                    <td colspan="3">
                        <div class="form-inline">
                            <select name="searchDateFl" class="form-control">
                                <option value="regDt" <?= gd_isset($selected['searchDateFl']['regDt']); ?>>등록일</option>
                                <option value="modDt" <?= gd_isset($selected['searchDateFl']['modDt']); ?>>수정일</option>
                            </select>

                            <div class="input-group js-datepicker">
                                <input type="text" class="form-control width-xs" name="searchDate[]"
                                       value="<?= $search['searchDate'][0]; ?>">
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                            </div>

                            ~
                            <div class="input-group js-datepicker">
                                <input type="text" class="form-control width-xs" name="searchDate[]"
                                       value="<?= $search['searchDate'][1]; ?>">
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                            </div>

                        <?=gd_search_date($search['searchPeriod'])?>

                    </td>
                </tr>
                <tr>
                    <th>진행상태</th>
                    <td>
                        <label class="radio-inline"><input type="radio" name="goodsBenefitState"
                                                           value="" <?= gd_isset($checked['goodsBenefitState']['']); ?> />전체</label>
                        <label class="radio-inline"><input type="radio" name="goodsBenefitState"
                                                           value="d" <?= gd_isset($checked['goodsBenefitState']['d']); ?> />대기</label>
                        <label class="radio-inline"><input type="radio" name="goodsBenefitState"
                                                           value="n" <?= gd_isset($checked['goodsBenefitState']['n']); ?> />진행중</label>
                        <label class="radio-inline"><input type="radio" name="goodsBenefitState"
                                                           value="e" <?= gd_isset($checked['goodsBenefitState']['e']); ?> />종료</label>
                    </td>
                </tr>
                <tr>
                    <th>진행유형</th>
                    <td>
                        <label class="radio-inline"><input type="radio" name="benefitUseType"
                                                           value="" <?= gd_isset($checked['benefitUseType']['']); ?> />전체</label>
                        <label class="radio-inline"><input type="radio" name="benefitUseType"
                                                           value="nonLimit" <?= gd_isset($checked['benefitUseType']['nonLimit']); ?> />제한
                            없음</label>
                        <label class="radio-inline"><input type="radio" name="benefitUseType"
                                                           value="newGoodsDiscount" <?= gd_isset($checked['benefitUseType']['newGoodsDiscount']); ?> />신상품
                            할인</label>
                        <label class="radio-inline"><input type="radio" name="benefitUseType"
                                                           value="periodDiscount" <?= gd_isset($checked['benefitUseType']['periodDiscount']); ?> />특정기간
                            할인</label>
                    </td>
                </tr>
                <tr>
                    <th>혜택대상</th>
                    <td>
                        <label class="radio-inline"><input type="radio" name="goodsDiscountGroup"
                                                           value="" <?= gd_isset($checked['goodsDiscountGroup']['']); ?> />전체</label>
                        <label class="radio-inline"><input type="radio" name="goodsDiscountGroup"
                                                           value="all" <?= gd_isset($checked['goodsDiscountGroup']['all']); ?> />전체(회원+비회원)</label>
                        <label class="radio-inline"><input type="radio" name="goodsDiscountGroup"
                                                           value="member" <?= gd_isset($checked['goodsDiscountGroup']['member']); ?> />회원전용(비회원제외)</label>
                        <label class="radio-inline"><input type="radio" name="goodsDiscountGroup"
                                                           value="group" <?= gd_isset($checked['goodsDiscountGroup']['group']); ?> />특정회원등급</label>
                    </td>
                </tr>
            </table>
        </div>


        <div class="table-btn">
            <input type="submit" value="검색" class="btn btn-lg btn-black">
        </div>

        <div class="table-header">
            <div class="pull-left">
                검색 <strong><?= number_format($page->recode['total']); ?></strong>개 /
                전체 <strong><?= number_format($page->recode['amount']); ?></strong>개
            </div>
            <div class="pull-right form-inline">
                <?= gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
                <?= gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
            </div>
        </div>

    </form>


    <div>
        <form id="frmList" action="" method="get" target="ifrmProcess">
            <input type="hidden" name="mode" value="">
            <table class="table table-rows">
                <thead>
                <tr>
                    <th class="width5p center"><input type="checkbox" class="js-checkall" data-target-name="sno"/></th>
                    <th class="width5p">번호</th>
                    <th class="width20p">혜택명</th>
                    <th class="width10p center">진행유형</th>
                    <th class="width30p center">진행기간</th>
                    <th class="width10p center">할인혜택 내용</th>
                    <th class="width10p center">혜택 대상</th>
                    <th class="width10p center">등록일 / 수정일</th>
                    <th class="width5p center">수정</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (gd_isset($data)) {
                    $arrUseType = array('nonLimit' => '제한없음', 'newGoodsDiscount' => '신상품 할인', 'periodDiscount' => '특정기간 할인');
                    $arrDiscountGroup = array('all' => '전체', 'member' => '회원전용', 'group' => '특정회원등급');
                    $arrNewGoodsReg = array('regDt' => '등록일', 'modDt' => '수정일');
                    $arrNewGoodsDate = array('day' => '일', 'hour' => '시간');


                    foreach ($data as $key => $val) {
                        $val['goodsDiscountGroupMemberInfo'] = json_decode($val['goodsDiscountGroupMemberInfo'], true);
                        $stateText = '';
                        if ($val['benefitUseType'] == 'periodDiscount') {

                            if ($val['periodDiscountStart'] < date('Y-m-d H:i:s') && $val['periodDiscountEnd'] > date('Y-m-d H:i:s')) {
                                $stateText = "<span class='text-blue'>진행중</span><br>";
                            } else if ($val['periodDiscountEnd'] < date('Y-m-d H:i:s')) {
                                $stateText = "<span class='text-red'>종료</span><br>";
                            } else if ($val['periodDiscountStart'] > date('Y-m-d H:i:s')) {
                                $stateText = "<span>대기중</span><br>";
                            }
                        }
                        if ($val['benefitUseType'] == 'nonLimit') {
                            $benefitPeriod = '<span class="text-blue">' . $arrUseType[$val['benefitUseType']] . '</span>';
                        } else if ($val['benefitUseType'] == 'newGoodsDiscount') {
                            $benefitPeriod = '<span class="text-blue">상품' . $arrNewGoodsReg[$val['newGoodsRegFl']] . '부터 ' . $val['newGoodsDate'] . $arrNewGoodsDate[$val['newGoodsDateFl']] . '까지</span>';
                        } else {
                            $benefitPeriod = '<span>' . gd_date_format("Y-m-d H:i", $val['periodDiscountStart']) . ' ~ ' . gd_date_format("Y-m-d H:i", $val['periodDiscountEnd']) . '</span>';
                        }
                        ?>
                        <tr>
                            <td class="center"><input type="checkbox" name="sno[<?= $val['sno']; ?>]"
                                                      value="<?= $val['sno']; ?>"/></td>
                            <td class="center number"><?= number_format($page->idx--); ?></td>
                            <td>
                                <?= $val['benefitNm']; ?>
                                <div>
                                    <?php
                                    // 상품 아이콘
                                    if (empty($val['goodsIcon']) === false && is_array($val['goodsIcon']) === true) {
                                        foreach ($val['goodsIcon'] as $iKey => $iVal) {
                                            echo gd_html_image(UserFilePath::icon('goods_icon', $iVal['iconImage'])->www(), $iVal['iconNm']) . ' ';
                                        }
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="center"><?= $arrUseType[$val['benefitUseType']]; ?></td>
                            <td><?= $stateText ?><?= $benefitPeriod ?></td>
                            <td class="center">
                                <?php
                                $goodsDiscountGroup = gd_isset($val['goodsDiscountGroup'], 'all');
                                if (in_array($goodsDiscountGroup, ['all', 'member']) === true) {
                                    if ($val['goodsDiscountUnit'] == 'percent') {
                                        echo $val['goodsDiscount'] . '%';
                                    } else {
                                        echo gd_money_format($val['goodsDiscount']) . gd_currency_default();
                                    }
                                } else {
                                    ?>

                                    <button type="button" class="btn btn-sm btn-white js-member-group-benefit"
                                            data-sno="<?= $val['sno']; ?>">보기
                                    </button>
                                    <?php
                                }
                                ?>
                            </td>
                            <td class="center"><?= $arrDiscountGroup[$val['goodsDiscountGroup']]; ?></td>
                            <td class="center"><?= gd_date_format('Y-m-d', $val['regDt']); ?>
                                <br><?= gd_date_format('Y-m-d', $val['modDt']); ?></td>
                            <td class="center">
                                <a href="./goods_benefit_register.php?sno=<?= $val['sno']; ?>"
                                   class="btn btn-white btn-xs"> 수정</a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td class="center" colspan="8">검색된 정보가 없습니다.</td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>


            <div class="table-action">
                <div class="pull-left">
                    <button type="button" class="btn btn-white checkDelete">선택 삭제</button>
                </div>
            </div>


        </form>
        <div class="center"><?= $page->getPage(); ?></div>
    </div>

<?php
}
?>
