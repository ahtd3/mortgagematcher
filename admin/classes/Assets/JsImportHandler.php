<?php
	namespace Assets;
	
	use Kwf_SourceMaps_SourceMap;
	
	/**
	 * Handles generating timestamps for JS imports, so the user doesn't need to force refresh
	 */
	class JsImportHandler
	{
		const CACHE_FOLDER = DOC_ROOT . "/resources/cache/scripts/";
		const CACHE_PATH = self::CACHE_FOLDER . "import-cache.json";
		const IMPORT_REGEX = "/import (.*? from )?('[^']*?'|\"[^\"]*?\");?/ms";
		
		private static ?object $cache = null;
		private static array $checkedPaths = [];
		private static bool $needsSave = false;
		private static array $timestamps = [];
		
		/**
		 * Gets the absolute path to a file, with "." and ".." resolved
		 * @param	string	$originPath		The path to the file this file is imported relative to
		 * @param	string	$importPath		The relative/absolute path to the file being imported
		 * @return	string					The absolute path to the file. If the file doesn't exist, "." and ".." will be retained
		 */
		private static function realImportPathFrom(string $originPath, string $importPath): string
		{
			if($importPath[0] === "/")
			{
				return DOC_ROOT . $importPath;
			}
			else
			{
				$root = dirname($originPath);
				return realPath($root . "/" . $importPath) ?: ($root . "/" . $importPath);
			}
		}
		
		/**
		 * Grabs the cache object
		 * @return	object	The cache object
		 */
		private static function getCache(): object
		{
			if(self::$cache !== null)
			{
				return self::$cache;
			}
			else if(file_exists(static::CACHE_PATH))
			{
				self::$cache = (object) json_decode(file_get_contents(static::CACHE_PATH), true);
			}
			else
			{
				self::$cache = (object)
				[
					"creationTime" => time(),
					"dependencies" => []
				];
			}
			
			register_shutdown_function(function()
			{
				if(self::$needsSave)
				{
					file_put_contents(static::CACHE_PATH, json_encode(self::$cache));
				}
			});
			
			return self::$cache;
		}
		
		/**
		 * Gets the paths to imported files from the cache
		 * @param	string			$path	The path to the file which might be importing other files
		 * @return	string[]|null			Either an array of imported paths, or null if it's not in the cache
		 */
		private static function getImportsFromCache(string $path): ?array
		{
			$cache = self::getCache();
			
			if(($cache->creationTime ?? null) === null)
			{
				$cache->creationTime = time();
			}
			
			if(!in_array($path, self::$checkedPaths))
			{
				$fileEditTime = self::getTimestampFor($path);
				
				if($fileEditTime < $cache->creationTime)
				{
					unset($cache->dependencies[$path]);
					self::$needsSave = true;
				}
			}
			
			if(!is_array($cache->dependencies[$path] ?? []))
			{
				return null;
			}
			
			return $cache->dependencies[$path] ?? null;
		}
		
		/**
		 * Gets the paths to imported files from parsing the file
		 * @param	string		$path	The path to the file which might be importing other files
		 * @return	string[]			An array of imported paths
		 */
		private static function getImportsFromFile(string $path): array
		{
			$pattern = static::IMPORT_REGEX;
			
			preg_match_all($pattern, file_get_contents($path) ?: "", $matches, PREG_SET_ORDER);
			$paths = [];
			
			foreach($matches as $match)
			{
				$relativePath = trim($match[2], "'\"");
				$paths[] = self::realImportPathFrom($path, $relativePath);
			}
			
			self::getCache()->dependencies[$path] = $paths;
			self::$needsSave = true;
			
			return $paths;
		}
		
		/**
		 * Gets the paths to imported files from the cache, or directly from the file if they haven't been cached
		 * @param	string		$path	The path to the file which might be importing other files
		 * @return	string[]			An array of imported paths
		 */
		private static function getImportsFromCacheOrFile(string $path): array
		{
			return self::getImportsFromCache($path) ?? self::getImportsFromFile($path);
		}
		
		/**
		 * Gets the timestamp for a file
		 * @param	string	$path	The path to the file to get the timestamp for
		 * @return	int				The timestamp for that file
		 */
		private static function getTimestampFor(string $path): int
		{
			return self::$timestamps[$path] ?? @filemtime($path) ?: 0;
		}
		
		/**
		 * Gets the latest timestamp for a file and for any files that are (recursively) imported by that file
		 * @param	string	$path	The path to the file to get the timestamp for
		 * @return	int				The highest timestamp for that file and its imports
		 */
		private static function getFinalTimestampFor(string $path): int
		{
			$checkedPaths = [];
			$uncheckedPaths = [$path];
			$highestTimestamp = max(self::getTimestampFor(__FILE__), self::getTimestampFor($path));
			
			while(count($uncheckedPaths) > 0)
			{
				$path = array_shift($uncheckedPaths);
				$checkedPaths[] = $path;
				
				if(!file_exists($path))
				{
					continue;
				}
				
				$highestTimestamp = max($highestTimestamp, self::getTimestampFor($path));
				$paths = self::getImportsFromCacheOrFile($path);
				
				foreach($paths as $possiblePath)
				{
					if(!in_array($possiblePath, $checkedPaths))
					{
						$uncheckedPaths[] = $possiblePath;
					}
				}
			}
			
			return $highestTimestamp;
		}
		
		/**
		 * Gets the path to the cache file for a specific file
		 * @param	string	$path	The path to the original file
		 * @return	string			The path to the cached file
		 */
		private static function getCachePath(string $path): string
		{
			return static::CACHE_FOLDER . substr(md5($path), 0, 12) . "-" . basename($path);
		}
		
		/**
		 * Gets a URL for a file from a path
		 * @param	string	$path	The path to the file
		 * @return	string			The URL to that file
		 */
		private static function getUrlFrom(string $path): string
		{
			return str_replace(DOC_ROOT, "", $path);
		}
		
		/**
		 * Generates a cached file from an existing file, replacing any import URLs with equivalent ones to that URL and timestamp
		 * @param	string	$path	The path to the file to generate from
		 */
		private static function generateFileFrom(string $path)
		{
			if(!file_exists($path))
			{
				return;
			}
			
			$pattern = static::IMPORT_REGEX;
			
			$result = preg_replace_callback($pattern, function(array $match) use($path)
			{
				$relativePath = trim($match[2], "'\"");
				$actualPath = self::realImportPathFrom($path, $relativePath);
				$cachePath = self::getCachePath($actualPath);
				$timestamp = self::getFinalTimestampFor($actualPath);
				$cacheUrl = self::getUrlFrom($cachePath);
				
				return "import {$match[1]}\"{$cacheUrl}?v={$timestamp}\";";
			}, file_get_contents($path) ?: "");
			
			$lines = substr_count($result, "\n") + 1;
			$originalSource = self::getUrlFrom($path);
			
			$map = Kwf_SourceMaps_SourceMap::createEmptyMap($result);
			for($i = 1; $i <= $lines; $i += 1) $map->addMapping($i, 1, $i, 1, $originalSource);
			$result = $map->getFileContentsInlineMap();
			
			$cachePath = self::getCachePath($path);
			
			file_put_contents($cachePath, $result);
			self::$timestamps[$cachePath] = time();
		}
		
		/**
		 * Checks if it's necessary to generate a new cache file, and generates one if so. Also (recursively) generates cache files for any imported files if necessary.
		 * @param	string	$path	The path to the file to generate from
		 */
		private static function generateFileIfNecessaryFrom(string $path)
		{
			$cachePath = self::getCachePath($path);
			$highestTimestamp = self::getFinalTimestampFor($path);
			
			if(!file_exists($cachePath) || self::getTimestampFor($cachePath) < $highestTimestamp)
			{
				self::generateFileFrom($path);
				
				foreach(self::getImportsFromCacheOrFile($path) as $importPath)
				{
					self::generateFileIfNecessaryFrom($importPath);
				}
			}
		}
		
		/**
		 * Generates new cache files if necessary, and retrieves the URL to the top level cache file
		 * @param	string	$path	The path to the top level file
		 * @return	string			The URL for the top level cache file
		 */
		public static function getCacheUrlFor(string $path): string
		{
			self::generateFileIfNecessaryFrom($path);
			
			return self::getUrlFrom(self::getCachePath($path)) . "?v=" . self::getFinalTimestampFor($path);
		}
	}