<?php
/**
 * Created for plugin-core
 * Date: 02.03.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Instance\Macros;


use Leadvertex\Plugin\Components\ApiClient\ApiClient;
use Leadvertex\Plugin\Components\ApiClient\ApiFilterSortPaginate;
use Leadvertex\Plugin\Components\Developer\Developer;
use Leadvertex\Plugin\Components\Form\Form;
use Leadvertex\Plugin\Components\Process\Components\Error;
use Leadvertex\Plugin\Components\Process\Process;
use Leadvertex\Plugin\Components\Purpose\PluginClass;
use Leadvertex\Plugin\Components\Purpose\PluginEntity;
use Leadvertex\Plugin\Components\Purpose\PluginPurpose;
use Leadvertex\Plugin\Components\Translations\Translator;
use Leadvertex\Plugin\Core\Macros\Components\AutocompleteInterface;
use Leadvertex\Plugin\Core\Macros\MacrosPlugin;
use Leadvertex\Plugin\Core\Macros\Models\Session;
use Leadvertex\Plugin\Instance\Macros\Autocomplete\Example;
use Leadvertex\Plugin\Instance\Macros\Forms\PreviewOptionsForm;
use Leadvertex\Plugin\Instance\Macros\Forms\ResponseOptionsForm;
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

    /**
     * @inheritDoc
     */
    public static function getDefaultLanguage(): string
    {
        return 'ru_RU';
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return Translator::get('info', 'PLUGIN_NAME');
    }

    /**
     * @inheritDoc
     */
    public static function getDescription(): string
    {
        return Translator::get('info', 'PLUGIN_DESCRIPTION');
    }

    /**
     * @inheritDoc
     */
    public static function getPurpose(): PluginPurpose
    {
        return new PluginPurpose(
            new PluginClass(PluginClass::CLASS_HANDLER),
            new PluginEntity(PluginEntity::ENTITY_ORDER)
        );
    }

    /**
     * @inheritDoc
     */
    public static function getDeveloper(): Developer
    {
        return new Developer(
            'LeadVertex',
            'support@leadvertex.com',
            'https://leadvertex.com'
        );
    }

    /**
     * @inheritDoc
     */
    public function getSettingsForm(): Form
    {
        if (is_null($this->settings)) {
            $this->settings = new SettingsForm();
        }

        return $this->settings;
    }

    /**
     * @inheritDoc
     */
    public function getRunForm(int $number): ?Form
    {
        switch ($number) {
            case 1:
                return ResponseOptionsForm::getInstance();
                break;
            case 2:
                return PreviewOptionsForm::getInstance();
                break;
            default:
                return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function autocomplete(string $name): ?AutocompleteInterface
    {
        if ($name === 'example') {
            return new Example();
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function run(Process $process, ?ApiFilterSortPaginate $fsp)
    {
        $session = Session::current();
        $responseOptions = $session->getOptions(1)->get('response_options');

        $session->getToken()->getPluginToken();

        if ($responseOptions['nullCount']) {
            $process->initialize(null);
        } else {
            $queryResult = self::getOrdersWithFsp($session->getFsp());
            if ($queryResult['success']) {
                $process->initialize((count($queryResult['data'])));
                $orderIds = array_map(function ($order) { return $order['id']; }, $queryResult['data']);
            } else {
                $process->initialize(null);
                $process->terminate(new Error('Bad GraphQL request. Errors: ' . json_encode($queryResult['errors'])));
                $process->save();
                return;
            }
        }

        if (isset($orderIds)) {
            for ($i = 1; $i <= $responseOptions['errors']; $i++) {
                $id = array_shift($orderIds);
                (($i % 2) == 0) ? $process->addError(new Error('Test error', $id)) : $process->addError(new Error('Test error'));
                sleep($responseOptions['delay']);
            }
        } else {
            for ($i = 1; $i <= $responseOptions['errors']; $i++) {
                (($i % 2) == 0) ? $process->addError(new Error('Test error', $i)) : $process->addError(new Error('Test error'));
                sleep($responseOptions['delay']);
            }
        }

        for ($i = 1; $i <= $responseOptions['skipped']; $i++) {
            $process->skip();
            sleep($responseOptions['delay']);
        }

        if (!is_null($process->initialized)) {
            if ($responseOptions['errors'] + $responseOptions['skipped'] < $process->initialized) {
                for ($i = 1; $i <= $process->initialized - ($responseOptions['errors'] + $responseOptions['skipped']); $i++) {
                    $process->handle();
                    sleep($responseOptions['delay']);
                }
            }
        }

        switch ($responseOptions['response'][0]) {
            case 'static_url': {
                $processResult = 'http://example.com';
                break;
            }
            case 'static_success': {
                $processResult = true;
                break;
            }
            case 'static_error': {
                $processResult = false;
                break;
            }
            default: $processResult = null;
        }

        $process->finish($processResult);
        $process->save();
    }

    static public function getOrdersWithFsp(ApiFilterSortPaginate $fsp): array
    {
        $api = new ApiClient($_ENV['LV_API_ENDPOINT'], $_ENV['LV_API_TOKEN']);

        $variables['query'] = '$pagination: Pagination!';
        $variables['fetcher'] = 'pagination: $pagination';
        $variablesValues = [
            'pagination' => ['pageSize' => $fsp->getPageSize()]
        ];

        if (!is_null($fsp->getFilters())) {
            $variables['query'] .= ', $filters: OrderFilter';
            $variables['fetcher'] .= ', filters: $filters';
            $variablesValues['filters'] = $fsp->getFilters();
        }

        if (!is_null($fsp->getSort())) {
            $variables['query'] .= ', $sort: OrderSort';
            $variables['fetcher'] .= ', sort: $sort';
            $variablesValues['sort'] = $fsp->getSort();
        }

        $query = <<<QUERY
query ({$variables['query']}){
  company {
    ordersFetcher({$variables['fetcher']}) {
      orders {
        id
        status {
          name
        }
      }
    }
  }
}
QUERY;

        $result = $api->query($query, $variablesValues);
        if ($result->hasErrors()) {
            return ['success' => false, 'errors' => $result->getErrors()];
        }
        return ['success' => true, 'data' => $result->getData()['company']['ordersFetcher']['orders']];
    }
}