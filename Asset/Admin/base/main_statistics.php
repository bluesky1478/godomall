<div class="main-statistics main-content reform mg0 pd0">
    <div class="content-main">
        <div class="main-section" id="presentationSection">
            <div class="table-title">
                <span class="">주요현황</span>
                <div class="pull-right more-view">
                    <a href="#" class="btn btn-icon-refresh btn-sm btn-white js-real-update">새로고침</a>
                    <a href="#" class="btn btn-icon-setting btn-sm btn-white js-setting-presentation">세팅</a>
                </div>
            </div>
            <div class="statics-box reform">
                <div class="nav-tabs-top">
                    <ul class="nav nav-tabs tab-fluid-5" role="tablist" id="presentationMainTab" data-period="<?= $searchPeriod; ?>">
                        <?php if ($mainStatisticsAccess['sales'] > 0) { ?>
                        <li role="presentation" class="<?= $active['sales']; ?>" data-name="sales">
                            <a href="#tab-type1-id1" role="tab" data-toggle="tab">매출 <img class="js-period-icon" src="<?= PATH_ADMIN_GD_SHARE ?>img/main_statistics/ico_<?= $searchPeriod; ?>days_<?= $icon['sales']; ?>.png">
                                <div class="count"><?= $tabStatistics['sales']; ?></div>
                            </a>
                        </li>
                        <?php } ?>
                        <?php if ($mainStatisticsAccess['order'] > 0) { ?>
                        <li role="presentation" class="<?= $active['order']; ?>" data-name="order">
                            <a href="#tab-type1-id2" role="tab" data-toggle="tab">주문 <img class="js-period-icon" src="<?= PATH_ADMIN_GD_SHARE ?>img/main_statistics/ico_<?= $searchPeriod; ?>days_<?= $icon['order']; ?>.png">
                                <div class="count"><?= $tabStatistics['order']; ?></div>
                            </a>
                        </li>
                        <?php } ?>
                        <?php if (!gd_is_provider()) { ?>
                            <?php if ($mainStatisticsAccess['visit'] > 0) { ?>
                            <li role="presentation" class="<?= $active['visit']; ?>" data-name="visit">
                                <a href="#tab-type1-id3" role="tab" data-toggle="tab">방문자 <img class="js-period-icon" src="<?= PATH_ADMIN_GD_SHARE ?>img/main_statistics/ico_<?= $searchPeriod; ?>days_<?= $icon['visit']; ?>.png">
                                    <div class="count"><?= $tabStatistics['visit']; ?></div>
                                </a>
                            </li>
                            <?php } ?>
                            <?php if ($mainStatisticsAccess['member'] > 0) { ?>
                            <li role="presentation" class="<?= $active['member']; ?>" data-name="member">
                                <a href="#tab-type1-id4" role="tab" data-toggle="tab">신규회원 <img class="js-period-icon" src="<?= PATH_ADMIN_GD_SHARE ?>img/main_statistics/ico_<?= $searchPeriod; ?>days_<?= $icon['member']; ?>.png">
                                    <div class="count"><?= $tabStatistics['member']; ?></div>
                                </a>
                            </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
                <div class="tab-content">
                    <div role="tab-type1-id1" class="main-tab-pane tab-pane fade <?= $tab['sales']; ?>" id="tab-type1-id1">
                        <div class="pos-r">
                            <div class="nav-pills-sub">
                                <ul class="gd5-tabnav" role="tablist">
                                    <?php if (!gd_is_provider()) { ?>
                                    <li role="presentation" class="active">
                                        <a href="#sales-sub-tab1" role="tab" data-toggle="tab" data-mode="salesTotal" data-link="sales_day">매출현황</a>
                                    </li>
                                    <li role="presentation">
                                        <a href="#sales-sub-tab2" role="tab" data-toggle="tab" data-mode="salesGoods" data-link="goods_sale_rank">상품판매순위</a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <div class="nav-pills-check">
                                데이터 노출형태 :
                                <select name="deviceFl" class="js-device">
                                    <option value="all">통합</option>
                                    <option value="pc">PC</option>
                                    <option value="mobile">모바일</option>
                                    <option value="write">수기</option>
                                </select>
                            </div>
                            <div class="nav-pills-option">
                                <a href="../statistics/sales_day.php" target="_top" class="btn btn-sm btn-link">더보기</a>
                            </div>
                        </div>
                        <div>
                            <div class="tab-content">
                                <div class="tab-pane fade in active" role="sales-sub-tab1" id="sales-sub-tab1">
                                    <div class="row row-pd10">
                                        <div class="col-xs-6 col">
                                            <table class="table table-rows table-fixed text-center">
                                                <colgroup>
                                                    <col>
                                                    <col>
                                                    <col>
                                                    <col>
                                                </colgroup>
                                                <thead>
                                                <tr>
                                                    <th>날짜</th>
                                                    <th>매출금액</th>
                                                    <th>판매금액</th>
                                                    <th>환불금액</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr class="now highlight">
                                                    <td class="bold">00/00</td>
                                                    <td class="bold">0</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                </tr>
                                                <tr class="active">
                                                    <td>7일합계</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                </tr>
                                                <tr class="active">
                                                    <td>15일합계</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                </tr>
                                                <tr class="active">
                                                    <td>30일합계</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-xs-6 col">
                                            <div class="graph bdtype">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div role="sales-sub-tab2" class="tab-pane fade" id="sales-sub-tab2">
                                    <div>
                                        <table class="table table-rows table-fixed text-center">
                                            <colgroup>
                                                <col class="width-3xs">
                                                <col>
                                                <col class="width-sm">
                                                <col class="width-2xs">
                                                <col class="width-2xs">
                                            </colgroup>
                                            <thead>
                                            <tr>
                                                <th>순위</th>
                                                <th>상품명</th>
                                                <th>상품금액</th>
                                                <th>구매수량</th>
                                                <th>구매건수</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td colspan="5">데이터가 존재하지 않습니다.</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="clearfix pull-right">
                                    <span class="js-display-search-period display-none">(0000.00.00~0000.00.00 기준)</span>
                                </div>
                            </div>
                        </div>
                        <div class="clear-both"></div>
                    </div>

                    <div role="tab-type1-id2" class="main-tab-pane tab-pane fade <?= $tab['order']; ?>" id="tab-type1-id2">

                        <div class="pos-r js-chart-checkbox">
                            <div class="nav-pills-sub">
                                <ul class="gd5-tabnav" role="tablist"></ul>
                            </div>
                            <div class="nav-pills-check">
                                데이터 노출형태 :
                                <select name="deviceFl" class="js-device">
                                    <option value="all">통합</option>
                                    <option value="pc">PC</option>
                                    <option value="mobile">모바일</option>
                                    <option value="write">수기</option>
                                </select>
                            </div>
                            <div class="nav-pills-option">
                                <a href="../statistics/order_day.php" target="_top" class="btn btn-sm btn-link">더보기</a>
                            </div>
                        </div>

                        <div class="row row-pd10">
                            <div class="col-xs-6">
                                <table class="table table-rows table-fixed text-center">
                                    <colgroup>
                                        <col>
                                        <col>
                                        <col>
                                        <col>
                                    </colgroup>
                                    <thead>
                                    <tr>
                                        <th>날짜</th>
                                        <th>판매금액</th>
                                        <th>구매건수</th>
                                        <th>구매개수</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="now highlight">
                                        <td class="bold">00/00</td>
                                        <td class="bold">0</td>
                                        <td>0</td>
                                        <td>0</td>
                                    </tr>
                                    <tr class="active">
                                        <td>7일합계</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                    </tr>
                                    <tr class="active">
                                        <td>15일합계</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                    </tr>
                                    <tr class="active">
                                        <td>30일합계</td>
                                        <td>0</td>
                                        <td>0</td>
                                        <td>0</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-xs-6">
                                <div class="graph bdtype">
                                </div>
                            </div>
                        </div>

                        <div class="clear-both"></div>
                    </div>
                    <div role="tab-type1-id3" class="main-tab-pane tab-pane fade <?= $tab['visit']; ?>" id="tab-type1-id3">

                        <div class="pos-r js-chart-checkbox">
                            <div class="nav-pills-sub">
                                <ul class="gd5-tabnav" role="tablist"></ul>
                            </div>
                            <div class="nav-pills-check">
                                데이터 노출형태 :
                                <select name="deviceFl" class="js-device">
                                    <option value="all">통합</option>
                                    <option value="pc">PC</option>
                                    <option value="mobile">모바일</option>
                                </select>
                            </div>
                            <div class="nav-pills-option">
                                <a href="../statistics/visit_today.php" target="_top" class="btn btn-sm btn-link">더보기</a>
                            </div>
                        </div>


                        <div class="row row-pd10">
                            <div class="col-xs-6">
                                <table class="table table-rows table-fixed text-center">
                                    <colgroup>
                                        <col>
                                        <col>
                                        <col>
                                    </colgroup>
                                    <thead>
                                    <tr>
                                        <th>날짜</th>
                                        <th>방문자수</th>
                                        <th>페이지뷰</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="now highlight">
                                        <td class="bold">00/00</td>
                                        <td class="bold">0</td>
                                        <td>0</td>
                                    </tr>
                                    <tr class="active">
                                        <td>7일합계</td>
                                        <td>0</td>
                                        <td>0</td>
                                    </tr>
                                    <tr class="active">
                                        <td>15일합계</td>
                                        <td>0</td>
                                        <td>0</td>
                                    </tr>
                                    <tr class="active">
                                        <td>30일합계</td>
                                        <td>0</td>
                                        <td>0</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-xs-6">
                                <div class="graph bdtype">
                                </div>
                            </div>
                        </div>
                        <div class="clear-both"></div>
                    </div>
                    <div role="tab-type1-id4" class="main-tab-pane tab-pane fade <?= $tab['member']; ?>" id="tab-type1-id4">
                        <div class="pos-r">
                            <div class="nav-pills-sub">
                                <ul class="gd5-tabnav" role="tablist">
                                    <li role="presentation" class="active">
                                        <a href="#member-sub-tab1" role="tab" data-toggle="tab" data-mode="memberTotal" data-link="member_day">회원현황</a>
                                    </li>
                                    <?php if ($mileageUseFl == 'y') { ?>
                                    <li role="presentation">
                                        <a href="#member-sub-tab2" role="tab" data-toggle="tab" data-mode="mileage" data-link="member_mileage">마일리지현황</a>
                                    </li>
                                    <?php } ?>
                                    <?php if ($depositUseFl == 'y') { ?>
                                    <li role="presentation">
                                        <a href="#member-sub-tab3" role="tab" data-toggle="tab" data-mode="deposit" data-link="member_deposit">예치금현황</a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </div>
                            <div class="nav-pills-option">
                                <a href="../statistics/member_day.php" target="_top" class="btn btn-sm btn-link">더보기</a>
                            </div>
                        </div>

                        <div>
                            <div class="tab-content">
                                <div class="tab-pane fade in active" role="member-sub-tab1" id="member-sub-tab1">
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <table class="table table-rows table-fixed text-center">
                                                <colgroup>
                                                    <col>
                                                    <col>
                                                    <col>
                                                    <col>
                                                    <col>
                                                </colgroup>
                                                <thead>
                                                <tr>
                                                    <th>날짜</th>
                                                    <th>전체회원</th>
                                                    <th>신규회원</th>
                                                    <th>탈퇴회원</th>
                                                    <th>휴면회원</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr class="now highlight">
                                                    <td class="bold">00/00</td>
                                                    <td class="bold">0</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                </tr>
                                                <tr class="active">
                                                    <td colspan="2">7일합계</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                </tr>
                                                <tr class="active">
                                                    <td colspan="2">15일합계</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                </tr>
                                                <tr class="active">
                                                    <td colspan="2">30일합계</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                    <td>0</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-xs-6">
                                            <div class="graph bdtype">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div role="member-sub-tab2" class="tab-pane fade" id="member-sub-tab2">
                                    <div>
                                        <table class="table table-rows table-fixed text-center">
                                            <colgroup>
                                                <col class="width-xs">
                                                <col>
                                                <col class="width-xs">
                                                <col>
                                                <col class="width-xs">
                                                <col>
                                            </colgroup>
                                            <thead>
                                            <tr>
                                                <th>날짜</th>
                                                <th>잔여마일리지</th>
                                                <th>지급건수</th>
                                                <th>지급금액</th>
                                                <th>사용건수</th>
                                                <th>사용금액</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>00/00</td>
                                                <td>0</td>
                                                <td>0</td>
                                                <td>0</td>
                                                <td>0</td>
                                                <td>0</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div role="member-sub-tab3" class="tab-pane fade" id="member-sub-tab3">
                                    <div>
                                        <table class="table table-rows table-fixed text-center">
                                            <colgroup>
                                                <col class="width-xs">
                                                <col>
                                                <col class="width-xs">
                                                <col>
                                                <col class="width-xs">
                                                <col>
                                            </colgroup>
                                            <thead>
                                            <tr>
                                                <th>날짜</th>
                                                <th>잔여예치금</th>
                                                <th>지급건수</th>
                                                <th>지급금액</th>
                                                <th>사용건수</th>
                                                <th>사용금액</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>00/00</td>
                                                <td>0</td>
                                                <td>0</td>
                                                <td>0</td>
                                                <td>0</td>
                                                <td>0</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="clearfix pull-right">
                                    <span class="js-display-search-period display-none">(0000.00.00~0000.00.00 기준)</span>
                                </div>
                            </div>
                        </div>
                        <div class="clear-both"></div>
                    </div>
                </div>
            </div>
            <div class="display-none mask-content js-layer-presentation">
                <p>주요현황 조회가<br>정상적으로 완료되지 않았습니다.</p>
                <div class="">
                    <button type="button" class="mask-btn">
                        <img src="<?= PATH_ADMIN_GD_SHARE ?>img/icon_re.png"> 다시 시도
                    </button>
                </div>
                <span class="mask-bg"></span>
            </div>
            <div class="display-none mask-content js-layer-reload">
                <p>데이터 업데이트는 60초당 1회 가능합니다.<br>상세한 통계 데이터는 통계 메뉴에서 확인 가능합니다.</p>
                <div class="">
                    <button type="button" class="mask-btn">확인</button>
                </div>
                <span class="mask-bg"></span>
            </div>
        </div>
    </div>
</div>
