<?
/*
Plugin Name: Команды
Author: t0v.ru
Author URI: http://t0v.ru/
*/
require_once(__DIR__ . '/loader.php');


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

    add_option("fnk_teams_ver", '1.001');
}

register_activation_hook(__FILE__, 'install_fnk');


function process_fnk_init()
{
    global $obFnk;

    $arTabs = array(
        'teams' => array(
            'NAME' => 'Команды',
            'ACTIVE' => $_REQUEST['tab'] == 'teams',
        ),
        'players' => array(
            'NAME' => 'Игроки',
            'ACTIVE' => $_REQUEST['tab'] == 'players'
        ),
    );

    switch ($_REQUEST['object']) {
        case 'teams':
            if (!empty($_REQUEST['new'])) {
                $arData = $_REQUEST['new'];
                $arData['logo'] = $obFnk->Helper->fileUpload($_FILES['pic']);
                add_teams($arData);
            }
            break;
        case 'players':
            if (!empty($_REQUEST['new'])) {
                $arData = $_REQUEST['new'];
                $arData['photo'] = $obFnk->Helper->fileUpload($_FILES['pic']);
                add_players($arData);
            }
            break;
    }

    $obFnk->View->tabs($arTabs);

    switch ($_REQUEST['tab']) {
        case 'teams':
            proccess_teams_form();
            proccess_fnk_teams();
            break;
        case 'players':
            proccess_players_form();
            proccess_fnk_players();
            break;
    }

    return true;
}

// fetch all teams

function get_teams()
{

    global $obFnk, $wpdb;

    $sql = 'SELECT * FROM ' . $obFnk->Helper->getTableName('teams') . ' ORDER BY id ASC';
    $results = $wpdb->get_results($sql, ARRAY_A);

    if (empty($results)) {
        return false;
    }

    return $results;

}

// fetches all players

function get_players()
{

    global $obFnk, $wpdb;

    $sql = 'SELECT * FROM ' . $obFnk->Helper->getTableName('players') . ' ORDER BY id ASC';
    $results = $wpdb->get_results($sql, ARRAY_A);

    if (empty($results)) {
        return false;
    }

    return $results;

}


// proccesses actions (delete / modify )

function proccess_teams_form()
{

    global $obFnk, $wpdb;

    $arCodes = $_REQUEST['code'];
    $arNames = $_REQUEST['name'];
    $arLocations = $_REQUEST['location'];
    $arRatings = $_REQUEST['rating'];

    $arLogo = $obFnk->Helper->prepareFilesUpload('pic');


    $arHome = $_REQUEST['home'];
    $arDeletes = $_REQUEST['del'];

    if (!empty($arDeletes)) {
        $arDeletes = array_merge(array(0), $arDeletes);
        $sDeletes = implode(' OR id=', $arDeletes);
        $sql = 'DELETE FROM ' . $obFnk->Helper->getTableName('teams') . ' WHERE id=' . $sDeletes;
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

            $sql = 'UPDATE ' . $obFnk->Helper->getTableName('teams') . ' SET name="' . $sName
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

    global $obFnk, $wpdb;

    $arKeys = array_keys($arData);
    $sKeys = implode(',', $arKeys);
    $sValues = '"' . implode('","', $arData) . '"';
    $sql = 'INSERT INTO ' . $obFnk->Helper->getTableName('teams') . ' (' . $sKeys . ') VALUES (' . $sValues . ')';
    return $wpdb->query($sql);
}

// adds players

function add_players($arData)
{

    global $obFnk, $wpdb;
    global $table_players;

    $arKeys = array_keys($arData);
    $sKeys = implode(',', $arKeys);
    $sValues = '"' . implode('","', $arData) . '"';


    $sql = 'INSERT INTO ' . $obFnk->Helper->getTableName('players') . ' (' . $sKeys . ') VALUES (' . $sValues . ')';
    return $wpdb->query($sql);

}


// main function

function proccess_fnk_teams()
{
    global $obFnk;
    $arTeams = get_teams();

    $arCaptions = array(
        'code' => 'Код',
        'name' => 'Название',
        'location' => 'Город',
        'home' => 'Домашняя площадка',
        'logo' => 'Логотип',
        'rating' => 'Рейтинг',
    );

    $arParams = array(
        'OBJECT' => 'teams',
        'TITLE' => 'Команды',
        'CAPTIONS' => $arCaptions,
        'TYPES' => array(
            'logo' => 'file',
        ),
        'ITEMS' => $arTeams
    );

    echo '<form action="" method="post" class="fnk_form_add" enctype="multipart/form-data">' .
        '<h2>Добавление команды</h2>' .
        $obFnk->View->getFormAdd($arParams) .
        '<input type="hidden" name="object" value="teams">' .
        '<div class="fnk_field"><input type="submit" value="Coхранить"></div></form>';

    if (!empty($arTeams)) {
        echo '<form action="" method="post" class="fnk_form_list" enctype="multipart/form-data">' .
            $obFnk->View->wrapArrayTableTeams($arTeams, $arCaptions) .
            '<input type="hidden" name="object" value="teams">' .
            '<input type="hidden" name="tab" value="teams">' .
            '</form>';
    }

}


// main function

function proccess_fnk_players()
{
    global $obFnk;

    $arPlayers = get_players();

    $arCaptions = array(
        'id' => 'ID',
        'code' => 'Код',
        'name' => 'Имя',
        'location' => 'Город',
        'team_id' => 'Команда',
        'photo' => 'Фото',
        'age' => 'Возраст',
        'email' => 'Email',
        'rating' => 'Рейтинг'
    );

    $arParams = array(
        'OBJECT' => 'players',
        'TITLE' => 'Игроки',
        'CAPTIONS' => $arCaptions,
        'ITEMS' => $arPlayers,
        'TYPES' => array(
            'location' => 'select',
            'team_id' => 'team',
            'photo' => 'file',
            'email' => 'email',
        )
    );

    if (!empty($arPlayers)) {
        echo '<form action="" method="post" class="fnk_form_list" enctype="multipart/form-data">' .
            $obFnk->View->getTabContent($arParams, array('photo')) .
            '<input type="hidden" name="object" value="players">' .
            '<div class="fnk_field"><input type="submit" value="Coхранить"></div></form>';
    }
    echo '<form action="" method="post" class="fnk_form_add" enctype="multipart/form-data">' .
        '<h2>Добавление игрока</h2>' .
        $obFnk->View->getFormAdd($arCaptions) .
        '<input type="hidden" name="object" value="players">' .
        '<input type="hidden" name="tab" value="players">' .
        '<div class="fnk_field"><input type="submit" value="Coхранить"></div></form>';

}


// adding menu`s page

function add_fnk_page()
{
    if (function_exists('add_submenu_page')) {
        add_submenu_page('index.php', 'Управление командами', 'Управление командами', 0, basename(__FILE__),
            'process_fnk_init');
    }
    return true;
}

add_action('admin_menu', 'add_fnk_page');

/*
 *
1
2
3
124
124
124
4
5
6
7
8
9
0
 * */