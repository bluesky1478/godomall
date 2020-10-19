<div class="page-header js-affix">
<h3><?= end($naviMenu->location); ?>
    <small></small>
</h3>
<div class="btn-group">
    <input type="button" value="이벤트 설정" class="btn btn-white" onclick="location.href='member_join_event_config.php?eventType=<?=$eventType?>'">
</div>
</div>

<ul class="nav nav-tabs mgb20">
    <li role="presentation" class="<?= $eventType == "order" ? "active" : "" ?>">
        <a href="/member/member_join_event_log.php?eventType=order" aria-controls="order">주문 간단 가입</a></li>
    <li role="presentation" class="<?= $eventType == "push" ? "active" : "" ?>">
        <a href="/member/member_join_event_log.php?eventType=push" aria-controls="push">회원가입 유도 푸시</a></li>
</ul>

<div class="table-title">이벤트 현황 검색</div>
<form id="frmSearchBase" method="get">
    <input type="hidden" name="eventType" value="<?=$eventType?>"/>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>기간검색</th>
            <td>
                <div class="form-inline">
                    <div class="input-group js-datepicker">
                        <input type="text" name="treatDate[]" value="<?= $search['treatDate'][0]; ?>" class="form-control width-xs">
                        <span class="input-group-addon">
                                <span class="btn-icon-calendar">
                                </span>
                            </span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" name="treatDate[]" value="<?= $search['treatDate'][1]; ?>" class="form-control width-xs">
                        <span class="input-group-addon">
                                <span class="btn-icon-calendar">
                                </span>
                            </span>
                    </div>

                    <?= gd_search_date(gd_isset($search['searchPeriod'], 6), 'treatDate[]', false) ?>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="table-btn">
        <button type="submit" class="btn btn-lg btn-black">검색</button>
    </div>


    <div class="table-dashboard">
        <table class="table table-cols">
            <colgroup>
                <col/>
                <col/>
                <?php if($eventType == 'push') { ?>
                    <col/>
                    <col/>
                    <col/>
                <?php } ?>
            </colgroup>
            <tbody>
            <tr>
                <?php if($eventType == 'push') { ?>
                    <th>푸시 노출수</th>
                    <th>푸시 클릭수</th>
                    <th>회원전환수</th>
                    <th class="point">회원전환율<br>(노출수 대비)</th>
                    <th class="point">회원전환율<br>(클릭수 대비)</th>
                <?php } else { ?>
                <th>회원전환수</th>
                <th class="point">회원전환율</th>
                <?php } ?>
            </tr>
            <tr>
                <?php if($eventType == 'push') { ?>
                <td><strong><?= number_format($total['pushViewCount']); ?></strong></td>
                <td><strong><?= number_format($total['pushClickCount']); ?></strong></td>
                <td><strong><?= number_format($total['memberCount']); ?></strong></td>
                <td class="point"><strong><?= number_format($total['memberConversionRateDisplay']); ?>%</strong></td>
                <td class="point"><strong><?= number_format($total['memberConversionRateClick']); ?>%</strong></td>
                <?php } else { ?>
                <td><strong><?= number_format($total['memberCount']); ?></strong></td>
                <td class="point"><strong><?= number_format($total['memberConversionRate']); ?>%</strong></td>
                <?php } ?>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-header ">
        <div class="pull-left">
            검색 <strong class="text-danger"><?= number_format(gd_isset($page->recode['total'], 0)); ?></strong>개
        </div>
        <div class="pull-right">
            <div class="form-inline">
                <?= gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10,20,30,40,50,60,70,80,90,100,200,300,500,]), '개 보기', $page->page['list']); ?>
            </div>
        </div>
    </div>
    <table class="table table-rows">
        <colgroup>
            <col class="width-xs"/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
        </colgroup>
        <thead>
        <tr>
            <th>번호</th>
            <th>아이디</th>
            <th>이름</th>
            <th>등급</th>
            <th>가입 시 제공혜택</th>
            <th>회원가입일</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            foreach ($data as $val) {
                ?>
                <tr class="center" data-member-no="<?= $val['memNo']; ?>">
                    <td class="font-num">
                        <span class="number"><?= $page->idx--; ?></span>
                    </td>
                    <td>
                        <span class="font-eng <?= $val['sleepFl'] == 'n' ? 'js-layer-crm hand' : ''?>"><?= $val['memId']; ?></span>
                    </td>
                    <td>
                        <span class="<?= $val['sleepFl'] == 'n' ? 'js-layer-crm hand' : ''?>"><?= $val['memNm']; ?></span>
                    </td>
                    <td>
                        <span><?= $val['sleepFl'] == 'n' ? gd_isset($groups[$val['groupSno']]) : ''; ?></span>
                    </td>
                    <td>
                        <span class="font-num">
                            <?php if($val['appFl'] == 'y') { ?>
                            <?php if($val['memberCouponNo']) { ?>
                                쿠폰 <?=count(explode(INT_DIVISION, $val['memberCouponNo'])); ?>개
                                <a href="javascript:void(0);" onclick="showCouponData('<?=$val['memNo']?>', <?=count(explode(INT_DIVISION, $val['memberCouponNo'])); ?>, '<?=$val['memberCouponNo'];?>');" class="btn btn-sm btn-gray">쿠폰 정보</a>
                            <?php } ?>
                            <?php if($val['memberCouponNo'] && $val['mileage'] > 0) echo '<br>'; ?>
                            <?php if($val['mileage'] > 0) { ?>
                                마일리지: <?=gd_money_format($val['mileage']) . gd_display_mileage_unit()?>
                            <?php } ?>
                            <?php if(!$val['memberCouponNo'] && $val['mileage'] == 0) { echo '-'; }?>
                            <?php } else { ?>
                                가입 시 미승인 회원
                            <?php } ?>
                        </span>
                    </td>
                    <td>
                        <span class="font-date"><?= substr($val['regDt'], 2, 8); ?></span>
                    </td>
                </tr>
                <?php
            }
        } else {
            echo '<tr><td class="center" colspan="6">기간내 가입한 회원이 없습니다.</td></tr>';
        }
        ?>
        </tbody>
    </table>

    <div class="table-action clearfix">
        <div class="pull-right">
            <button type="button" class="btn btn-white btn-icon-excel btn-excel">엑셀 다운로드</button>
        </div>

    </div>

    <div class="center"><?= $page->getPage(); ?></div>
</form>

<script>
    $(document).ready(function(){
        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchBase').submit();
        });
        $('.btn-excel').click(function (e) {
            var $form = $('<form></form>');
            $form.attr('action', './excel_member_ps.php');
            $form.attr('method', 'post');
            $form.attr('target', 'ifrmProcess');
            $form.appendTo('body');

            var mode = $('<input type="hidden" name="mode" value="excel_simple_join_event">');
            var eventType = $('<input type="hidden" name="eventType" value="<?=$eventType?>">');
            var searchStartDate = $('<input type="hidden" name="treatDate[]" value="<?=$search['treatDate'][0];?>">');
            var searchEndDate = $('<input type="hidden" name="treatDate[]" value="<?=$search['treatDate'][1];?>">');

            $form.append(mode).append(eventType).append(searchStartDate).append(searchEndDate);
            $form.submit();
        });
    });
    function showCouponData(memNo, amount, couponNo) {
        $.post('layer_simple_join_coupon.php', {'memNo': memNo, 'amount': amount, 'couponNo': couponNo}, function (data) {
            layer_popup(data, '쿠폰 정보 보기', 'wide');
        });
    }
</script>