<?php

namespace Xsanisty\Gantt\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use SilexStarter\SilexStarter;
use Xsanisty\Gantt\Model\Chart;
use Xsanisty\Gantt\Model\Task;
use Xsanisty\Gantt\Model\Link;
use Xsanisty\Gantt\Repository\ChartRepository;
use Xsanisty\Gantt\Repository\TaskRepository;
use Xsanisty\Gantt\Repository\LinkRepository;

class RepositoryServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['gantt.chart.repository'] = $app->share(
            function (Application $app) {
                return new ChartRepository(new Chart);
            }
        );
        $app['gantt.task.repository'] = $app->share(
            function (Application $app) {
                return new TaskRepository(new Task);
            }
        );
        $app['gantt.link.repository'] = $app->share(
            function (Application $app) {
                return new LinkRepository(new Link);
            }
        );

        if ($app instanceof SilexStarter) {
            $app->bind('Xsanisty\Gantt\Contract\ChartRepositoryInterface', 'gantt.chart.repository');
            $app->bind('Xsanisty\Gantt\Contract\TaskRepositoryInterface', 'gantt.task.repository');
            $app->bind('Xsanisty\Gantt\Contract\LinkRepositoryInterface', 'gantt.link.repository');
        }

    }

    public function boot(Application $app)
    {
    }
}
