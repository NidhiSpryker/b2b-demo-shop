<?php

/**
 * This file is part of the Spryker Demoshop.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\Shipment;

use Pyz\Zed\Shipment\Communication\Plugin\Availability\DHLExpressPlugin;
use Pyz\Zed\Shipment\Communication\Plugin\Availability\DHLPaketPlugin;
use Pyz\Zed\Shipment\Communication\Plugin\DeliveryTime\DHLExpressPlugin as DeliveryTimeDHLExpressPlugin;
use Pyz\Zed\Shipment\Communication\Plugin\DeliveryTime\DHLPaketPlugin as DeliveryTimeDHLPaketPlugin;
use Pyz\Zed\Shipment\Communication\Plugin\PriceCalculation\DHLExpressPlugin as PriceCalculationDHLExpressPlugin;
use Pyz\Zed\Shipment\Communication\Plugin\PriceCalculation\DHLPaketPlugin as PriceCalculationDHLPaketPlugin;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Shipment\ShipmentDependencyProvider as SprykerShipmentDependencyProvider;

class ShipmentDependencyProvider extends SprykerShipmentDependencyProvider
{

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container)
    {
        parent::provideCommunicationLayerDependencies($container);

        $container[self::PLUGINS] = function (Container $container) {
            return [
                self::AVAILABILITY_PLUGINS => $this->getAvailabilityPlugins($container),
                self::PRICE_CALCULATION_PLUGINS => $this->getPriceCalculationPlugins($container),
                self::DELIVERY_TIME_PLUGINS => $this->getDeliveryTimePlugins($container),
                self::TAX_CALCULATION_PLUGINS => $this->getTaxCalculationPlugins($container),
            ];
        };

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        parent::provideBusinessLayerDependencies($container);

        $container[self::PLUGINS] = function (Container $container) {
            return [
                self::AVAILABILITY_PLUGINS => $this->getAvailabilityPlugins($container),
                self::PRICE_CALCULATION_PLUGINS => $this->getPriceCalculationPlugins($container),
                self::DELIVERY_TIME_PLUGINS => $this->getDeliveryTimePlugins($container),
                self::TAX_CALCULATION_PLUGINS => $this->getTaxCalculationPlugins($container),
            ];
        };

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return array
     */
    protected function getAvailabilityPlugins(Container $container)
    {
        $plugins = [
            ShipmentConfig::DHL_EXPRESS => new DHLExpressPlugin(),
            ShipmentConfig::DHL_PAKET => new DHLPaketPlugin(),
        ];

        return $plugins;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return array
     */
    protected function getPriceCalculationPlugins(Container $container)
    {
        $plugins = [
            ShipmentConfig::DHL_EXPRESS => new PriceCalculationDHLExpressPlugin(),
            ShipmentConfig::DHL_PAKET => new PriceCalculationDHLPaketPlugin(),
        ];

        return $plugins;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return array
     */
    protected function getDeliveryTimePlugins(Container $container)
    {
        $plugins = [
            ShipmentConfig::DHL_EXPRESS => new DeliveryTimeDHLExpressPlugin(),
            ShipmentConfig::DHL_PAKET => new DeliveryTimeDHLPaketPlugin(),
        ];

        return $plugins;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return array
     */
    protected function getTaxCalculationPlugins(Container $container)
    {
        return [];
    }

}
