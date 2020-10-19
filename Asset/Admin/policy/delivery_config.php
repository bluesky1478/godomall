<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location);?></h3>
    <div class="btn-group">
        <a href="../policy/delivery_regist.php" class="btn btn-red-line">배송비조건 등록</a>
    </div>
</div>

<form id="frmSearchDelivery" method="get" class="js-form-enter-submit">
    <input type="hidden" name="detailSearch" value="<?php echo $search['detailSearch']; ?>"/>

    <div class="table-title gd-help-manual">
        배송비조건 검색
    </div>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm">
                <col>
                <col class="width-sm">
                <col>
            </colgroup>
            <tbody>
            <?php if(gd_use_provider() === true) { ?>
            <tr>
                <th>공급사 구분</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="" <?php echo gd_isset($checked['scmFl']['']); ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="0" <?php echo gd_isset($checked['scmFl']['0']); ?>/>본사
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="1" class="js-layer-register" <?php echo gd_isset($checked['scmFl']['1']); ?> data-type="scm" data-mode="radio"/> 공급사
                    </label>
                    <input type="button" value="공급사 선택" class="btn btn-sm btn-gray js-layer-register" data-type="scm" data-mode="radio"/>

                    <div id="scmLayer" class="selected-btn-group <?=$search['scmFl'] == '1' && $search['scmNo'] ? 'active' : ''?>">
                        <h5>선택된 공급사 : </h5>
                        <?php if ($search['scmFl'] == '1' && $search['scmNo']) { ?>
                        <div id="idscm_<?=$search['scmNo']?>" class="btn-group btn-group-xs">
                            <input type="hidden" name="scmNo" value="<?=$search['scmNo']?>"/>
                            <input type="hidden" name="scmNoNm" value="<?=$search['scmNoNm'] ?>"/>
                            <span class="btn"><?=$search['scmNoNm']?></span>
                            <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#idscm_<?=$search['scmNo']?>">삭제</button>
                        </div>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?php echo gd_select_box('key', 'key', $search['combineSearch'], null, $search['key']); ?>
                        <input type="text" name="keyword" value="<?=$search['keyword']?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            </tbody>
            <tbody class="js-search-detail">
            <tr>
                <th>기간검색</th>
                <td>
                    <div class="form-inline">
                        <div class="form-inline">
                            <div class="input-group js-datepicker">
                                <input type="text" name="treatDate[]" value="<?=$search['treatDate'][0]?>" class="form-control">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar"></span>
                                </span>
                            </div>
                            <label class="control-label">~</label>
                            <div class="input-group js-datepicker">
                                <input type="text" name="treatDate[]" value="<?=$search['treatDate'][1]?>" class="form-control">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </td>
                <th>배송 방식</th>
                <td>
                    <?php echo gd_select_box('deliveryMethodFl', 'deliveryMethodFl', $search['deliveryMethodFlSearch'], null, $search['deliveryMethodFl']); ?>
                </td>
            </tr>
            <tr>
                <th>배송비 부과방법</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="goodsDeliveryFl" value="all" <?php echo gd_isset($checked['goodsDeliveryFl']['all']); ?>/> 전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="goodsDeliveryFl" value="y" <?php echo gd_isset($checked['goodsDeliveryFl']['y']); ?>/> 배송비조건별
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="goodsDeliveryFl" value="n" <?php echo gd_isset($checked['goodsDeliveryFl']['n']); ?>/> 상품별
                    </label>
                </td>
                <th>배송비유형</th>
                <td>
                    <label class="mgb0">
                        <?php echo gd_select_box('fixFl', 'fixFl', $mode['fix'], null, $search['fixFl']); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th>결제방법</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="collectFl" value="" <?php echo gd_isset($checked['collectFl']['']); ?>/> 전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="collectFl" value="pre" <?php echo gd_isset($checked['collectFl']['pre']); ?>/> 선불
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="collectFl" value="later" <?php echo gd_isset($checked['collectFl']['later']); ?>/> 착불
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="collectFl" value="both" <?php echo gd_isset($checked['collectFl']['both']); ?>/> 선불/착불
                    </label>
                </td>
                <th>지역별추가배송비</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="areaFl" value="" <?php echo gd_isset($checked['areaFl']['']); ?>/> 전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="areaFl" value="y" <?php echo gd_isset($checked['areaFl']['y']); ?>/> 있음
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="areaFl" value="n" <?php echo gd_isset($checked['areaFl']['n']); ?>/> 없음
                    </label>
                </td>
            </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-link js-search-toggle bold">상세검색
            <span>닫힘</span></button>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>

    <div class="table-header">
        <div class="pull-left">
            검색 <strong class="text-red"><?php echo number_format(gd_isset($page->recode['total'], 0)); ?></strong>건 /
            전체 <strong><?php echo number_format(gd_isset($page->recode['amount'], 0)); ?></strong>건
        </div>
        <div class="pull-right">
            <div class="form-inline">
                <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
                <?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500,]), '개 보기', $page->page['list']); ?>
            </div>
        </div>
    </div>
</form>

<form id="frmDeliveryList" action="../policy/delivery_ps.php" method="post">
    <input type="hidden" name="mode" value="delete"/>
    <table class="table table-rows">
        <thead>
        <tr>
            <th><input type="checkbox" class="js-checkall" data-target-name="deliverChk[]" /></th>
            <th>번호</th>
            <th>배송비코드</th>
            <th>배송비조건명</th>
            <th>공급사구분</th>
            <th>배송방식</th>
            <th>배송비유형</th>
            <th>배송비설정</th>
            <th>배송비부과방법</th>
            <th>결제방법</th>
            <th>지역별추가배송비</th>
            <th>등록자</th>
            <th>등록일</th>
            <th>수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($data) === false && is_array($data)) {
            $sortNo = 1;    // 번호 설정
            $totalCnt = 0;    // 주문서 수량 설정
            $totalGoods = 0;    // 주문서 수량 설정
            $totalPrice = 0;    // 주문 총 금액 설정
            foreach ($data as $row) {
                $deliveryMethodName = gd_get_delivery_method_display($row['deliveryMethodFl']);
        ?>
        <tr class="text-center">
            <td><input type="checkbox" name="deliverChk[]" value="<?=$row['basicKey']?>" <?=$row['defaultFl']=='y' ?'disabled="disabled"':''?>/></td>
            <td><?=$page->idx--?></td>
            <td><?=$row['basicKey']?></td>
            <td>
                <button type="button" class="btn btn-sm js-tooltip" data-placement="right" title="<?=$row['description']?>"><?=$row['method'] . ($row['defaultFl'] != 'y' ? '' : '<br />(기본설정)')?></span></button>
            </td>
            <td><?=$row['companyNm']?></td>
            <td style="width: 100px; word-break: break-all;"><?=$deliveryMethodName?></td>
            <td><?=$row['fixFlText']?></td>
            <td>
                <?php
                if (!in_array($row['fixFl'], ['price','count','weight'])) {
                    if ($row['multipleDeliveryFl']) {
                        ?>
                        <button type="button" class="btn btn-sm btn-white js-layer-charge" data-sno="<?=$row['basicKey']?>">상세보기</button>
                        <?php
                    } else {
                        echo gd_currency_display($row['price']);
                    }
                } else {
                ?>
                <button type="button" class="btn btn-sm btn-white js-layer-charge" data-sno="<?=$row['basicKey']?>">상세보기</button>
                <?php } ?>
            </td>
            <td><?=$row['goodsDeliveryFlText']?></td>
            <td><?=$row['collectFlText']?></td>
            <td><?=$row['areaFlText']?></td>
            <td><?=$row['managerNm']?><br /><small>(<?=$row['managerId']?>)</small><?=$row['deleteText']?></td>
            <td><?=gd_date_format('Y-m-d', $row['regDt'])?></td>
            <td><a href="../policy/delivery_regist.php?sno=<?=$row['basicKey']?>" class="btn btn-sm btn-white">수정</a></td>
        </tr>
        <?php $sortNo++; } } else { ?>
        <tr>
            <td colspan="14" class="no-data">
                검색된 배송비 조건이 없습니다.
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white js-delete-delivery">선택 삭제</button>
        </div>
    </div>
</form>

<div class="center"><?= $page->getPage(); ?></div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 리스트 정렬
        $('#sort, #pageNum').change(function(e){
            $('#frmSearchDelivery').submit();
        });

        // 검색 폼 체크
        $('#frmSearchDelivery').validate({
            dialog: false,
            submitHandler: function (form) {
                form.submit();
            }
//            ,
//            rules: {
//                'keyword': 'required'
//            },
//            messages: {
//                'keyword': {
//                    required: "검색어를 입력하세요.",
//                }
//            }
        });

        // 배송리스트 폼 체크
        $('#frmDeliveryList').validate({
            dialog: false,
            submitHandler: function (form) {
                BootstrapDialog.confirm({
                    title: "배송비조건 삭제 확인",
                    message: '선택한 ' + $('input[name="deliverChk[]"]:checked').length + '개 배송비조건을 정말로 삭제하시겠습니까?<br>삭제 시 정보는 복구 되지 않습니다.',
                    btnOKLabel: "삭제",
                    btnCancelLabel: "취소",
                    callback: function (result) {
                        if (result) {
                            form.target = 'ifrmProcess';
                            form.submit();
                        }
                    }
                });
            },
            rules: {
                'deliverChk[]': 'required'
            },
            messages: {
                'deliverChk[]': {
                    required: "하나 이상 체크하세요.",
                }
            }
        });

        // 배송지 삭제
        $('.js-delete-delivery').click(function(e){
            $('#frmDeliveryList').submit();
        });

        // 지역 및 배송비 추가 iframe 다이얼로그 호출
        $('.js-layer-charge').click(function(e){
            var addParam = {
                sno: $(this).data('sno')
            };
            $.get('./layer_delivery_charge.php', addParam, function (data) {
                var layerForm = '<div id="chargeDelivery">' + data + '</div>';
                BootstrapDialog.show({
                    title: '배송비 설정 상세보기',
                    message: $(layerForm),
                    closable: true,
                    onhidden: function (dialog) {

                    }
                });
            });
        });
    });
    //-->
</script>
