<?php
$basicCommissionText = $basicCommissionDeliveryText = '';
if($data['scmCommissionSno'] == 0 ) {
    $basicCommissionText = ' (기본 수수료)';
}
if($data['scmCommissionDeliverySno'] == 0) {
    $basicCommissionDeliveryText = ' (기본 수수료)';
}
if($data['applyCommissionLog'] && ($data['delAbleFl'] == 'n' && $data['modifyAbleFl'] == 'n')) {
    $overTimeCommissionData = explode(INT_DIVISION, $data['applyCommissionLog']);
    if(count($overTimeCommissionData) == 2) {
        $basicCommissionText = $basicCommissionDeliveryText = '';
        if(empty($overTimeCommissionData[0]) == false && empty($overTimeCommissionData[1]) == false) {
            if($overTimeCommissionData[0] == $overTimeCommissionData[1]) {
                $data['commissionSameFl'] = true;
            } else {
                $data['commissionSameFl'] = false;
            }
        }
    }
}
?>
<style>
    @media screen and (-webkit-min-device-pixel-ratio:0) {
        .popup-scm-commission-register table:not(.not-flex) table {
            display: flex;
            flex-flow: column;
            width: 100%;
        }
        .popup-scm-commission-register table:not(.not-flex) table tbody {
            overflow-y: auto;
            max-height: 220px;
        }
        .popup-scm-commission-register table:not(.not-flex) table tbody tr,
        .popup-scm-commission-register table:not(.not-flex) table tfoot tr {
            width: 100%;
        }
        .popup-scm-commission-register table:not(.not-flex) table thead,
        .popup-scm-commission-register table:not(.not-flex) table tbody tr,
        .popup-scm-commission-register table:not(.not-flex) table tfoot tr {
            display: table;
        }
        .popup-scm-commission-register table:not(.not-flex) table tbody tr td:first-child{width:8%;}
        .popup-scm-commission-register table:not(.not-flex) table tbody tr td:last-child{width:68px;}
    }

</style>
<div class="popup-scm-commission-register">
    <div class="popup-page-header js-affix">
        <h3>공급사 수수료 일정 <?php if(!$scmScheduleSno) { ?> 등록<?php } else {?> 수정<?php }?></h3>
    </div>
    <div class="popup-display-main-list">
        <form id="frmSearchBase" name="frmSearchBase" action="./scm_ps.php" method="post" class="js-popup-submit">
            <input type="hidden" name="detailSearch" value="<?=$search['detailSearch']; ?>"/>
            <input type="hidden" name="popupMode" value="<?=$popupMode;?>">
            <input type="hidden" name="mode" value="insertScmCommissionSchedule">
            <input type="hidden" name="scmScheduleSno" value="<?=$scmScheduleSno;?>">
            <div class="table-title">
                기본 정보
                <span class="notice-info" style="vertical-align: top;margin-left: 15px;">공급사별 하루 하나의 수수료 일정만 등록 가능합니다.</span>
                <?php if(!$isProvider && $data['delAbleFl'] == 'y' && $scmScheduleSno) { ?><span style="float:right;"><button type="button" class="mgl5 btn btn-white btn-icon-minus js-delete-scmSchedule">삭제</button></span> <?php } ?>
                <?php if(!$isProvider && ($data['delAbleFl'] == 'n' && $data['modifyAbleFl'] == 'y') && $scmScheduleSno) { ?><span style="float:right;"><button type="button" class="mgl5 btn btn-white btn-icon-minus js-stop-scmSchedule">일정 종료</button></span> <?php } ?>
            </div>
            <div>
                <table class="table table-cols not-flex">
                    <?php if(!$isProvider) { ?>
                        <tr>
                            <th>공급사</th>
                            <td class="width300">
                                <input type="hidden" name="scmNo" class="form-control" value="<?=$scmNo;?>" >
                                <?php if($data['delAbleFl'] =='y') { ?>
                                    <input type="text" name="selectCompanyNm" class="form-control" id="scmLayer" value="<?=$data['companyNm'];?>" disabled="disabled" data-scm-no="" placeholder="공급사를 선택해주세요.">
                                <?php } else {  ?>
                                    <?=$data['companyNm'];?>
                                <?php } ?>
                            </td>
                            <?php if($data['delAbleFl'] =='y') { ?>
                                <td colspan="2">
                                    <input type="button" value="공급사 선택" class="btn btn-sm btn-gray js-layer-register" data-type="scm" data-mode="inputValue" data-scm-commission-set="p"/>
                                </td>
                            <?php } else {  ?>
                                <td colspan="2"></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                    <tr>
                        <th>판매수수료</th>
                        <td id="scmCommissionSelect">
                            <?php if(!$isProvider && ($data['delAbleFl'] == 'y' || $data['modifyAbleFl'] == 'y')) { ?>
                                <?php echo gd_select_box('scmCommissionSno', 'scmCommissionSno', $data['selectBoxData']['scmCommission'], null, $data['scmCommissionSno'] , '=선택='); ?>
                            <?php } else {
                                if($overTimeCommissionData[0] && $overTimeCommissionData[1]) {
                                    echo $overTimeCommissionData[0] . '%' . $basicCommissionText;
                                } else {
                                    echo $data['selectBoxData']['scmCommission'][$data['scmCommissionSno']] . '%' . $basicCommissionText;
                                }
                            } ?>
                        </td>
                        <th>배송비수수료</th>
                        <td id="scmCommissionDeliverySelect">
                            <?php
                            if(!$isProvider && ($data['delAbleFl'] == 'y' || $data['modifyAbleFl'] == 'y')) {
                                if($scmNo && $scmScheduleSno) {
                                    if($data['commissionSameFl'] == false) {
                                        echo gd_select_box('scmCommissionDeliverySno', 'scmCommissionDeliverySno', $data['selectBoxData']['scmCommissionDelivery'], null, $data['scmCommissionDeliverySno'], '=선택=');
                                    } else {
                                        echo '판매수수료 동일적용';
                                    }
                                }
                            } else {
                                if($data['commissionSameFl'] == false) {
                                    if($overTimeCommissionData[0] && $overTimeCommissionData[1]) {
                                        echo $overTimeCommissionData[1] . '%' . $basicCommissionDeliveryText;
                                    } else {
                                        echo $data['selectBoxData']['scmCommissionDelivery'][$data['scmCommissionDeliverySno']] . '%' . $basicCommissionDeliveryText;
                                    }
                                } else {
                                    echo '판매수수료 동일적용';
                                }
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>기간설정</th>
                        <td colspan="3">
                            <?php
                            if(!$isProvider && ($data['delAbleFl'] == 'y' && $data['modifyAbleFl'] == 'y')) {
                                ?>
                                <div><label><input type="checkbox" name="startDateAutoFl" value="y" <?= $data['checked']['startDateAutoFl']['y']; ?> > 저장 시 바로 적용</label></div>
                                <?php
                            }
                            ?>
                            <div class="form-inline pdt10">
                                <span id="startDateStr">시작일 / </span>종료일
                                <?php if(!$isProvider && $data['delAbleFl'] == 'y') { ?>
                                    <div class="input-group js-datetimepicker">
                                        <input type="text" class="form-control width-sm" name="startDate" value="<?=$data['startDate'] ?>" >
                                        <span class="input-group-addon">
                                <span class="btn-icon-calendar">
                                </span>

                                </span>
                                    </div>
                                <?php } else {
                                    echo $data['startDate'];
                                    echo "<input type='hidden' name='startDate' value='" . $data['startDate'] . "'> ";
                                } ?>
                                <span id="durationStr"> ~ </span>
                                <?php if(!$isProvider && ($data['delAbleFl'] == 'y' || $data['modifyAbleFl'] == 'y')) { ?>
                                    <div class="input-group js-datetimepicker">
                                        <input type="text" class="form-control width-sm" name="endDate" value="<?=$data['endDate'] ?>" >
                                        <span class="input-group-addon">
                                <span class="btn-icon-calendar">
                                </span>
                                </span>
                                    </div>
                                <?php } else {
                                    echo $data['endDate'];
                                } ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="table-title ">
                상품 조건 설정
            </div>
            <div>
                <table class="table table-cols"<?php if($isProvider || $data['modifyAbleFl'] == 'n') { ?> style="display:none;" <?php } ?>>
                    <colgroup><col class="width-md" /><col /></colgroup>
                    <tr>
                        <th >적용 상품 선택</th>
                        <td >
                            <label class="radio-inline"><input type="radio" name="applyKind" value="all" onclick="commissionApply_conf(this.value);" <?=gd_isset($checked['applyKind']['all']);?> />전체 상품</label>
                            <label class="radio-inline"><input type="radio" name="applyKind" value="goods" onclick="commissionApply_conf(this.value);" <?=gd_isset($checked['applyKind']['goods']);?> />특정 상품</label>
                            <label class="radio-inline"><input type="radio" name="applyKind" value="brand" onclick="commissionApply_conf(this.value);" <?=gd_isset($checked['applyKind']['brand']);?> />특정 브랜드</label>
                            <label class="radio-inline"><input type="radio" name="applyKind" value="coupon" onclick="commissionApply_conf(this.value);" <?=gd_isset($checked['applyKind']['coupon']);?> />특정 쿠폰</label>
                            <label class="radio-inline"><input type="radio" name="applyKind" value="member" onclick="commissionApply_conf(this.value);" <?=gd_isset($checked['applyKind']['member']);?> />특정 회원등급</label>
                        </td>
                    </tr>
                </table>

                <div id="applyKindFl_all" class="display-none">
                    <table class="table table-cols">
                        <colgroup><col class="width-md" /><col /></colgroup>
                        <tr>
                            <th>전체 상품</th>
                            <td>
                                <div class="notice-info">전체 상품에 대해서 기본 정보에 설정된 기간동안 선택된 수수료를 적용하게 됩니다.</div>
                                <div class="notice-info">단, 예외조건에 해당하는 상품은 상품에 설정된 수수료 / 배송비는 공급사의 기본 수수료가 적용됩니다.</div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="applyKindFl_goods" class="display-none">
                    <table class="table table-cols">
                        <colgroup><col class="width-md" /><col /></colgroup>
                        <tr>
                            <th >특정 상품
                                <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                    <div><input type="button" value="상품 선택" onclick="layer_register('goods');"  class="btn btn-sm btn-gray"/></div>
                                <?php } ?>
                            </th>
                            <td >
                                <div class="notice-info">선택된 상품에 대해서 기본 정보에 설정된 기간동안 선택된 수수료를 적용하게 됩니다.</div>
                                <div class="notice-info">단, 예외조건에 해당하는 상품은 상품에 설정된 수수료 / 배송비는 공급사의 기본 수수료가 적용됩니다.</div>
                                <table id="applyKindGoodsTable" class="table table-cols" style="width:80%">
                                    <thead <?php if (empty($data['applyDataConvert']) === true || $data['applyKind'] != 'goods')  { echo "style='display:none'"; } ?>>
                                    <tr>
                                        <th class="width5p">번호</th>
                                        <th class="width10p">이미지</th>
                                        <th class="width20p">상품명</th>
                                        <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                            <th class="width5p">삭제</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody id="applyKindGoods">
                                    <?php
                                    if ($data['applyKind'] == 'goods' && empty($data['applyDataConvert']) === false) {
                                        foreach ($data['applyDataConvert'] as $key => $val) {
                                            echo '<tr id="idGoods_'.$val['goodsNo'].'">'.chr(10);
                                            echo '<td class="center width5p"><span class="number">'.($key+1).'</span><input type="hidden" name="applyKindGoods[]" value="'.$val['goodsNo'].'" /></td>'.chr(10);
                                            echo '<td class="center width10p">'.gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank').'</td>'.chr(10);
                                            echo '<td class="center width20p">'.$val['goodsNm'].'</td>'.chr(10);
                                            if(!$isProvider && $data['modifyAbleFl'] == 'y') {
                                                echo '<td  class="center width5p"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idGoods_' . $val['goodsNo'] . '\');" value="삭제" /></td>' . chr(10);
                                            }
                                            echo '</tr>'.chr(10);
                                        }
                                    }
                                    ?>
                                    </tbody>
                                    <tfoot <?php if (empty($data['applyDataConvert']) === true || $data['applyKind'] != 'goods' || ($isProvider || $data['modifyAbleFl'] == 'n'))  { echo "style='display:none'"; } ?>>
                                    <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#applyKindGoods').html('');"></td></tr>
                                    </tfoot>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="applyKindFl_brand" class="display-none">
                    <table class="table table-cols">
                        <colgroup><col class="width-md" /><col /></colgroup>
                        <tr>
                            <th >특정 브랜드
                                <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                    <div><input type="button" value="브랜드 선택" onclick="layer_register('brand');" class="btn btn-sm btn-gray" /></div>
                                <?php } ?>
                            </th>
                            <td >

                                <div class="notice-info">선택된 브랜드 상품에 대해서 기본 정보에 설정된 기간동안 선택된 수수료를 적용하게 됩니다.</div>
                                <div class="notice-info">단, 예외조건에 해당하는 상품은 상품에 설정된 수수료 / 배송비는 공급사의 기본 수수료가 적용됩니다.</div>
                                <table id="applyKindBrandTable" class="table table-cols" style="width:80%">
                                    <thead <?php if (empty($data['applyDataConvert']) === true || $data['applyKind'] != 'brand')  { echo "style='display:none'"; } ?>>
                                    <tr>
                                        <th class="width5p">번호</th>
                                        <th>브랜드</th>
                                        <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                            <th class="width8p">삭제</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody id="applyKindBrand">
                                    <?php
                                    if ($data['applyKind'] == 'brand' && empty($data['applyDataConvert']) === false) {
                                        foreach ($data['applyDataConvert']['code'] as $key => $val) {
                                            echo '<tr id="idBrand_'.$val.'">'.chr(10);
                                            echo '<td class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="applyKindBrand[]" value="'.$val.'" /></td>'.chr(10);
                                            echo '<td>'.$data['applyDataConvert']['name'][$key].'</td>'.chr(10);
                                            if(!$isProvider && $data['modifyAbleFl'] == 'y') {
                                                echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idBrand_' . $val . '\');" value="삭제" /></td>' . chr(10);
                                            }
                                            echo '</tr>'.chr(10);
                                        }
                                    }
                                    ?>
                                    </tbody>
                                    <tfoot <?php if ( empty($data['applyDataConvert']) === true || $data['applyKind'] != 'brand' || ($isProvider || $data['modifyAbleFl'] == 'n'))  { echo "style='display:none'"; } ?>>
                                    <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#applyKindBrand').html('');"></td></tr></tfoot>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="applyKindFl_coupon" class="display-none">
                    <table class="table table-cols">
                        <colgroup><col class="width-md" /><col /></colgroup>
                        <tr>
                            <th >특정 쿠폰
                                <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                    <div><input type="button" value="쿠폰 선택" onclick="layer_register('coupon');"   class="btn btn-sm btn-gray" /></div>
                                <?php } ?>
                            </th>
                            <td >
                                <div class="notice-info">선택된 쿠폰을 적용하여 주문한 상품에 대해서 기본 정보에 설정된 기간동안 선택된 수수료를 적용하게 됩니다.</div>
                                <div class="notice-info">단, 예외조건에 해당하는 상품은 상품에 설정된 수수료 / 배송비는 공급사의 기본 수수료가 적용됩니다.</div>
                                <table id="applyKindCouponTable" class="table table-cols" style="width:80%">
                                    <thead <?php if (empty($data['applyDataConvert']) === true || $data['applyKind'] != 'coupon')  { echo "style='display:none'"; } ?>>
                                    <tr>
                                        <th class="width5p">번호</th>
                                        <th>쿠폰명</th>
                                        <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                            <th class="width8p">삭제</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody id="applyKindCoupon">
                                    <?php
                                    if ($data['applyKind'] == 'coupon' && empty($data['applyDataConvert']) === false) {
                                        foreach ($data['applyDataConvert']['code'] as $key => $val) {
                                            echo '<tr id="idCoupon_'.$val.'">'.chr(10);
                                            echo '<td  class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="applyKindCoupon[]" value="'.$val.'" /></td>'.chr(10);
                                            echo '<td>'.$data['applyDataConvert']['name'][$key].'</td>'.chr(10);
                                            if(!$isProvider && $data['modifyAbleFl'] == 'y') {
                                                echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idCoupon_' . $val . '\');" value="삭제" /></td>' . chr(10);
                                            }
                                            echo '</tr>'.chr(10);
                                        }
                                    }
                                    ?>
                                    </tbody>
                                    <tfoot <?php if (empty($data['applyDataConvert']) === true|| $data['applyKind'] != 'coupon' || ($isProvider ||$data['modifyAbleFl'] == 'n'))  { echo "style='display:none'"; } ?>>
                                    <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#applyKindCoupon').html('');"></td></tr>
                                    </tfoot>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="applyKindFl_member" class="display-none">
                    <table class="table table-cols">
                        <colgroup><col class="width-md" /><col /></colgroup>
                        <tr>
                            <th >특정 회원등급
                                <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                    <div><input type="button" value="회원등급 선택" onclick="layer_register('member_group');"   class="btn btn-sm btn-gray" /></div>
                                <?php } ?>
                            </th>
                            <td >
                                <div class="notice-info">선택된 회원등급이 주문한 상품에 대해서 기본 정보에 설정된 기간동안 선택된 수수료를 적용하게 됩니다.</div>
                                <div class="notice-info">단, 예외조건에 해당하는 상품은 상품에 설정된 수수료 / 배송비는 공급사의 기본 수수료가 적용됩니다.</div>
                                <table id="applyKindMember_groupTable" class="table table-cols" style="width:80%">
                                    <thead <?php if (empty($data['applyDataConvert']) === true || $data['applyKind'] != 'member')  { echo "style='display:none'"; } ?>>
                                    <tr>
                                        <th class="width5p">번호</th>
                                        <th>회원등급</th>
                                        <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                            <th class="width8p">삭제</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody id="applyKindMember_group" >
                                    <?php
                                    if ($data['applyKind'] == 'member' && empty($data['applyDataConvert']) === false) {
                                        foreach ($data['applyDataConvert']['code'] as $key => $val) {
                                            echo '<tr id="idMember_group_'.$val.'">'.chr(10);
                                            echo '<td  class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="applyKindMember_group[]" value="'.$val.'" /></td>'.chr(10);
                                            echo '<td>'.$data['applyDataConvert']['name'][$key].'</td>'.chr(10);
                                            if(!$isProvider && $data['modifyAbleFl'] == 'y') {
                                                echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idMember_group_' . $val . '\');" value="삭제" /></td>' . chr(10);
                                            }
                                            echo '</tr>'.chr(10);
                                        }
                                    }
                                    ?>
                                    </tbody>
                                    <tfoot <?php if (empty($data['applyDataConvert']) === true || $data['applyKind'] != 'member' || ($isProvider || $data['modifyAbleFl'] == 'n'))  { echo "style='display:none'"; } ?>>
                                    <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#applyKindMember_group').html('');"></td></tr>
                                    </tfoot>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="table-title">
                예외 조건 설정
            </div>
            <?php if($scmScheduleSno && ($isProvider || $data['modifyAbleFl'] == 'n') && (empty($data['exceptGoodsNo']) && empty($data['exceptBrandCd']) && empty($data['exceptCouponSno']) && empty($data['exceptMemberGroupSno']))) { ?>
                <table class="table table-cols">
                    <tr>
                        <td class="center">
                            예외 조건 설정 정보가 없습니다.
                        </td>
                    </tr>
                </table>
            <?php } else { ?>
                <div>
                    <table class="table table-cols">
                        <colgroup><col class="width-md" /><col /></colgroup>
                        <tr <?php if($isProvider || $data['modifyAbleFl'] == 'n') { ?> class="display-none" <?php } ?>>
                            <th >예외 조건</th>
                            <td>
                                <span id="applyKindFlExcept_goods"><label class="checkbox-inline"><input type="checkbox" name="applyExceptFl[]" value="goods" onclick="applyKindExcept_conf(this.value)" <?=gd_isset($checked['exceptKind']['goods']);?> >예외 상품</label></span>
                                <span id="applyKindFlExcept_brand"><label class="checkbox-inline"><input type="checkbox" name="applyExceptFl[]" value="brand" onclick="applyKindExcept_conf(this.value)" <?=gd_isset($checked['exceptKind']['brand']);?> >예외 브랜드</label></span>
                                <span id="applyKindFlExcept_coupon"><label class="checkbox-inline"><input type="checkbox" name="applyExceptFl[]" value="coupon" onclick="applyKindExcept_conf(this.value)" <?=gd_isset($checked['exceptKind']['coupon']);?> >예외 쿠폰</label></span>
                                <span id="applyKindFlExcept_member"><label class="checkbox-inline"><input type="checkbox" name="applyExceptFl[]" value="member" onclick="applyKindExcept_conf(this.value)" <?=gd_isset($checked['exceptKind']['member']);?> >예외 회원등급</label></span>
                            </td>
                        </tr>
                        <tr id="applyKindFlExcept_goods_tbl" style="display:none">
                            <th>예외 상품
                                <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                    <div><input type="button"  class="btn btn-sm btn-gray" value="상품 선택" onclick="layer_register('goods','except');" /></div>
                                <?php } ?>
                            </th>
                            <td>
                                <table id="exceptGoodsTable" class="table table-cols" style="width:80%">
                                    <thead <?php if (is_array($data['exceptGoodsNo']) == false)  { echo "style='display:none'"; } ?>>
                                    <tr>
                                        <th class="width5p">번호</th>
                                        <th class="width10p">이미지</th>
                                        <th class="width20p">상품명</th>
                                        <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                            <th class="width8p">삭제</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody id="exceptGoods" >
                                    <?php
                                    if (is_array($data['exceptGoodsNo'])) {
                                        foreach ($data['exceptGoodsNo'] as $key => $val) {
                                            echo '<tr id="idExceptGoods_'.$val['goodsNo'].'">'.chr(10);
                                            echo '<td class="center width5p"><span class="number">'.($key+1).'</span><input type="hidden" name="exceptGoods[]" value="'.$val['goodsNo'].'" /></td>'.chr(10);
                                            echo '<td class="center width10p">'.gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank').'</td>'.chr(10);
                                            echo '<td class="center width20p">'.$val['goodsNm'].'</td>'.chr(10);
                                            if(!$isProvider && $data['modifyAbleFl'] == 'y') {
                                                echo '<td  class="center width5p"><input type="button" class="btn btn-gray btn-sm" onclick="field_remove(\'idExceptGoods_' . $val['goodsNo'] . '\');" value="삭제" /></td>' . chr(10);
                                            }
                                            echo '</tr>'.chr(10);
                                        }
                                    }
                                    ?>
                                    </tbody>
                                    <tfoot <?php if (is_array($data['exceptGoodsNo']) == false || ($isProvider || $data['modifyAbleFl'] == 'n'))  { echo "style='display:none'"; } ?>>
                                    <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptGoods').html('');"></td></tr>
                                    </tfoot>
                                </table>
                            </td>
                        </tr>

                        <tr id="applyKindFlExcept_brand_tbl" style="display:none">
                            <th>예외 브랜드
                                <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                    <div><input type="button"  class="btn btn-sm btn-gray" value="예외 브랜드 선택" onclick="layer_register('brand','except');" /></div>
                                <?php } ?>
                            </th>
                            <td>
                                <table id="exceptBrandTable" class="table table-cols" style="width:80%">
                                    <thead <?php if (is_array($data['exceptBrandCd']) == false)  { echo "style='display:none'"; } ?>>
                                    <tr>
                                        <th class="width5p">번호</th>
                                        <th>브랜드</th>
                                        <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                            <th class="width8p">삭제</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody id="exceptBrand" >
                                    <?php
                                    if (is_array($data['exceptBrandCd'])) {
                                        foreach ($data['exceptBrandCd']['code'] as $key => $val) {
                                            echo '<tr id="idExceptBrand_'.$val.'">'.chr(10);
                                            echo '<td  class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="exceptBrand[]" value="'.$val.'" /></td>'.chr(10);
                                            echo '<td>'.$data['exceptBrandCd']['name'][$key].'</td>'.chr(10);
                                            if(!$isProvider && $data['modifyAbleFl'] == 'y') {
                                                echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idExceptBrand_' . $val . '\');" value="삭제" /></td>' . chr(10);
                                            }
                                            echo '</tr>'.chr(10);
                                        }
                                    }
                                    ?>
                                    </tbody>
                                    <tfoot <?php if (is_array($data['exceptBrandCd']) == false || ($isProvider || $data['modifyAbleFl'] == 'n'))  { echo "style='display:none'"; } ?>>
                                    <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptBrand').html('');"></td></tr>
                                    </tfoot>
                                </table>

                            </td>

                        </tr>
                        <tr id="applyKindFlExcept_coupon_tbl" style="display:none">
                            <th>예외 쿠폰
                                <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                    <div><input type="button" class="btn btn-sm btn-gray" value="예외 쿠폰 선택" onclick="layer_register('coupon','except');" /></div>
                                <?php } ?>
                            </th>
                            <td>
                                <table id="exceptCouponTable" class="table table-cols" style="width:80%">
                                    <thead <?php if (is_array($data['exceptCouponSno']) == false)  { echo "style='display:none'"; } ?>>
                                    <tr>
                                        <th class="width5p">번호</th>
                                        <th>쿠폰명</th>
                                        <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                            <th class="width8p">삭제</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody id="exceptCoupon" >
                                    <?php
                                    if (is_array($data['exceptCouponSno'])) {
                                        foreach ($data['exceptCouponSno']['code'] as $key => $val) {
                                            echo '<tr id="idExceptCoupon_'.$val.'">'.chr(10);
                                            echo '<td  class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="exceptCoupon[]" value="'.$val.'" /></td>'.chr(10);
                                            echo '<td>'.$data['exceptCouponSno']['name'][$key].'</td>'.chr(10);
                                            if(!$isProvider && $data['modifyAbleFl'] == 'y') {
                                                echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idExceptCoupon_' . $val . '\');" value="삭제" /></td>' . chr(10);
                                            }
                                            echo '</tr>'.chr(10);
                                        }
                                    }
                                    ?>
                                    </tbody>
                                    <tfoot <?php if (is_array($data['exceptCouponSno']) == false || ($isProvider || $data['modifyAbleFl'] == 'n'))  { echo "style='display:none'"; } ?>>
                                    <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptCoupon').html('');"></td></tr>
                                    </tfoot>
                                </table>

                            </td>

                        </tr>
                        <tr id="applyKindFlExcept_member_tbl" style="display:none">
                            <th>예외 회원등급
                                <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                    <div><input type="button"  class="btn btn-sm btn-gray" value="예외 회원등급 선택" onclick="layer_register('member_group','except');" /></div>
                                <?php } ?>
                            </th>
                            <td>
                                <table id="exceptMember_groupTable" class="table table-cols" style="width:80%">
                                    <thead <?php if (is_array($data['exceptMemberGroupSno']) == false)  { echo "style='display:none'"; } ?>>
                                    <tr>
                                        <th class="width5p">번호</th>
                                        <th>회원등급</th>
                                        <?php if(!$isProvider && $data['modifyAbleFl'] == 'y') { ?>
                                            <th class="width8p">삭제</th>
                                        <?php } ?>
                                    </tr>
                                    </thead>
                                    <tbody id="exceptMember_group">
                                    <?php
                                    if (is_array($data['exceptMemberGroupSno'])) {
                                        foreach ($data['exceptMemberGroupSno']['code'] as $key => $val) {
                                            echo '<tr id="idExceptMember_group_'.$val.'">'.chr(10);
                                            echo '<td  class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="exceptMember_group[]" value="'.$val.'" /></td>'.chr(10);
                                            echo '<td>'.$data['exceptMemberGroupSno']['name'][$key].'</td>'.chr(10);
                                            if(!$isProvider && $data['modifyAbleFl'] == 'y') {
                                                echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idExceptMember_group_'.$val.'\');" value="삭제" /></td>'.chr(10);
                                            }
                                            echo '</tr>'.chr(10);
                                        }
                                    }
                                    ?>
                                    </tbody>
                                    <tfoot <?php if (is_array($data['exceptMemberGroupSno']) == false || ($isProvider || $data['modifyAbleFl'] == 'n'))  { echo "style='display:none'"; } ?>>
                                    <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptMember_group').html('');"></td></tr>
                                    </tfoot>
                                </table>

                            </td>

                        </tr>
                    </table>
                </div>
            <?php } ?>
            <div class="text-center">
                <?php if(!$isProvider && ($data['delAbleFl'] == 'y' || $data['modifyAbleFl'] == 'y')) { ?>
                    <input type="submit" class="btn btn-black js-check-save" value="저장">
                    <input type="button" class="btn btn-white js-check-close" value="취소">
                <?php } else { ?>
                    <input type="button" class="btn btn-white js-check-close" value="확인">
                <?php } ?>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {

        // 레이어 닫기
        $('.js-check-close').click(function () {
            close();
        });

        // 바로 적용 로딩
        if($("input[name='startDateAutoFl']").prop('checked') == true) {
            $('.js-datetimepicker:eq(0)').hide();
            $('#durationStr, #startDateStr').hide();
        } else {
            $('.js-datetimepicker:eq(0)').show();
            $('#durationStr, #startDateStr').show();
        }
        // 바로 적용 클릭 시
        $("input[name='startDateAutoFl']").click(function() {
            if($("input[name='startDateAutoFl']").prop('checked') == true) {
                $('.js-datetimepicker:eq(0)').hide();
                $('#startDateStr, #durationStr').hide();
            } else {
                $('.js-datetimepicker:eq(0)').show();
                $('#durationStr, #startDateStr').show();
            }
        });
        // 공급사 미선택 시 datepicker 이벤트
        $('input[name="startDate"], input[name="endDate"]').click(function(e) {
            if($('input[name="scmNo"]').val() == '') {
                alert('공급사를 선택해주세요.')
                $(this).val('');
                return false;
            }
        });
        <?php
        if($data['modifyAbleFl'] == 'y') {
        foreach($data['checked']['exceptKind'] as $exceptKindKey => $exceptKindVal) { ?>
        applyKindExcept_conf('<?=$exceptKindKey;?>');
        <?php }
        }
        if($isProvider) { ?>
        // 적용 조건 초기로딩
        commissionApply_conf('<?=$data['applyKind'];?>', 'init');
        <?php } else {
        if($scmNo && $scmScheduleSno) { ?>

        // 수수료 선택 박스 제어
        $('select[name="scmCommissionSno"] option[value=<?=$data['scmCommissionSno'];?>]').prop('selected', true);
        $('select[name="scmCommissionDeliverySno"] option[value=<?=$data['scmCommissionDeliverySno'];?>]').prop('selected', true);
        // 기본 수수료 값 option text 변경
        var basicCommissionText = $('select[name="scmCommissionSno"] option[value=0]').text();
        $('select[name="scmCommissionSno"] option[value=0]').text(basicCommissionText + '(기본 수수료)');
        var basicCommissionDeliveryText = $('select[name="scmCommissionDeliverySno"] option[value=0]').text();
        $('select[name="scmCommissionDeliverySno"] option[value=0]').text(basicCommissionDeliveryText + '(기본 수수료)');

        // 적용 조건 초기로딩
        commissionApply_conf('<?=$data['applyKind'];?>', 'init');
        <?php } else {  ?>
        // 적용 조건 초기로딩
        commissionApply_conf('all', 'init');
        <?php }
        }
        if(!$isProvider) {
        if($scmNo && $scmScheduleSno) { ?>
        $('.js-stop-scmSchedule').click(function () {
            var params = {
                mode : 'stopScmCommissionSchedule',
                scmNo : '<?=$scmNo;?>',
                scmScheduleSno : '<?=$scmScheduleSno;?>',
            };
            dialog_confirm('공급사 수수료 일정을 종료하시겠습니까?', function (result) {
                if(result) {
                    $.ajax({
                        method: "POST",
                        url: "../scm/scm_ps.php",
                        data : params,
                        success : function(data){
                            alert(data.message);
                            if(data.mode == 'success') {
                                setTimeout(function () {
                                    opener.parent.location.reload();
                                    window.close();
                                }, 2000);
                            }
                        },
                        error: function (data){
                        }
                    });
                }
            });
        });
        $('.js-delete-scmSchedule').click(function () {
            var params = {
                mode : 'deleteScmCommissionSchedule',
                scmNo : '<?=$scmNo;?>',
                scmScheduleSno : '<?=$scmScheduleSno;?>',
            };
            dialog_confirm('공급사 수수료 일정을 삭제하시겠습니까?', function (result) {
                if(result) {
                    $.ajax({
                        method: "POST",
                        url: "../scm/scm_ps.php",
                        data : params,
                        success : function(data){
                            alert(data.message);
                            if(data.mode == 'success') {
                                setTimeout(function () {
                                    opener.parent.location.reload();
                                    window.close();
                                }, 2000);
                            }
                        },
                        error: function (data){
                        }
                    });
                }
            });
        });
        <?php } ?>

        // submit 검증
        $("#frmSearchBase").validate({
            dialog: false,
            submitHandler: function (form) {
                if(_.isUndefined($('#scmCommissionSno').val()) && _.isUndefined($('#scmCommissionDeliverySno').val())) {
                    alert('설정된 추가 수수료가 없습니다.');
                    return false;
                }

                var params = jQuery("#frmSearchBase").serializeArray();
                $.ajax({
                    method: "POST",
                    url: "../scm/scm_ps.php",
                    data : params,
                    success : function(data){
                        alert(data.message);
                        if(data.mode == 'success') {
                            setTimeout(function () {
                                location.reload();
                                opener.parent.location.reload();
                                window.close();
                            }, 2000);
                        }
                    },
                    error: function (data){
                    }
                });
            },
            rules: {
                selectCompanyNm: {
                    required: true,
                },
                scmCommissionSno: {
                    required: function() {
                        if($('#scmCommissionSno').val() == 'undefined') {
                            return false;
                        } else {
                            return true;
                        }
                    },
                },
                scmCommissionDeliverySno: {
                    required: function() {
                        if($('#scmCommissionDeliverySno').val() == 'undefined') {
                            if($('#scmCommissionSno').val() == 'undefined') {
                                return true;
                            } else {
                                return true;
                            }
                        } else {
                            return true;
                        }
                    },
                },
                startDate: {
                    required: function() {
                        if($("input[name='startDateAutoFl']").prop('checked') == false) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                },
                endDate: {
                    required: true,
                }
            },
            messages: {
                selectCompanyNm: {
                    required: '공급사를 선택하세요.',
                },
                scmCommissionSno: {
                    required: '판매수수료를 선택하세요.',
                },
                scmCommissionDeliverySno: {
                    required: '배송비수수료를 선택하세요.',
                },
                startDate: {
                    required: '기간을 설정해주세요.',
                },
                endDate: {
                    required: '기간을 설정해주세요.',
                }
            }
        });
        <?php } ?>
    });
    //-->

    /**
     * 공급사정보(수수료)
     *
     * @param string thisValue 공급사 번호
     */
    function getScmInfo(thisValue) {
        if($(thisValue).val() != '') {
            var scmNo = $(thisValue).attr('data-scm-no');

            var param = {
                'mode': 'getScmCommissionSelectBox',
                'scmNo': scmNo
            };
            $.ajax({
                type: "POST",
                url: "../scm/scm_ps.php",
                data: param,
                dataType: "json",
                success: function (data) {
                    $('#scmCommissionSelect').html(data.selectBoxData.scmCommission);
                    $('#scmCommissionDeliverySelect').html(data.selectBoxData.scmCommissionDelivery);
                    var basicCommissionText = $('select[name="scmCommissionSno"] option[value=0]').text();
                    $('select[name="scmCommissionSno"] option[value=0]').text(basicCommissionText + '(기본 수수료)');
                    var basicCommissionDeliveryText = $('select[name="scmCommissionDeliverySno"] option[value=0]').text();
                    $('select[name="scmCommissionDeliverySno"] option[value=0]').text(basicCommissionDeliveryText + '(기본 수수료)');

                    var scmNo =$('#scmLayer').attr('data-scm-no');
                    $('input[name="scmNo"]').val(scmNo);
                }
            });
        }
    }
    /**
     * 구매 상품 범위에 따른 상품 선택
     *
     * @param string thisValue 레이어창 종류 (구매상품 범위 값)
     * @param string loadType 호출종류
     */
    function commissionApply_conf(thisValue, loadType)
    {
        <?php if(!$isProvider) { ?>
        if($('input[name="scmNo"]').val() == '' && loadType != 'init') {
            alert('공급사를 선택해주세요.');
            $('input[name="applyKind"]:eq(0)').prop('checked', true);
            return false;
        }
        <?php } ?>
        $('div[id*=\'applyKindFl_\']').removeClass();
        $('div[id*=\'applyKindFl_\']').addClass('display-none');
        $('div[id*=\'applyKindFl_'+thisValue+'\']').removeClass();
        $('div[id*=\'applyKindFl_'+thisValue+'\']').addClass('display-block');

        $('span[id*=\'applyKindFlExcept_\']').removeClass();
        $('span[id*=\'applyKindFlExcept_\']').addClass('display-inline');
        $('span[id*=\'applyKindFlExcept_'+thisValue+'\']').removeClass();
        $('span[id*=\'applyKindFlExcept_'+thisValue+'\']').addClass('display-none');
        $('span[id*=\'applyKindFlExcept_'+thisValue+'\']').find("input:checkbox").prop("checked",false);

        $('tr[id*=\'applyKindFlExcept_\']').hide();
        if($("input[name='applyExceptFl[]']:checked").length > 0 ) {
            $("input[name='applyExceptFl[]']:checked").each(function () {
                $('#applyKindFlExcept_'+$(this).val()+"_tbl").show();
            });
        }
    }

    function applyKindExcept_conf(thisValue) {
        if($('input[name="scmNo"]').val() == '') {
            alert('공급사를 선택해주세요.');
            $('input[name="applyExceptFl[]"]').prop('checked', false);
            return false;
        }
        if($('#applyKindFlExcept_'+thisValue +"_tbl").is(':hidden')) $('#applyKindFlExcept_'+thisValue +"_tbl").show();
        else  $('#applyKindFlExcept_'+thisValue +"_tbl").hide();
    }

    /**
     * 구매 상품 범위 등록 / 예외 등록 Ajax layer
     *
     * @param string typeStr 타입
     * @param string modeStr 예외 여부
     */
    function layer_register(typeStr, modeStr,isDisabled)
    {
        var layerFormID		= 'addScmCommissionForm';

        typeStrId =  typeStr.substr(0,1).toUpperCase() + typeStr.substr(1);

        if (typeof modeStr == 'undefined') {
            var parentFormID	= 'applyKind'+typeStrId;
            var dataFormID		= 'id'+typeStrId;
            var dataInputNm		= 'applyKind'+typeStrId;
            var layerTitle		= '공급사 수수료 조건- ';
        } else {
            var parentFormID	= 'except'+typeStrId;
            var dataFormID		= 'idExcept'+typeStrId;
            var dataInputNm		= 'except'+typeStrId;
            var layerTitle		= '공급사 수수료 건예외 조건 - ';
        }

        // 레이어 창
        if (typeStr == 'goods') {
            var layerTitle		= layerTitle+'상품';
            var mode =  'simple';

            $("#"+parentFormID+"Table thead").show();
            $("#"+parentFormID+"Table tfoot").show();
        }
        if (typeStr == 'brand') {
            var layerTitle		= layerTitle+'브랜드';
            var mode =  'simple';

            $("#"+parentFormID+"Table thead").show();
            $("#"+parentFormID+"Table tfoot").show();
        }
        if (typeStr == 'coupon') {
            var layerTitle		= layerTitle+'브랜드';
            var mode =  'simple';

            $("#"+parentFormID+"Table thead").show();
            $("#"+parentFormID+"Table tfoot").show();
        }
        if (typeStr == 'member_group') {
            var layerTitle		= layerTitle+'회원 등급';
            var mode =  'simple';

            $("#"+parentFormID+"Table thead").show();
            $("#"+parentFormID+"Table tfoot").show();
        }

        var addParam = {
            "mode": mode,
            "layerFormID": layerFormID,
            "parentFormID": parentFormID,
            "dataFormID": dataFormID,
            "dataInputNm": dataInputNm,
            "layerTitle": layerTitle,
        };

        if(typeStr == 'goods'){
            addParam['scmFl'] = 'y';
            addParam['scmNo'] = $('input[name="scmNo"]').val();
        }

        if(typeStr == 'scm'){
            addParam['callFunc'] = 'set_scm_select';
        }


        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }

</script>