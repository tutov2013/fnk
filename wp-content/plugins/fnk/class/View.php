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
                    <li><a href="/wp-admin/index.php?page=fnk.php&tab=<?= $sId ?>"><?= $arTab['NAME'] ?></a></li>
                    <?
                }
                ?>
            </ul>
            <?
        }
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
            ' . $sField . '</label>
</div>';
        }

        return $sWrapper;
    }

    function getTabContent($arParams, $arFileKeys)
    {
        ob_start();
        ?>
        <h2><?= $arParams['TITLE'] ?></h2>
        <? foreach ($arParams['ITEMS'] as $arItem): ?>
        <? foreach ($arItem as $sKey => $sVal): ?>
            <? if (in_array($sKey, $arFileKeys)): ?>
                <?= $this->wrapField('<img src="' . $sVal . '"><input type="file" name="pic[' . $arItem['id'] . ']"/>'); ?>
            <? else: ?>
                <?= $this->wrapField('<input type="text" name="' . $sKey . '[' . $arItem['id'] . ']" value=' . $sVal . '>', $arParams['CAPTIONS'][$sKey]); ?>
            <? endif; ?>
        <? endforeach; ?>
    <? endforeach; ?>
        <?
        $sContent = ob_get_clean();
        return $this->wrapTabContent($sContent);
    }

    function getFormAdd() {

    }

}