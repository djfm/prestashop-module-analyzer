#PrestaShop Module Analyzer

This is a simple script to parse PrestaShop modules (in the form of .zip archives) and extract the hooks they register and the overrides they install.

We use the data collected by this tool to help us make better software design decisions, e.g. if file `A` is overriden by 20% of modules, then it is a good indicator that file `A` is not doing its job correctly.

## Installation

```bash
git clone https://github.com/djfm/prestashop-module-analyzer
cd prestashop-module-analyzer
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```

## Usage

```bash
php prestashop-module-analyzer/index.php path/to/dir/with/module/zips path/to/report/file.json
```

It will output a file that looks like this:

```json
{
    "freelivery": {
        "moduleName": "freelivery",
        "availableHooks": [
            "updateCarrier",
            "displayShoppingCart"
        ],
        "registeredHooks": [
            "updateCarrier",
            "displayShoppingCart"
        ],
        "overrides": {
            "Carrier": [
                "freelivery_check",
                "getDeliveryPriceByPrice",
                "getDeliveryPriceByWeight"
            ],
            "Cart": [
                "getSummaryDetails",
                "getPackageShippingCost"
            ]
        }
    },
    "zipcodezone": {
        "moduleName": "zipcodezone",
        "availableHooks": [],
        "registeredHooks": [],
        "overrides": {
            "Address": [
                "getZoneById"
            ]
        }
    }
}
```
