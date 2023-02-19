#Sparsh Login With Mobile Number Module

Magento 2 Login With Mobile Number extension provides an option for the customer to log in using a mobile number or email address.

##Support: 
version - 2.3.x, 2.4.x

##How to install Extension

1. Download the archive file.
2. Unzip the file
3. Create a folder [Magento_Root]/app/code/Sparsh/MobileNumberLogin
4. Drop/move the unzipped files to directory '[Magento_Root]/app/code/Sparsh/MobileNumberLogin'

#Enable Extension:
- php bin/magento module:enable Sparsh_MobileNumberLogin
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

#Disable Extension:
- php bin/magento module:disable Sparsh_MobileNumberLogin
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush