<?
/*
Plugin Name: Команды
Author: t0v.ru
Author URI: http://t0v.ru/
*/
require_once(__DIR__.'/loader.php');



function install_fnk()
{
    global $wpdb;
    global $obFnk;

    if ($wpdb->get_var("SHOW TABLES LIKE '" . $obFnk->Helper->getTableName('teams') . "'") != $obFnk->Helper->getTableName('teams')) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $sql = ' CREATE TABLE ' . $obFnk->Helper->getTableName('teams') . ' (
					`id` INT NOT NULL AUTO_INCREMENT ,
					`code` VARCHAR( 254 ) ,
					`name` VARCHAR( 254 ) NOT NULL ,
					`location` VARCHAR( 512 ) NOT NULL ,
					`rating` INT( 11 ) NOT NULL ,
					`logo` VARCHAR( 254 ) NOT NULL ,
					`home` VARCHAR( 254 ) NOT NULL ,
					PRIMARY KEY ( `id` )
					) ENGINE = MYISAM ';

        dbDelta($sql);

    }

    if ($wpdb->get_var("SHOW TABLES LIKE '" . $obFnk->Helper->getTableName('players') . "'") != $obFnk->Helper->getTableName('players')) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $sql = ' CREATE TABLE ' . $obFnk->Helper->getTableName('players') . ' (
					`id` INT NOT NULL AUTO_INCREMENT ,
					`code` VARCHAR( 254 ) ,
					`name` VARCHAR( 254 ) NOT NULL ,
					`location` VARCHAR( 512 ) NOT NULL ,
					`rating` INT( 11 ) NOT NULL ,
					`photo` VARCHAR( 254 ) NOT NULL ,
					`email` VARCHAR( 254 ) NOT NULL ,
					`team_id` VARCHAR( 254 ) NOT NULL ,
					`age` int( 2 ) NOT NULL ,
					PRIMARY KEY ( `id` )
					) ENGINE = MYISAM ';

        dbDelta($sql);
    }

    add_option("fnk_teams_ver", '1.0');
}

register_activation_hook(__FILE__, 'install_fnk');


function process_fnk_init()
{
    global $obFnk;

    $arTabs = array(
        'teams' => array(
            'NAME' => 'Команды',
            'ACTIVE' => $_REQUEST['object'] == 'teams'
        ),
        'players' => array(
            'NAME' => 'Игроки',
            'ACTIVE' => $_REQUEST['object'] == 'teams'
        ),
    );

    switch ($_REQUEST['object']) {
        case 'teams':
            if (!empty($_REQUEST['new'])) {
                $arData = $_REQUEST['new'];
                $arData['logo'] = $obFnk->Helper->fileUpload($_FILES['pic']);
                add_teams($arData);
            } else {
                proccess_teams_form();
            }
            break;
        case 'players':
            if (!empty($_REQUEST['new'])) {
                $arData = $_REQUEST['new'];
                $arData['photo'] = $obFnk->Helper->fileUpload($_FILES['pic']);
                add_players($arData);
            } else {
                proccess_players_form();
            }
            break;
    }

    $obFnk->View->tabs($arTabs);
    proccess_fnk_teams();
    proccess_fnk_players();
    return true;
}

// fetches all teams

function get_teams()
{

    global $wpdb;
    global $table_teams;

    $sql = 'SELECT * FROM ' . $table_teams . ' ORDER BY id ASC';
    $results = $wpdb->get_results($sql, ARRAY_A);

    if (empty($results)) return false;

    return $results;

}

// fetches all players

function get_players()
{

    global $wpdb;
    global $table_players;

    $sql = 'SELECT * FROM ' . $table_players . ' ORDER BY id ASC';
    $results = $wpdb->get_results($sql, ARRAY_A);

    if (empty($results)) return false;

    return $results;

}


// proccesses actions (delete / modify )

function proccess_teams_form()
{

    global $wpdb;
    global $table_teams;

    $arCodes = $_REQUEST['code'];
    $arNames = $_REQUEST['name'];
    $arLocations = $_REQUEST['location'];
    $arRatings = $_REQUEST['rating'];


    $arLogo = prepare_files_upload('pic');


    $arHome = $_REQUEST['home'];
    $arDeletes = $_REQUEST['del'];

    if (!empty($arDeletes)) {
        $arDeletes = array_merge(array(0), $arDeletes);
        $sDeletes = implode(' OR id=', $arDeletes);
        $sql = 'DELETE FROM ' . $table_teams . ' WHERE id=' . $sDeletes;
        $wpdb->query($sql);
    }


    if (is_array($arNames)) {

        foreach ($arNames as $iId => $sName) {

            $sName = esc_sql($sName);
            $sCode = esc_sql($arCodes[$iId]);
            $sLocation = esc_sql($arLocations[$iId]);
            $iRating = intval($arRatings[$iId]);
            $sLogo = esc_sql($arLogo[$iId]);

            $sHome = esc_sql($arHome[$iId]);

            $sql = 'UPDATE ' . $table_teams . ' SET name="' . $sName
                . '", code="' . $sCode
                . '", rating="' . $iRating
                . ($sLogo ? '", logo="' . $sLogo : '')
                . '", home="' . $sHome
                . '", location="' . $sLocation
                . '" WHERE id=' . $iId;
            $wpdb->query($sql);

        }

    }

}


// proccesses actions (delete / modify )

function proccess_players_form()
{

    global $wpdb;
    global $table_players;

    $arCodes = $_REQUEST['code'];
    $arNames = $_REQUEST['name'];
    $arLocations = $_REQUEST['location'];
    $arRatings = $_REQUEST['rating'];
    $arPhotos = prepare_files_upload('pic');
    $arEmails = $_REQUEST['email'];
    $arTeams = $_REQUEST['team_id'];
    $arAges = $_REQUEST['age'];
    $arDeletes = $_REQUEST['del'];

    if (!empty($arDeletes)) {
        $arDeletes = array_merge(array(0), $arDeletes);
        $sDeletes = implode(' OR id=', $arDeletes);
        $sql = 'DELETE FROM ' . $table_players . ' WHERE id=' . $sDeletes;
        $wpdb->query($sql);
    }


    if (is_array($arNames)) {

        foreach ($arNames as $iId => $sName) {

            $sName = esc_sql($sName);
            $sCode = esc_sql($arCodes[$iId]);
            $sLocation = esc_sql($arLocations[$iId]);
            $iRating = intval($arRatings[$iId]);
            $sPhoto = esc_sql($arPhotos[$iId]);
            $sEmail = esc_sql($arEmails[$iId]);
            $sTeam = esc_sql($arTeams[$iId]);
            $sAge = esc_sql($arAges[$iId]);

            $sql = 'UPDATE ' . $table_players . ' SET name="' . $sName
                . '", code="' . $sCode
                . '", rating="' . $iRating
                . ($sPhoto ? '", photo="' . $sPhoto : '')
                . '", email="' . $sEmail
                . '", location="' . $sLocation
                . '", team_id="' . $sTeam
                . '", age="' . $sAge
                . '" WHERE id=' . $iId;
            $wpdb->query($sql);

        }

    }

}


// adds teams

function add_teams($arData)
{

    global $wpdb;
    global $table_teams;

    $arKeys = array_keys($arData);
    $sKeys = implode(',', $arKeys);
    $sValues = '"' . implode('","', $arData) . '"';
    $sql = 'INSERT INTO ' . $table_teams . ' (' . $sKeys . ') VALUES (' . $sValues . ')';
    $status = $wpdb->query($sql);

}

// adds players

function add_players($arData)
{

    global $wpdb;
    global $table_players;

    $arKeys = array_keys($arData);
    $sKeys = implode(',', $arKeys);
    $sValues = '"' . implode('","', $arData) . '"';


    $sql = 'INSERT INTO ' . $table_players . ' (' . $sKeys . ') VALUES (' . $sValues . ')';
    $wpdb->query($sql);

}


// main function

function proccess_fnk_teams()
{
    global $obFnk;
    $arTeams = get_teams();
    if (!empty($arTeams)) {
        $arParams = array(
            'OBJECT' => 'teams',
            'TITLE' => 'Команды',
            'CAPTIONS' => array(
                'id' => 'ID',
                'code' => 'Код',
                'name' => 'Имя',
                'city' => 'Город',
                'home' => 'Домашняя площадка',
                'logo' => 'Логотип',
                'rating' => 'Рейтинг'
            ),
            'ITEMS' => $arTeams,
        );

        $arFields =array(
            'code',
            'name',
            'city',
            'home',
            'logo',
            'rating',
        );
        
        return $obFnk->View->getTabContent($arParams, array('logo')).$obFnk->View->getFormAdd($arFields);
    }
    ?>

    <div class="wrap">

        <h2>Список команд</h2>

        <table width="60%">

            <tr>
                <td><b>ID</b></td>
                <td><b>Код</b></td>
                <td><b>Имя</b></td>
                <td><b>Город</b></td>
                <td><b>Домашняя площадка</b></td>
                <td><b>Логотип</b></td>
                <td><b>Рейтинг</b></td>
                <td><b>Удалить</b></td>
            </tr>
            <? if (!empty($arTeams)) { ?>
                <form action="" method="post" enctype="multipart/form-data">

                    <? foreach ($arTeams as $arTeam) { ?>
                        <tr>
                            <td><?= $arTeam['id'] ?></td>
                            <td>
                                <input type="text" name="code[<?= $arTeam['id'] ?>]" value="<?= $arTeam['code'] ?>"/>
                            </td>
                            <td>
                                <input type="text" name="name[<?= $arTeam['id'] ?>]" value="<?= $arTeam['name'] ?>"/>
                            </td>
                            <td>
                                <input type="text" name="location[<?= $arTeam['id'] ?>]"
                                       value="<?= $arTeam['location'] ?>"/>
                            </td>
                            <td>
                                <input type="text" name="home[<?= $arTeam['id'] ?>]" value="<?= $arTeam['home'] ?>"/>
                            </td>
                            <td>
                                <label class="upload_pic" style="position: relative;width:200px;min-height:30px;display:block;">
                                <span style="display:block;border-bottom:1px dotted #333;position: absolute;">Загрузить изображение...</span>
                                <img style="max-width:150px;max-height:150px;" src="<?= $arTeam['logo'] ?>">
                                <input type="file" name="pic[<?= $arTeam['id'] ?>]" style="display: none;"/>
                                </label>
                            </td>
                            <td>
                                <input type="text" name="rating[<?= $arTeam['id'] ?>]"
                                       value="<?= $arTeam['rating'] ?>"/>
                            </td>
                            <td align="center">
                                <input type="checkbox" name="del[<?= $arTeam['id'] ?>]" value="<?= $arTeam['id'] ?>"/>
                            </td>
                        </tr>
                    <? } ?>

                    <tr>
                        <td>&nbsp;</td>
                        <td colspan="4">
                            <input type="hidden" name="object" value="teams"/>
                            <input type="submit" value="Обновить"/>
                        </td>
                    </tr>
                </form>

                <tr>
                    <td colspan="4">&nbsp;</td>
                </tr>
            <? } ?>

            <form action="" method="post" enctype="multipart/form-data">
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="text" name="new[code]"/></td>
                    <td><input type="text" name="new[name]"/></td>
                    <td><input type="text" name="new[location]"/></td>
                    <td><input type="text" name="new[rating]"/></td>
                    <td><input type="file" name="pic"/></td>
                    <td><input type="text" name="new[home]"/></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td colspan="5">
                        <input type="hidden" name="object" value="teams"/>
                        <input type="submit" value="Добавить"/>
                    </td>
                </tr>
            </form>

        </table>
    </div>

    <?
}


// main function

function proccess_fnk_players()
{

    $arPlayers = get_players();

    ?>
    <div class="wrap">

        <h2>Список игроков</h2>

        <table width="60%">

            <tr>
                <td><b>ID</b></td>
                <td><b>Код</b></td>
                <td><b>Имя</b></td>
                <td><b>Город</b></td>
                <td><b>ID-команды</b></td>
                <td><b>Фото</b></td>
                <td><b>Возраст</b></td>
                <td><b>Email</b></td>
                <td><b>Рейтинг</b></td>
                <td><b>Удалить</b></td>
            </tr>
            <? if (!empty($arPlayers)) { ?>
                <form action="" method="post" enctype="multipart/form-data">

                    <? foreach ($arPlayers as $arPlayer) { ?>
                        <tr>
                            <td><?= $arPlayer['id'] ?></td>
                            <td>
                                <input type="text" name="code[<?= $arPlayer['id'] ?>]"
                                       value="<?= $arPlayer['code'] ?>"/>
                            </td>
                            <td>
                                <input type="text" name="name[<?= $arPlayer['id'] ?>]"
                                       value="<?= $arPlayer['name'] ?>"/>
                            </td>
                            <td>
                                <input type="text" name="location[<?= $arPlayer['id'] ?>]"
                                       value="<?= $arPlayer['location'] ?>"/>
                            </td>
                            <td><?= $arPlayer['team_id'] ?></td>
                            <td>
                                <label class="upload_pic" style="position: relative;width:200px;min-height:30px;display:block;">
                                    <span style="display:block;border-bottom:1px dotted #333;position: absolute;">Загрузить изображение...</span>
                                    <img style="max-width:150px;max-height:150px;" src="<?= $arPlayer['photo'] ?>">
                                    <input type="file" name="pic[<?= $arPlayer['id'] ?>]" style="display: none;"/>
                                </label>
                            </td>
                            <td>
                                <input type="text" name="age[<?= $arPlayer['id'] ?>]" value="<?= $arPlayer['age'] ?>"/>
                            </td>
                            <td>
                                <input type="text" name="email[<?= $arPlayer['id'] ?>]"
                                       value="<?= $arPlayer['email'] ?>"/>
                            </td>
                            <td>
                                <input type="text" name="rating[<?= $arPlayer['id'] ?>]"
                                       value="<?= $arPlayer['rating'] ?>"/>
                            </td>
                            <td align="center">
                                <input type="checkbox" name="del[<?= $arPlayer['id'] ?>]"
                                       value="<?= $arPlayer['id'] ?>"/>
                            </td>
                        </tr>
                    <? } ?>

                    <tr>
                        <td>&nbsp;</td>
                        <td colspan="4">
                            <input type="hidden" name="object" value="players"/>
                            <input type="submit" value="Обновить"/>
                        </td>
                    </tr>
                </form>

                <tr>
                    <td colspan="4">&nbsp;</td>
                </tr>
            <? } ?>

            <form action="" method="post" enctype="multipart/form-data">
                <tr>
                    <td>&nbsp;</td>
                    <td><input type="text" name="new[code]"/></td>
                    <td><input type="text" name="new[name]"/></td>
                    <td><input type="text" name="new[location]"/></td>
                    <td><input type="text" name="new[team_id]"/></td>
                    <td><input type="file" name="pic"/></td>
                    <td><input type="text" name="new[age]"/></td>
                    <td><input type="text" name="new[email]"/></td>
                    <td><input type="text" name="new[rating]"/></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td colspan="5">
                        <input type="hidden" name="object" value="players"/>
                        <input type="submit" value="Добавить"/>
                    </td>
                </tr>
            </form>

        </table>
    </div>

    <?
}


// adding menu`s page

function add_fnk_page()
{
    if (function_exists('add_submenu_page')) {
        add_submenu_page('index.php', 'Управление командами', 'Управление командами', 0, basename(__FILE__), 'process_fnk_init');
    }
    return true;
}

add_action('admin_menu', 'add_fnk_page');