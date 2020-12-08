<?php

use Leadvertex\Plugin\Components\Batch\BatchFormRegistry;
use Leadvertex\Plugin\Components\Batch\BatchHandler;
use Leadvertex\Plugin\Components\Db\Components\Connector;
use Leadvertex\Plugin\Components\Form\Components\AutocompleteRegistry;
use Leadvertex\Plugin\Components\Info\Developer;
use Leadvertex\Plugin\Components\Info\Info;
use Leadvertex\Plugin\Components\Info\PluginType;
use Leadvertex\Plugin\Components\Purpose\PluginClass;
use Leadvertex\Plugin\Components\Purpose\PluginEntity;
use Leadvertex\Plugin\Components\Purpose\PluginPurpose;
use Leadvertex\Plugin\Components\Settings\Settings;
use Leadvertex\Plugin\Components\Translations\Translator;
use Leadvertex\Plugin\Instance\Macros\Autocomplete\Example;
use Leadvertex\Plugin\Instance\Macros\Components\ExampleHandler;
use Leadvertex\Plugin\Instance\Macros\Components\PathHelper;
use Leadvertex\Plugin\Instance\Macros\Forms\PreviewOptionsForm;
use Leadvertex\Plugin\Instance\Macros\Forms\ResponseOptionsForm;
use Leadvertex\Plugin\Instance\Macros\Forms\SecondResponseOptionsForm;
use Medoo\Medoo;
use XAKEPEHOK\Path\Path;

require_once __DIR__ . '/vendor/autoload.php';

# 1. Configure DB (for SQLite *.db file and parent directory should be writable)
Connector::config(new Medoo([
    'database_type' => 'sqlite',
    'database_file' => Path::root()->down('db/database.db')
]));

# 2. Set plugin default language
Translator::config('ru_RU');

# 3. Configure info about plugin
Info::config(
    new PluginType(PluginType::MACROS),
    fn() => Translator::get('info', 'PLUGIN_NAME'),
    fn() => Translator::get('info', 'PLUGIN_DESCRIPTION') . "\n" . file_get_contents(PathHelper::getRoot()->down('markdown.md')),
    new PluginPurpose(
        new PluginClass(PluginClass::CLASS_HANDLER),
        new PluginEntity(PluginEntity::ENTITY_ORDER)
    ),
    new Developer(
        'LeadVertex',
        'support@leadvertex.com',
        'leadvertex.com',
    )
);

# 4. Configure settings form
Settings::setForm(fn() => new \Leadvertex\Plugin\Instance\Macros\Forms\SettingsForm());

# 5. Configure form autocompletes (or return null if dont used)
AutocompleteRegistry::config(function (string $name) {
    switch ($name) {
        case 'example': return new Example();
        default: return null;
    }
});

# 6. Configure batch forms (or return null if dont used)
BatchFormRegistry::config(function (int $number) {
    switch ($number) {
        case 1: return new ResponseOptionsForm();
        case 2: return new SecondResponseOptionsForm();
        case 3: return new PreviewOptionsForm();
        default: return null;
    }
});

# 6.1 Configure batch handler (or remove this block if dont used)
BatchHandler::config(fn() => new ExampleHandler());