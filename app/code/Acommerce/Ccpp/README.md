# README #
Acommerce_Ccpp

### What is this repository for? ###

* Module to add payment method 2C2P
* [More detail](https://bitbucket.org/acommerce/magento2-extension-acommerce-ccpp/)

### Module Dependency ###

* acommerce/all

### Menu and Needed Configurations ###

* Menu: Stores > Sales > Payment Methods > 2C2P

### Changelog ###
* version 2.3.3
	- Bug fixed implode function
* version 2.3.2
	- Bug fixed (Need to update to this version at least)
* version 2.3.1
	- New Console/Command to check OrderID from 2c2p
* version 2.3.0
	- Add new QR Code Payment
* version 2.2.3
	- Bug fixed
* version 2.2.2
	- Add Payment Channels Option Config
* version 2.2.1
    - fixed CSRF form key invalid
* version 2.2.0
    - Info.php fixing instance of
* version 2.1.0
    - Support multi payment channel (Installment, Over the couter)
    - Allow to config promotion for sending to 2C2p (config from backend)
    - Allow to cancel order when payment failed (config from backend)
    - Store 2c2p response into magento and display values in sales order backend.
* version 2.0.5
    - Added option in the backend for sending email after order status is changed to payment received
* version 2.0.4
    - Support proxy in Curl
    - Require acommerce/curlproxy
    - Move to vendor
* version 2.0.3
    - Fix send unrelated code to 2c2p
* version 2.0.2
    - Sending order promocode to 2c2p
* version 2.0.1
    - Add new feature for inquiry pending transaction from 2c2p
* version 2.0.0


