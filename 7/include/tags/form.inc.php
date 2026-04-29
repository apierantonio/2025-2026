<?php

    Class form extends taglibrary {

        function dummy(){}

        function input($name, $data, $pars) {
            global $mysql, $main;

            $tpl = new Template("skins/admin/dtml/input_{$pars['type']}.html");
            foreach($pars as $key => $field) {
                $tpl->setContent($key, $field);
            }

            switch ($pars['type']) {
                
                case "select":
                    $result = $mysql->query("SELECT * FROM {$pars['table']}");
                    if (!$result) {
                        $main->setContent("message", "<i style='padding-top: 20px; padding-right: 20px; padding-bottom: 30px; float: left; font-size: 4rem; color: red;' class='bi bi-exclamation-triangle'></i> <div><strong>Error on {$pars['label']}</strong>: {$mysql->error}</div>");
                        return;
                    }
                    $fields = explode(',', $pars['text']);

                    while ($data = $result->fetch_assoc()) {
                        $tpl->setContent('value', $data[$pars['value']]);
                        $text = "";
                        foreach($fields as $field) {
                            $text .= $data[$field];
                        }
                        $tpl->setContent('text', $text);
                    }
                    break;

            }

            return $tpl->get();




        }

    }

?>