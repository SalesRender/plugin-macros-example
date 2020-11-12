<?php
/**
 * Created for plugin-exporter-excel
 * Date: 05.11.2020
 * @author Timur Kasumov (XAKEPEHOK)
 */

namespace Leadvertex\Plugin\Instance\Macros\Components;

use Leadvertex\Plugin\Components\Batch\Batch;
use Leadvertex\Plugin\Components\Batch\BatchHandlerInterface;
use Leadvertex\Plugin\Components\Process\Process;
use Leadvertex\Plugin\Components\Token\GraphqlInputToken;
use Leadvertex\Plugin\Instance\Macros\Plugin;

class BatchHandler implements BatchHandlerInterface
{

    /**
     * @var Plugin
     */
    private $plugin;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function __invoke(Process $process, Batch $batch)
    {
        $iterator = new OrdersFetcherIterator($process, $batch->getApiClient(), $batch->getFsp());
        $fields = GraphqlInputToken::getInstance()->getSettings()->getData()->get('group_1.fields');

        $delay = $batch->getOptions(1)->get('response_options.delay');

        $iterator->iterator(
            Columns::getQueryColumns($fields),
            function (Process $process) use ($delay) {
                $process->handle();
                $process->save();
                sleep($delay);
            }
        );

        $process->finish(true);
        $process->save();
    }
}