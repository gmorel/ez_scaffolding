<?php

class Scaffold {

    CONST REGEX_CREATE_TABLE = '/CREATE TABLE (IF NOT EXISTS )?`(.)+` \(/';
    public $table = array();
    public $download = false;
    public $twig = null;

    public function Scaffold($table) {
        $this->table = $table;
        // template engine
        $twig_lib_path = '/usr/share/php/Twig';
        $path_parts = pathinfo(__FILE__);
        $root = $path_parts['dirname'];
        require_once $twig_lib_path . '/Autoloader.php';

        Twig_Autoloader::register();
        $loader = new Twig_Loader_Filesystem($root . '/templates');
        $this->twig = new Twig_Environment($loader, array('debug' => true));
    }

    /**
     * Generate DBManager class 
     * @return string PHP
     */
    public function generateClassDbManager() {
        return $this->twig->render('classes/DbManager.html');
    }

    /**
     * Generate table class
     * @return string PHP
     */
    public function generateClassGenericTable() {
        return $this->twig->render('classes/GenericTable.html');
    }
    
    /**
     * Generate Paginator class 
     * @return string PHP
     */
    public function generateClassPaginator() {
        $vars = array();
        $vars['table_name'] = $this->table['name'];
        $vars['extension_name'] = $this->table['extension_name'];
        return $this->twig->render('classes/Paginator.html', $vars);
    }
    
    /**
     * Generate Validator class 
     * @return string PHP
     */
    public function generateClassFormValidator() {
        $vars = array();
        $vars['table_name'] = $this->table['name'];
        $vars['extension_name'] = $this->table['extension_name'];
        return $this->twig->render('classes/FormValidator.html', $vars);
    }

    /**
     * Generate table class
     * @return string PHP
     */
    public function generateClassTable() {
        // Variables
        $class_name = $this->table['class_name'];
        $table_name = $this->table['name'];
        $primary_key = $this->table['id_key'];
        $column_array = array();
        foreach ($this->table['columns'] AS $key => $value) {
            if (is_array($value) && $key != $primary_key) {
                $column_array[] = $key;
            }
        }
        $column_array_as_var = array();
        foreach ($column_array as $value) { {
                $column_array_as_var[] = '$' . $value;
            }
        }
        $vars = array();
        
        $vars['table_name'] = $table_name;
        $vars['class_name'] = $class_name;
        $vars['primary_key'] = $primary_key;
        $vars['extension_name'] = $this->table['extension_name'];

        $vars['columns'] = implode('\', \'', $column_array);
        $vars['selectMethod'] = $this->_generateClassTableSelectMethod($column_array);
        $vars['insertMethod'] = $this->_generateClassTableInsertMethod($column_array, $column_array_as_var);
        $vars['updateMethod'] = $this->_generateClassTableUpdateMethod($primary_key, $column_array, $column_array_as_var);

        return $this->twig->render('classes/CurrentTable.html', $vars);
    }

    /**
     * Generate select method for the Table class
     * @param array $column_array column array without primary key
     * @return string PHP
     */
    protected function _generateClassTableSelectMethod($column_array) {
        $table_name = $this->table['name'];
        $return_string = '';

        $return_string .= "/**\n";
        $return_string .= "     * Select * from $table_name\n";
        $return_string .= "     * @param int \$offset offset for pagination\n";
        $return_string .= "     * @param int \$limit limit\n";
        $return_string .= "     * @return array\n";
        $return_string .= "     */\n";
        $return_string .= "    public function getAll(\$offset=null, \$limit=null) {\n";
        $return_string .= "        \$q = \$this->_getEzcDbInstance()->createSelectQuery();\n";
        $return_string .= "        \$q->select('" . implode(', ', $column_array) . "')\n";
        $return_string .= "            ->from(self::TABLE_NAME)\n";
        $return_string .= "            ->orderBy('date', ezcQuerySelect::DESC);\n";
        $return_string .= "        if(is_numeric(\$offset) && is_numeric(\$limit))\n";
        $return_string .= "        {\n";
        $return_string .= "            \$q->limit(\$limit, \$offset);\n";
        $return_string .= "        }\n";
        $return_string .= "        return \$this->_getDbQueryResults(\$q);\n";
        $return_string .= "    }";

        return $return_string;
    }

    /**
     * Generate update method for the Table class
     * @param array $column_array column array without primary key
     * @param array $column_array_as_var column array without primary key with $prefix
     * @return string PHP
     */
    protected function _generateClassTableInsertMethod($column_array, $column_array_as_var) {
        $table_name = $this->table['name'];
        $return_string = '';

        $return_string .= "/**\n";
        $return_string .= "     * Insert a row in the $table_name table\n";
        foreach ($column_array_as_var as $value) {
            $return_string .= "     * @param string $value\n";
        }
        $return_string .= "     *\n";
        $return_string .= "     */\n";
        $return_string .= "    public function insert(" . implode(', ', $column_array_as_var) . ") {\n";
        $return_string .= "        \$q = \$this->_getEzcDbInstance()->createInsertQuery();\n";
        $return_string .= "        \$q->insertInto(self::TABLE_NAME);\n";
        foreach ($column_array as $value) {
            $return_string .= "        \$q->set('$value', \$q->bindValue($$value));\n";
        }
        $return_string .= "        \$stmt = \$q->prepare();\n";
        $return_string .= "        \$stmt->execute();\n";
        $return_string .= "    }";

        return ($return_string);
    }

    /**
     * Generate update method for the Table class
     * @param int $primary_key primary key
     * @param array $column_array column array without primary key
     * @param array $column_array_as_var column array without primary key with $prefix
     * @return string PHP
     */
    protected function _generateClassTableUpdateMethod($primary_key, $column_array, $column_array_as_var) {
        $table_name = $this->table['name'];
        $return_string = '';

        $return_string .= "/**\n";
        $return_string .= "     * Update a row in the $table_name table\n";
        $return_string .= "     * @param int $$primary_key primary key\n";
        foreach ($column_array_as_var as $value) {
            $return_string .= "     * @param string $value\n";
        }
        $return_string .= "     */\n";
        $return_string .= "    public function update($$primary_key, " . implode(', ', $column_array_as_var) . ") {\n";
        $return_string .= "        \$q = \$this->_getEzcDbInstance()->createUpdateQuery();\n";
        $return_string .= "        \$q->update(self::TABLE_NAME);\n";
        foreach ($column_array as $value) {
            $return_string .= "        \$q->set('$value', \$q->bindValue($$value));\n";
        }
        $return_string .= "        \$q->where(\$q->expr->eq('$primary_key', \$q->bindValue(\$$primary_key)));\n";
        $return_string .= "\n";
        $return_string .= "        \$stmt = \$q->prepare();\n";
        $return_string .= "        \$stmt->execute();\n";
        $return_string .= "    }";

        return $return_string;
    }

    /**
     * Generate Ez Controller to list rows from the table
     * @return string PHP
     */
    public function generateListTableCtrl() {
        $vars = array();
        $vars['table_name'] = $this->table['name'];
        $vars['class_name'] = $this->table['class_name'];
        $vars['real_class_name'] = $this->table['real_class_name'];
        $vars['extension_name'] = $this->table['extension_name'];

        return $this->twig->render('ctrl/list.html', $vars);
    }

    /**
     * Generate Ez Template to list rows from the table
     * @return string Ez Template
     */
    public function generateListTableDesign() {

        $vars = array();
        $vars['table_name'] = $this->table['name'];
        $vars['class_name'] = $this->table['class_name'];
        $vars['extension_name'] = $this->table['extension_name'];
        $vars['edit_page'] = $this->table['edit_page'];
        $vars['id_key'] = $this->table['id_key'];
        $vars['delete_page'] = $this->table['delete_page'];
        $vars['new_page'] = $this->table['new_page'];

        foreach ($this->table['columns'] AS $key => $value) {
            if (is_array($value)) {
                $headers[] = $this->title($key);
            }
        }
        $vars['headers'] = $headers;

        return $this->twig->render('design/list.html', $vars);
    }

    /**
     * Generate Ez Controller to add a new row in the table
     * @return string PHP
     */
    public function generateNewrowCtrl() {
        $primary_key = $this->table['id_key'];

        $vars = array();
        $vars['table_name'] = $this->table['name'];
        $vars['class_name'] = $this->table['class_name'];
        $vars['real_class_name'] = $this->table['real_class_name'];
        $vars['extension_name'] = $this->table['extension_name'];
        $vars['list_page'] = $this->table['list_page'];
        $vars['table'] = $this->table;
        $column_array = array();
        foreach ($this->table['columns'] AS $key => $value) {
            if (is_array($value) && $key != $primary_key) {
                $column_array[] = $key;
            }
        }
        $vars['column_array'] = $column_array;
        $column_array_as_var = array();
        foreach ($column_array as $value) { {
                $column_array_as_var[] = '$' . $value;
            }
        }
        $vars['function_args'] = implode(', ', $column_array_as_var);

        return $this->twig->render('ctrl/new.html', $vars);


//        
//        foreach ($this->table AS $key => $value) {
//            if (is_array($value)) {
//                $column = $key;
//                if ($column != $this->table['id_key']) {
//                    $column_array[] = $key;
//                    if ($value['blob'] == 1) {
//                        $text .= $this->html_chars("<p><b>" . $this->title($column) . ":</b><br /><textarea name='$column'></textarea> \n");
//                    } else {
//                        $text .= "<p><b>" . $this->title($column) . ":</b><br /><input type='text' name='$column'/> \n";
//                    }
//                }
//            }
//        }
    }

    /**
     * Generate Ez Template to add a new row in the table
     * @return string Ez Template
     */
    public function generateNewrowDesign() {
        $vars = array();
        $vars['table_name'] = $this->table['name'];
        $vars['class_name'] = $this->table['class_name'];
        $vars['extension_name'] = $this->table['extension_name'];
        $vars['list_page'] = $this->table['list_page'];
        $vars['new_page'] = $this->table['new_page'];
        $vars['inputs'] = $this->_generateForm();

        return $this->twig->render('design/new.html', $vars);
    }

    /**
     * Generate a form (insert/edit)
     * @param array $filledInColumnValues
     * @return string eZ Template
     */
    protected function _generateForm($filledInColumnValues = null) {
        $column_array = array();
        $inputs = '';

        foreach ($this->table['columns'] AS $key => $value) {
            if (is_array($value)) {
                $column = $key;
                if ($column != $this->table['id_key']) {
                    //If edit value is present
                    if (isset($filledInColumnValues) && isset($filledInColumnValues[$column])) {
                        $editValue = $filledInColumnValues[$column];
                    } else {
                        $editValue = '';
                    }

                    $column_array[] = $key;
                    if ($value['blob'] == 1) {
                        $input = "    <label for=\"$column\">" . $this->title($column) . ":</label>\n";
                        $input .= "    <textarea id=\"$column\" name=\"$column\">{\$$column}</textarea>\n";
                        $inputs .= $this->html_chars($input) . "\n";
                    } else {
                        $input = "    <label for=\"$column\">" . $this->title($column) . ":</label>\n";
                        $input .= "    <input type=\"text\" id=\"$column\" name=\"$column\" value=\"{\$$column}\"/>\n";
                        $inputs .= $input . "\n";
                    }
                }
            }
        }
        return $inputs;
    }

    /**
     * Generate Ez Controller to edit a row from the table
     * @return string PHP
     */
    public function generateEditrowCtrl() {
        $primary_key = $this->table['id_key'];

        $vars = array();
        $vars['table_name'] = $this->table['name'];
        $vars['class_name'] = $this->table['class_name'];
        $vars['extension_name'] = $this->table['extension_name'];
        $vars['real_class_name'] = $this->table['real_class_name'];
        $vars['list_page'] = $this->table['list_page'];
        $vars['primary_key'] = $primary_key;
        $vars['table'] = $this->table;
        $column_array = array();
        foreach ($this->table['columns'] AS $key => $value) {
            if (is_array($value)) {
                $column_array[] = $key;
            }
        }
        $vars['column_array'] = $column_array;
        $column_array_as_var = array();
        foreach ($column_array as $value) { {
                if ($value != $primary_key) {
                    $column_array_as_var[] = '$' . $value;
                }
            }
        }
        $vars['function_args'] = implode(', ', $column_array_as_var);

        return $this->twig->render('ctrl/edit.html', $vars);

        $return_string = '';
        $return_string .= "<?php \n";
        if ($this->table['include'] != '')
            $return_string .= "include('{$this->table['include']}'); \n";

        $column_array = array();
        $text = '';

        $return_string .= "if (isset(\$_GET['{$this->table['id_key']}']) ) { \n";

        $return_string .= "\${$this->table['id_key']} = (int) \$_GET['{$this->table['id_key']}']; \n";


        foreach ($this->table['columns'] AS $key => $value) {
            if (is_array($value)) {
                $column = $key;
                if ($column != $this->table['id_key']) {
                    $column_array[] = $column;
                    if ($value['blob'] == 1) {
                        $text .= $this->html_chars("<p><b>" . $this->title($column) . ":</b><br /><textarea name='$column'><?php= stripslashes(\$row['$column']) ?></textarea> \n");
                    } else {
                        $text .= "<p><b>" . $this->title($column) . ":</b><br /><input type='text' name='$column' value='<?php= stripslashes(\$row['$column']) ?>' /> \n";
                    }
                }
            }
        }



        $return_string .= "if (isset(\$_POST['submitted'])) { \n";
        $return_string .= "foreach(\$_POST AS \$key => \$value) { \$_POST[\$key] = mysql_real_escape_string(\$value); } \n";
        $insert = "UPDATE `{$this->table['name']}` SET ";
        $counter = 0;
        foreach ($column_array as $value) {
            $insert .= " `$value` =  '{\$_POST['$value']}' ";
            if ($counter < count($column_array) - 1)
                $insert .= ", ";

            $counter++;
        }
        $insert .= "  WHERE `{$this->table['id_key']}` = '\${$this->table['id_key']}' ";


        $return_string .= "\$sql = \"$insert\"; \n";
        $return_string .= "mysql_query(\$sql) or die(mysql_error()); \n";
        $return_string .= "echo (mysql_affected_rows()) ? \"Edited row.<br />\" : \"Nothing changed. <br />\"; \n";
        $return_string .= "echo \"<a href='{$this->table['list_page']}'>Back To Listing</a>\"; \n";

        // get the new updated row
        $return_string .= "} \n";
        $return_string .= "\$row = mysql_fetch_array ( mysql_query(\"SELECT * FROM `{$this->table['name']}` WHERE `{$this->table['id_key']}` = '\${$this->table['id_key']}' \")); \n";


        $return_string .= "?>\n\n";
        $return_string .= "<form action='' method='POST'> \n";
        $return_string .= $text;
        $return_string .= "<p><input type='submit' value='Edit Row' /><input type='hidden' value='1' name='submitted' /> \n";
        $return_string .= "</form> \n";

        $return_string .= "<?php } ?> \n";

        return $return_string;
    }

    /**
     * Generate Ez Template to edit a row from the table
     * @return string Ez Template
     */
    public function generateEditrowDesign() {
        $vars = array();
        $vars['table_name'] = $this->table['name'];
        $vars['class_name'] = $this->table['class_name'];
        $vars['extension_name'] = $this->table['extension_name'];
        $vars['list_page'] = $this->table['list_page'];
        $vars['edit_page'] = $this->table['edit_page'];
        $vars['new_page'] = $this->table['new_page'];
        $vars['inputs'] = $this->_generateForm();
        $vars['id_key'] = $this->table['id_key'];

        return $this->twig->render('design/edit.html', $vars);
    }

    /**
     * Generate Ez Controller to delete a row from the table
     * @return string PHP
     */
    public function generateDeleterowCtrl() {
        $primary_key = $this->table['id_key'];

        $vars = array();
        $vars['table_name'] = $this->table['name'];
        $vars['class_name'] = $this->table['class_name'];
        $vars['extension_name'] = $this->table['extension_name'];
        $vars['real_class_name'] = $this->table['real_class_name'];
        $vars['list_page'] = $this->table['list_page'];
        $vars['primary_key'] = $primary_key;
        return $this->twig->render('ctrl/delete.html', $vars);

        $return_string = '';
        $return_string .= "<?php \n";
        if ($this->table['include'] != '')
            $return_string .= "include('{$this->table['include']}'); \n";

        $return_string .= "\${$this->table['id_key']} = (int) \$_GET['{$this->table['id_key']}']; \n";
        $return_string .= "mysql_query(\"DELETE FROM `{$this->table['name']}` WHERE `{$this->table['id_key']}` = '\${$this->table['id_key']}' \") ; \n";
        $return_string .= "echo (mysql_affected_rows()) ? \"Row deleted.<br /> \" : \"Nothing deleted.<br /> \"; \n";
        $return_string .= "?> \n\n";
        $return_string .= "<a href='{$this->table['list_page']}'>Back To Listing</a>";

        return $return_string;
    }

    /**
     * Generate Ez Template to delete a row from the table
     * @return string Ez Template
     */
    public function generateDeleterowDesign() {
        $vars = array();
        $vars['table_name'] = $this->table['name'];
        $vars['class_name'] = $this->table['class_name'];
        $vars['extension_name'] = $this->table['extension_name'];
        $vars['list_page'] = $this->table['list_page'];

        return $this->twig->render('design/delete.html', $vars);
    }

    /**
     * Generate Ez Template to for the module.php of the module the table
     * @return string PHP
     */
    public function generateModuleCtrl() {
        $vars = array();
        $vars['table_name'] = $this->table['name'];
        $vars['class_name'] = $this->table['class_name'];
        $vars['extension_name'] = $this->table['extension_name'];
        $vars['list_page'] = $this->table['list_page'];
        $vars['edit_page'] = $this->table['edit_page'];
        $vars['new_page'] = $this->table['new_page'];
        $vars['delete_page'] = $this->table['delete_page'];

        return $this->twig->render('ctrl/module.html', $vars);
    }
    
    /**
     * Generate Ez design.ini
     * @return string PHP
     */
    public function generateSettingsDesignIni() {
        $vars = array();
        $vars['extension_name'] = $this->table['extension_name'];

        return $this->twig->render('settings/design.ini.append.html', $vars);
    }
    
    /**
     * Generate Ez module.ini
     * @return string PHP
     */
    public function generateSettingsModuleIni() {
        $vars = array();
        $vars['table_name'] = $this->table['name'];
        $vars['extension_name'] = $this->table['extension_name'];

        return $this->twig->render('settings/module.ini.append.html', $vars);
    }
    
    /**
     * Generate Ez site.ini
     * @return string PHP
     */
    public function generateSettingsSiteIni() {
        $vars = array();
        $vars['table_name'] = $this->table['name'];
        $vars['extension_name'] = $this->table['extension_name'];
        $vars['list_page'] = $this->table['list_page'];
        $vars['edit_page'] = $this->table['edit_page'];
        $vars['new_page'] = $this->table['new_page'];
        $vars['delete_page'] = $this->table['delete_page'];

        return $this->twig->render('settings/site.ini.append.html', $vars);
    }
    
    /**
     * Generate readme.txt
     * @return string txt
     */
    public function generateReadMe() {
        $vars = array();
        $vars['table_name'] = $this->table['name'];
        $vars['extension_name'] = $this->table['extension_name'];
        $vars['list_page'] = $this->table['list_page'];
        $vars['edit_page'] = $this->table['edit_page'];
        $vars['new_page'] = $this->table['new_page'];
        $vars['delete_page'] = $this->table['delete_page'];

        return $this->twig->render('readme.html', $vars);
    }

    public function title($name) {
        return ucwords(str_replace("_", " ", trim($name)));
    }

    public function html_chars($var) {
        return ($this->download) ? $var : htmlspecialchars($var);
    }

    /**
     * Translates a camel case string into a string with underscores (e.g. firstName -&gt; first_name)
     * @param    string   $str    String in camel case format
     * @return    string            $str Translated into underscore format
     */
    public static function fromCamelCase($str) {
        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return "_" . strtolower($c[1]);');
        return preg_replace_callback('/([A-Z])/', $func, $str);
    }

    /**
     * Translates a string with underscores into camel case (e.g. first_name -&gt; firstName)
     * @param    string   $str                     String in underscore format
     * @param    bool     $capitalise_first_char   If true, capitalise the first char in $str
     * @return   string                              $str translated into camel caps
     */
    public static function toCamelCase($str, $capitalise_first_char = false) {
        if ($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        }
        $func = create_function('$c', 'return strtoupper($c[1]);');
        return preg_replace_callback('/_([a-z])/', $func, $str);
    }

    public static function find_text($text, $delimit_start = '`', $delimit_end = '`') {
        $start = strpos($text, $delimit_start);
        if ($start === false)
            return false;

        $end = strpos(substr($text, $start + 1), $delimit_end);
        if ($end === false)
            return false;

        return substr($text, $start + 1, $end);
    }
    
    /**
     * Extract PRIMARY_KEY from a mySQL statement
     * @param string $text
     * @return string primary key name or false 
     */
    public static function get_mysql_primary_key($text) {
       if (preg_match('/PRIMARY KEY\s*\(`(.*)`\)/', $text, $matches)) {
           if(isset($matches) && isset($matches[1]))
           {
               return $matches[1];
           }
       }
       return false;
    }
    
    /**
     * Return the type of the SQL line
     * @param string $line SQL line
     * @return string type or false if not found 
     */
    public static function findFieldType($line)
    {
        $line = strtolower($line);
        $check_types = array('varchar', 'int');
        foreach ($check_types as $check_type) {
            if(strpos($line, $check_type))
            {
                return $check_type;
            }
        }
        return false;
    }
    
    /**
     * Check for a particular type size
     * @param string $line SQL line
     * @param string $type varchar/int
     * @return int size or false if not found 
     */
    public static function findTypeMaxSize($line, $type = 'varchar')
    {
        $line = strtolower($line);
        if(strpos($line, $type) && preg_match_all('/' . $type . '\(\d+/',$line,$matches))
        {
            if($matches)
            {
                $size = str_replace($type . '(', '', $matches[0][0]); 
                return $size;
            }
        }
        return false;
    }
    
    /**
     * Check if a field is requiered or not
     * if NOT NULL is present
     * @param string $line SQL line
     * @return boolean true if requiered 
     */
    public static function isFieldRequiered($line)
    {
        $line = strtolower($line);
        if(strpos($line, 'not null'))
        {
            return true;
        }
        return false;   
    }

}

?>