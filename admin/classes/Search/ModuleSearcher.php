<?php
	namespace Search;

	use Configuration\Registry;
	use Exception;
	use Wamania\Snowball\Stemmer\Stemmer;
	use Wamania\Snowball\StemmerFactory;
	
	/**
	 * Searches all Searchable modules
	 */
	class ModuleSearcher
	{
		const STOP_WORDS = ["the", "of", "and", "a", "to", "in", "is", "that", "it", "was", "for", "on", "are", "as", "with", "at", "be", "this", "have", "from", "or", "had", "by", "but", "were", "an", "do", "has", "than", "its"];
		
		private static Stemmer $stemmer;
		
		/** @var SearchResult[] */
		private $results = [];
		
		/**
		 * Retrieves the normalised words from some content
		 * @param	string		$content	The content to get the words from
		 * @return	string[]				The normalised words
		 */
		public static function getNormalisedWords(string $content): array
		{
			$processed = self::processContent($content);
			$result = preg_split("/\\s+/", $processed); // Split on any amount of white space
			
			if(count($result) > 0 && $result[0] === "")
			{
				return [];
			}
			
			$allWords = array_values(array_filter($result, fn(string $word) => !in_array($word, static::STOP_WORDS)));
			return array_map(fn(string $word) => static::getStem($word), $allWords);
		}
		
		/**
		 * Massages the content to be more consistent
		 * @param	string	$content	The content to normalise
		 * @return	string				The normalised content
		 */
		private static function processContent(string $content): string
		{
			// This regex searches for two word-characters separated by an apostrophe, or for non-word characters. Non-word characters get replaced, while the apostrophe is left as-is. This prevents apostrophes in the middle of words from being replaced, while allowing all other words to be replaced.
			return strtolower(preg_replace_callback("/(\\w'\\w)|([^\\w\\d\\s])/", fn(array $match) => ($match[1] !== "" ? $match[0] : ""), strtolower(trim($content))));
		}
		
		/**
		 * Generates the stem for a word
		 * @param	string	$word	The word
		 * @return	string			The stem
		 */
		public static function getStem(string $word): string
		{
			try
			{
				self::$stemmer = self::$stemmer ?? StemmerFactory::create("en");
				return self::$stemmer->stem($word);
			}
			catch(Exception $exception)
			{
				// If something goes wrong, it won't hurt to just use the original form of the word
				return $word;
			}
		}

		/**
		 * Searches all Searchable Entities for a particular term
		 * @param	string	$term		The term to search for
		 * @param 	array	$classes	The classes to search
		 */
		public function __construct(string $term, array $classes = Registry::SEARCHABLE_CLASSES)
		{
			$searchResults = [];

			/** @var string|Searchable|SearchResultGenerator $searchableClass */
			foreach($classes as $searchableClass)
			{
				assert(is_a($searchableClass, Searchable::class, true), "{$searchableClass} must be searchable");

				foreach($searchableClass::search($term) as $object)
				{
					assert($object instanceof SearchResultGenerator, get_class($object) . " must be a search result generator");
					$searchResults[] = $object->generateSearchResult();
				}
			}

			usort($searchResults, function(SearchResult $first, SearchResult $second)
			{
				return $second->relevance <=> $first->relevance;
			});

			$searchResults = array_unique($searchResults);

			$this->results = $searchResults;
		}

		/**
		 * Gets the search results
		 * @return	SearchResult[]	The search results
		 */
		public function getResults()
		{
			return $this->results;
		}
	}