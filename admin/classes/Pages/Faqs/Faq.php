<?php
	namespace Pages\Faqs;

	use Core\Attributes\Data;
	use Core\Attributes\IsSearchable;
	use Core\Attributes\LinkTo;
	use Core\Elements\Editor;
	use Core\Elements\Text;
	use Core\Generator;
	use Search\SearchResult;
	use Search\SearchResultGenerator;
	
	/**
	 * A simple module for a question and answer
	 *
	 */
	class Faq extends Generator implements SearchResultGenerator
	{
		/*~~~~~
		 * setup
		 **/
		// Entity / Generator
		const TABLE = 'faqs';
		const ID_FIELD = 'faq_id';
		const SINGULAR = 'FAQ';
		const PLURAL = 'FAQs';
		const HAS_POSITION = true;
		const HAS_ACTIVE = true;
		const LABEL_PROPERTY = 'title';
		const PARENT_PROPERTY = 'page';

		// Faq
		public bool $active = true;
		
		#[Data("title"), IsSearchable]
		public string $title = "";
		
		#[Data("text", "html"), IsSearchable]
		public string $text = "";
		
		#[LinkTo("page_id")]
		public FaqPage $page;

		/*~~~~~
		 * non-static methods excluding interface methods
		 **/
		/**
		 * Sets the Form Elements for this object
		 */
		 protected function elements()
		{
			parent::elements();

			$this->addElement(new Text('title', 'Question'));
			$this->addElement(new Editor('text', 'Answer'));
		}

		/*~~~~~
		 * Interface methods
		 **/

		// region SearchResultGenerator

		/**
		 * create a SearchResult containing relevant information for the object
		 *
		 * @return SearchResult usually for output to a page alongside other SearchResults
		 */
		public function generateSearchResult()
		{
			return (new SearchResult($this->page->getNavPath(), $this->title, $this->text))->setRelevance($this->relevance);
		}

		// endregion
	}
