<?php

include("Scaffold.php");
include("CreateZip.php");
include("eZPublish.php");

if (isset($_COOKIE['scaffold_info'])) {
    $data = trim($_COOKIE['sql']);
    $data_lines = explode("\n", $data);
    $table = array();





    $table['list_page'] = stripslashes($_COOKIE['list_page']);
    $table['edit_page'] = stripslashes($_COOKIE['edit_page']);
    $table['new_page'] = stripslashes($_COOKIE['new_page']);
    $table['delete_page'] = stripslashes($_COOKIE['delete_page']);
    $table['extension_name'] = stripslashes($_COOKIE['extension']);
    


    $table['id_key'] = trim(Scaffold::get_mysql_primary_key($data));
    if ($table['id_key'] == '' || !$table['id_key'])
    {
        $table['id_key'] = 'id';
    }

    // get first table name
    if(substr_count($data, 'CREATE TABLE') > 1)
    {
        $message .= "Please scaffold one table at a time";
    }
    elseif (preg_match(Scaffold::REGEX_CREATE_TABLE, $data, $matches)) {
        $table['name'] = Scaffold::find_text($matches[0]);
        $table['class_name'] = Scaffold::toCamelCase($table['name'], true);
        $table['real_class_name'] = 'Service_Database_'.$table['class_name'];
        $max = count($data_lines);
        for ($i = 1; $i < $max; $i++) {
            if (strpos(trim($data_lines[$i]), '`') === 0) { // this line has a column
                $col = Scaffold::find_text(trim($data_lines[$i]));
                $blob = ( stripos($data_lines[$i], 'TEXT') || stripos($data_lines[$i], 'BLOB') ) ? 1 : 0;
                $datetime = ( stripos($data_lines[$i], 'DATETIME') ) ? 1 : 0;
                eval("\$table['columns']['$col'] = array('blob' => $blob, 'datetime' => $datetime );");
                $type = Scaffold::findFieldType($data_lines[$i]);
                $size = Scaffold::findTypeMaxSize($data_lines[$i], $type);
                $required = Scaffold::isFieldRequiered($data_lines[$i]);
                $table['columns'][$col]['type'] = $type;
                $table['columns'][$col]['size'] = $size;
                $table['columns'][$col]['required'] = $required;
            }
        }

        $show_form = 1;

    } else {
        $message .= "Cannot find 'CREATE TABLE `table_name` ( '";
    }
}

if ($show_form) {

    $base = md5(rand(0, 99999) + time());

    $eZPublish = new eZPublish($table['extension_name'], $table['name']);
    $s = new Scaffold($table);
    $s->download = true;


    $createZip = new CreateZip;
    $createZip->addFile($s->generateClassDbManager(), $eZPublish->getClassesPath() . '/' . 'DAL' . '/' . 'DbManager.php');
    $createZip->addFile($s->generateClassGenericTable(), $eZPublish->getClassesPath() . '/' . 'DAL' . '/' . 'Base.php');
    $createZip->addFile($s->generateClassTable(), $eZPublish->getClassesPath() . '/' . 'DAL' . '/' . $table['class_name'] . '.php');
    $createZip->addFile($s->generateListTableCtrl(), $eZPublish->getControllersPath() . '/' . $table['list_page'] . '.php');
    $createZip->addFile($s->generateListTableDesign(), $eZPublish->getTemplatesPath() . '/' . $table['list_page'] . '.tpl');
    $createZip->addFile($s->generateNewrowCtrl(), $eZPublish->getControllersPath() . '/' . $table['new_page'] . '.php');
    $createZip->addFile($s->generateNewrowDesign(), $eZPublish->getTemplatesPath() . '/' . $table['new_page'] . '.tpl');
    $createZip->addFile($s->generateEditrowCtrl(), $eZPublish->getControllersPath() . '/' . $table['edit_page'] . '.php');
    $createZip->addFile($s->generateEditrowDesign(), $eZPublish->getTemplatesPath() . '/' . $table['edit_page'] . '.tpl');
    $createZip->addFile($s->generateDeleterowCtrl(), $eZPublish->getControllersPath() . '/' . $table['delete_page'] . '.php');
    $createZip->addFile($s->generateDeleterowDesign(), $eZPublish->getTemplatesPath() . '/' . $table['delete_page'] . '.tpl');
    $createZip->addFile($s->generateModuleCtrl(), $eZPublish->getControllersPath() . '/' . eZPublish::FILE_MODULE_PHP);
    $createZip->addFile($s->generateClassPaginator(), $eZPublish->getClassesPath() . '/' . 'Helper' . '/' . 'Paginator.php');
    $createZip->addFile($s->generateClassFormValidator(), $eZPublish->getClassesPath() . '/' . 'Helper' . '/' . 'FormValidator.php');
    $createZip->addFile($s->generateSettingsDesignIni(), $eZPublish->getIniPath() . '/' . 'design.ini.append.php');
    $createZip->addFile($s->generateSettingsModuleIni(), $eZPublish->getIniPath() . '/' . 'module.ini.append.php');
    $createZip->addFile($s->generateSettingsSiteIni(), $eZPublish->getIniPath() . '/' . 'site.ini.append.php');
    $createZip->addFile($s->generateReadMe(),'/' . 'readme.txt');
    $createZip->addFile($data,'/' . $table['name'] . '.sql');

    $fileName = "temp/$base.zip";

    $fd = fopen($fileName, "wb");
    $out = fwrite($fd, $createZip->getZippedfile());
    fclose($fd);
    $createZip->forceDownload($fileName);

    @unlink($fileName); 
} else {

    header("Location: index.php");
}
?>