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
            ->registerClass('locationCountry', \JobMetric\Location\Services\CountryManager::class)
            ->registerClass('locationProvince', \JobMetric\Location\Services\ProvinceManager::class)
            ->registerClass('locationCity', \JobMetric\Location\Services\CityManager::class)
            ->registerClass('locationDistrict', \JobMetric\Location\Services\DistrictManager::class)
            ->registerClass('locationGeoArea', \JobMetric\Location\Services\GeoAreaManager::class)
            ->registerClass('locationAddress', \JobMetric\Location\Services\AddressManager::class);
    }
}
