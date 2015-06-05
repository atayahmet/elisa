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
		'\{(\s*)\!(.*?)\}' => '<?php echo $2; ?>'
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

	protected function controller($path, array $params)
	{
		$filePath = preg_replace('/\./', $this->separator, $path);
		$fullPath = preg_replace('/\/\//', $this->separator, $this->storage . '/views/' . $filePath) . '.html';

		if(! file_exists($fullPath)) {
			throw new Exception($fullPath . ' not existsing');
		}

		$rawData = file_get_contents($fullPath);
		$cacheFileName = md5($fullPath);
		$cacheFullPath = $this->storage . '/cache/' . $cacheFileName . '.php';
		
		clearstatcache();
		
		$modifiedDate = filemtime($fullPath);
		$currentDate = time() - 60;

		if($modifiedDate > $currentDate) {
			$rendered = $this->render($rawData);
			file_put_contents($cacheFullPath, $rendered);
		}
		
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
