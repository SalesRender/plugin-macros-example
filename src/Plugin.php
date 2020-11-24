<?php
/**
 * Created for plugin-core
 * Date: 02.03.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Instance\Macros;


use Leadvertex\Plugin\Components\Batch\BatchHandlerInterface;
use Leadvertex\Plugin\Components\Developer\Developer;
use Leadvertex\Plugin\Components\Form\Form;
use Leadvertex\Plugin\Components\Purpose\PluginClass;
use Leadvertex\Plugin\Components\Purpose\PluginEntity;
use Leadvertex\Plugin\Components\Purpose\PluginPurpose;
use Leadvertex\Plugin\Components\Translations\Translator;
use Leadvertex\Plugin\Core\Macros\MacrosPlugin;
use Leadvertex\Plugin\Instance\Macros\Autocomplete\Example;
use Leadvertex\Plugin\Instance\Macros\Components\BatchHandler;
use Leadvertex\Plugin\Instance\Macros\Components\PathHelper;
use Leadvertex\Plugin\Instance\Macros\Forms\PreviewOptionsForm;
use Leadvertex\Plugin\Instance\Macros\Forms\ResponseOptionsForm;
use Leadvertex\Plugin\Instance\Macros\Forms\SecondResponseOptionsForm;
use Leadvertex\Plugin\Instance\Macros\Forms\SettingsForm;

class Plugin extends MacrosPlugin
{

    /** @var SettingsForm */
    private $settings;

    /**
     * @inheritDoc
     */
    public static function getLanguages(): array
    {
        return [
            'en_US',
            'ru_RU'
        ];
    }

    public static function getDefaultLanguage(): string
    {
        return 'ru_RU';
    }

    public static function getName(): string
    {
        return Translator::get('info', 'PLUGIN_NAME');
    }

    public static function getDescription(): string
    {
        return Translator::get('info', 'PLUGIN_DESCRIPTION') . "\n" . file_get_contents(PathHelper::getRoot()->down('markdown.md'));
    }

    public static function getPurpose(): PluginPurpose
    {
        return new PluginPurpose(
            new PluginClass(PluginClass::CLASS_HANDLER),
            new PluginEntity(PluginEntity::ENTITY_ORDER)
        );
    }

    public static function getDeveloper(): Developer
    {
        return new Developer(
            'LeadVertex',
            'support@leadvertex.com',
            'https://leadvertex.com'
        );
    }

    public function getSettingsForm(): Form
    {
        if (is_null($this->settings)) {
            $this->settings = new SettingsForm();
        }

        return $this->settings;
    }

    public function getBatchForm(int $number): ?Form
    {
        switch ($number) {
            case 1:
                return ResponseOptionsForm::getInstance();
            case 2:
                return SecondResponseOptionsForm::getInstance();
            case 3:
                return PreviewOptionsForm::getInstance();
            default:
                return null;
        }
    }

    public function handler(): BatchHandlerInterface
    {
        return new BatchHandler($this);
    }

    public function autocomplete(string $name): ?\Leadvertex\Plugin\Components\Form\Components\AutocompleteInterface
    {
        return new Example();
    }
}