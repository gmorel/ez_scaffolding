<?php

include("Scaffold.php");
include("CreateZip.php");

$show_form = 0;
$message = '';


if (isset($_POST['scaffold_info'])) {

    $data = trim($_POST['sql']);
    $data_lines = explode("\n", $data);

    // strip all comments
    foreach ($data_lines AS $key => $value) {
        $value = trim($value);
        if (isset($value[0]) && isset($value[1]) && $value[0] == '-' && $value[1] == '-')
            unset($data_lines[$key]);
        elseif (stripos($value, 'insert into'))
            unset($data_lines[$key]);
    }


    $table = array();


    // store into cookie
    foreach ($_POST AS $key => $value) {
        $date = time() + 999999;
        if ($key == 'sql')
            $date = time() + 600;
        setcookie($key, $value, $date, '/');
    }

    $table['list_page'] = stripslashes($_POST['list_page']);
    $table['edit_page'] = stripslashes($_POST['edit_page']);
    $table['new_page'] = stripslashes($_POST['new_page']);
    $table['delete_page'] = stripslashes($_POST['delete_page']);
    $table['extension_name'] = stripslashes($_POST['extension']);

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
            if (isset($data_lines[$i]) && strpos(trim($data_lines[$i]), '`') === 0) { // this line has a column
                $col = Scaffold::find_text(trim($data_lines[$i]));
                $blob = ( stripos($data_lines[$i], 'TEXT') || stripos($data_lines[$i], 'BLOB') ) ? 1 : 0;
                $datetime = ( stripos($data_lines[$i], 'DATETIME') ) ? 1 : 0;
                eval("\$table['columns']['$col'] = array('blob' => $blob, 'datetime' => $datetime );");
                $type = Scaffold::findFieldType($data_lines[$i]);
                $size = Scaffold::findTypeMaxSize($data_lines[$i], $type);
                $required =  Scaffold::isFieldRequiered($data_lines[$i]);
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

// template engine
$twig_lib_path = '/usr/share/php/Twig';
$path_parts = pathinfo(__FILE__);
$root = $path_parts['dirname'];
require_once $twig_lib_path . '/Autoloader.php';

Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem($root . '/templates');
$twig = new Twig_Environment($loader, array('debug' => true));
$vars = array();
$vars['show_form'] = $show_form;
$vars['message'] = $message;
$vars['request_sql'] = '';
if(isset($_REQUEST['sql']))
{
	$vars['request_sql'] = $_REQUEST['sql'];
}
if(isset($_REQUEST['extension']))
{
	$vars['request_extension'] = $_REQUEST['extension'];
}
if(isset($_REQUEST['id_key']))
{
	$vars['request_id_key'] = $_REQUEST['id_key'];
}
if(isset($_REQUEST['list_page']))
{
	$vars['request_list_page'] = $_REQUEST['list_page'];
}
if(isset($_REQUEST['new_page']))
{
	$vars['request_new_page'] = $_REQUEST['new_page'];
}
if(isset($_REQUEST['edit_page']))
{
	$vars['request_edit_page'] = $_REQUEST['edit_page'];
}
if(isset($_REQUEST['delete_page']))
{
	$vars['request_delete_page'] = $_REQUEST['delete_page'];
}



if ($show_form) {

    if (stripos($_SERVER['HTTP_USER_AGENT'], 'msie') !== false) {
        $vars['copy'] = " &amp; Copy";
    } else {
        $vars['copy'] = '';
    }

    $s = new Scaffold($table);
    $vars['generateClassDbManager'] = $s->generateClassDbManager();
    $vars['generateClassGenericTable'] = $s->generateClassGenericTable();
    $vars['generateClassTable'] = $s->generateClassTable();
    $vars['generateListTableCtrl'] = $s->generateListTableCtrl();
    $vars['generateListTableDesign'] = $s->generateListTableDesign();
    $vars['generateNewrowCtrl'] = $s->generateNewrowCtrl();
    $vars['generateNewrowDesign'] = $s->generateNewrowDesign();
    $vars['generateEditrowCtrl'] = $s->generateEditrowCtrl();
    $vars['generateEditrowDesign'] = $s->generateEditrowDesign();
    $vars['generateDeleterowCtrl'] = $s->generateDeleterowCtrl();
    $vars['generateDeleterowDesign'] = $s->generateDeleterowDesign();
    $vars['generateModuleCtrl'] = $s->generateModuleCtrl();
    
    
}
echo $twig->render('index.html', $vars);
?>
