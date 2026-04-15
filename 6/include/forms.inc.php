<?php

    Abstract class namedElement {
        protected $name;

        public function __construct($name) {
            $this->name = $name;
        }
    }

    Abstract class Element extends namedElement {
        protected $label; 
        protected $value;

        public function __construct($label, $name, $value) {
            parent::__construct($name);
            $this->label = $label;
            $this->value = $value;
        }

        public function render() {
            echo "<div class=\"mb-3\">\n";
            echo "<p>{$this->label}: {$this->name} (generic)</p>\n";
            echo "</div>\n";
        }
    }

    Class TextElement extends Element {
        public function __construct($label, $name, $value = "") {
            parent::__construct($label, $name, $value);
        }

        public function render() {
            echo "<div class=\"mb-3\">\n";
            echo "<label for=\"$this->name\" class=\"form-label\">$this->label</label>\n";
            echo "<input type=\"text\" class=\"form-control\" id=\"$this->name\" name=\"$this->name\" value=\"$this->value\">\n";
            echo "</div>\n";
        }
    }
    Class PasswordElement extends Element {
        public function __construct($label, $name, $value = "") {
            parent::__construct($label, $name, $value);
        }

        public function render() {
            echo "<div class=\"mb-3\">\n";
            echo "<label for=\"$this->name\" class=\"form-label\">$this->label</label>\n";
            echo "<input type=\"password\" class=\"form-control\" id=\"$this->name\" name=\"$this->name\" value=\"$this->value\">\n";
            echo "</div>\n";
        }
    }

    Class TextAreaElement extends Element {
        public function __construct($label, $name, $value = "") {
            parent::__construct($label, $name, $value);
        }

        public function render() {
            echo "<div class=\"mb-3\">\n";
            echo "<label for=\"$this->name\" class=\"form-label\">$this->label</label>\n";
            echo "<textarea class=\"form-control\" id=\"$this->name\" name=\"$this->name\">$this->value</textarea>\n";
            echo "</div>\n";
        }
    }

    Class Form extends namedElement {

        private $method;
        private $elements = array();

        public function __construct($name, $method = "POST") {
            parent::__construct($name);
            $this->method = $method;
        }

        public function addElement($element) {
            $this->elements[] = $element;
            return $this;
        }



        public function render() {
            echo "<div class=\"container\">\n";
            echo "<form method=\"$this->method\" name=\"$this->name\">\n";

            foreach ($this->elements as $element) {
                $element->render();
            }

            echo "</form>\n";
            echo "</div>\n";

        }

    }


?>