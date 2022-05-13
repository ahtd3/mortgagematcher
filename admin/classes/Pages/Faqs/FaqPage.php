<?php
	namespace Pages\Faqs;

	use Core\Attributes\LinkFromMultiple;
	use Core\Elements\GeneratorElement;
	use Pages\Page;

	/**
	 * The class file for the FAQ (Frequently Asked Question) class; A simple module for a question and answer
	 *
	 */
	class FaqPage extends Page
	{
		/*~~~~~
		 * Setup
		 **/
		// FaqPage
		#[LinkFromMultiple(Faq::class, "page")]
		public array $faqs;

		/*~~~~~
		 * non-static methods excluding interface methods
		 **/
		/**
		 * Sets the Form Elements for this object
		 */
		protected function elements()
		{
			parent::elements();
			$this->insertTab('FAQs', 'Banners');
			$this->addElement(new GeneratorElement('faqs'), 'FAQs');
		}

		/**
		 * @return Faq[] just the FAQs which are to be displayed to the public
		 */
		public function getVisibleFaqs()
		{
			return filterActive($this->faqs);
		}
	}
