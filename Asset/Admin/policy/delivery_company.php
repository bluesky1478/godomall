<form id="frmSort" name="frmSort" action="./delivery_ps.php" method="post">
    <input type="hidden" name="mode" value="company_register"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location);?></h3>
        <input type="submit" value="저장" class="btn btn-red">
    </div>

    <div class="table-title gd-help-manual">
        <?php echo end($naviMenu->location); ?>
    </div>

    <div class="table-header">
        <div class="pull-left">총 <strong><?php echo $dataCnt; ?></strong>개의 배송 업체가 등록되었습니다.</div>
    </div>
    <p class="notice-info">
        “사용”으로 설정된 배송업체 중, 리스트 가장 위에 노출되는 배송업체가 주문리스트에서 배송정보 입력시 기본으로 선택됩니다.
    </p>

    <div class="table-action mgb0 mgt0">
        <div class="pull-left">
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
        </div>
    </div>

    <table class="table table-rows" data-toggle="" data-use-row-attr-func="false" data-reorderable-rows="true">
        <thead>
        <tr>
            <th class="width-2xs">번호</th>
            <th class="width-2xs">배송업체번호</th>
            <th class="width-sm">배송업체명</th>
            <th class="">배송추적URL</th>
            <th class="width-sm">사용설정</th>
            <th class="width-xs">추가/삭제</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (is_array($data)) {
            $num = 1;
            $arrUseFl = ['y' => '사용', 'n' => '미사용'];
            $arrBgColor = ['y' => '', 'n' => 'background:#f0f0f0'];
            $checkUseFl = 'y';
            foreach ($data as $key => $val) {
        ?>
                <tr class="text-center move-row<?php if ($val['useFl'] == 'n') { ?> active<?php } ?>">
                    <td>
                        <?php echo $num; ?>
                        <input type="hidden" name="fixFl[]" value="<?php echo $val['fixFl']; ?>"/>
                        <input type="hidden" name="sno[]" value="<?php echo $val['sno']; ?>"/>
                    </td>
                    <td><?php echo $val['sno']; ?></td>
                    <?php if($val['fixFl'] =='y') { ?>
                        <td>
                            <input type="hidden" name="companyName[]" value="<?=$val['companyName']?>" class="form-control"  /><?=$val['companyName']?>
                        </td>
                    <?php } else { ?>
                        <td>
                            <input type="text" name="companyName[]" value="<?=$val['companyName']?>" class="form-control"  />
                        </td>
                    <?php } ?>
                    <td class="text-left">
                        <?php if($val['deliveryFl'] == 'y' && in_array($val['companyKey'], ['cargo', 'visit', 'quick', 'etc']) === false){ ?>
                            <input type="text" name="traceUrl[]" value="<?=$val['traceUrl']?>" class="form-control" />
                        <?php } else { ?>
                            <input type="hidden" name="traceUrl[]" value="" />
                        <?php } ?>
                    </td>
                    <td>
                        <?php if($val['deliveryFl'] == 'y'){ ?>
                            <?= gd_select_box('useFl[]', 'useFl[]', $arrUseFl, null, $val['useFl']) ?>
                        <?php } else { ?>
                            <input type="hidden" name="useFl[]" value="y" />
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($num == 1) { ?>
                        <button type="button" class="btn btn-sm btn-white btn-icon-plus js-company-regist" />추가</button>
                        <?php } else { ?>
                            <?php if($val['fixFl'] !='y') { ?>
                        <button type="button" data-sno="<?=$val['sno']?>" class="btn btn-sm btn-white btn-icon-minus js-company-delete" />삭제</button>
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
                <?php
                $num++;
            }
        }
        ?>
    </table>
    <div class="table-action">
        <div class="pull-left">
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
        </div>
    </div>
</form>

<br/>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 폼체크
        $('#frmSort').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                "companyName[]": "required"
            },
            messages: {
                "companyName[]": "배송업체명을 입력하세요.",
            }
        });

        // 리스트 클릭 활성/비활성화
        var iciRow = '';
        var preRow = '';
        $(document).on('click', 'tbody tr', function (e) {
            // 인풋박스 선택시 입력만 활성화
            if($(e.target).is('input[type=text]') || $(e.target).is('button') || ($(e.target).is('td') && $(e.target).find('input[type=text]').length)) {
                return false;
            }

            // 이동할 셀 선택처리
            if (preRow && iciRow) {
                $(this).siblings().each(function () {
                    $(this).removeClass('warning');
                });
                preRow = iciRow = '';
            } else {
                if (iciRow) preRow = iciRow;
                iciRow = $(this);
                iciRow.addClass('warning');
                if (preRow && iciRow) {
                    var preRowIndex = (preRow.index('.move-row') + 1);
                    var iciRowIndex = (iciRow.index('.move-row') + 1);

                    if (preRowIndex == iciRowIndex) {
                        $(this).removeClass('warning');
                        preRow = iciRow = '';
                    } else {
                        if (preRowIndex > iciRowIndex) {
                            var startNo = iciRowIndex;
                            var endNo = preRowIndex;
                        } else {
                            var startNo = preRowIndex;
                            var endNo = iciRowIndex;
                        }
                        for (var j = startNo; j <= endNo; j++) {
                            $(this).siblings().each(function () {
                                var thisFormRow = $(this).index('.move-row') + 1;
                                if (thisFormRow == j) {
                                    $(this).addClass('warning');
                                }
                            });
                        }
                    }
                }
            }
        });

        // 리스트 키보드 이동
        $(document).keydown(function (event) {
            if (iciRow) {
                var idx = (iciRow.index('.move-row') + 1);
                switch (event.keyCode) {
                    case 38:
                        iciRow.moveRow(-1);
                        break;
                    case 40:
                        iciRow.moveRow(1);
                        break;
                }
                return false;
            }
        });

        // 위/아래이동 버튼 이벤트
        $('.js-moverow').click(function(e){
            if (iciRow) {
                var idx = (iciRow.index('tr') + 1);
                switch ($(this).data('direction')) {
                    case 'up':
                        iciRow.moveRow(-1);
                        break;
                    case 'down':
                        iciRow.moveRow(1);
                        break;
                    case 'top':
                        iciRow.moveRow(-100);
                        break;
                    case 'bottom':
                        iciRow.moveRow(100);
                        break;
                }
            } else {
                alert('순서 변경을 원하시는 배송업체를 클릭해주세요.');
            }
        });

        // 테이블 ROW 삭제 처리
        $(document).on('click', '.js-company-delete', function(e){
            $(this).closest('tr').remove();
        });

        // 테이블 ROW 추가 처리
        $('.js-company-regist').click(function(e){
            var compiled = _.template($('#moveRowTemplate').html());
            $('tbody').append(compiled({no: $(this).closest('tbody').find('tr').length + 1, sno: ''}));
        });
    });
    //-->
</script>
<script id="moveRowTemplate" type="text/html">
    <tr class="text-center move-row">
        <td>
            <%=no%>
            <input type="hidden" name="sno[]" value="<%=sno%>">
        </td>
        <td></td>
        <td>
            <input type="text" name="companyName[]" value="" class="form-control">
        </td>
        <td class="text-left">
            <input type="text" name="traceUrl[]" value="" class="form-control">
        </td>
        <td>
            <select class="form-control" name="useFl[]">
                <option value="y" selected="selected">사용</option>
                <option value="n">미사용</option>
            </select>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-white btn-icon-minus js-company-delete">삭제</button>
        </td>
    </tr>
</script>
