<?php

namespace Xsanisty\Gantt\Provider;

use Hashids\Hashids;
use Silex\Application;
use Silex\ServiceProviderInterface;
use SilexStarter\SilexStarter;

class HashidsServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['hashids'] = $app->share(
            function ($app) {
                return new Hashids;
            }
        );
    }

    public function boot(Application $app)
    {

    }
}
