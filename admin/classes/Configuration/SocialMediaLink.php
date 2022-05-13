<?php
	namespace Configuration;

	use Template\Links\ExternalLink;
	
	/**
	 * A link to an external website (social media account), with an icon (HAS_IMAGE true by default) and always opens in a new tab
	 */
	class SocialMediaLink extends ExternalLink
	{
		/*~~~~~
		 * setup
		 **/

		// AbstractLink

		/** @var string */
		const PARENT_CLASS = Configuration::class;

		/** @var int */
		const IMAGE_WIDTH = 40;
		const IMAGE_HEIGHT = 40;
	}
