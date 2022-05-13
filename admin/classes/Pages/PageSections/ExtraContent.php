<?php
	namespace Pages\PageSections;
	
	use Core\Attributes\Data;
	use Core\Elements\Editor;
	use Core\Elements\Text;
	use Core\Generator;
	
	/**
	 * An extra piece of content to be displayed after some other content
	 */
	class ExtraContent extends Generator implements PageSection
	{
		const TABLE = "extra_contents";
		const ID_FIELD = "extra_content_id";
		
		#[Data("content", "html")]
		public string $content = "";
		
		#[Data('code')]
		public string $code = '';

		protected function elements()
		{
			parent::elements();
			
			$this->addElement(new Editor("content", "Content"));
			$this->addElement(new Text("code", "Class"));
		}
		
		//region PageSection
		
		public function getTemplateLocation(): string
		{
			return "pages/sections/extra-content.twig";
		}
		
		//endregion
	}