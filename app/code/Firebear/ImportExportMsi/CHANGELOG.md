1.1.0
=============
* Bugfixes:
    * The export stock source issue was fixed when the mapping feature is used.
    * Export stock source filter issue was fixed.
    * processedEntitiesCount calculate issue was fixed.
    * sku validation was added.
    * stock source qty export for existing products.

1.1.1
=============
* Bugfixes:
    * Fixed composer.json validation issue on Magento marketplace.
    * Composer.json was changed to support Magento 2.3 version.

1.1.2
=============
* Bugfixes:
    * Fixed the skip errors validation strategy issue when importing entities from a console.
    * Fixed issue with the stock sources qty import, when after each import the message 'No data imported' message appeared.

1.1.3
=============
* Featues:
    * Speed improvement.
    * Compatibilities with PHP 8.

1.1.4
=============
* Features:
  * Pickup Location attributes were added to sync.
  * Magento 2.4.6 support.
  * Improve Source Qty import speed.
* Bugfixes:
  * Fix issue when sources are not deleted with `Delete` behavior.
  * Fix the issue with importing integer product SKUs.
  * Fix the problem when import logs were not shown during source qty import. 
  * Prevent default source from being deleted