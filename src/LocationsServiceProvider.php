<?php

namespace Digbang\Locations;

use Digbang\Locations\Doctrine\Mappings;
use Digbang\Locations\Doctrine\Repositories\DoctrineCountryRepository;
use Digbang\Locations\Doctrine\Repositories\DoctrineLocationRepository;
use Doctrine\DBAL\Types\Type;
use Digbang\Locations\Doctrine\Types\UuidType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Provider\Provider;
use Http\Adapter\Guzzle6\Client;
use Illuminate\Support\ServiceProvider;
use LaravelDoctrine\Fluent\FluentDriver;
use LaravelDoctrine\ORM\Configuration\MetaData\MetaDataManager;
use LaravelDoctrine\ORM\Extensions\MappingDriverChain;

class LocationsServiceProvider extends ServiceProvider
{
    private const PACKAGE = 'locations';

    public function boot(ManagerRegistry $managerRegistry, MetaDataManager $metadata)
    {
        /** @var EntityManager $entityManager */
        foreach ($managerRegistry->getManagers() as $entityManager) {
            $this->doctrineMappings($entityManager, $metadata);
        }

        $this->resources();
    }

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), static::PACKAGE);

        $this->app->bind(Provider::class, config('locations.provider'));
        $this->app->bind(LocationRepository::class, DoctrineLocationRepository::class);
        $this->app->bind(CountryRepository::class, DoctrineCountryRepository::class);

        $this->app->bind(GoogleMaps::class,
            function ($app) {
                return new GoogleMaps($app->make(Client::class),
                                      config('locations.region'),
                                      config('locations.apikey'));
            });

        $this->registerTypes();
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param MetaDataManager        $metadata
     * @return void
     */
    protected function doctrineMappings(EntityManagerInterface $entityManager, MetaDataManager $metadata): void
    {
        /** @var FluentDriver $fluentDriver */
        $fluentDriver = $metadata->driver('fluent',
                                          [
                                              'mappings' => [
                                                  Mappings\AddressMapping::class,
                                                  Mappings\AdministrativeLevelMapping::class,
                                                  Mappings\BoundsMapping::class,
                                                  Mappings\CoordinatesMapping::class,
                                                  Mappings\CountryMapping::class,
                                              ],
                                          ]);

        /** @var MappingDriverChain $chain */
        $chain = $entityManager->getConfiguration()->getMetadataDriverImpl();
        $chain->addDriver($fluentDriver, 'Digbang\Locations');
    }

    /**
     * @return void
     */
    protected function resources(): void
    {
        $this->publishes([
                             $this->configPath() => config_path(static::PACKAGE . '.php'),
                         ],
                         'config');
    }

    /**
     * @return string
     */
    private function configPath(): string
    {
        return dirname(__DIR__) . '/config/locations.php';
    }

    private function registerTypes()
    {
        if (!Type::hasType(UuidType::NAME)) {
            Type::addType(UuidType::NAME, UuidType::class);
        }
    }
}