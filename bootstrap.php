<?php

use SalesRender\Plugin\Components\Batch\BatchContainer;
use SalesRender\Plugin\Components\Db\Components\Connector;
use SalesRender\Plugin\Components\Form\Autocomplete\AutocompleteRegistry;
use SalesRender\Plugin\Components\Form\MarkdownPreview\MarkdownPreviewRegistry;
use SalesRender\Plugin\Components\Form\TableView\TablePreviewRegistry;
use SalesRender\Plugin\Components\Info\Developer;
use SalesRender\Plugin\Components\Info\Info;
use SalesRender\Plugin\Components\Info\PluginType;
use SalesRender\Plugin\Components\Purpose\MacrosPluginClass;
use SalesRender\Plugin\Components\Purpose\PluginEntity;
use SalesRender\Plugin\Components\Purpose\PluginPurpose;
use SalesRender\Plugin\Components\Settings\Settings;
use SalesRender\Plugin\Components\Translations\Translator;
use SalesRender\Plugin\Core\Actions\Upload\LocalUploadAction;
use SalesRender\Plugin\Core\Actions\Upload\UploadersContainer;
use SalesRender\Plugin\Instance\Macros\Autocomplete\Example;
use SalesRender\Plugin\Instance\Macros\Autocomplete\ExampleWithDeps;
use SalesRender\Plugin\Instance\Macros\Components\ExampleHandler;
use SalesRender\Plugin\Instance\Macros\Forms\PreviewOptionsForm;
use SalesRender\Plugin\Instance\Macros\Forms\ResponseOptionsForm;
use SalesRender\Plugin\Instance\Macros\Forms\SecondResponseOptionsForm;
use SalesRender\Plugin\Instance\Macros\Forms\SettingsForm;
use Medoo\Medoo;
use SalesRender\Plugin\Instance\Macros\MarkdownPreviewAction\MarkdownPreviewExample;
use SalesRender\Plugin\Instance\Macros\TablePreviewAction\TablePreviewExample;
use SalesRender\Plugin\Instance\Macros\TablePreviewAction\TablePreviewExcel;
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
UploadersContainer::addDefaultUploader(new LocalUploadAction([
    'jpg' => 1 * 1024 * 1024,       //Max 1 MB for *.jpg file
    'png' => 2 * 1024 * 1024,       //Max 2 MB for *.jpg file
    'zip' => 10 * 1024 * 1024, //Max 10 MB for *.zip archive
    'rar' => 10 * 1024 * 1024, //Max 10 MB for *.rar archive
]));

# 4. Configure info about plugin
Info::config(
    new PluginType(PluginType::MACROS),
    fn() => Translator::get('info', 'PLUGIN_NAME'),
    fn() => Translator::get('info', 'PLUGIN_DESCRIPTION') . "\n" . file_get_contents(Path::root()->down('markdown.md')),
    new PluginPurpose(
        new MacrosPluginClass(MacrosPluginClass::CLASS_HANDLER),
        new PluginEntity(PluginEntity::ENTITY_ORDER)
    ),
    new Developer(
        'LeadVertex',
        'support@leadvertex.com',
        'leadvertex.com',
    )
);

# 5. Configure settings form
Settings::setForm(fn($context) => new SettingsForm($context));

# 6. Configure form autocompletes
AutocompleteRegistry::config(function (string $name) {
    switch ($name) {
        case 'example': return new Example();
        case 'exampleWithDeps': return new ExampleWithDeps();
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

TablePreviewRegistry::config(
    function (string $name) {
        switch ($name) {
            case 'example': return new TablePreviewExample();
            case 'excel': return new TablePreviewExcel();
            default: return null;
        }
    }
);

MarkdownPreviewRegistry::config(
    function (string $name) {
        switch ($name) {
            case 'markdown_example': return new MarkdownPreviewExample();
            default: return null;
        }
    }
);