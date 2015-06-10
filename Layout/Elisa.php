<?php

/**
 * Elisa Template Engine
 * 
 * @category  Template Management
 * @package   Elisa
 * @author    Ahmet ATAY <ahmet.atay@hotmail.com>
 * @copyright 2015
 * @license   http://opensource.org/licenses/MIT MIT license
 * @link      http://www.atayahmet.com
 */
class Elisa {

	protected $separator = DIRECTORY_SEPARATOR;
	/**
     * Filesystem class
     * 
     * @var object
     */
	protected $filesystem;
		
	/**
     * Raw data variable
     * 
     * @var string
     */
	protected $data;
	
	/**
     * Converted data storage path
     * 
     * @var string
     */
	protected $storage;
	
	/**
     * Raw data path
     * 
     * @var string
     */
	protected $view;

	/**
     * Default template file extension
     * 
     * @var string
     */
	protected $ext = '.html';

	/**
     * Default master file
     * 
     * @var string
     */
	protected $master = 'master';

	protected $caching = true;
	protected $aliases = [];
	protected $reserved = ['extend', 'append'];

	/**
     * Elisa Regex patterns
     * 
     * @var string
     */
	protected $tags = [
		'\{(\s*)(if)(\s*)\((.*?)\)(\s*)\}' => '<?php $2($4): ?>',
		'\{(\s*)(elseif)(\s*)\((.*?)\)(\s*)\}' => '<?php $2($4): ?>',
		'\{(\s*)(endif)(\s*)\}' => '<?php $2; ?>',
		'\{(\s*)(else)(\s*)\}' => '<?php $2: ?>',
		'\{(\s*)(for)\((.*?)\)(\s*)\}' => '<?php $2($3): ?>',
		'\{(\s*)(endfor)(\s*)\}' => '<?php $2; ?>',
		'\{(\s*)(foreach)\((.*?)\)(\s*)\}' => '<?php $2($3): ?>',
		'\{(\s*)(endforeach)(\s*)\}' => '<?php $2; ?>',
		'\{(\s*)(while)\((.*?)\)(\s*)\}' => '<?php $2($3): ?>',
		'\{(\s*)(endwhile)(\s*)\}' => '<?php $2; ?>',
		'\{(\s*)(endeach)(\s*)\}' => '<?php $2; ?>',
		'\{(\s*)\$+(.*?)(\s?)\}' => '<?php $$2; ?>',
		'\{(\s*)(([a-z_]+)\((.*?)\))\}' => '<?php $2;?>',
		'\{(\s*)\!(.*?)\}' => '<?php echo $2; ?>'
	];

	/**
     * Raw data render
     *
     * @param $tpl string
     * @param $attr array
     * 
     * @return string
     */
	public function render($tpl)
	{
		$this->data = $master = $this->setAliases($tpl);

		foreach($this->tags as $pattern => $tag) {

			preg_match_all('/' . $pattern . '/',$tpl, $matches);
			
			$filtredMatches = $this->filterMutliArray($matches);

			if(isset($filtredMatches[0]) && is_array($filtredMatches[0])) {

				foreach($filtredMatches[0] as $matchedTag) {
					$replacedTag = preg_replace(
						[
							'/'.$pattern.'/i'
						],
						[
							$tag
						], $matchedTag);
					
					$this->data  = preg_replace('/' . preg_quote($matchedTag) . '/', $replacedTag, $this->data);
				}
			}
		}
		return $this->data;
	}

	protected function extendFiles($stream)
	{
		$extendPattern = '\{\s*\@extend\([\'\"]?(.*?)[\'\"]?\)\s*\}';
		preg_match_all('/' . $extendPattern . '/', $stream, $matches);

		if(isset($matches[1]) && is_array($matches[1])) {

			foreach ($matches[1] as $key => $path) {
				
				$filePath = $this->setSeparator($path);
				$fullPath = $this->setFullViewPath($filePath);

				if(! file_exists($fullPath)) {
					throw new Exception($fullPath . ' not existsing');
				}

				$stream = str_replace($matches[0][$key], $this->render(file_get_contents($fullPath)), $stream);

				preg_match_all('/' . $extendPattern . '/', $stream, $nestedMatches);

				if(isset($nestedMatches[1]) && is_array($nestedMatches[1])) {
					$stream = $this->extendFiles($stream);
				}

			}
		}

		return $stream;
	}

	protected function setAliases($stream)
	{
		preg_match_all('/' . sprintf('(\{\!*\s*\@*(%s)\((.*?)\)\s*\})', implode(array_flip($this->aliases), '|')) . '/i', $stream, $matches);

		exit(var_dump($matches));
	}

	/**
     * Replace dot from to default separator
     *
     * @param $path string
     *
     * @return string
     */
	protected function setSeparator($path)
	{
		return preg_replace('/\./', $this->separator, $path);
	}

	/**
     * Make the view file path
     *
     * @param $filePath string
     *
     * @return string
     */
	protected function setFullViewPath($filePath)
	{
		return preg_replace('/\/\//', $this->separator, $this->storage . '/views/' . $filePath) . $this->ext;
	}

	/**
     * Make the cache file path
     *
     * @param $filePath string
     *
     * @return string
     */
	protected function setFullCachePath($cacheFileName)
	{
		return $this->storage . '/cache/' . $cacheFileName . '.php';
	}

	/**
     * controller of multi process
     *
     * @param $path string
     * @param $params array
     * @param $caching bool
     *
     * @return string
     */
	protected function controller($path, array $params = [])
	{
		$filePath = $this->setSeparator($path);
		$fullPath = $this->setFullViewPath($filePath);

		if(! file_exists($fullPath)) {
			throw new Exception($fullPath . ' not existsing');
		}

		$cacheFileName = md5($fullPath);
		$cacheFullPath = $this->setFullCachePath($cacheFileName);

		if($this->caching && file_exists($cacheFullPath) === true) {
			$this->writeToCacheIfExpired($cacheFullPath, $fullPath);
			return $this->includeCache($cacheFullPath, $params);
		}else{
			$rawData = file_get_contents($fullPath);
			return $this->render($rawData);
		}
	}

	/**
     * Write to cache file if expired
     *
     * @param $cacheFullPath string
     * @param $fullPath string
     *
     * @return void
     */
	protected function writeToCacheIfExpired($cacheFullPath, $fullPath)
	{
		clearstatcache();
		
		$modifiedDate = filemtime($fullPath);
		$currentDate  = time() - 60;

		if($modifiedDate > $currentDate) {
			$rawData  = file_get_contents($fullPath);
			$rendered = $this->render($rawData);
			file_put_contents($cacheFullPath, $rendered);
		}
	}

	protected function writeToCache($cacheFullPath, $renderedData)
	{
		return file_put_contents($cacheFullPath, $renderedData);
	}

	/**
     * Include cache file
     *
     * @param $cacheFullPath string
     * @param $params array
     *
     * @return string
     */
	protected function includeCache($cacheFullPath, array $params = [])
	{
		ob_start();
		foreach($params as $var => $value){
			$$var = $value;
		}

		include $cacheFullPath;
		$viewData = ob_get_clean();

		return $viewData;
	}

	/**
     * Filter multi array
     *
     * @param $matches array
     * @return array
     */
	protected function filterMutliArray(array $matches)
	{
		$forMulti = function(array $matched) {
			foreach($matched as $key => $m){
				if(!$m || strlen($m) < 2){
					unset($matched[$key]);
				}
			}

			return $matched;
		};

		foreach($matches as $key => $matched){
			if(is_array($matched)) {
				$matches[$key] = $forMulti($matched);
			}
		}
		return $matches;
	}
	
	/**
     * Set storage path
     *
     * @param $path string
     * @return void
     */
	public function storage($path)
	{
		$this->existsDir($path, 'storage');
		
		foreach(['views', 'cache'] as $repo) {
			if(! file_exists($path.'/' . $repo)) {
				mkdir($path.'/' . $repo);
			}
		}

		$this->storage = $path;
	}
	
	public function aliases($aliases)
	{
		$this->aliases = array_merge($this->aliases, $aliases);
	}

	/**
     * Set the files extension
     *
     * @param $ext string
     *
     * @return void
     */
	public function ext($ext = false)
	{
		if(preg_match('/^\.[a-zA-Z]+$/i', $ext)) {
			$this->ext = $ext;
		}
	}

	/**
     * Set the master file
     *
     * @param $master string
     *
     * @return void
     */
	public function master($master = false)
	{
		$this->master = $master ? $master : $this->master;
	}

	/**
     * Clear cache file if any different
     *
     * @param $cacheFullPath string
     *
     * @return void
     */
	protected function clearCacheIfRefreshed($contentFilePath, $cacheFullPath)
	{
		if(file_exists($contentFilePath) === true && file_exists($cacheFullPath) === true) {

			clearstatcache();
		
			$modifiedDate = filemtime($contentFilePath);
			$currentDate  = time() - 60;

			if($modifiedDate > $currentDate) {
				unlink($cacheFullPath);
			}
		}
	}

	/**
     * Generate from template file to php file 
     *
     * @param $path string
     * @param $params array
     *
     * @return string
     */
	public function composer($path, array $params = [], $show = false)
	{
		$masterPage = $this->storage . '/views/' . $this->setSeparator($this->master);

		if(! file_exists($masterPage . $this->ext)) {
			throw new Exception($masterPage . $this->ext . ' not existsing');
		}

		$contentFilePath = $this->setSeparator($path);
		$contentFileFullPath = $this->setFullViewPath($contentFilePath);

		$cacheFileName = md5($contentFileFullPath);
		$cacheFullPath = $this->setFullCachePath($cacheFileName);

		$this->clearCacheIfRefreshed($contentFileFullPath, $cacheFullPath);

		// caching scenario
		if($this->caching && file_exists($cacheFullPath) === true) {

			$cacheData = $this->includeCache($cacheFullPath, $params);

			if($show) {
				echo $cacheData;
			}
			return $cacheData;
		}

		// without caching scenario
		$content = $this->controller($path, $params, false);
		$master = $this->controller($this->master, $params, false);
		$master = preg_replace('/\{\s*\@content\s*\}/', $content, $master);
		
		$appendPattern  = '\{\s*\@append\([\',\"]*(.*?)[\',\"]*\)\s*\}([\s\S]*?)\{\s*\@end\s*\}';
		$sectionPattern = '\{\s*\@section\((%s)\)\s*\}([\s\S]*?)\{\s*\@end\s*\}';

		preg_match_all('/' . $appendPattern . '/i', $master, $matches);
		
		$rawData = [];

		if(isset($matches[1]) && is_array($matches[1])) {
			foreach($matches[1] as $key => $tag) {
				preg_match_all('/' . sprintf($sectionPattern, $tag) . '/i', $master, $tagMatches);
				
				if(isset($tagMatches[2]) && is_array($tagMatches[2])) {
					$rawData[$tag] = (isset($rawData[$tag]) ? $rawData[$tag] . "\n" .$matches[2][$key] : $tagMatches[2][0] . "\n" . $matches[2][$key]);
				}
			}

			$master = preg_replace('/' . $appendPattern . '/i', '', $master);

			foreach($matches[1] as $key => $tag) {
				$master = preg_replace('/' . sprintf($sectionPattern, $tag) . '/i', $rawData[$tag], $master);
			}

			$master = preg_replace('/(\n{2,})/',"\n", $master);
			$master = $this->extendFiles($master);

			$this->writeToCache($cacheFullPath, $master);
			$viewData = $this->includeCache($cacheFullPath, $params);

			if($show) {
				echo $viewData;
			}
			return $viewData;
		}
	}

	/**
     * Get view file
     *
     * @param $path string
     * @param $params array
     * 
     * @return string
     */
	public function view($path, array $params)
	{
		return $this->getContent($path, $params);
	}

	/**
     * Show view file
     *
     * @param $path string
     * @param $params array
     * 
     * @return string
     */
	public function show($path, array $params)
	{
		echo $this->getContent($path, $params);
	}
	
	/**
     * Check the directory
     *
     * @param $path string
     * @param $dirName bool
     * 
     * @return void|Exception
     */
	protected function existsDir($path, $dirName = false)
	{
		if(! file_exists($path)) {
			if($dirName){
				throw new Exception($path.' dir not exists');
			}
		}
	}

	protected function getContent($path, array $params)
	{
		$contentFilePath = $this->setSeparator($path);
		$contentFileFullPath = $this->setFullViewPath($contentFilePath);

		if(! file_exists($contentFileFullPath)) {
			throw new Exception($contentFileFullPath . ' not existsing');
		}

		return $this->includeCache($contentFilePath, $params);
	}
}