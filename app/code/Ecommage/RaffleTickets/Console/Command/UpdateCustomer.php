<?php

namespace Ecommage\RaffleTickets\Console\Command;

use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCustomer extends Command
{
    const FIRST_NAME_DEFAULT = 'Customer';
    const LAST_NAME_DEFAULT = 'Name';
    const DOB_DEFAULT = '18/01/1999';
    const COUNTRY_ID_DEFAULT = 'BH';
    const REGION_ID_DEFAULT = '570';
    const REGION_DEFAULT = 'Muharraq';
    const CITY_DEFAULT = 'Bahrain';
    const POSTCODE_DEFAULT = '1714';
    const STREET_DEFAULT = ['Pickup from Bdfc'];
    const TELEPHONE_DEFAULT = '+97317303030';

    public function __construct
    (
        \Psr\Log\LoggerInterface $logger,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        State $state,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        string $name = null
    )
    {
        $this->logger = $logger;
        $this->regionFactory = $regionFactory;
        $this->addressRepository = $addressRepository;
        $this->addressFactory = $addressFactory;
        $this->state = $state;
        $this->_customerFactory = $customerFactory;
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('ecommage:update:accountCustomer');
        $this->setDescription('This is my console command run in update discount price!');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $output->writeln("Start Update Discount Price For All Products...");
        try {
            foreach ($this->getCustomerCollection() as $item)
            {
                $address = $this->setShipping($item->getEntityId());
                if (empty($item->getDefaultBilling()) && empty($item->getDefaultShipping()) && !empty($address->getId()))
                {
                    $item->setDefaultBilling($address->getId());
                    $item->setDefaultShipping($address->getId());
                    $item->save();
                }
            }
        }catch (\Exception $e){
            $this->logger->error($e->getMessage());
        }
        $output->writeln("Finished!");
    }

    public function getCustomerCollection()
    {
        return $this->_customerFactory->create();
    }

    public function setShipping($customerId)
    {
        $address = $this->addressFactory->create();
        if ($customerId){
            try {
                $address->setFirstname(self::FIRST_NAME_DEFAULT)
                        ->setLastname(self::LAST_NAME_DEFAULT)
                        ->setCountryId(self::COUNTRY_ID_DEFAULT)
                        ->setRegionId(self::REGION_ID_DEFAULT)
                        ->setRegion(self::REGION_DEFAULT)
                        ->setCity(self::CITY_DEFAULT)
                        ->setPostcode(self::POSTCODE_DEFAULT)
                        ->setCustomerId($customerId)
                        ->setStreet(self::STREET_DEFAULT)
                        ->setTelephone(self::TELEPHONE_DEFAULT);
                $address->save();
                 return $address;
            }catch (\Exception $e){
                $this->logger->error($e->getMessage());
            }
        }
    }

}
