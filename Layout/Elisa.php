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

	protected $ext = '.html';
	protected $master = 'master';
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
		'\{(\s*)\$+(.*?)(\s?)\}' => '<?php echo $$2; ?>',
		'\{(\s*)(([a-z_]+)\((.*?)\))\}' => '<?php $2;?>',
		'\{(\s*)\!(.*?)\}' => '<?php echo $2; ?>',
		'\{(\s*)\@section\((.*?)\)(\s*)\}\n*(.*?)(\n*)\{(\s*)\@end(\s*)\}'
	];
	
	protected $elisaFuncs = [
		'\{\s*\@content\s*\}'
	];

	protected $replaced = [



	];

	 /**
     * Raw data render
     *
     * @param $tpl string
     * @param $attr array
     * @return string
     */
	public function render($tpl)
	{
		$this->data = $tpl;

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

	protected function setSeparator($path)
	{
		return preg_replace('/\./', $this->separator, $path);
	}

	protected function setFullViewPath($filePath)
	{
		return preg_replace('/\/\//', $this->separator, $this->storage . '/views/' . $filePath) . $this->ext;
	}

	protected function setFullCachePath($cacheFileName)
	{
		return $this->storage . '/cache/' . $cacheFileName . '.php';
	}

	protected function controller($path, array $params = [], $caching = true)
	{
		$filePath = $this->setSeparator($path);
		$fullPath = $this->setFullViewPath($filePath);

		if(! file_exists($fullPath)) {
			throw new Exception($fullPath . ' not existsing');
		}
		
		$cacheFileName = md5($fullPath);
		$cacheFullPath = $this->setFullCachePath($cacheFileName);

		if($caching) {
			$this->writeToCacheIfExpired($cacheFullPath, $fullPath);
			return $this->includeCache($cacheFullPath, $params);
		}else{
			$rawData = file_get_contents($fullPath);
			return $this->render($rawData);
		}
	}

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
			}else{
				
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
	
	public function ext($ext = false)
	{
		if(preg_match('/^\.[a-zA-Z]+$/i', $ext)) {
			$this->ext = $ext;
		}
	}

	public function master($master = false)
	{
		$this->master = $master ? $master : $this->master;
	}

	public function composer($path, array $params = [])
	{
		$masterPage = $this->storage . '/views/' . $this->setSeparator($this->master);

		if(! file_exists($masterPage . $this->ext)) {
			throw new Exception($masterPage . $this->ext . ' not existsing');
		}

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

			echo '<pre>';
			exit(var_dump($master));
			
		}
	}

	/**
     * Set raw data path
     *
     * @param $path string
     * @return void
     */
	public function view($path, array $params)
	{
		return $this->controller($path, $params);
	}
	
	protected function existsDir($path, $dirName = false)
	{
		if(! file_exists($path)) {
			if($dirName){
				throw new Exception($path.' dir not exists');
			}
		}
	}
}
