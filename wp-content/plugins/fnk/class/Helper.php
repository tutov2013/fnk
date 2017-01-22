<?php

class Helper
{

    public function getTableName($sTable)
    {
        global $wpdb;
        return $wpdb->prefix . 'fnk_' . $sTable;
    }


    /**
     * @param $sKey
     * @return string
     */
    function fileUpload($arFile)
    {
        $sFilename = '';

        if (!empty($arFile)) {
            $type = $t = '';
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
                $sPath = '/fnk_images/';
                $sFilename = $sPath . time() . $t;
                if (!file_exists($sPath = $_SERVER['DOCUMENT_ROOT'] . $sPath)) {
                    mkdir($sPath);
                }
                move_uploaded_file($arFile['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $sFilename);
            }
        }

        return $sFilename;
    }

    function prepareFilesUpload($sKey)
    {
        if (empty($_FILES[$sKey])) return false;

        if (is_array($_FILES[$sKey]['error'])) {
            $arFiles = array();
            foreach ($_FILES[$sKey]['error'] as $sID => $iError) {
                if ($iError > 0) continue;
                $arFiles[$sID] = file_upload(array(
                    'name' => $_FILES[$sKey]['name'][$sID],
                    'type' => $_FILES[$sKey]['type'][$sID],
                    'tmp_name' => $_FILES[$sKey]['tmp_name'][$sID],
                    'error' => $iError,
                    'size' => $_FILES[$sKey]['size'][$sID],
                ));
            }
            return !empty($arFiles) ? $arFiles : false;
        } else {
            if ($_FILES[$sKey]['error'] > 0) {
                return false;
            } else {
                return file_upload($_FILES[$sKey]);
            }
        }
    }

}