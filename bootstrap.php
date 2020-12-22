<?php

use Leadvertex\Plugin\Components\Batch\BatchContainer;
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
use Leadvertex\Plugin\Core\Actions\UploadAction;
use Leadvertex\Plugin\Instance\Macros\Autocomplete\Example;
use Leadvertex\Plugin\Instance\Macros\Components\ExampleHandler;
use Leadvertex\Plugin\Instance\Macros\Forms\PreviewOptionsForm;
use Leadvertex\Plugin\Instance\Macros\Forms\ResponseOptionsForm;
use Leadvertex\Plugin\Instance\Macros\Forms\SecondResponseOptionsForm;
use Leadvertex\Plugin\Instance\Macros\Forms\SettingsForm;
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

# 3. Set permitted file extensions (* for any ext) and max sizes (in bytes). Pass empty array for disable file uploading
UploadAction::config([
    'jpg' => 1 * 1024 * 1024,       //Max 1 MB for *.jpg file
    'png' => 2 * 1024 * 1024,       //Max 2 MB for *.jpg file
    'zip' => 10 * 1024 * 1024, //Max 10 MB for *.zip archive
    'rar' => 10 * 1024 * 1024, //Max 10 MB for *.rar archive
]);

# 4. Configure info about plugin
Info::config(
    new PluginType(PluginType::MACROS),
    fn() => Translator::get('info', 'PLUGIN_NAME'),
    fn() => Translator::get('info', 'PLUGIN_DESCRIPTION') . "\n" . file_get_contents(Path::root()->down('markdown.md')),
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

# 5. Configure settings form
Settings::setForm(fn() => new SettingsForm());

# 6. Configure form autocompletes
AutocompleteRegistry::config(function (string $name) {
    switch ($name) {
        case 'example': return new Example();
        default: return null;
    }
});

# 7. Configure batch forms
BatchContainer::config(
    function (int $number) {
        switch ($number) {
            case 1: return new ResponseOptionsForm();
            case 2: return new SecondResponseOptionsForm();
            case 3: return new PreviewOptionsForm();
            default: return null;
        }
    },
    new ExampleHandler()
);