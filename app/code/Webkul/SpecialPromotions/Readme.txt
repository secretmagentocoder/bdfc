#Installation

Magento2 SpecialPromotions module installation is very easy, please follow the steps for installation-

1. Unzip the respective extension zip and then move "app" folder (inside "src" folder) into magento root directory.

Run Following Command via terminal 
-----------------------------------
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

now module is properly installed

#User Guide 

Magento2 SpecialPromotions module's working process follow user guide - https://webkul.com/blog/magento2-special-promotions-module/

#Uninstallation

Please follow the steps for uninstallation-

1.  Run Following Command via terminal
    -----------------------------------
    php bin/magento specialpromotions:disable
    php bin/magento setup:di:compile
    php bin/magento setup:static-content:deploy

2. Flush the cache and reindex all.

now module is properly uninstalled.

#Support

Find us our support policy - https://store.webkul.com/support.html/

#Refund

Find us our refund policy - https://store.webkul.com/refund-policy.html/