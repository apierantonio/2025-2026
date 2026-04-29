<?php
    class tpl extends taglibrary {

        function dummy(){}

        function get($name, $data, $pars) {
            global $mysql;

            $tpl = new Template("skins/revision/dtml/{$pars['template']}.html");

            $result = $mysql->query("SELECT * FROM {$pars['template']} WHERE active <> '' ORDER BY position");
            while ($data = $result->fetch_assoc()) {
                foreach($data as $key => $value) {
                    $tpl->setContent($key, $value);
                }
            }
            return $tpl->get();

        }
        
    }

?>