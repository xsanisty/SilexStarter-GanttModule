<?php

namespace Xsanisty\Gantt;

use Silex\Application;
use SilexStarter\Module\ModuleInfo;
use SilexStarter\Module\ModuleResource;
use SilexStarter\Contracts\ModuleProviderInterface;
use Xsanisty\Gantt\Provider\RepositoryServiceProvider;
use Xsanisty\Gantt\Provider\HashidsServiceProvider;
use Xsanisty\Admin\DashboardModule;

class GanttModule implements ModuleProviderInterface
{
    protected $app;

    /**
     * {@inheritdoc}
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleIdentifier()
    {
        return 'xsanisty-gantt';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredModules()
    {
        return ['silexstarter-dashboard'];
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo()
    {
        return new ModuleInfo(
            [
                'name'          => 'SilexStarter Gantt Chart Module',
                'description'   => 'Provide gantt chart functionality into SilexStarter application',
                'author_name'   => 'Xsanisty Development Team',
                'author_email'  => 'developers@xsanisty.com',
                'repository'    => 'https://bitbucket.com/xsanisty/bankai',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getResources()
    {
        return new ModuleResource(
            [
                'migrations'    => 'Resources/migrations',
                'config'        => 'Resources/config',
                'assets'        => 'Resources/assets',
                'views'         => 'Resources/views',
                'controllers'   => 'Controller',
                'routes'        => 'Resources/routes.php'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredPermissions()
    {
        return [
            'gantt.chart.create'    => 'Create new gantt chart',
            'gantt.chart.read'      => 'Read existing gantt chart',
            'gantt.chart.delete'    => 'Delete existing gantt chart',
            'gantt.chart.edit'      => 'Edit Existing gantt chart'
        ];

        /** other task or link permission is defined in the chart object itself and linked to the author/contributor */
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function uninstall()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $app = $this->app;

        $app->register(new RepositoryServiceProvider);
        $app->register(new HashidsServiceProvider);

        $app['dispatcher']->addListener(
            DashboardModule::INIT,
            function () use ($app) {
                $ganttMenu = $app['menu_manager']
                    ->get('admin_sidebar')
                    ->createItem(
                        'xsanisty-gantt',
                        [
                            'label' => 'Gantt Chart',
                            'icon'  => 'tasks',
                            'url'   => '#'
                        ]
                    );

                $ganttMenu->addChildren(
                    'my-gantt-chart',
                    [
                        'label' => 'My Chart',
                        'icon'  => 'tasks',
                        'url'   => Url::to('gantt.chart.index')
                    ]
                );

                $ganttMenu->addChildren(
                    'my-gantt-bookmark',
                    [
                        'label' => 'Manage Bookmark',
                        'icon'  => 'bookmark',
                        'url'   => Url::to('gantt.bookmark.index')
                    ]
                );
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {

    }
}
