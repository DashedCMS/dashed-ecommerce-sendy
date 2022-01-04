<?php

namespace Qubiqx\QcommerceEcommerceKeendelivery;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Qubiqx\QcommerceEcommerceKeendelivery\Commands\QcommerceEcommerceKeendeliveryCommand;

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
