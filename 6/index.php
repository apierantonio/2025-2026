<html>
<head>
    <title>PHP Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    require "include/forms.inc.php";
    
    (new Form("myForm"))
        ->addElement(new TextElement("Email address", "email"))
        ->addElement(new TextElement("Name", "name"))
        ->addElement(new PasswordElement("Password", "password"))
        ->addElement(new TextAreaElement("Note", "note"))
        ->addElement(
            (new Radio("Gender", "gender"))
            ->addOption("Maschio")
            ->addOption("Femmina")
            )
        ->addElement((new SelectCountry("Country", "country"))
    )->render();


?>
</div>
</html>