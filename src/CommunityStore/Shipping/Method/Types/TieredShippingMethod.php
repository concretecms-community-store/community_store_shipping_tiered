<?php
namespace Concrete\Package\CommunityStoreShippingTiered\Src\CommunityStore\Shipping\Method\Types;

use Package;
use Core;
use Database;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodTypeMethod;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;
use Concrete\Package\CommunityStore\Src\CommunityStore\Customer\Customer as StoreCustomer;
use Concrete\Package\CommunityStore\Src\CommunityStore\Shipping\Method\ShippingMethodOffer as StoreShippingMethodOffer;

/**
 * @Entity
 * @Table(name="CommunityStoreTieredMethods")
 */
class TieredShippingMethod extends ShippingMethodTypeMethod
{

    /**
     * @Column(type="float")
     */
    protected $minimumAmount;

    /**
     * @Column(type="float")
     */
    protected $maximumAmount;

    /**
     * @Column(type="text",nullable=true)
     */
    protected $countries;


    public function setMinimumAmount($minAmount)
    {
        $this->minimumAmount = $minAmount;
    }
    public function setMaximumAmount($maxAmount)
    {
        $this->maximumAmount = $maxAmount;
    }

    public function getMinimumAmount()
    {
        return $this->minimumAmount;
    }
    public function getMaximumAmount()
    {
        return $this->maximumAmount;
    }

    public function getCountries()
    {
        return $this->countries;
    }

    public function setCountries($countries)
    {
        $this->countries = $countries;
    }

    public function getShippingMethodTypeName() {
        return t('Tiered based shipping');
    }

    public function addMethodTypeMethod($data)
    {
        return $this->addOrUpdate('add', $data);
    }

    public function update($data)
    {
        return $this->addOrUpdate('update', $data);
    }

    private function addOrUpdate($type, $data)
    {
        if ($type == "update") {
            $sm = $this;
        } else {
            $sm = new self();
        }
        // do any saves here
        //$sm->setRate($data['rate']);

        $sm->setMinimumAmount($data['minimumAmount']);
        $sm->setMaximumAmount($data['maximumAmount']);



        if (!empty($data['countries'])) {
            $countriesSelected = implode(',', $data['countries']);
            $sm->setCountries($countriesSelected);
        } else {
            $sm->setCountries('');
        }


        $em = Database::connection()->getEntityManager();
        $em->persist($sm);
        $em->flush();

        $rates = array();

        $count = 0;

        if (!empty($this->post('weight'))) {
            foreach ($this->post('weight') as $weight) {

                if ($weight != '') {
                    $rates[] = array('weight' => $weight, 'label' => $this->post('label')[$count], 'rate' => $this->post('rate')[$count], );
                }
                $count++;
            }
        }

        \Config::save('community_store_shipping_tiered.' . 'shipping_method_' . $sm->getShippingMethodTypeMethodID() , $rates);

        return $sm;
    }

    public function dashboardForm($shippingMethod = null)
    {
        $this->set('form', Core::make("helper/form"));
        $this->set('smt', $this);
        $this->set('countryList', Core::make('helper/lists/countries')->getCountries());

        if (is_object($shippingMethod)) {
            $smtm = $shippingMethod->getShippingMethodTypeMethod();
            $rates = \Config::get('community_store_shipping_tiered.' . 'shipping_method_' . $smtm->getShippingMethodTypeMethodID());
        } else {
            $smtm = new self();
            $rates = array();
        }

        $this->set("smtm", $smtm);
        $this->set('rates', $rates);
    }


    public function isEligible()
    {
        $customer = new StoreCustomer();
        $custCountry = $customer->getValue('shipping_address')->country;
        $countries = explode(',',$this->getCountries());

        if (in_array($custCountry, $countries) ) {
            $subtotal = StoreCalculator::getSubTotal();
            $max = $this->getMaximumAmount();

            if ($max != 0) {
                if ($subtotal >= $this->getMinimumAmount() && $subtotal <= $this->getMaximumAmount()) {

                    return true;
                } else {
                    return false;
                }
            } elseif ($subtotal >= $this->getMinimumAmount()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function findMatch() {
        $customer = new StoreCustomer();

        $shippingpostcode = $customer->getAddressValue('shipping_address', 'postal_code');
        $weight = StoreCart::getCartWeight();

        $allrates = \Config::get('community_store_shipping_tiered.' . 'shipping_method_' . $this->getShippingMethodTypeMethodID());

        $finalrate = false;
        $finallabel = '';

        $sortedList = array();


        foreach($allrates as $rate) {
            $sortedList[$rate['weight']] = $rate;
        }

        ksort($sortedList);


        foreach($sortedList as $rateweight=>$rate) {
            if ($weight >= $rateweight) {
                $finalrate = $rate['rate'];
                $finallabel =  $rate['label'];
            }
        }

        return array('rate'=>$finalrate, 'label'=>$finallabel);
    }


    public function getOffers() {
        $offers = array();

        $rate = $this->findMatch();

        if ($rate['rate'] != '') {
            $offer = new StoreShippingMethodOffer();
            $offer->setRate($rate['rate']);
            $offer->setOfferLabel($rate['label']);
            $offers[] = $offer;
        }

        return $offers;
    }


}
