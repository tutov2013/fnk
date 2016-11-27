<?
/*
Plugin Name: Команды
Author: t0v.ru
Author URI: http://t0v.ru/
*/
global $wpdb;
global $table_teams;
global $table_players;

$table_teams = $wpdb->prefix . 'fnk_teams';
$table_players = $wpdb->prefix . 'fnk_players';

function install_fnk()
{
    global $wpdb;
    global $table_teams;
    global $table_players;

    if ($wpdb->get_var("SHOW TABLES LIKE '" . $table_teams . "'") != $table_teams) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $sql = ' CREATE TABLE ' . $table_teams . ' (
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

    if ($wpdb->get_var("SHOW TABLES LIKE '" . $table_players . "'") != $table_players) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $sql = ' CREATE TABLE ' . $table_players . ' (
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
/**
 * @param $sKey
 * @return string
 */
function file_upload($arFile)
{
    $sFilename = '';
    if (!empty($arFile)) {

        switch ($arFile['type']) {
            case 'image/gif' :
                $t = '.gif';
                $type = imagecreatefromgif($arFile['tmp_name']);
                break;
            case 'image/jpeg' :
                $t = '.jpg';
                $type = imagecreatefromjpeg($arFile['tmp_name']);
                break;
            case 'image/png' :
                $t = '.png';
                $type = imagecreatefrompng($arFile['tmp_name']);
                break;
        }

        if ($type) {
            $sFilename = '/fnk_images/' . time() . $t;
            move_uploaded_file($arFile['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/fnk_images/' . time() . $t);
        }
    }
    return $sFilename;
}


// adding menu`s page

function add_fnk_page()
{

    if (function_exists('add_submenu_page')) {
        add_submenu_page('index.php', 'Управление командами', 'Управление командами', 0, basename(__FILE__), 'process_fnk_init');
    }

}


function process_fnk_init()
{

    switch ($_REQUEST['object']) {
        case 'teams':
            if (!empty($_REQUEST['new'])) {
                add_players($_REQUEST['new']);
            } else {
                proccess_players_form();
            }
            break;
        case 'players':
            if (!empty($_REQUEST['new'])) {
                add_players($_REQUEST['new']);
            } else {
                proccess_players_form();
            }
            break;
    }
    proccess_fnk_teams();
    proccess_fnk_players();
    return true;
}

// fetches all teams

function get_teams()
{

    global $wpdb;
    global $table_teams;

    $sql = 'SELECT * FROM ' . $table_teams . ' ORDER BY name ASC';
    $results = $wpdb->get_results($sql, ARRAY_A);

    if (empty($results)) return false;

    return $results;

}

// fetches all players

function get_players()
{

    global $wpdb;
    global $table_players;

    $sql = 'SELECT * FROM ' . $table_players . ' ORDER BY name ASC';
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
    $arLogo = $_REQUEST['logo'];
    $arHome = $_REQUEST['home'];
    $arDeletes = $_REQUEST['del'];

    if (!empty($arDeletes)) {
        $arDeletes = array_merge(array(0),$arDeletes);
        $sDeletes = implode(' OR id=',$arDeletes);
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
                . '", logo="' . $sLogo
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
    $arPhotos = $_REQUEST['photo'];
    $arEmails = $_REQUEST['email'];
    $arTeams = $_REQUEST['team_id'];
    $arAges = $_REQUEST['age'];
    $arDeletes = $_REQUEST['del'];

    if (!empty($arDeletes)) {
        $arDeletes = array_merge(array(0),$arDeletes);
        $sDeletes = implode(' OR id=',$arDeletes);
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
                . '", photo="' . $sPhoto
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

    $arTeams = get_teams();

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
                                <img src="<?= $arTeam['logo'] ?>">
                                <input type="file" name="pic"/>
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

            <form action="" method="post">
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
                                <img src="<?= $arPlayer['photo'] ?>">
                                <input type="file" name="pic"/>
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

            <form action="" method="post">
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

add_action('admin_menu', 'add_fnk_page');