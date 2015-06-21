<?php

namespace Elisa;

use Elisa\Dispatcher;

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

	/**
     * Caching status
     * 
     * @var bool
     */
	protected $caching = true;

	/**
     * Function aliases
     * 
     * @var array
     */
	protected $aliases = [];

	/**
     * Function aliases
     * 
     * @var array
     */
	protected $reserved = ['extend', 'append', 'content'];

	/**
     * Start tag
     * 
     * @var string
     */
	protected $open = '{';

	/**
     * End tag
     * 
     * @var string
     */
	protected $close = '}';

	/**
     * Params Depends
     * 
     * @var arrayW
     */
	protected $params = [];

	/**
     * Params send to each views 
     * 
     * @var array
     */
	protected $eachParams = [];

	/**
     * Event dispatcher
     * 
     * @var object
     */
	protected $dispatcher;
	
	/**
     * Elisa Regex patterns
     * 
     * @var string
     */
	protected $tags = [
		"%s(\s*)(if)(\s*)\((.*?)\)(\s*)%s" => '<?php $2($4): ?>',
		'%s(\s*)(elseif)(\s*)\((.*?)\)(\s*)%s' => '<?php $2($4): ?>',
		'%s(\s*)(endif)(\s*)%s' => '<?php $2; ?>',
		'%s(\s*)(else)(\s*)%s' => '<?php $2: ?>',
		'%s(\s*)(for)\((.*?)\)(\s*)%s' => '<?php $2($3): ?>',
		'%s(\s*)(endfor)(\s*)%s' => '<?php $2; ?>',
		'%s(\s*)(foreach)\((.*?)\)(\s*)%s' => '<?php $2($3): ?>',
		'%s(\s*)(endforeach)(\s*)%s' => '<?php $2; ?>',
		'%s(\s*)(while)\((.*?)\)(\s*)%s' => '<?php $2($3): ?>',
		'%s(\s*)(endwhile)(\s*)%s' => '<?php $2; ?>',
		'%s(\s*)(endeach)(\s*)%s' => '<?php $2; ?>',
		'%s(\s*)\$+(.*?)(\s?)%s' => '<?php $$2; ?>',
		'(%s\s*(?!\!\@)(([a-z_]+)\(.*?\))\s*%s)' => '<?php $2;?>',
		'%s(\s*)\!(.*?)%s' => '<?= $2; ?>'
	];

	public function __construct()
	{
		$this->tags = $this->compileTag($this->tags);
		$this->dispatcher = new Dispatcher;
	}

	protected function extend($filePath, array $params = [], $show = true)
	{
		$fullViewPath  = $this->setFullViewPath($filePath);
		$fullCachePath = $this->setFullCachePathWithMd5($filePath);
		
		$this->writeToCacheIfExpired($fullCachePath, $fullViewPath);

		$extendData = $this->includeCache($fullCachePath, $params);

		if($show === true) {
			echo $extendData;
			return;
		}
		return $extendData;
	}

	protected function compileTag($patterns)
	{
		$string = false;
		$left  = preg_quote($this->open);
		$right = preg_quote($this->close);

		if(! is_array($patterns)) {
			return sprintf($patterns, $left, $right);
		}

		$compiledTags = [];
		
		foreach($patterns as $pattern => $replacement) {
			$pattern = sprintf($pattern, $left, $right);
			$compiledTags[$pattern] = $replacement;
		}

		return $compiledTags;
	}

	/**
     * Raw data render
     *
     * @param $tpl string
     * @param $attr array
     * 
     * @return string
     */
	protected function render($tpl)
	{
		$this->data = $this->setAliases($tpl);

		foreach($this->tags as $pattern => $tag) {

			preg_match_all('/' . $pattern . '/',$this->data, $matches);
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
					
					$this->data  = preg_replace('~' . preg_quote($matchedTag) . '~', $replacedTag, $this->data);
				}
			}
		}
		return $this->data;
	}

	/**
     * Make the extend files (recursive)
     *
     * @param $strem string
     * 
     * @return string
     */
	protected function extendFiles($stream)
	{
		//$extendPattern = $this->compileTag('%s\s*\@extend\([\'\"]?(.*?)[\'\"]?\)\s*%s');

		$extendPattern = $this->compileTag('%s\s*\@extend\([\'\"]?(.*?)[\'\"]\s*(,)?(.*?)\s*\)\s*%s');
		preg_match_all('/' . $extendPattern . '/', $stream, $matches);
		
		if(isset($matches[1]) && is_array($matches[1])) {

			foreach ($matches[1] as $key => $path) {
				
				$filePath = $this->setSeparator($path);
				$fullPath = $this->setFullViewPath($filePath);

				if(! file_exists($fullPath)) {
					throw new \Exception($fullPath . ' not existsing');
				}

				$this->writeToCache($this->setFullCachePathWithMd5($path), $this->render(file_get_contents($fullPath)));

				$stream = str_replace($matches[0][$key], sprintf('<?php $_elisa->extend(\'%s\'); ?>', $path) , $stream);
				
				preg_match_all('/' . $extendPattern . '/', $stream, $nestedMatches);

				if(isset($nestedMatches[1]) && is_array($nestedMatches[1])) {
					$stream = $this->extendFiles($stream);
				}
			}
		}
		return $stream;
	}

	/**
     * Replace the aliases funcion name
     *
     * @param $strem string
     * 
     * @return string
     */
	protected function setAliases($stream)
	{
		$aliases = implode(array_flip($this->aliases), '|');

		$open  = preg_quote($this->open);
		$close = preg_quote($this->close);

		$aliasesPattern = sprintf('(%s\!*\s*.*?\@*(%s)\((.*?)\)\s*%s)', $open, $aliases, $close);

		preg_match_all('/' . $aliasesPattern . '/i', $stream, $matches);

		if(isset($matches[2]) && is_array($matches[2])) {
			foreach($matches[2] as $key => $func) {
				if( isset($this->aliases[$func])) {
					$pattern = sprintf('((%s\!*\s*)(.*?)(\@*(%s)\((.*?)\))(\s*%s))', $open, $func, $close);
					$replacement = sprintf('$2$3%s(%s)$7', $this->aliases[$func], $matches[3][$key]);
					$stream = preg_replace('/' . $pattern . '/i', $replacement, $stream);
				}
			}
		}
		return $stream;
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
		return preg_replace('/\/\//', $this->separator, $this->storage . '/cache/' . $cacheFileName) . '.php';
	}

	protected function setFullCachePathWithMd5($path)
	{
		$filePath = $this->setSeparator($path);
		$fullPath = $this->setFullViewPath($filePath);

		$cacheFileName = md5($fullPath);
		return $this->setFullCachePath($cacheFileName);
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
			throw new \Exception($fullPath . ' not existsing');
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

	/**
     * Write to cache
     *
     * @param $cacheFullPath string
     * @param $renderedData string
     *
     * @return int
     */
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
		$params = array_merge($this->eachParams, $params);

		foreach($params as $var => $value){
			$$var = $value;
		}

		$_elisa = $this;

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
	
	/**
     * Save the function names aliases
     *
     * @param $path string
     *
     * @return void
     */
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

	public function cache($status = null)
	{
		if(is_bool($status)) {
			$this->caching = $status;
		}
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
	public function composer($path, $show = false)
	{
		if(ob_get_level()) ob_end_clean();

		$masterPage = $this->storage . '/views/' . $this->setSeparator($this->master);

		if(! file_exists($masterPage . $this->ext)) {
			throw new \Exception($masterPage . $this->ext . ' not existsing');
		}

		$contentFilePath = $this->setSeparator($path);
		$contentFileFullPath = $this->setFullViewPath($contentFilePath);

		$cacheFileName = md5($contentFileFullPath);
		$cacheFullPath = $this->setFullCachePath($cacheFileName);
		$this->clearCacheIfRefreshed($contentFileFullPath, $cacheFullPath);

		// caching scenario
		if($this->caching && file_exists($cacheFullPath) === true) {

			$cacheData = $this->includeCache($cacheFullPath, $this->params);

			$this->params = [];

			$this->runEvent('before', $path);
			
			if($show) {
				echo $cacheData;
				$this->runEvent('after', $path);
				return;
			}
			return $cacheData;
		}

		// without caching scenario
		$content= $this->controller($path, $this->params, false);
		$master = $this->controller($this->master, $this->params, false);
		$master = preg_replace('/' . $this->compileTag('%s\s*\@content\s*%s') . '/', $content, $master);
		
		// Set the open/close tags
		$open  = preg_quote($this->open);
		$close = preg_quote($this->close);
		
		$appendPattern = sprintf('%s\s*\@append\([\',\"]*(.*?)[\',\"]*\)\s*%s([\s\S]*?)%s\s*\@end\s*%s', $open, $close, $open, $close);
		$sectionPattern = '%s\s*\@section\([\'\"]*(%s)[\'\"]*\)\s*%s([\s\S]*?)%s\s*\@end\s*%s';
		preg_match_all('/' . $appendPattern . '/i', $master, $matches);
		
		$rawData = [];

		if(isset($matches[1]) && is_array($matches[1])) {
			foreach($matches[1] as $key => $tag) {

				preg_match_all('/' . sprintf($sectionPattern, $open, $tag, $close, $open, $close) . '/i', $master, $tagMatches);
				
				if(isset($tagMatches[2]) && is_array($tagMatches[2]) && isset($tagMatches[2][0])) {
					$rawData[$tag] = (isset($rawData[$tag]) ? $rawData[$tag] . PHP_EOL . $matches[2][$key] : $tagMatches[2][0] . PHP_EOL . $matches[2][$key]);
				}
			}

			$master = preg_replace('/' . $appendPattern . '/i', '', $master);

			foreach($matches[1] as $key => $tag) {
				if(isset($rawData[$tag])) {
					$master = preg_replace('/' . sprintf($sectionPattern, $open, $tag, $close, $open, $close) . '/i', $rawData[$tag], $master);
				}
			}

			// Merges the extended files
			$master = $this->extendFiles($master);

			$sectionPattern2 = sprintf('(%s\s*\@section\([\'\"]*(.*?)[\'\"]*\)\s*%s([\s\S]*?)%s\s*\@end\s*%s)', $open, $close, $open, $close);
			
			// make the single newline of multi newlines
			$master = preg_replace(['/' . $sectionPattern2 . '/i', '/(\n{2,})/'], ['$3', PHP_EOL], $master);

			// Lastly replaces the matching tags
			$master = preg_replace('/' . sprintf('%s\s*(.*?)\s*%s', $open, $close) . '/', '<?php $1;?>', $master);

			$this->writeToCache($cacheFullPath, $master);
			$master = $this->includeCache($cacheFullPath, $this->params);
		}

		$this->params = [];

		// Run the before events
		$this->runEvent('before', $path);

		if($show) {
			echo $master;
			// Run the after events
			$this->runEvent('after', $path);
			return;
		}
		return $master;
	}

	public function beforeEvent($name = false, \Closure $event)
	{
		if($name) {
			$this->dispatcher->set('before_' . $name, $event);
		}
	}

	public function afterEvent($name = false, \Closure $event)
	{
		if($name) {
			$this->dispatcher->set('after_' . $name, $event);
		}
	}

	protected function runEvent($prefix, $name)
	{
		$this->dispatcher->run($prefix.'_'.$name);
	}

	public function clear()
	{
		foreach (glob($this->storage."/cache/*.php") as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        return true;
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

		// Run the after events
		$this->runEvent('after', $path);
	}
	
	public function setup(array $config)
	{
		foreach($config as $method => $value) {

			if(! method_exists($this, $method)) {
				throw new \BadMethodCallException('Call to undefined method ' . __CLASS__ . '::' . $method);
			}
			$this->$method($value);
		}
	}

	public function with(array $params)
	{
		$this->params = $params;
	}

	public function each(array $params)
	{
		$this->eachParams = $params;
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
				throw new \Exception($path.' dir not exists');
			}
		}
	}

	/**
     * Get the content
     *
     * @param $path string
     * @param $params array
     * 
     * @return Exception|string
     */
	protected function getContent($path, array $params)
	{
		$contentFilePath = $this->setSeparator($path);
		$contentFileFullPath = $this->setFullViewPath($contentFilePath);

		if(! file_exists($contentFileFullPath)) {
			throw new \Exception($contentFileFullPath . ' not existsing');
		}

		// Run the before events
		$this->runEvent('before', $path);

		return $this->includeCache($contentFilePath, $params);
	}
}