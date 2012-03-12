<?php
class ShopgateCustomer extends ShopgateObject {
	private $customer_number;
	private $customer_group;
	private $customer_group_id;
	
	private $first_name;
	private $last_name;

 	private $gender;
	private $birthday;
	
	private $phone;
	private $mobile;
	private $mail;
	
	private $newsletter_subscription;
	
	private $addresses;


	/**
	 * @param array $data An array containing the customer's details as defined in the wiki.
	 * @see http://wiki.shopgate.com/Shopgate_Plugin_API_get_customer/en
	 */
	public function __construct($data = null) {
		$methods = get_class_methods($this);
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				$setter = 'set'.camelize($key, true);
				if (!in_array($setter, $methods)) {
					throw new ShopgateLibraryException('ShopgateCustomer::__construct(): Unbekanntes Attribut "'.$key.'" übergeben.');
				}
				$this->$setter($value);
			}
		}
	}
	
	
	/**********
	 * Setter *
	 **********/
	
	/**
	 * @param string $value
	 */
	public function setCustomerNumber($value) { $this->customerNumber = $value; }
	
	/**
	 * @param string $value
	 */
	public function setCustomerGroup($value) { $this->customer_group = $value; }
	
	/**
	 * @param id $value
	 * @throws ShopgateLibraryException if a non-integer is passed
	 */
	public function setCustomerGroupId($value) {
		if (is_numeric($value)) {
			$this->customer_group_id = (int) $value;
		} else {
			throw new ShopgateLibraryException("Non-numeric value passed to setCustomerGroupId");
		}
	}

	/**
	 * @param string $value
	 */
	public function setFirstName($value) { $this->first_name = $value; }

	/**
	 * @param string $value
	 */
	public function setLastName($value) { $this->last_name = $value; }

	/**
	 * @param string $value <ul><li>"m" = Male</li><li>"f" = Female</li></ul>
	 * @throws ShopgateLibraryException if a value other than "m" or "f" is passed.
	 */
	public function setGender($value) {
		if (($value === "m") || ($value === "f")) {
			$this->gender = $value;
		} else {
			throw new ShopgateLibraryException("Value passed to setGender is not 'm', nor 'f'.");
		}
	}

	/**
	 * @param string $value Format: yyyy-mm-dd (1983-02-17)
	 * @throws ShopgateLibraryException
	 * @todo Exception werfen
	 */
	public function setBirthday($value) { $this->birthday = $value; }
	
	/**
	 * @param string $value
	 */
	public function setPhone($value) { $this->phone = $value; }
	
	/**
	 * @param string $value
	 */
	public function setMobile($value) { $this->mobile = $value; }
	
	/**
	 * @param string $value
	 * @throws ShopgateLibraryException
	 * @todo Exception werfen
	 */
	public function setMail($value) { $this->mail = $value; }
	
	/**
	 * @param bool $value
	 * @throws ShopgateLibraryException
	 * @todo Exception werfen
	 */
	public function setNewsletterSubscription($value) { $this->newsletter_subscription = $value; }
	
	/**
	 * @param ShopgateAddress[] $value List of customer's addresses.
	 * @throws ShopgateLibraryException
	 * @todo Exception werfen
	 */
	public function setAddresses($value) {
		$this->addresses = $value;
	}
	
	
	/**********
	 * Getter *
	 **********/
	
	/**
	 * @return string
	 */
	public function getCustomerNumber() { return $this->customerNumber; }
	
	/**
	 * @return string
	 */
	public function getCustomerGroup() { return $this->customer_group; }
	
	/**
	 * @return int
	 */
	public function getCustomerGroupId() { return (int) $this->customer_group_id; }

	/**
	 * @return string
	 */
	public function getFirstName() { return $this->first_name; }

	/**
	 * @return string
	 */
	public function getLastName() { return $this->last_name; }

	/**
	 * @return string <ul><li>"m" = Male</li><li>"f" = Female</li></ul>
	 */
	public function getGender() { return $this->gender; }

	/**
	 * @return string Format: yyyy-mm-dd (1983-02-17)
	 */
	public function getBirthday() { return $this->birthday; }
	
	/**
	 * @return string
	 */
	public function getPhone() { return $this->phone; }
	
	/**
	 * @return string
	 */
	public function getMobile() { return $this->mobile; }
	
	/**
	 * @return string
	 */
	public function getMail() { return $this->mail; }
	
	/**
	 * @return bool
	 */
	public function getNewsletterSubscription() { return (bool) $this->newsletter_subscription; }
	
	/**
	 * @param int $type <ul><li>ShopgateAddress::BOTH</li><li>ShopgateAddress::INVOICE</li><li>ShopgateAddress::DELIVERY</li></ul>
	 * @return ShopgateAddress[] List of customer's addresses, filtered by $type.
	 */
	public function getAddresses($type = ShopgateAddress::BOTH) {
		$addresses = array();
		
		foreach ($this->addresses as $address)
			if ($address->getAddressType == $type) $addresses[] = $address;
		
		return $addresses;
	}
	
	/**
	 * Returns an array with user's and address data.
	 *
	 * Index 1 contains the ShopgateCustomer object's attributes as associative array.
	 * Index 2 contains a list of associative arrays containing the corresponding ShopgateAddress object's attributes.
	 *
	 * @return mixed[]
	 */
	public function toArray() {
		$attributes = get_object_vars($this);
		$customer = array();
		$addresses = array();
		foreach ($attributes as $attribute => $value) {
			if ($attribute == 'addresses') {
				foreach ($value as $address) {
					$addresses[] = $address->toArray();
				}
			} else {
				$getter = 'get'.$this->camelize($attribute);
				$customer[$attribute] = $this->{$getter}();
			}
		}
		
		return array($customer, $addresses);
	}
}

class ShopgateAddress extends ShopgateObject {
	const INVOICE  = 0x01;
	const DELIVERY = 0x10;
	const BOTH     = 0x11;
	
	private $id;
	private $is_invoice_address;
	private $is_delivery_address;
	
	private $first_name;
	private $last_name;
	
	private $gender;
	private $birthday;
	
	private $company;
	private $street_1;
	private $street_2;
	private $zipcode;
	private $city;
	private $country;
	private $state;
	
	private $phone;
	private $mobile;
	private $mail;
	
	/**
	 * @param array $data An array with the address information.
	 */
	public function __construct($data = null) {
		$methods = get_class_methods($this);
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				if (($key == 'is_invoice_address') || ($key == 'is_delivery_address')) {
					$prefix = '';
					$capitalizeFirst = false;
				} else {
					$prefix = 'get';
					$capitalizeFirst = true;
				}
				
				$setter = $prefix.camelize($key, $capitalizeFirst);
				if (!in_array($setter, $methods)) {
					throw new ShopgateLibraryException('ShopgateCustomer::__construct(): Unbekanntes Attribut "'.$key.'" übergeben.');
				}
				$this->$setter($value);
			}
		}
	}
	
	/**********
	 * Setter *
	 **********/
	
	/**
	 * @param int $value
	 */
	public function setId($value) {
		$this->id = $value;
	}
	
	/**
	* @param int $value ShopgateAddress::BOTH or ShopgateAddress::INVOICE or ShopgateAddress::DELIVERY
	* @throws ShopgateLibraryException
	*/
	public function setAddressType($value) {
		if (
			$value != self::INVOICE &&
			$value != self::DELIVERY &&
			$value != self::BOTH
		) {
			throw new ShopgateLibraryException('ShopgateAddress::setAddressType(): Ungültiger Wert.');
		}
		
		$this->is_invoice_address  = (bool) ($value & self::INVOICE);
		$this->is_delivery_address = (bool) ($value & self::DELIVERY);
	}
	
	/**
	 * @param string $value
	 */
	public function setFirstName($value) {
		$this->first_name = $value;
	}
	
	/**
	 * @param string $value
	 */
		public function setLastName($value) {
		$this->last_name = $value;
	}
	
	/**
	 * @param string $value <ul><li>"m" = Male</li><li>"f" = Female</li></ul>
	 * @throws ShopgateLibraryException
	 */
	public function setGender($value) {
		if (($value != "m") && ($value != "f")) {
			throw new ShopgateLibraryException('ShopgateAddress::setGender(): Ungültiger Wert.');
		}
		
		$this->gender = $value;
	}
	
	/**
	 * @param string $value Format: yyyy-mm-dd (1983-02-17)
	 */
	public function setBirthday($value) {
		$this->birthday = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setCompany($value) {
		$this->company = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setStreet1($value) {
		$this->street_1 = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setStreet2($value) {
		$this->street_2 = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setCity($value) {
		$this->city = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setZipcode($value) {
		$this->zipcode = $value;
	}
	
	/**
	 * Sets the Country
	 *
	 * Format: ISO-3166-1
	 *
	 * Example: <ul><li>DE</li><li>US</li></ul>
	 *
	 * @see http://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
	 * @param string $value Country as ISO-3166-1
	 */
	public function setCountry($value) {
		$this->country = $value;
	}
	
	/**
	 * Sets the state / province
	 *
	 * Format: ISO 3166-2
	 *
	 * Example: <ul><li>DE-HE</li><li>US-NY</li><ul>
	 *
	 * @see http://en.wikipedia.org/wiki/ISO_3166-2#Current_codes
	 * @param string $value State as ISO-3166-2
	 */
	public function setState($value) {
		$this->state = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setPhone($value) {
		$this->phone = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setMobile($value) {
		$this->mobile = $value;
	}
	
	/**
	 * @param string $value
	 */
	public function setMail($value) {
		$this->mail = $value;
	}
	
	
	/**********
	 * Getter *
	 **********/
	
	/**
	 * @return int
	 */
	public function getId() { return (int) $this->id; }
	
	/**
	* @return bool
	*/
	public function isInvoiceAddress() { return (bool) $this->is_invoice_address; }
	
	/**
	 * @return bool
	 */
	public function isDeliveryAddress() { return (bool) $this->is_delivery_address; }
	
	/**
	 * @return int ShopgateAddress::BOTH or ShopgateAddress::INVOICE or ShopgateAddress::DELIVERY
	 */
	public function getAddressType() {
		return (int) (
			($this->isInvoiceAddress()  ? self::INVOICE  : 0) |
			($this->isDeliveryAddress() ? self::DELIVERY : 0)
		);
	}
	
	/**
	 * @return string
	 */
	public function getFirstName() { return $this->first_name; }

	/**
	 * @return string
	 */
	public function getLastName() { return $this->last_name; }

	/**
	 * @return string <ul><li>"m" = Male</li><li>"f" = Female</li></ul>
	 */
	public function getGender() { return $this->gender; }

	/**
	 * @return string Format: yyyy-mm-dd (1983-02-17)
	 */
	public function getBirthday() { return $this->birthday; }
	
	/**
	 * @return string
	 */
	public function getCompany() { return $this->company; }
	
	/**
	 * @return string
	 */
	public function getStreet1() { return $this->street_1; }
	
	/**
	 * @return string
	 */
	public function getStreet2() { return $this->street_2; }
	
	/**
	 * @return string
	 */
	public function getCity() { return $this->city; }
	
	/**
	 * @return string
	 */
	public function getZipcode() { return $this->zipcode; }
	
	/**
	 * Returns the country
	 *
	 * Format: ISO-3166-1
	 *
	 * Example: <ul><li>DE</li><li>US</li></ul>
	 *
	 * @see http://en.wikipedia.org/wiki/ISO_3166-1#Current_codes
	 * @return string Country as ISO-3166-1
	 */
	public function getCountry() { return $this->country; }
	
	/**
	 * Returns the state / province
	 *
	 * Format: ISO 3166-2
	 *
	 * Example: <ul><li>DE-HE</li><li>US-NY</li><ul>
	 *
	 * @see http://en.wikipedia.org/wiki/ISO_3166-2#Current_codes
	 * @return string State as ISO-3166-2
	 */
	public function getState() { return $this->state; }
	
	/**
	 * @return string
	 */
	public function getPhone() { return $this->phone; }
	
	/**
	 * @return string
	 */
	public function getMobile() { return $this->mobile; }
	
	/**
	 * @return string
	 */
	public function getMail() { return $this->mail; }
	
	/**
	 * Returns an array with the address data.
	 *
	 * @return mixed[] The object as an associative array.
	 */
	 public function toArray() {
		$attributes = get_object_vars($this);
		$address = array();
		foreach ($attributes as $key => $value) {
			if (($key == 'is_invoice_address') || ($key == 'is_delivery_address')) {
				$prefix = '';
				$capitalizeFirst = false;
			} else {
				$prefix = 'get';
				$capitalizeFirst = true;
			}
			
			$getter = $prefix.$this->camelize($key, $capitalizeFirst);
			$address[$key] = $this->{$getter}();
		}
	
		return $address;
	}
}