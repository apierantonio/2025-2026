<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/include/forms.inc.php';

$form = (new Form('signup'))
    ->action('')
    ->method('POST')
    ->submit('Crea account');

$form->add((new FieldSet())->legend('Dati personali')
    ->add((new Row())
        ->add((new Text('firstname'))
            ->label('Nome')
            ->placeholder('Mario')
            ->span(6)
            ->rule('required')
            ->rule('minLength', 2))
        ->add((new Text('lastname'))
            ->label('Cognome')
            ->placeholder('Rossi')
            ->span(6)
            ->rule('required')))
    ->add((new Row())
        ->add((new Email('email'))
            ->label('Email')
            ->placeholder('nome@esempio.it')
            ->span(8)
            ->autocomplete('email')
            ->rule('required')
            ->rule('email'))
        ->add((new DateEl('birthdate'))
            ->label('Data di nascita')
            ->span(4)
            ->rule('required')))
    ->add((new Radio('gender'))
        ->label('Genere')
        ->inline()
        ->option('m', 'Maschio')
        ->option('f', 'Femmina')
        ->option('x', 'Altro')
        ->rule('required'))
);

$form->add((new FieldSet())->legend('Credenziali')
    ->add((new Row())
        ->add((new Password('password'))
            ->label('Password')
            ->span(6)
            ->help('Almeno 8 caratteri.')
            ->rule('required')
            ->rule('minLength', 8))
        ->add((new Password('password2'))
            ->label('Conferma password')
            ->span(6)
            ->rule('required')
            ->rule('same', 'password')))
);

$form->add((new FieldSet())->legend('Profilo')
    ->add((new Row())
        ->add((new SelectCountry('country'))
            ->label('Paese')
            ->placeholder('— seleziona —')
            ->span(6)
            ->rule('required'))
        ->add((new Select('role'))
            ->label('Ruolo')
            ->span(6)
            ->placeholder('— seleziona —')
            ->options(['student' => 'Studente', 'dev' => 'Sviluppatore', 'other' => 'Altro'])
            ->rule('required')))
    ->add((new TextArea('bio'))
        ->label('Bio')
        ->rows(4)
        ->placeholder('Racconta qualcosa di te…')
        ->rule('maxLength', 500)
        ->help('Max 500 caratteri.'))
    ->add((new CheckboxGroup('interests'))
        ->label('Interessi')
        ->inline()
        ->option('web', 'Web')
        ->option('ai', 'AI')
        ->option('mobile', 'Mobile')
        ->option('games', 'Gaming'))
    ->add((new Checkbox('terms'))
        ->label('Accetto i termini di servizio')
        ->rule('required'))
);

// Processa la richiesta (popola i valori da $_POST e valida).
$ok = $form->handle();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Fluent Form API — demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 860px;">
    <h1 class="mb-4">Registrazione</h1>

    <?php if ($form->isSubmitted() && $form->isValid()): ?>
        <div class="alert alert-success">
            <strong>Form valida!</strong> Dati ricevuti:
            <pre class="mb-0"><?= Html::e(print_r($form->data(), true)) ?></pre>
        </div>
    <?php elseif ($form->isSubmitted()): ?>
        <div class="alert alert-danger">
            Correggi i campi evidenziati.
        </div>
    <?php endif; ?>

    <?php $form->render(); ?>
</div>
</body>
</html>
