<?php

namespace JobMetric\Location;

use JobMetric\PackageCore\Exceptions\AssetFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\MigrationFolderNotFoundException;
use JobMetric\PackageCore\Exceptions\RegisterClassTypeNotFoundException;
use JobMetric\PackageCore\Exceptions\ViewFolderNotFoundException;
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
     * @throws AssetFolderNotFoundException
     * @throws ViewFolderNotFoundException
     */
    public function configuration(PackageCore $package): void
    {
        $package->name('laravel-location')
            ->hasConfig()
            ->hasAsset()
            ->hasView()
            ->hasRoute()
            ->hasMigration()
            ->hasTranslation()
            ->registerClass('location-country', \JobMetric\Location\Services\Country::class)
            ->registerClass('locationProvince', \JobMetric\Location\Services\ProvinceManager::class)
            ->registerClass('locationCity', \JobMetric\Location\Services\CityManager::class)
            ->registerClass('locationDistrict', \JobMetric\Location\Services\DistrictManager::class)
            ->registerClass('locationGeoArea', \JobMetric\Location\Services\GeoAreaManager::class)
            ->registerClass('locationAddress', \JobMetric\Location\Services\AddressManager::class);
    }
}
