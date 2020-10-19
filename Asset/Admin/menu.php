<!-- close/open -->
<div class="js-adminmenu-toggle"></div>
<div class="js-listgroup-toggle"></div>
<!-- //close/open -->
<?php
gd_isset($naviMenu);
?>
<div class="menu-header <?= $naviMenu->cd[0]; ?>">
    <h2><?= reset($naviMenu->location); ?></h2>
</div>
<div class="panel ">
    <?php
    $sdate = \Globals::get('gLicense.sdate');
    $oldDepth = 2;
    foreach ($naviMenu->leftMenus as $leftKey => $leftVal) {
        if ($naviMenu->accessMenu !== 'all' && empty($leftVal['sNo']) === false) {
            $menuAccessDisplay = true;
            if (array_key_exists($leftVal['sNo'], $naviMenu->accessMenu) === false) {
                $menuAccessDisplay = false;
            } elseif (count($naviMenu->accessMenu[$leftVal['sNo']]) < 1) {
                $menuAccessDisplay = false;
            } elseif ((int) $leftVal['depth'] === 3
                && in_array($leftVal['tNo'], $naviMenu->accessMenu[$leftVal['sNo']], true) === false) {
                $menuAccessDisplay = false;
            }
            if ($menuAccessDisplay === false) {
                continue;
            }
        }

        if ((int) $leftVal['depth'] === 2) {
            if ($leftVal['sDisplay'] === 'y') {
                echo $oldDepth != $leftVal['depth'] ? '</ul>' : '';
                ?>
                <div class="panel-heading menu-icon-minus <?= gd_isset($naviMenu->menuSelected['mid'][$leftVal['sNo']]); ?>"><?= $leftVal['sName']; ?></div>
                <?php
                $oldDepth = 2;
            }
        } elseif ((int) $leftVal['depth'] === 3) {
            if ($leftVal['tDisplay'] === 'y') {
                echo $oldDepth != $leftVal['depth'] ? '<ul class="list-group">' : '';

                //20171220 에 제공된 주문개선에 대하여 (구)교환리스트는 신규몰에게 리스트를 노출시키지 않는다.
                if ($leftVal['tNo'] !== 'godo00113'
                    || ($leftVal['tNo'] === 'godo00113' && $sdate < '20171220')) {
                    ?>
                    <li class="list-group-item <?= gd_isset($naviMenu->menuSelected['this'][$leftVal['tNo']]); ?>">
                        <a href="<?= $leftVal['tUrl']; ?>"><?= $leftVal['tName']; ?></a> <?= gd_isset($leftVal['levelLimit']); ?>
                    </li>
                    <?php
                }
                $oldDepth = 3;
            }
        }
    }
    ?>
    </ul>
    <?php if ($naviMenu->cd[0] === 'plusshop') {?>
    <div style="margin-top:100px;text-align:center"><a href="http://plus.godo.co.kr/
" target="_blank"><img src="<?=PATH_ADMIN_GD_SHARE?>img/ban_plus.png"></a></div>
    <?php }?>
</div>
