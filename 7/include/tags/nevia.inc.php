<?php

    class nevia extends tagLibrary {

        public function dummy() {}

        public function getnews($name, $data, $pars) {

            global $conn;

            $news = new Template('skins/nevia/dtml/recentnews.html');

            $result = $conn->query("SELECT * FROM news ORDER BY publication_date DESC LIMIT {$pars['count']}");
            if($conn->error) {
                die("Query error: " . $conn->error);
            }   
            
            while($row = $result->fetch_assoc()) {
                //$news->setContent('id', $row['id']);
                $news->setContent('title', $row['title']);
                $news->setContent('body', substr($row['body'], 0, 100) . '...');
                //$news->setContent('publication_date', $row['publication_date']);
                //$news->setContent('username', $row['username']);
            }



            return $news->get();    
        }


        public function getslider($name, $data, $pars) {
            $slider = new Template('skins/nevia/dtml/slider.html');
            return $slider->get();    
        }   

    }

?>