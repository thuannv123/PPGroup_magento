1.0.0
=============
* Features:
    * Base functionality feeds builder/parser 
    * Support of Google Feeds

1.1.0
==============
* Bugfixes:
    * Remove const visibility scope to support older PHP versions
    
1.1.1
==============
* Bugfixes:
    * Default export attribute job_id was changed
    
1.1.2
==============
* Bugfixes:
    * Remove temporary file once job has been completed
    * Hide preview button when feeds format was deselected

2.0.0
=============
* Features:
    * Added mapping category
    * Get category
    * Categories cache save
    * Replace id on category name for facebook
    * Added product id
    * Added modifier str_replace for mapped feed category

2.1.0
=============
* Features:
    * Amazon inventory feed
    * Amazon marketplace feed
    * Amazon ads feed
    * Ebay shopping feed
    * Yandex feed

* Bugs:
    * Default data for job run, if not possible to fetch attributes
    * Fix bug if no mapping is set


2.2.0
=============
* Bugs:
    * Fix a pattern when attribute code contains a digit
    * Compatibility with magento 2.4.4 and php8.1
