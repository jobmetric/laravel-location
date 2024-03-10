<?php

namespace JobMetric\Location\Services;

use Illuminate\Contracts\Foundation\Application;

class GeoAreaManager
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Create a new country instance.
     *
     * @param Application $app
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
}
