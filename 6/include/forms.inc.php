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

    Class Radio extends Element {
        private $options;

        public function __construct($label, $name, $value = "") {
            parent::__construct($label, $name, $value);
        }

        public function addOption($option) {
            $this->options[] = $option;
            return $this;
        }

        public function render() {
            echo "<div class=\"mb-3\">\n";
            echo "<p>$this->label</p>\n";
            foreach ($this->options as $option) {
                $checked = ($option == $this->value) ? "checked" : "";

                echo "<div class=\"form-check\">\n";
                echo "<input class=\"form-check-input\" type=\"radio\" name=\"$this->name\" id=\"{$this->name}_".md5($option)."\" value=\"$option\" $checked>\n";
                echo "<label class=\"form-check-label\" for=\"{$this->name}_".md5($option)."\">$option</label>\n";
                echo "</div>\n";
            }
            echo "</div>\n";
        }
    }

    Class Select extends Element {
        private $options;

        public function __construct($label, $name, $value = "") {
            parent::__construct($label, $name, $value);
        }

        public function addOption($option) {
            $this->options[] = $option;
            return $this;
        }

        public function render() {
            echo "<div class=\"mb-3\">\n";
            echo "<p>$this->label</p>\n";
            echo "<select class=\"form-control\" id=\"$this->name\" name=\"$this->name\">\n";
            foreach ($this->options as $option) {
                $selected = ($option == $this->value) ? "selected" : "";
                echo "<option value=\"$option\" $selected>$option</option>\n";
            }
            echo "</select>\n";
            echo "</div>\n";
        }
    }

    

class SelectCountry extends Select {
    public function __construct($label, $name, $value = "") {
        $this->label = $label;
        $this->name  = $name;
        $this->value = $value;
    }

    public function render() {
        $countries = [
            "Afghanistan", "Albania", "Algeria", "Andorra", "Angola",
            "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria",
            "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados",
            "Belarus", "Belgium", "Belize", "Benin", "Bhutan",
            "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei",
            "Bulgaria", "Burkina Faso", "Burundi", "Cabo Verde", "Cambodia",
            "Cameroon", "Canada", "Central African Republic", "Chad", "Chile",
            "China", "Colombia", "Comoros", "Congo (Congo-Brazzaville)", "Congo (DRC)",
            "Costa Rica", "Croatia", "Cuba", "Cyprus", "Czech Republic",
            "Denmark", "Djibouti", "Dominica", "Dominican Republic", "Ecuador",
            "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia",
            "Eswatini", "Ethiopia", "Fiji", "Finland", "France",
            "Gabon", "Gambia", "Georgia", "Germany", "Ghana",
            "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau",
            "Guyana", "Haiti", "Honduras", "Hungary", "Iceland",
            "India", "Indonesia", "Iran", "Iraq", "Ireland",
            "Israel", "Italy", "Jamaica", "Japan", "Jordan",
            "Kazakhstan", "Kenya", "Kiribati", "Kuwait", "Kyrgyzstan",
            "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia",
            "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Madagascar",
            "Malawi", "Malaysia", "Maldives", "Mali", "Malta",
            "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia",
            "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco",
            "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal",
            "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria",
            "North Korea", "North Macedonia", "Norway", "Oman", "Pakistan",
            "Palau", "Palestine", "Panama", "Papua New Guinea", "Paraguay",
            "Peru", "Philippines", "Poland", "Portugal", "Qatar",
            "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia",
            "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia",
            "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore",
            "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa",
            "South Korea", "South Sudan", "Spain", "Sri Lanka", "Sudan",
            "Suriname", "Sweden", "Switzerland", "Syria", "Taiwan",
            "Tajikistan", "Tanzania", "Thailand", "Timor-Leste", "Togo",
            "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan",
            "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom",
            "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City",
            "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe",
        ];

        $optsHtml = implode("\n", array_map(fn($c) =>
            "<option value=\"$c\"" . ($this->value === $c ? " selected" : "") . ">$c</option>",
            $countries
        ));

        $id  = htmlspecialchars($this->name);
        $val = htmlspecialchars($this->value);

        echo <<<HTML
        <div class="cs-wrap">
            <label for="{$id}">{$this->label}</label>
            <input
                type="text"
                id="{$id}-search"
                placeholder="Cerca paese..."
                autocomplete="off"
                class="cs-input"
                data-target="{$id}"
            />
            <select id="{$id}" name="{$id}" class="cs-select">
                {$optsHtml}
            </select>
        </div>
        HTML;
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