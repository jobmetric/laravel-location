<?php

namespace JobMetric\Location;

use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\RegisterClassTypeNotFoundException;
use JobMetric\PackageCore\PackageCore;
use JobMetric\PackageCore\PackageCoreServiceProvider;

class LocationServiceProvider extends PackageCoreServiceProvider
{
    /**
     * @param PackageCore $package
     *
     * @return void
     * @throws MigrationFolderNotFoundException
     * @throws RegisterClassTypeNotFoundException
     */
    public function configuration(PackageCore $package): void
    {
        $package->name('laravel-location')
            ->hasConfig()
            ->hasMigration()
            ->hasTranslation()
            ->registerClass('location-country', \JobMetric\Location\Services\Country::class)
            ->registerClass('location-province', \JobMetric\Location\Services\Province::class)
            ->registerClass('location-city', \JobMetric\Location\Services\City::class)
            ->registerClass('location-district', \JobMetric\Location\Services\District::class)
            ->registerClass('location-location', \JobMetric\Location\Services\Location::class)
            ->registerClass('location-geo-area', \JobMetric\Location\Services\GeoArea::class)
            ->registerClass('location-address', \JobMetric\Location\Services\Address::class);
    }
}
