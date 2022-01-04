<?php

namespace Qubiqx\QcommerceEcommerceKeendelivery;

use Qubiqx\QcommerceEcommerceKeendelivery\Commands\QcommerceEcommerceKeendeliveryCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class QcommerceEcommerceKeendeliveryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('qcommerce-ecommerce-keendelivery')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_qcommerce-ecommerce-keendelivery_table')
            ->hasCommand(QcommerceEcommerceKeendeliveryCommand::class);
    }
}
