<div class="panel ">
    <?php
    $oldDepth = 2;
    foreach ($naviMenu->leftMenus as $leftKey => $leftVal) {
        if (empty($leftVal['sNo']) === false && $naviMenu->accessMenu != 'all') {
            $menuAccessDisplay = true;
            if (array_key_exists($leftVal['sNo'], $naviMenu->accessMenu) === false) {
                $menuAccessDisplay = false;
            } else if (count($naviMenu->accessMenu[$leftVal['sNo']]) < 1) {
                $menuAccessDisplay = false;
            } else if ($leftVal['depth'] == 3 && in_array($leftVal['tNo'], $naviMenu->accessMenu[$leftVal['sNo']]) === false) {
                $menuAccessDisplay = false;
            }
            if ($menuAccessDisplay === false) {
                continue;
            }
        }

        if ($leftVal['depth'] == 2) {
            if ($leftVal['sDisplay'] == 'y') {
                echo $oldDepth != $leftVal['depth'] ? '</ul>' : '';
                ?>
                <div class="panel-heading <?php echo $naviMenu->menuSelected['mid'][$leftVal['sNo']]; ?>"><?php echo $leftVal['sName']; ?></div>
                <?php
                $oldDepth = 2;
            }
        } else if ($leftVal['depth'] == 3) {
            if ($leftVal['tDisplay'] == 'y') {
                echo $oldDepth != $leftVal['depth'] ? '<ul class="list-group">' : '';
                ?>
                <li class="list-group-item <?php echo $naviMenu->menuSelected['this'][$leftVal['tNo']]; ?>">
                    <a href="<?php echo $leftVal['tUrl'];?>" <?php if ($leftVal['tCode'] == 'developGuide') { ?> target="_blank" <?php } ?>><?php echo $leftVal['tName']; ?></a> <?php echo $leftVal['levelLimit']; ?>
                </li>
                <?php
                $oldDepth = 3;
            }
        }
    }
    ?>
    </ul>
</div>
