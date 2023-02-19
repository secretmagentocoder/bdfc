# Mage2 Module ExperiencesDigital Flights

    ``experiencesdigital/module-flights``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
This is for handling Flights API from Navision

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/ExperiencesDigital`
 - Enable the module by running `php bin/magento module:enable ExperiencesDigital_Flights`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require experiencesdigital/module-flights`
 - enable the module by running `php bin/magento module:enable ExperiencesDigital_Flights`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications

 - API Endpoint
	- GET - ExperiencesDigital\Flights\Api\FlightsManagementInterface > ExperiencesDigital\Flights\Model\FlightsManagement

 - Controller
	- frontend > flights/index/index


## Attributes



