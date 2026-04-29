<?php
/**
 * Fluent Form API — Bootstrap 5
 * Validazione server-side + client-side (HTML5 + Bootstrap was-validated + JS custom rules).
 *
 * Uso:
 *   $form = (new Form('signup'))
 *       ->action('')
 *       ->add((new Text('email'))->label('Email')->rule('required')->rule('email'))
 *       ->add((new Password('password'))->label('Password')->rule('required')->rule('minLength', 8))
 *       ->add((new Password('password2'))->label('Conferma')->rule('same', 'password'));
 *   $form->handle();
 *   $form->render();
 */

/* ----------------------------------------------------------------- *
 *  Helper HTML: escape + serializzazione attributi.
 * ----------------------------------------------------------------- */
final class Html {
    public static function e($s): string {
        return htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** @param array<string,mixed> $attrs */
    public static function attrs(array $attrs): string {
        $out = [];
        foreach ($attrs as $k => $v) {
            if ($v === null || $v === false) continue;
            if ($v === true) { $out[] = self::e($k); continue; }
            $out[] = self::e($k) . '="' . self::e($v) . '"';
        }
        return $out ? ' ' . implode(' ', $out) : '';
    }
}

/* ----------------------------------------------------------------- *
 *  Registry delle regole di validazione.
 *  Ogni regola è una closure (mixed $value, array $args, array $data): ?string
 *  Ritorna null se valida, altrimenti messaggio d'errore.
 *  Restituisce anche (via seconda closure) la configurazione per il lato client.
 * ----------------------------------------------------------------- */
final class Rules {
    /** @var array<string, callable> */
    private static array $server = [];
    /** @var array<string, callable> */
    private static array $clientAttrs = [];

    public static function init(): void {
        if (self::$server) return;

        self::register(
            'required',
            fn($v) => (is_string($v) && trim($v) === '') || $v === null || $v === []
                ? 'Campo obbligatorio.' : null,
            fn() => ['required' => true]
        );

        self::register(
            'email',
            fn($v) => ($v === '' || $v === null || filter_var($v, FILTER_VALIDATE_EMAIL))
                ? null : 'Email non valida.',
            fn() => ['type' => 'email']
        );

        self::register(
            'url',
            fn($v) => ($v === '' || $v === null || filter_var($v, FILTER_VALIDATE_URL))
                ? null : 'URL non valido.',
            fn() => ['type' => 'url']
        );

        self::register(
            'numeric',
            fn($v) => ($v === '' || $v === null || is_numeric($v))
                ? null : 'Deve essere un numero.',
            fn() => ['inputmode' => 'numeric', 'pattern' => '[0-9]+([\.,][0-9]+)?']
        );

        self::register(
            'minLength',
            function ($v, $args) {
                $n = (int)($args[0] ?? 0);
                return (is_string($v) && $v !== '' && mb_strlen($v) < $n)
                    ? "Minimo {$n} caratteri." : null;
            },
            fn($args) => ['minlength' => (int)($args[0] ?? 0)]
        );

        self::register(
            'maxLength',
            function ($v, $args) {
                $n = (int)($args[0] ?? 0);
                return (is_string($v) && mb_strlen($v) > $n)
                    ? "Massimo {$n} caratteri." : null;
            },
            fn($args) => ['maxlength' => (int)($args[0] ?? 0)]
        );

        self::register(
            'min',
            function ($v, $args) {
                $n = $args[0] ?? 0;
                return ($v !== '' && $v !== null && is_numeric($v) && $v < $n)
                    ? "Valore minimo {$n}." : null;
            },
            fn($args) => ['min' => $args[0] ?? 0]
        );

        self::register(
            'max',
            function ($v, $args) {
                $n = $args[0] ?? 0;
                return ($v !== '' && $v !== null && is_numeric($v) && $v > $n)
                    ? "Valore massimo {$n}." : null;
            },
            fn($args) => ['max' => $args[0] ?? 0]
        );

        self::register(
            'pattern',
            function ($v, $args) {
                $re = $args[0] ?? '';
                if ($v === '' || $v === null || $re === '') return null;
                // Il pattern HTML ancora implicitamente l'intera stringa.
                return preg_match('/^(?:' . $re . ')$/u', (string)$v) ? null : 'Formato non valido.';
            },
            fn($args) => ['pattern' => $args[0] ?? '']
        );

        self::register(
            'same',
            function ($v, $args, $data) {
                $other = $args[0] ?? '';
                return ((string)($data[$other] ?? '')) === (string)$v
                    ? null : 'I valori non corrispondono.';
            },
            fn($args) => ['data-rule-same' => $args[0] ?? '']
        );

        self::register(
            'in',
            function ($v, $args) {
                if ($v === '' || $v === null) return null;
                return in_array((string)$v, array_map('strval', $args), true)
                    ? null : 'Valore non ammesso.';
            },
            fn($args) => []
        );
    }

    public static function register(string $name, callable $validator, ?callable $clientAttrs = null): void {
        self::$server[$name] = $validator;
        self::$clientAttrs[$name] = $clientAttrs ?? fn() => [];
    }

    public static function validate(string $name, $value, array $args, array $data): ?string {
        self::init();
        if (!isset(self::$server[$name])) return null;
        return (self::$server[$name])($value, $args, $data);
    }

    public static function clientAttrs(string $name, array $args): array {
        self::init();
        if (!isset(self::$clientAttrs[$name])) return [];
        return (self::$clientAttrs[$name])($args);
    }
}

/* ----------------------------------------------------------------- *
 *  Element — base di ogni campo. Tutti i setter sono fluenti.
 * ----------------------------------------------------------------- */
abstract class Element {
    protected string $name;
    protected ?string $label = null;
    protected $value = null;
    protected ?string $help = null;
    protected ?string $placeholder = null;
    /** @var array<string,mixed> */
    protected array $attrs = [];
    /** @var string[] */
    protected array $classes = [];
    /** @var array<int, array{0:string, 1:array}> */
    protected array $rules = [];
    /** @var string[] */
    protected array $errors = [];
    /** @var int 0..12 per griglia; 0 = auto (full) */
    protected int $span = 0;
    protected ?Form $form = null;

    public function __construct(string $name) {
        $this->name = $name;
    }

    /* ---- setters fluenti ---- */
    public function name(string $n): static          { $this->name = $n;            return $this; }
    public function label(?string $l): static         { $this->label = $l;           return $this; }
    public function value($v): static                 { $this->value = $v;           return $this; }
    public function help(?string $h): static          { $this->help = $h;            return $this; }
    public function placeholder(?string $p): static   { $this->placeholder = $p;     return $this; }
    public function attr(string $k, $v = true): static{ $this->attrs[$k] = $v;       return $this; }
    public function addClass(string ...$c): static    { foreach ($c as $x) $this->classes[] = $x; return $this; }
    public function span(int $cols): static           { $this->span = max(0, min(12, $cols)); return $this; }
    public function required(bool $b = true): static  { return $b ? $this->rule('required') : $this; }
    public function disabled(bool $b = true): static  { return $this->attr('disabled', $b); }
    public function readonly(bool $b = true): static  { return $this->attr('readonly', $b); }
    public function autocomplete(string $v): static   { return $this->attr('autocomplete', $v); }

    public function rule(string $name, ...$args): static {
        $this->rules[] = [$name, $args];
        return $this;
    }

    /* ---- accessor ---- */
    public function getName(): string { return $this->name; }
    public function getValue()        { return $this->value; }
    public function getErrors(): array{ return $this->errors; }
    public function hasErrors(): bool { return !empty($this->errors); }
    public function setForm(Form $f): void { $this->form = $f; }

    /* ---- binding & validazione ---- */
    public function populate(array $data): void {
        if (array_key_exists($this->name, $data)) {
            $this->value = $data[$this->name];
        }
    }

    public function validate(array $data): bool {
        $this->errors = [];
        foreach ($this->rules as [$ruleName, $args]) {
            $err = Rules::validate($ruleName, $this->value, $args, $data);
            if ($err !== null) $this->errors[] = $err;
        }
        return !$this->hasErrors();
    }

    /* ---- helper rendering ---- */
    protected function controlAttrs(array $extra = []): array {
        $attrs = $this->attrs;

        // attributi da regole lato client
        foreach ($this->rules as [$ruleName, $args]) {
            foreach (Rules::clientAttrs($ruleName, $args) as $k => $v) {
                if (!isset($attrs[$k])) $attrs[$k] = $v;
            }
        }

        $classes = array_merge(['form-control'], $this->classes);
        if ($this->hasErrors()) $classes[] = 'is-invalid';

        $merged = array_merge([
            'id'          => $this->name,
            'name'        => $this->name,
            'class'       => implode(' ', $classes),
            'placeholder' => $this->placeholder,
        ], $extra, $attrs);

        if (isset($extra['class'], $attrs['class'])) {
            $merged['class'] = trim($extra['class'] . ' ' . $attrs['class']);
        }
        return $merged;
    }

    protected function renderLabel(): string {
        if ($this->label === null) return '';
        $req = '';
        foreach ($this->rules as [$r, $_]) if ($r === 'required') { $req = ' <span class="text-danger">*</span>'; break; }
        return '<label for="' . Html::e($this->name) . '" class="form-label">' . Html::e($this->label) . $req . "</label>\n";
    }

    protected function renderFeedback(): string {
        $out = '';
        if ($this->help) {
            $out .= '<div class="form-text">' . Html::e($this->help) . "</div>\n";
        }
        if ($this->hasErrors()) {
            foreach ($this->errors as $err) {
                $out .= '<div class="invalid-feedback d-block">' . Html::e($err) . "</div>\n";
            }
        } else {
            // contenitore per messaggi del client-side validation
            $out .= '<div class="invalid-feedback">Campo non valido.</div>' . "\n";
        }
        return $out;
    }

    public function render(): string {
        $col = $this->span > 0 ? ' col-md-' . $this->span : ' col-12';
        $out  = '<div class="mb-3' . $col . '">' . "\n";
        $out .= $this->renderLabel();
        $out .= $this->renderControl() . "\n";
        $out .= $this->renderFeedback();
        $out .= "</div>\n";
        return $out;
    }

    abstract public function renderControl(): string;
}

/* ----------------------------------------------------------------- *
 *  Input semplici basati su <input type=…>
 * ----------------------------------------------------------------- */
class Input extends Element {
    protected string $type = 'text';

    public function type(string $t): static { $this->type = $t; return $this; }

    public function renderControl(): string {
        $attrs = $this->controlAttrs([
            'type'  => $this->type,
            'value' => is_scalar($this->value) ? (string)$this->value : '',
        ]);
        return '<input' . Html::attrs($attrs) . '>';
    }
}

class Text     extends Input { protected string $type = 'text'; }
class Email    extends Input { protected string $type = 'email'; }
class Password extends Input { protected string $type = 'password'; }
class Number   extends Input { protected string $type = 'number'; }
class DateEl   extends Input { protected string $type = 'date'; }
class Tel      extends Input { protected string $type = 'tel'; }
class Url      extends Input { protected string $type = 'url'; }
class Color    extends Input { protected string $type = 'color'; }
class Range    extends Input { protected string $type = 'range'; }

class Hidden extends Input {
    protected string $type = 'hidden';
    public function render(): string { return $this->renderControl() . "\n"; }
}

class FileUpload extends Input {
    protected string $type = 'file';
    public function multiple(bool $b = true): static { return $this->attr('multiple', $b); }
    public function accept(string $mime): static     { return $this->attr('accept', $mime); }
}

/* ----------------------------------------------------------------- *
 *  TextArea
 * ----------------------------------------------------------------- */
class TextArea extends Element {
    public function rows(int $n): static { return $this->attr('rows', $n); }

    public function renderControl(): string {
        $attrs = $this->controlAttrs();
        unset($attrs['value']);
        $body = Html::e(is_scalar($this->value) ? (string)$this->value : '');
        return '<textarea' . Html::attrs($attrs) . '>' . $body . '</textarea>';
    }
}

/* ----------------------------------------------------------------- *
 *  Select (+ gruppi di opzioni)
 * ----------------------------------------------------------------- */
class Select extends Element {
    /** @var array<int, array{value:string,label:string,group:?string}> */
    protected array $options = [];
    protected bool  $multiple = false;

    public function multiple(bool $b = true): static {
        $this->multiple = $b;
        return $this->attr('multiple', $b);
    }

    public function option(string $value, ?string $label = null, ?string $group = null): static {
        $this->options[] = ['value' => $value, 'label' => $label ?? $value, 'group' => $group];
        return $this;
    }

    /** @param array<string,string>|string[] $map */
    public function options(array $map): static {
        foreach ($map as $k => $v) {
            if (is_int($k)) $this->option((string)$v);
            else            $this->option((string)$k, (string)$v);
        }
        return $this;
    }

    public function renderControl(): string {
        $classes = ['form-select'];
        if ($this->hasErrors()) $classes[] = 'is-invalid';
        $attrs = array_merge([
            'id'    => $this->name,
            'name'  => $this->multiple ? $this->name . '[]' : $this->name,
            'class' => implode(' ', array_merge($classes, $this->classes)),
        ], $this->attrs);
        foreach ($this->rules as [$r, $args]) {
            foreach (Rules::clientAttrs($r, $args) as $k => $v) {
                if (!isset($attrs[$k])) $attrs[$k] = $v;
            }
        }

        $selected = is_array($this->value)
            ? array_map('strval', $this->value)
            : [ (string)($this->value ?? '') ];

        $html  = '<select' . Html::attrs($attrs) . ">\n";
        if ($this->placeholder !== null && !$this->multiple) {
            $html .= '<option value="">' . Html::e($this->placeholder) . "</option>\n";
        }

        // Raggruppamento per optgroup (mantiene l'ordine di inserimento)
        $lastGroup = null; $groupOpen = false;
        foreach ($this->options as $opt) {
            if ($opt['group'] !== $lastGroup) {
                if ($groupOpen) { $html .= "</optgroup>\n"; $groupOpen = false; }
                if ($opt['group'] !== null) {
                    $html .= '<optgroup label="' . Html::e($opt['group']) . "\">\n";
                    $groupOpen = true;
                }
                $lastGroup = $opt['group'];
            }
            $isSel = in_array((string)$opt['value'], $selected, true);
            $html .= '<option value="' . Html::e($opt['value']) . '"' . ($isSel ? ' selected' : '') . '>'
                  . Html::e($opt['label']) . "</option>\n";
        }
        if ($groupOpen) $html .= "</optgroup>\n";

        $html .= '</select>';
        return $html;
    }
}

class SelectCountry extends Select {
    public function __construct(string $name) {
        parent::__construct($name);
        $this->options([
            'IT' => 'Italia', 'FR' => 'Francia', 'DE' => 'Germania', 'ES' => 'Spagna',
            'GB' => 'Regno Unito', 'US' => 'Stati Uniti', 'CA' => 'Canada', 'JP' => 'Giappone',
            'CN' => 'Cina', 'IN' => 'India', 'BR' => 'Brasile', 'AR' => 'Argentina',
            'AU' => 'Australia', 'NZ' => 'Nuova Zelanda', 'CH' => 'Svizzera',
        ]);
    }
}

/* ----------------------------------------------------------------- *
 *  Checkbox singola
 * ----------------------------------------------------------------- */
class Checkbox extends Element {
    protected string $checkedValue = '1';

    public function checkedValue(string $v): static { $this->checkedValue = $v; return $this; }

    public function renderControl(): string {
        $classes = ['form-check-input'];
        if ($this->hasErrors()) $classes[] = 'is-invalid';
        $attrs = array_merge([
            'type'  => 'checkbox',
            'id'    => $this->name,
            'name'  => $this->name,
            'value' => $this->checkedValue,
            'class' => implode(' ', array_merge($classes, $this->classes)),
        ], $this->attrs);
        foreach ($this->rules as [$r, $args]) {
            foreach (Rules::clientAttrs($r, $args) as $k => $v) {
                if (!isset($attrs[$k])) $attrs[$k] = $v;
            }
        }
        if ((string)$this->value === $this->checkedValue) $attrs['checked'] = true;
        return '<input' . Html::attrs($attrs) . '>';
    }

    public function render(): string {
        $col = $this->span > 0 ? ' col-md-' . $this->span : ' col-12';
        $out  = '<div class="mb-3' . $col . '">' . "\n";
        $out .= '<div class="form-check">' . "\n";
        $out .= $this->renderControl() . "\n";
        if ($this->label !== null) {
            $out .= '<label class="form-check-label" for="' . Html::e($this->name) . '">'
                 . Html::e($this->label) . "</label>\n";
        }
        $out .= "</div>\n";
        $out .= $this->renderFeedback();
        $out .= "</div>\n";
        return $out;
    }
}

/* ----------------------------------------------------------------- *
 *  Radio e Checkbox multipli condividono la logica "gruppo di opzioni".
 * ----------------------------------------------------------------- */
abstract class OptionGroup extends Element {
    /** @var array<int,array{value:string,label:string}> */
    protected array $options = [];
    protected bool  $inline  = false;

    public function option(string $value, ?string $label = null): static {
        $this->options[] = ['value' => $value, 'label' => $label ?? $value];
        return $this;
    }
    public function options(array $map): static {
        foreach ($map as $k => $v) {
            if (is_int($k)) $this->option((string)$v);
            else            $this->option((string)$k, (string)$v);
        }
        return $this;
    }
    public function inline(bool $b = true): static { $this->inline = $b; return $this; }

    protected abstract function inputType(): string;
    protected abstract function inputName(): string;
    protected abstract function isChecked(string $optValue): bool;

    public function renderControl(): string {
        $html = '';
        $inline = $this->inline ? ' form-check-inline' : '';
        foreach ($this->options as $i => $opt) {
            $id = $this->name . '_' . $i;
            $classes = ['form-check-input'];
            if ($this->hasErrors()) $classes[] = 'is-invalid';
            $attrs = array_merge([
                'type'  => $this->inputType(),
                'class' => implode(' ', $classes),
                'id'    => $id,
                'name'  => $this->inputName(),
                'value' => $opt['value'],
            ], $this->attrs);
            // le regole client-side si applicano solo al primo input del gruppo
            if ($i === 0) {
                foreach ($this->rules as [$r, $args]) {
                    foreach (Rules::clientAttrs($r, $args) as $k => $v) {
                        if (!isset($attrs[$k])) $attrs[$k] = $v;
                    }
                }
            }
            if ($this->isChecked($opt['value'])) $attrs['checked'] = true;

            $html .= '<div class="form-check' . $inline . "\">\n"
                   . '<input' . Html::attrs($attrs) . ">\n"
                   . '<label class="form-check-label" for="' . Html::e($id) . '">'
                   . Html::e($opt['label']) . "</label>\n"
                   . "</div>\n";
        }
        return $html;
    }

    public function render(): string {
        $col = $this->span > 0 ? ' col-md-' . $this->span : ' col-12';
        $out  = '<div class="mb-3' . $col . '">' . "\n";
        if ($this->label !== null) {
            $out .= '<div class="form-label">' . Html::e($this->label)
                 . (array_filter($this->rules, fn($r) => $r[0]==='required') ? ' <span class="text-danger">*</span>' : '')
                 . "</div>\n";
        }
        $out .= $this->renderControl();
        $out .= $this->renderFeedback();
        $out .= "</div>\n";
        return $out;
    }
}

class Radio extends OptionGroup {
    protected function inputType(): string { return 'radio'; }
    protected function inputName(): string { return $this->name; }
    protected function isChecked(string $v): bool { return (string)$this->value === $v; }
}

class CheckboxGroup extends OptionGroup {
    protected function inputType(): string { return 'checkbox'; }
    protected function inputName(): string { return $this->name . '[]'; }
    protected function isChecked(string $v): bool {
        $vals = is_array($this->value) ? array_map('strval', $this->value) : [];
        return in_array($v, $vals, true);
    }
}

/* ----------------------------------------------------------------- *
 *  Containers (FieldSet, Row, Column) — consentono layout complessi.
 *  Sono anch'essi Element così partecipano alla catena fluente.
 * ----------------------------------------------------------------- */
abstract class Container extends Element {
    /** @var Element[] */
    protected array $children = [];

    public function __construct(string $name = '') { parent::__construct($name); }

    public function add(Element $el): static {
        $this->children[] = $el;
        return $this;
    }

    /** @return Element[] elenco piatto dei soli campi con nome */
    public function flatFields(): array {
        $out = [];
        foreach ($this->children as $c) {
            if ($c instanceof Container) $out = array_merge($out, $c->flatFields());
            elseif ($c->getName() !== '') $out[] = $c;
        }
        return $out;
    }

    public function renderControl(): string { return ''; }
}

class Row extends Container {
    public function render(): string {
        $out = '<div class="row g-3">' . "\n";
        foreach ($this->children as $c) $out .= $c->render();
        $out .= "</div>\n";
        return $out;
    }
}

class Column extends Container {
    public function render(): string {
        $col = $this->span > 0 ? ' col-md-' . $this->span : ' col';
        $out = '<div class="' . trim($col) . '">' . "\n";
        foreach ($this->children as $c) $out .= $c->render();
        $out .= "</div>\n";
        return $out;
    }
}

class FieldSet extends Container {
    private ?string $legend = null;
    public function legend(string $t): static { $this->legend = $t; return $this; }
    public function render(): string {
        $out = '<fieldset class="mb-4 p-3 border rounded">' . "\n";
        if ($this->legend) $out .= '<legend class="float-none w-auto px-2 fs-5">' . Html::e($this->legend) . "</legend>\n";
        foreach ($this->children as $c) $out .= $c->render();
        $out .= "</fieldset>\n";
        return $out;
    }
}

/* ----------------------------------------------------------------- *
 *  Form: root del DOM, gestisce action/method, populate, validate, submit.
 * ----------------------------------------------------------------- */
class Form {
    private string $name;
    private string $method = 'POST';
    private string $action = '';
    private ?string $enctype = null;
    private ?string $submitLabel = 'Invia';
    /** @var Element[] */
    private array $children = [];

    private bool  $submitted = false;
    private bool  $validated = false;
    private bool  $valid     = true;
    /** @var array<string,string[]> */
    private array $errors    = [];
    /** @var array<string,mixed> */
    private array $data      = [];

    public function __construct(string $name) { $this->name = $name; }

    /* ---- configurazione ---- */
    public function method(string $m): static  { $this->method = strtoupper($m); return $this; }
    public function action(string $a): static  { $this->action = $a;             return $this; }
    public function enctype(string $e): static { $this->enctype = $e;            return $this; }
    public function submit(?string $label): static { $this->submitLabel = $label; return $this; }

    public function add(Element $el): static {
        $el->setForm($this);
        $this->children[] = $el;
        return $this;
    }

    /* ---- binding ---- */
    public function fill(array $data): static {
        foreach ($this->flatFields() as $f) $f->populate($data);
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Processa la richiesta in arrivo:
     *  - se il metodo HTTP corrisponde e il flag form_name è presente nel payload
     *  - popola i valori e lancia la validazione
     * Ritorna true se la form è valida.
     */
    public function handle(?array $data = null): bool {
        $incoming = $data ?? ($this->method === 'GET' ? $_GET : $_POST);
        $flag = '_form_' . $this->name;
        $reqMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
        if ($data === null && ($reqMethod !== $this->method || !isset($incoming[$flag]))) {
            return false;
        }
        $this->submitted = true;
        $this->fill($incoming);
        return $this->validate();
    }

    public function validate(): bool {
        $this->validated = true;
        $this->valid     = true;
        $this->errors    = [];
        foreach ($this->flatFields() as $f) {
            if (!$f->validate($this->data)) {
                $this->valid = false;
                $this->errors[$f->getName()] = $f->getErrors();
            }
        }
        return $this->valid;
    }

    /* ---- accessor ---- */
    public function isSubmitted(): bool { return $this->submitted; }
    public function isValid(): bool     { return $this->validated && $this->valid; }
    public function errors(): array     { return $this->errors; }
    public function data(): array {
        $out = [];
        foreach ($this->flatFields() as $f) $out[$f->getName()] = $f->getValue();
        return $out;
    }

    /** @return Element[] */
    public function flatFields(): array {
        $out = [];
        foreach ($this->children as $c) {
            if ($c instanceof Container) $out = array_merge($out, $c->flatFields());
            elseif ($c->getName() !== '') $out[] = $c;
        }
        return $out;
    }

    /* ---- rendering ---- */
    public function toHtml(): string {
        $wasValidated = $this->submitted ? ' was-validated' : '';
        $attrs = [
            'id'      => $this->name,
            'name'    => $this->name,
            'method'  => $this->method,
            'action'  => $this->action,
            'enctype' => $this->enctype,
            'class'   => 'needs-validation' . $wasValidated,
            'novalidate' => true,
        ];
        $out  = '<form' . Html::attrs($attrs) . ">\n";
        $out .= '<input type="hidden" name="_form_' . Html::e($this->name) . '" value="1">' . "\n";

        foreach ($this->children as $c) $out .= $c->render();

        if ($this->submitLabel !== null) {
            $out .= '<div class="mt-3"><button type="submit" class="btn btn-primary">'
                 . Html::e($this->submitLabel) . "</button></div>\n";
        }

        $out .= "</form>\n";
        $out .= $this->clientScript();
        return $out;
    }

    public function render(): void { echo $this->toHtml(); }

    /**
     * Script inline:
     *  - attiva lo stile was-validated di Bootstrap alla submission
     *  - implementa regole custom (per ora "same") in aggiunta a quelle HTML5
     */
    private function clientScript(): string {
        $fid = Html::e($this->name);
        return <<<HTML
<script>
(function () {
    const form = document.getElementById('{$fid}');
    if (!form) return;

    // Regole custom: data-rule-same="otherFieldName"
    const checkCustom = (input) => {
        const other = input.dataset.ruleSame;
        if (other) {
            const ref = form.querySelector('[name="' + other + '"]');
            if (ref && input.value !== ref.value) {
                input.setCustomValidity('mismatch');
                return;
            }
        }
        input.setCustomValidity('');
    };

    form.querySelectorAll('input, select, textarea').forEach(el => {
        el.addEventListener('input', () => checkCustom(el));
        el.addEventListener('blur',  () => checkCustom(el));
    });

    form.addEventListener('submit', (e) => {
        form.querySelectorAll('input, select, textarea').forEach(checkCustom);
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
})();
</script>
HTML;
    }
}
