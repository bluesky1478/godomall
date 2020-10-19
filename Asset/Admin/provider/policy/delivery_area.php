<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location);?></h3>
    <div class="btn-group">
        <a href="../policy/delivery_area_regist.php" class="btn btn-red-line">지역별 추가배송비 등록</a>
    </div>
</div>

<form id="frmSearchAddGroupDelivery" method="get" class="js-form-enter-submit">
    <input type="hidden" name="scmFl" value="1">
    <div class="table-title gd-help-manual">
        지역별 추가배송비 검색
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md">
            <col>
            <col class="width-md">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th>검색어</th>
            <td colspan="3">
                <div class="form-inline">
                    <?php echo gd_select_box('key', 'key', $search['combineSearch'], null, $search['key']); ?>
                    <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                </div>
            </td>
        </tr>
        <tr>
            <th>기간검색</th>
            <td colspan="3">
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
        </tbody>
    </table>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>

    <div class="table-header">
        <div class="pull-left">
            검색 <strong><?php echo number_format(gd_isset($page->recode['total'], 0)); ?></strong>건 /
            전체 <strong><?php echo number_format(gd_isset($page->recode['amount'], 0)); ?></strong>건
        </div>
        <div class="pull-right">
            <div class="form-inline">
                <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
                <?php echo gd_select_box(
                    'pageNum', 'pageNum', gd_array_change_key_value(
                    [
                        10,
                        20,
                        30,
                        40,
                        50,
                        60,
                        70,
                        80,
                        90,
                        100,
                        200,
                        300,
                        500,
                    ]
                ), '개 보기', $page->page['list']
                ); ?>
            </div>
        </div>
    </div>
</form>

<form id="frmAddGroupDeliveryList" action="./delivery_ps.php" method="post">
    <input type="hidden" name="scmID" value="<?php echo SCM_ID_LOCAL; ?>"/>
    <input type="hidden" name="mode" value="area_delete"/>
    <table class="table table-rows">
        <thead>
        <tr>
            <th><input type="checkbox" class="js-checkall" data-target-name="deliverChk[]" /></th>
            <th>번호</th>
            <th>지역별 추가배송비 제목</th>
            <th>공급사구분</th>
            <th>등록자</th>
            <th>등록일</th>
            <th>수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($data) === false && is_array($data)) {
            $totalCnt = 0;    // 주문서 수량 설정
            $totalGoods = 0;    // 주문서 수량 설정
            $totalPrice = 0;    // 주문 총 금액 설정
            foreach ($data as $row) {
                if ($row['scmNo'] == '0') {
                    $manage = $delivery->getDataSnoDelivery('1');
                    $row['companyNm'] = $manage['manage']['companyNm'];
                }
                ?>
                <tr class="text-center">
                    <td><input type="checkbox" name="deliverChk[]" value="<?=$row['sno']?>" <?=$row['defaultFl']=='y'?'disabled="disabled"':''?> /></td>
                    <td><?=$page->idx--?></td>
                    <td>
                        <button type="button" class="btn btn-sm js-tooltip" data-placement="right" title="<?=$row['description']?>"><?=$row['method'] . ($row['defaultFl'] != 'y' ? '' : ($row['scmNo'] == 0 ? '<br />(공용 기본설정)' : '<br />(기본설정)'))?></span></button>
                    </td>
                    <td><?=$row['companyNm']?></td>
                    <td><?=$row['managerNm']?><br /><small>(<?=$row['managerId']?>)</small></td>
                    <td><?=gd_date_format('Y-m-d', $row['regDt'])?></td>
                    <td><a href="../policy/delivery_area_regist.php?sno=<?=$row['sno']?>" class="btn btn-sm btn-white">수정</a></td>
                </tr>
            <?php } } else { ?>
            <tr>
                <td colspan="12" class="no-data">
                    검색된 지역별 추가배송비가 없습니다.
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
            $('#frmSearchAddGroupDelivery').submit();
        });

        // 검색 폼 체크
        $('#frmSearchAddGroupDelivery').validate({
            dialog: false,
            submitHandler: function (form) {
                form.submit();
            },
            rules: {
//                'keyword': 'required'
            },
            messages: {
//                'keyword': {
//                    required: "검색어를 입력하세요.",
//                }
            }
        });

        // 검색 폼 체크
        $('#frmAddGroupDeliveryList').validate({
            dialog: false,
            ignore: [],
            submitHandler: function (form) {
                dialog_confirm('선택한 지역별 추가배송비를 삭제하시겠습니까?', function(result){
                    if (result) {
                        form.target = 'ifrmProcess';
                        form.submit();
                    }
                });
            },
            rules: {
                'deliverChk[]': {
                    required: true,
                    minlength: 1
                },

            },
            messages: {
                'deliverChk[]': {
                    required: "하나 이상 체크하세요.",
                    minlength: '하나 이상 체크하세요.'
                }
            }
        });

        // 선택후 삭제 처리
        $('#frmAddGroupDeliveryList').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
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
        $('.js-delete-delivery').click(function(e){
            $('#frmAddGroupDeliveryList').submit();
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
