<?php

class View
{

    function tabs($arTabs)
    {
        if (!empty($arTabs)) {
            ?>
            <ul class="fnk_tabs">
                <?
                foreach ($arTabs as $sId => $arTab) {
                    ?>
                    <li<?= $arTab['ACTIVE'] ? ' class="active"' : '' ?>>
                        <a href="/wp-admin/index.php?page=fnk.php&tab=<?= $sId ?>"><?= $arTab['NAME'] ?></a>
                        <?= $arTab['CONTENT'] ?>
                    </li>
                    <?
                }
                ?>
            </ul>
            <?
        }
    }

    function wrapArrayTableTeams($arRows, $arCaptions)
    {
        ob_start();
        ?>
        <table class="list_table">
            <thead>
            <tr>
                <td><?= $arCaptions['code'] ?></td>
                <td><?= $arCaptions['name'] ?></td>
                <td><?= $arCaptions['location'] ?></td>
                <td><?= $arCaptions['home'] ?></td>
                <td><?= $arCaptions['logo'] ?></td>
                <td><?= $arCaptions['rating'] ?></td>
            </tr>
            </thead>
            <tbody>
            <? foreach ($arRows as $arRow): ?>
                <tr>
                    <td><?= $this->wrapField('<input type="text" name="code['.$arRow['id'].']" value="' . $arRow['code'] . '">'); ?></td>
                    <td><?= $this->wrapField('<input type="text" name="name['.$arRow['id'].']" value="' . $arRow['name'] . '">'); ?></td>
                    <td><?= $this->wrapField('<input type="text" name="location['.$arRow['id'].']" value="' . $arRow['location'] . '">'); ?></td>
                    <td><?= $this->wrapField('<input type="text" name="home['.$arRow['id'].']" value="' . $arRow['home'] . '">'); ?></td>
                    <td><?=  $this->wrapField('<img style="max-width:50px;" src="' . $arRow['logo'] . '"><input style="display:none;" type="file" name="pic[' . $arRow['id'] . ']"/>'); ?></td>
                    <td><?= $this->wrapField('<input type="text" name="rating['.$arRow['id'].']" value="' . $arRow['rating'] . '">'); ?></td>
                </tr>
            <? endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td>
                    <?= $this->wrapField('<input type="submit" value="Сохранить">'); ?>
                </td>
            </tr>
            </tfoot>
        </table>
        <?
        return ob_get_clean();
    }

    function wrapArrayTablePlayers($arRows, $arCaptions)
    {
        ob_start();
        ?>
        <table class="list_table">
            <thead>
            <tr>
                <td><?= $arCaptions['code'] ?></td>
                <td><?= $arCaptions['name'] ?></td>
                <td><?= $arCaptions['location'] ?></td>
                <td><?= $arCaptions['home'] ?></td>
                <td><?= $arCaptions['logo'] ?></td>
                <td><?= $arCaptions['rating'] ?></td>
            </tr>
            </thead>
            <tbody>
            <? foreach ($arRows as $arRow): ?>
                <tr>
                    <td><?= $this->wrapField('<input type="text" name="code['.$arRow['id'].']" value="' . $arRow['code'] . '">'); ?></td>
                    <td><?= $this->wrapField('<input type="text" name="name['.$arRow['id'].']" value="' . $arRow['name'] . '">'); ?></td>
                    <td><?= $this->wrapField('<input type="text" name="location['.$arRow['id'].']" value="' . $arRow['location'] . '">'); ?></td>
                    <td><?= $this->wrapField('<input type="text" name="home['.$arRow['id'].']" value="' . $arRow['home'] . '">'); ?></td>
                    <td><?= $this->wrapField('<img style="max-width:50px;" src="' . $arRow['logo'] . '"><input style="display:none;" type="file" name="pic[' . $arRow['id'] . ']"/>'); ?></td>
                    <td><?= $this->wrapField('<input type="text" name="rating['.$arRow['id'].']" value="' . $arRow['rating'] . '">'); ?></td>
                </tr>
            <? endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td>
                    <?= $this->wrapField('<input type="submit" value="Сохранить">'); ?>
                </td>
            </tr>
            </tfoot>
        </table>
        <?
        return ob_get_clean();
    }

    function wrapTabContent($sContent)
    {
        $sResult = '';
        ob_start();
        ?>
        <div class="fnk_tab_content">
            <?= $sContent ?>
        </div>
        <?
        $sResult = ob_get_clean();
        return $sResult;
    }

    function wrapField($sField, $sFieldCaption = '')
    {
        $sWrapper = '';
        if (!empty($sField)) {
            $sWrapper = '<div class="fnk_field">
            <label>' . $sFieldCaption . '
            ' . $sField . '</label></div>';
        }

        return $sWrapper;
    }

    function getTabContent($arParams)
    {
        $sContent = '';
        if (empty($arParams['ITEMS'])) {
            return '';
        }

        $sContent = '<h2>' . $arParams['TITLE'] . '</h2>';
        foreach ($arParams['ITEMS'] as $arItem) {
            foreach ($arItem as $sKey => $sVal) {
                switch ($arParams['TYPES'][$sKey]) {
                    case 'file':
                        $sContent .= $this->wrapField('<img src="' . $sVal . '"><input type="file" name="pic[' . $arItem['id'] . ']"/>',
                            $arParams['CAPTIONS'][$sKey]);
                        break;
                    default:
                        $sContent .= $this->wrapField('<input type="text" name="' . $sKey . '[' . $arItem['id'] . ']" value=' . $sVal . '>',
                            $arParams['CAPTIONS'][$sKey]);
                        break;
                }
            }
        }
        return $this->wrapTabContent($sContent);
    }

    function getFormAdd($arFields)
    {
        $sContent = '';
        unset($arFields['CAPTIONS']['id']);
        foreach ($arFields['CAPTIONS'] as $sField => $sCaption) {
            switch ($arFields['TYPES'][$sField]) {
                case 'file':
                    $sContent .= $this->wrapField('<input type="file" name="pic"/>', $sCaption);
                    break;
                default:
                    $sContent .= $this->wrapField('<input type="text" name="new[' . $sField . ']">', $sCaption);
                    break;
            }
        }

        return $this->wrapTabContent($sContent);
    }

}