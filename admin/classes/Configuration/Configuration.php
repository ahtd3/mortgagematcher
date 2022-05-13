<?php
	namespace Configuration;
	
	use Admin\AdminNavItem;
	use Admin\AdminNavItemGenerator;
	use Admin\PreviousPageDetails;
	use Core\Attributes\Data;
	use Core\Attributes\ImageValue;
	use Core\Attributes\LinkFromMultiple;
	use Core\Attributes\LinkTo;
	use Core\Elements\Base\ResultElement;
	use Core\Elements\GeneratorElement;
	use Core\Elements\Group;
	use Core\Elements\Html;
	use Core\Elements\ImageElement;
	use Core\Elements\Section;
	use Core\Elements\Text;
	use Core\Elements\Textarea;
	use Core\Elements\Editor;
	use Core\Generator;

	use Files\Image;
	use Payments\BankDeposit;
	use Payments\PaymentGateway;
	use ResizedImage;
	use Template\Banners\DefaultBanner;
	use Template\Banners\FooterBanner;
	use Users\User;
	use Configuration\Advisers;
	/**
	 * Handles global configuration
	 */
	class Configuration extends Generator implements AdminNavItemGenerator
	{
		const TABLE = "configuration";
		const ID_FIELD = "configuration_id";
		const SINGULAR = "configuration";
		const PLURAL = "configuration";
		const LABEL_PROPERTY = "siteName";

		const ENABLE_RECAPTCHA = false;

		// does the site have a default banner (DefaultBanner::class) to be included in Configuration
		const SITE_HAS_DEFAULT_BANNER = true;
		// does the site have a footer banner location (FooterBanner::class) to be included in Configuration
		const SITE_HAS_FOOTER_BANNER = false;
		// Form element label eg 'Testimonials background'
		const FOOTER_BANNER_LABEL = '';

		private static ?Configuration $singleton = null;
		
		#[Data("site_name")]
		public string $siteName = "";
		
		#[Data("phone")]
		public string $phone = "";
		
		#[Data("email")]
		public string $email = "";
		
		#[Data("address")]
		public string $address = "";
		
		#[Data("gst_number")]
		public string $gstNumber = "";
		
		#[Data("bank_details")]
		public string $bankDetails = "";
		
		#[Data("analytics_id")]
		public string $analyticsId = "";
		
		#[Data("tag_manager_id")]
		public string $tagManagerId = "";
		
		#[Data("google_site_verification")]
		public string $googleSiteVerification = "";
		
		#[Data("recaptcha_site_key")]
		public string $recaptchaSiteKey = "";
		
		#[Data("recaptcha_secret")]
		public string $recaptchaSecret = "";
		
		#[Data("free_shipping_threshold")]
		public float $freeShippingThreshold = 0;
		
		#[ImageValue("favicon", DOC_ROOT . "/resources/images/favicon/", 228, 228, ImageValue::CROP)]
		public ?Image $favicon = null;
		
		#[LinkTo("default_banner_id")]
		public DefaultBanner $defaultBanner;
		
		#[LinkTo("footer_banner_id")]
		public FooterBanner $footerBanner;
		
		/** @var SocialMediaLink[] */
		#[LinkFromMultiple(SocialMediaLink::class, "parent")]
		public array $socialMediaLinks;

		// custom field
		#[ImageValue("logo_header", DOC_ROOT . "/resources/images/logo/", 1000, 1000, ImageValue::SCALE)]
		public ?Image $logo_header = null;

		#[Data("get_in_touch")]
		public string $get_in_touch = '';

		/** @var Advisers[] */
		#[LinkFromMultiple(Advisers::class, "parent")]
		public array $advisers;
		// footer 

		#[Data("quick_link", 'html')]
		public string $quick_link = '';

		#[ImageValue("logo_footer", DOC_ROOT . "/resources/images/logo/", 500, 500, ImageValue::SCALE)]
		public ?Image $logo_footer = null;

		#[Data("get_matched_now")]
		public string $get_matched_now = '';

		#[Data("description_site")]
		public string $description_site = "";

		#[Data("footer_section_title")]
		public string $footer_section_title = "";

		#[Data("footer_section_description")]
		public string $footer_section_description = "";

		#[ImageValue("footer_section_bg", DOC_ROOT . "/resources/images/logo/", 1000, 1000, ImageValue::SCALE)]
		public ?Image $footer_section_bg = null;

		#[Data("footer_section_link")]
		public string $footer_section_link = "";

		#[Data("footer_section_name_link")]
		public string $footer_section_name_link = "";

		/**
		 * Gets the Configuration, or, if it doesn't exist, creates it
		 * @return	Configuration	The configuration object
		 */
		public static function acquire()
		{
			if(self::$singleton === null)
			{
				$query = "SELECT ~PROPERTIES "
					   . "FROM ~TABLE "
					   . "LIMIT 1";

				self::$singleton = static::makeOne($query, []);

				if(self::$singleton->isNull())
				{
					self::$singleton = new static();
					self::$singleton->save();
				}
			}

			return self::$singleton;
		}

		/**
		 * Any attempt to get this object should return the singleton
		 * @param	array			$values		Unused
		 * @return	Configuration				The Configuration object
		 */
		public static function loadForMultiple(array $values)
		{
			return static::acquire();
		}

		/**
		 * Any attempt to get multiple of this object should return the singleton in an array
		 * @param	array			$values		Unused
		 * @param	bool[]			$orderBy	Unused
		 * @return	Configuration[]				An array containing the single Configuration object
		 */
		public static function loadAllForMultiple(array $values, array $orderBy = [])
		{
			return [static::acquire()];
		}
		
		public function getPreviousPageDetails(): ?PreviousPageDetails
		{
			return null;
		}
		
		/**
		 * Gets the path that the user will be sent to when they click the cancel button
		 * @return	string|null	The path to exit to
		 */
		public function previousPageLink(): ?string
		{
			return Registry::getDefaultView();
		}

		/**
		 * Whether a specific user can create a new object of this type
		 * @param	User	$user	The user to check
		 * @return	bool			Whether that user can add a new object
		 */
		public static function canAdd(User $user): bool
		{
			return false;
		}

		/**
		 * Gets the appropriate site email address depending on if the site is a dev site or not
		 *
		 *	@return string 	email address
	   	 * 		- send emails for site visitors (eg confirmations) from this address
	   	 *		- send email for the site owner (eg from contact forms) to this address unless otherwise
	   	 *			defined
		 */
		public static function getAdminEmail()
		{
			return (IS_DEV_SITE) ? Registry::DEV_EMAIL : static::acquire()->email;
		}

		/**
		 * Gets the site name so we can just call this menthod instead of acquire()ing everywhere we need it
		 *
		 * @return string the site name
		 */
		public static function getSiteName()
		{
			return static::acquire()->siteName;
		}

		/**
		 * Preserving USER_NOTIFICATIONS_ADDRESS until the new users stuff gets here
		 *
		 * @return string USER_NOTIFICATIONS_ADDRESS email address to send notifications of details changes
	   	 *		(not addresses at present) when a user updates their own details. Set to '' to disable these
	   	 *		notifications
		*/
		public static function getUserNotificationEmail()
		{
			return static::getAdminEmail();
		}

		/**
		 * Sets the Form Elements for this object
		 */
		protected function elements()
		{
			$this->addElement((new Text('siteName', 'Site name'))->setDescription("Appears in template locations and emails")->addValidation(ResultElement::REQUIRED), 'Contact Details');
			$this->addElement(new Text('phone', 'Phone'), 'Contact Details');
			$this->addElement((new Text('email', 'Email'))->addValidation(ResultElement::REQUIRED), 'Contact Details');
			$this->addElement(new Textarea('address', 'Address'), 'Contact Details');
			$this->addElement(new GeneratorElement('socialMediaLinks', 'Social media links'), 'Contact Details');
			$this->addElement(new ImageElement("favicon", "Favicon"), "Contact Details");

			// note: GeneratorElement doesn't suppoort setHint() or setDescription()
			if(static::SITE_HAS_DEFAULT_BANNER)
			{
				$this->addElement(new GeneratorElement('defaultBanner', 'Default banner'), 'Template');
				$this->addElement(new Html('bannerNote', 'Default banner is used on pages which do not have an individual banner uploaded.'), 'Template');
			}

			if(static::SITE_HAS_FOOTER_BANNER)
			{
				$this->addElement(new GeneratorElement('footerBanner', static::FOOTER_BANNER_LABEL), 'Template');
			}

			if (Registry::ORDERS || Registry::BILL_PAYMENTS)
			{
				if(PaymentGateway::CLASSES[BankDeposit::class] ?? false)
				{
					$this->addElement(new Text('bankDetails', 'Bank account details'), 'Billing');
				}

				$this->addElement(new Text('gstNumber', 'GST Number'), 'Billing');
			}

			$this->addElement(new Text('analyticsId', 'Google Analytics ID'), '3rd Party Integrations');
			$this->addElement(new Text('tagManagerId', 'Google Tag Manager ID'), '3rd Party Integrations');
			$this->addElement(new Text('googleSiteVerification', 'Google Site Verification'), '3rd Party Integrations');

			if(static::ENABLE_RECAPTCHA)
			{
				$recaptchaGroup = (new Group("recaptcha"))->addClass("columns");
				$recaptchaGroup->add(new Text('recaptchaSiteKey', 'Recaptcha Site Key'));
				$recaptchaGroup->add(new Text('recaptchaSecret', 'Recaptcha Secret'));
				$this->addElement($recaptchaGroup, "3rd Party Integrations");
			}

			// custom field

			$this->addElement(new ImageElement('logo_header', 'logo_header'), 'Theme');
			$this->addElement(new Text('get_in_touch', 'get_in_touch'), 'Theme');
			$this->addElement(new Editor('quick_link', 'quick_link'), 'Theme');
			$this->addElement(new ImageElement('logo_footer', 'logo_footer'), 'Theme');
			$this->addElement(new Text('get_matched_now', 'get_matched_now'), 'Theme');
			$this->addElement(new Textarea('description_site', 'description_site'), 'Theme');

			$this->addElement(new Text('footer_section_title', 'footer_section_title'), 'footer site');
			$this->addElement(new Textarea('footer_section_description', 'footer_section_description'), 'footer site');
			$this->addElement(new ImageElement('footer_section_bg', 'footer_section_bg'), 'footer site');
			$this->addElement(new Text('footer_section_link', 'footer_section_link'), 'footer site');
			$this->addElement(new Text('footer_section_name_link', 'footer_section_name_link'), 'footer site');
			$this->addElement(new GeneratorElement('advisers', 'Advisers'), 'Advisers');
		}

		/**
		 * Same as in Generator, but without a return link
		 *
		 * @return	string	The HTML for the html above the form
		 */
		 public function generateEditFormHeading()
 		{
 			$labelProperty = static::LABEL_PROPERTY;

 			$html = "";
 			$html .= "<h1>\n";
 				$html .= "Edit " . ($labelProperty === "id" ? ucfirst(static::SINGULAR) : $this->$labelProperty) . "\n";
 			$html .= "</h1>\n";

 			return $html;
 		}

		/**
		 * Returns only digits of a phone number
		 * @param	string	$phone	The non processed phone number
		 * @return	string			The digits of the phone number
		 */
		public static function getDigits($phone)
		{
			return preg_replace("/[^0-9]/", "", $phone);
		}

		/**
		 * Converts a phone number to use the international format (for New Zealand)
		 * @param	string	$str	the phone number to format
		 * @return	string			the phone number in international format
		 */
		public static function formatAsInternationalPhone($str)
		{
			if(strpos($str,'+') === 0)
			{
				// already in international format
				$return = '+'.preg_replace('/[^\d]/','',$str);
			}
			else
			{
				$return = '+64'.ltrim(preg_replace('/[^\d]/','',$str),0);
			}

			return $return;
		}

		/**
		 * For update messages
		 *
		 * @deprecated with addition of siteName
		 *
		 * @return	string	The word "Configuration"
		 */
		public function get_label()
		{
			return "Configuration";
		}

		/**
		 * @return SocialMediaLink[] only active links for display
		 */
		public function getVisibleSocialMediaLinks()
		{
			return filterActive($this->socialMediaLinks);
		}

		/**
		 * Gets the phone number digits
		 * @return	string	The phone number with all non-digit characters stripped out
		 */
		public function getPhoneDigits()
		{
			return static::getDigits($this->phone);
		}

		/**
		 * Retrieves the link to this Generator's table
		 * @return	string	The link to the table
		 */
		public static function getAdminNavLink()
		{
			return static::acquire()->getEditLink();
		}

		/**
		 * Get a google map for the configuration address
		 *
		 * @param Bool	$useBusinessName	If true the site name is prepended to the address,
		 * 		 and is then used as the label for the map pin.
		 *
		 * @return String 	html for an embeded map
		 */
		public function getMap($useBusinessName = false)
		{
			return static::makeMap($this->address, $useBusinessName);
		}

		/**
		 * Gets a favicon of a specific size
		 * @param	int		$size	The size of favicon to get
		 */
		public function getFavicon(int $size)
		{
			return ResizedImage::crop($this->favicon, $size, $size);
		}

		/**
		 * Get a google map for an address
		 * @param	string	$address			Address to get a map for
		 * @param	bool	$useBusinessName	Whether to add the site name to the start of the address. This is unreliable and mostly requires them to have a business listing on Google.
		 * @return	string						HTML for the embedded map
		 */
		public static function makeMap($address = '', $useBusinessName = false)
		{
			if($address !== "")
			{
				$fullAddress = "";

				if($useBusinessName)
				{
					$fullAddress .= static::getSiteName() . ", ";
				}

				// Remove carriage returns, and replace new lines with commas, which are typically preferred by Google
				$fullAddress .= str_replace(["\r", "\n"],["", ", "],$address);

				$url = 'https://maps.google.com/maps?hl=en&amp;q=' . rawurlencode($fullAddress) . "&amp;output=embed";
				return '<iframe style="border:0" src="' . $url . '" allowfullscreen="allowfullscreen"></iframe>';
			}
			else
			{
				return "";
			}
		}

		//region AdminNavItemGenerator

		/**
		 * Gets the nav item for this class
		 * @return    AdminNavItem        The admin nav item for this class
		 */
		public static function getAdminNavItem()
		{
			return new AdminNavItem(static::getAdminNavLink(), "Configuration", [static::class], Registry::CONFIGURATION);
		}

		//endregion
	}