<?php
	namespace Controller;

	use Assets\Script;
	use Cart\Cart;
	use Configuration\Configuration;
	use Pages\Page;
	use Template\Banners\Banner;
	use Users\User;
	
	/**
	 * Handles displaying content to the user
	 */
	abstract class Controller
	{
		/**
		 * Retrieves the location of the template to display to the user
		 * @return	string	The location of the template
		 */
		abstract protected function getTemplateLocation();

		/**
		 * Sets the variables that the template has access to
		 * @return	array	An array of [string => mixed] variables that the template has access to
		 */
		protected function getTemplateVariables()
		{
			$user = User::get();
			$defaultBanner = Configuration::acquire()->defaultBanner;

			return
			[
				"_get" => $_GET,
				"_post" => $_POST,
				"banners" => $defaultBanner->isNull() ? [] : [$defaultBanner],
				"cart" => Cart::get(),
				"config" => Configuration::acquire(),
				"controller" => $this,
				"homePath" => Page::getHomepagePath(),
				"isPopup" => isset($_GET["popup"]),
				"message" => outputMessage(),
				"script" => new Script, // For javascript includes in twig
				"templateDir" => DOC_ROOT . "/theme",
				"user" => $user,
				
				"navItems" => Page::loadAllForMultiple(
				[
					"active" => true,
					"onNav" => true,
					"parent" => null
				]),
			];
		}

		/**
		 * Sets the template variables and loads the template
		 */
		public function output()
		{
			echo Twig::render($this->getTemplateLocation(), $this->getTemplateVariables());
		}

		/**
		 * Handy function to be able to call static methods from twig (twig includes the controller as a variable)
		 *
		 * @param	string	$className 	The namespaced name of the class which has the method
		 * @param	string	$methodName 	What to call
		 * @param	array<int, mixed>	$params 		an array of any parameters the static method might need
		 * @return	mixed					The result of that static call
		 */
		public function callStatic($className, $methodName, ...$params)
		{
			return call_user_func_array([$className, $methodName], $params);
		}

		/**
		 * Handy function to be able to get static constants from twig (twig includes the controller as a variable)
		 *
		 * @param	string	$className 	The namespaced name of the class which has the constant
		 * @param	string	$constName 	What constant to get
		 * @return	mixed				The contents of that constant
		 */
		public function getStaticConst($className, $constName)
		{
			return constant($className . '::' . $constName);
		}
		
		/**
		 * Generates a canonical URL from the current URL
		 * @return	string	The canonical URL
		 */
		public function generateCanonicalUrl()
		{
			$domain = SITE_ROOT;
			$path = "/" . trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/") . "/";
			$path = $path === "//" ? "/" : strtolower($path);
			$query = http_build_query($_GET);
			$query = $query === "" ? "" : "?{$query}";
			
			return "https://{$domain}{$path}{$query}";
		}
	}
