<form id="frmOverseasDelivery" method="post" target="ifrmProcess" action="../policy/mall_delivery_ps.php">
    <input type="hidden" name="basic[sno]" value="<?= $data['sno']; ?>"/>
    <input type="hidden" name="mode" value="modify"/>
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?></h3>
        <input type="submit" value="저장" class="btn btn-red js-btn-save"/>
    </div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-md">
            <col class="">
        </colgroup>
        <tbody>
        <tr>
            <th class="">상점명</th>
            <td class="">
                <span class="flag flag-16 flag-<?=$data['domainFl']?>"></span>
                <?=$data['mallName']?>
            </td>
        </tr>
        <tr>
            <th>기본 포장 입력값</th>
            <td class="form-inline">
                <dl class="dl-horizontal mgb10">
                    <dt>무게 설정</dt>
                    <dd>
                        <input type="text" name="basic[basicWeight]" value="<?=$data['basicWeight']?>" size="3" class="form-control input-sm">
                        <?=$data['weightUnit']?> (0.01<?=$data['weightUnit']?> 단위로 입력 가능)
                    </dd>
                </dl>
                <dl class="dl-horizontal mgb10 display-none">
                    <dt>부피 설정</dt>
                    <dd>
                        가로 <input type="hidden" name="basic[basicBulk][x]" value="<?=$data['basicBulk']['x']?>" size="3" class="form-control input-sm" placeholder="가로"> x
                        세로 <input type="hidden" name="basic[basicBulk][y]" value="<?=$data['basicBulk']['y']?>" size="3" class="form-control input-sm" placeholder="세로"> x
                        높이 <input type="hidden" name="basic[basicBulk][z]" value="<?=$data['basicBulk']['z']?>" size="3" class="form-control input-sm" placeholder="높이"> cm
                    </dd>
                </dl>
                <p class="notice-info">
                    상품 추가시 해당 값을 디폴트로 세팅합니다.<br>
                    무게의 경우, 각 상품등록시 상품별 입력된 상품무게가 우선되며, 해당값이 없을 경우, 위에 설정된 무게로 계산합니다.
                </p>
            </td>
        </tr>
        <tr>
            <th>박스 무게</th>
            <td class="form-inline">
                <input type="text" name="basic[boxWeight]" value="<?=$data['boxWeight']?>" size="3" class="form-control input-sm">
                <?=$data['weightUnit']?> (0.01<?=$data['weightUnit']?> 단위로 입력 가능)
                <p class="notice-info">
                    배송료 계산시 배송비 조건이 상품 무게일 경우 상품의 최종 합산 무게에 해당 박스 무게를 더합니다.
                </p>
            </td>
        </tr>
        <tr>
            <th>배송비 기준 설정</th>
            <td>
                <div class="radio">
                    <label>
                        <input type="radio" name="basic[standardFl]" id="standardFl" value="self" <?=$checked['standardFl']['self']?>>
                        자체 설정 - 배송비조건 사용
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="basic[standardFl]" id="standardFl" value="ems" <?=$checked['standardFl']['ems']?>>
                        우체국 EMS 표준 요율표 사용
                    </label>
                </div>
                <div class="well form-inline mgb0" id="EmsAddPrice">
                    추가 배송요금 부여
                    <input type="text" name="basic[emsAddCost]" value="<?=$data['emsAddCost']?>" size="12" class="form-control input-sm">
                    <?=gd_select_box(null, 'basic[emsAddCostUnit]', ['won'=>'원', 'percent'=>'%'], null, $data['emsAddCostUnit'], null, null, 'form-control');?>

                    <p class="notice-info">
                        자동 책정된 우체국 EMS 배송비에서 해당 금액만큼 추가 청구됩니다.
                    </p>
                </div>
            </td>
        </tr>
        </tbody>
        <tbody id="canDeliveryInsurance">
        <tr>
            <th>해외배송 보험료</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="basic[insuranceFl]" value="y" <?=$checked['insuranceFl']['y']?>>
                    청구
                </label>
                <label class="radio-inline">
                    <input type="radio" name="basic[insuranceFl]" value="n" <?=$checked['insuranceFl']['n']?>>
                    청구하지 않음
                </label>
                <p class="notice-info">
                    '해외배송 보험료'란, EMS를 이용하여 해외배송시 분리/파손되는 상품에 대하여 우체국에서 해당 상품가에 따라 보상받을 수 있는 서비스로 배송비와 별개로 고객이 부담하는 비용입니다.<br>
                    &gt; 보험료 기본요금 : 최초 <?=number_format($data['emsGuide']['baseRate'])?>원 청구(~<?=number_format($data['emsGuide']['baseRateLimit'])?>원 이하), <?=number_format($data['emsGuide']['baseRateLimit'])?>원 단위로 초과시마다 <?=number_format($data['emsGuide']['baseRateOverInterval'])?>원 추가 청구
                </p>
            </td>
        </tr>
        </tbody>
        <tbody id="canDeliveryCountry">
        <tr>
            <th>배송가능 국가 선택</th>
            <td>
                <table class="table table-rows table-condensed width-3xl mgb10" id="selectedGroup">
                    <colgroup>
                        <col class="width-2xs">
                        <col>
                        <col>
                        <col>
                    </colgroup>
                    <thead>
                    <tr>
                        <th>NO</th>
                        <th>배송 국가 그룹</th>
                        <th>배송비조건명</th>
                        <th>추가/삭제</th>
                    </tr>
                    </thead>
                    <tbody class="js-table-row-draggable">
                    <?php foreach ($group as $key => $val) { ?>
                        <tr class="text-center <?=$key===0 ? 'active' : ''?>">
                            <td><?=$key + 1?></td>
                            <td>
                                <input type="hidden" name="group[sno][]" value="<?=$val['sno']?>">
                                <input type="hidden" name="group[groupSort][]" value="<?=$key + 1?>">
                                <?=gd_select_box(null, 'group[countryGroupSno][]', $countryGroupData, null, $val['countryGroupSno'], '- 선택 -', null, 'width-lg');?>
                            </td>
                            <td>
                                <?=gd_select_box(null, 'group[deliverySno][]', $scmDeliveryData, null, $val['deliverySno'], '- 선택 -', null, 'width-lg');?>
                            </td>
                            <td>
                                <?php if ($key > 0) { ?>
                                <button type="button" class="btn btn-sm btn-white btn-icon-minus js-delete-row mgr0">삭제</button>
                                <?php } else { ?>
                                <button type="button" class="btn btn-sm btn-white btn-icon-plus js-overseas-add-group mgr0">추가</button>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="4">
                            <div class="btn-group">
                                <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">
                                    맨아래
                                </button>
                                <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down">
                                    아래
                                </button>
                                <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up">
                                    위
                                </button>

                                <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top">
                                    맨위
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tfoot>
                </table>
                <ul class="notice-info">
                    <li>"배송 국가 그룹" 추가는 <a href="../policy/mall_delivery_group_list.php" class="text-primary" target="_blank">"기본설정 > 해외상점 > 해외 배송그룹 관리"</a>에서 설정하실 수 있습니다.</li>
                    <li>"배송지조건명" 추가는 <a href="../policy/delivery_config.php" class="text-primary" target="_blank">"기본설정 > 배송정책 > 배송지조건 관리"</a>에서 설정하실 수 있습니다.</li>
                </ul>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<!-- 배송가능 국가 그룹 템플릿 시작 -->
<script type="text/template" id="deliveryUsableCountryTemplate">
    <tr class="text-center">
        <td><%=idx%></td>
        <td>
            <input type="hidden" name="group[sno][]" value="">
            <input type="hidden" name="group[groupSort][]" value="">
            <?=gd_select_box(null, 'group[countryGroupSno][]', $countryGroupData, null, null, '- 선택 -', null, 'width-lg');?>
        </td>
        <td>
            <?=gd_select_box(null, 'group[deliverySno][]', $scmDeliveryData, null, null, '- 선택 -', null, 'width-lg');?>
        </td>
        <td><button type="button" class="btn btn-sm btn-white btn-icon-minus js-delete-row mgr0">삭제</button></td>
    </tr>
</script>
<!--// 배송가능 국가 그룹 템플릿 끝 -->

<script type="text/javascript">
    /**
     * 테이블 NO 텍스트 정렬
     */
    function tableSortNumber() {
        $.each($('#selectedGroup tbody tr'), function(idx, obj){
            $(obj).find('td:first-child').text(idx + 1);
            $(obj).find('input[name="group[groupSort][]"]').val(idx + 1);
        });
    }

    // load DOM
    $(function(){
        // 배송비 기준 설정 change 이벤트
        $('input[name="basic[standardFl]"]').change(function(e){
            if ($(this).val() === 'ems') {
                $('#EmsAddPrice').show();
                $('#canDeliveryInsurance').show();
                $('#canDeliveryCountry').hide();
            } else {
                $('#EmsAddPrice').hide();
                $('#canDeliveryInsurance').hide();
                $('#canDeliveryCountry').show();
            }
        });
        $('input[name="basic[standardFl]"]:checked').trigger('change');

        // 배송그룹 추가
        $(document).on('click', '.js-overseas-add-group', function(e){
            var compiled = _.template($('#deliveryUsableCountryTemplate').html());
            var data = {
                idx: $('#selectedGroup tbody tr').length + 1,
            };
            $('#selectedGroup tbody').append(compiled(data));
            tableSortNumber();
        });

        // 테이블 ROW 삭제
        $(document).on('click', '.js-delete-row', function(e){
            $(this).closest('tr').remove();
            tableSortNumber();
        });

        // 테이블 ROW 드래그
        $('tbody.js-table-row-draggable').sortable({
            connectWith: '.js-table-row-draggable',
            items: "> tr:not(:first)",
            helper: "clone",
            zIndex: 999990,
            start: function() {},
            stop: function() {
                tableSortNumber();
            }
        });

        // 테이블 ROW 셀렉트
        var rowIndex = null;
        var selectedRow = null;
        $(document).on("click", "#selectedGroup tbody > tr:not(:first)", function(e){
            var hasClass = $(this).hasClass('warning');
            $("#selectedGroup tbody > tr:not(:first)").removeClass('warning');
            if (!hasClass) {
                $(this).addClass('warning');
                selectedRow = $(this);
                rowIndex = $('#selectedGroup tbody > tr').index(selectedRow);
            } else {
                selectedRow = null;
                rowIndex = null;
            }
        });

        // 테이블 ROW 화살표 클릭
        $(".js-moverow").click(function(){
            var direction = $(this).data('direction');
            var totalRow = $("#selectedGroup tbody > tr:not(:first)").length;
            if (selectedRow !== null) {
                switch (direction) {
                    case 'up':
                        if (rowIndex != 1) {
                            selectedRow.insertBefore(selectedRow.prev());
                            rowIndex--;
                        }
                        break;
                    case 'down':
                        if (rowIndex != totalRow) {
                            selectedRow.insertAfter(selectedRow.next());
                            rowIndex++;
                        }
                        break;
                    case 'top':
                        if (rowIndex != 1) {
                            selectedRow.insertBefore(selectedRow.siblings().eq(1));
                            rowIndex = 1;
                        }
                        break;
                    case 'bottom':
                        if (rowIndex != totalRow) {
                            selectedRow.insertAfter(selectedRow.siblings().eq(totalRow - 1));
                            rowIndex = totalRow - 1;
                        }
                        break;
                }
                tableSortNumber();
            } else {
                alert('이동을 원하는 셀을 선택하세요.');
            }
        });

        // 폼 체크
        $('#frmOverseasDelivery').validate({
            dialog: false,
            rules: {
                'basic[emsAddCost]': {
                    max: function(form) {
                        if ($('select[name="basic[emsAddCostUnit]"]').val() == 'percent' && $('input[name="basic[standardFl]"]:checked').val() == 'ems') {
                            return 100;
                        } else {
                            return 999999999999;
                        }

                    }
                },
                'countryGroup[groupName]': {
                    required: true
                },
                'group[countryGroupSno][]': {
                    required: function() {
                        return ($('input[name="basic[standardFl]"]:checked').val() != 'ems');
                    }
                },
                'group[deliverySno][]': {
                    required: function() {
                        return ($('input[name="basic[standardFl]"]:checked').val() != 'ems');
                    }
                }
            },
            messages: {
                'basic[emsAddCost]': {
                    max: '추가 배송요금이 퍼센트인 경우 100을 넘길 수 없습니다.'
                },
                'countryGroup[groupName]': {
                    required: '배송국가 그룹명을 입력해주세요.'
                },
                'group[countryGroupSno][]': {
                    required: '배송국가 그룹을 선택해주세요.'
                },
                'group[deliverySno][]': {
                    required: '배송비조건명을 선택해주세요.'
                }
            },
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            }
        });
    });
</script>
<script type="text/html" id="templateConnectDomain">
    <div class="btn-group btn-group-xs" id="connectDomain<%=index%>" data-domain="<%=domain%>">
        <input type="hidden" name="connectDomain[]" value="<%=domain%>">
        <button type="button" class="btn btn-sm btn-gray"><%=domain%></button>
        <button type="button" class="btn btn-sm btn-red" data-toggle="delete" data-target="connectDomain<%=index%>">삭제</button>
    </div>
</script>
