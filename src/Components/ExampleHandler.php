<?php
/**
 * Created for plugin-exporter-excel
 * Date: 05.11.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Instance\Macros\Components;

use Leadvertex\Plugin\Components\Batch\Batch;
use Leadvertex\Plugin\Components\Batch\BatchHandlerInterface;
use Leadvertex\Plugin\Components\Process\Components\Error;
use Leadvertex\Plugin\Components\Process\Process;
use Leadvertex\Plugin\Components\Settings\Settings;

class ExampleHandler implements BatchHandlerInterface
{

    private static $skipped;
    private static $errors;
    private static $isNullCount;
    private static $response;

    public function __invoke(Process $process, Batch $batch)
    {
        Settings::guardIntegrity();
        $fields = Settings::find()->getData()->get('group_1.fields');

        $delay = $batch->getOptions(1)->get('response_options.delay');
        self::$isNullCount = $batch->getOptions(1)->get('response_options.nullCount');
        self::$skipped = $batch->getOptions(1)->get('response_options.skipped');
        self::$errors = $batch->getOptions(1)->get('response_options.errors');
        self::$response = $batch->getOptions(1)->get('response_options.response');

        $iterator = new OrdersFetcherIterator(Columns::getQueryColumns($fields), $batch->getApiClient(), $batch->getFsp());
        $process->initialize(count($iterator));

        foreach ($iterator as $field) {
            if (self::$isNullCount) {
                $process->initialize(null);
            }
            if (self::$skipped !== 0) {
                $process->skip();
                self::$skipped--;
            } elseif (self::$errors !== 0) {
                $process->addError(new Error('Test error', $field['id']));
                self::$errors--;
            } else {
                $process->handle();
            }
            $process->save();
            sleep($delay);
        }

        $process->setState(Process::STATE_POST_PROCESSING);
        $process->save();
        sleep($batch->getOptions(1)->get('response_options.post_processing_time'));

        $response = '';

        switch (self::$response[0]) {
            case 'static_success': {
                $response = true;
                break;
            }
            case 'static_uri': {
                $response = "https://leadvertex.ru/img/logo.png";
                break;
            }
            case 'static_error': {
                $response = false;
                break;
            }
        }

        $process->finish($response);
        $process->save();
    }
}