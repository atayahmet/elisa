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
		
		'\{(\s*)for\((.*?)\)(\s*)\}',
		'\{(\s*)endfor(\s*)\}',
		'\{(\s*)(foreach)\((.*?)\)(\s*)\}',
		'\{(\s*)(endforeach)(\s*)\}',
		'\{(\s*)(endeach)(\s*)\}',
		'\{(\s*)\$+[a-z](\s?)\}'

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
	public function render($tpl, array $attr)
	{
		$this->data = $tpl;

		foreach($this->tags as $pattern => $tag) {

			preg_match_all('/' . $pattern . '/',$tpl, $matches);
			
			$filtredMatches = $this->filterMutliArray($matches);

			if(isset($filtredMatches[0]) && is_array($filtredMatches[0])) {

				foreach($filtredMatches[0] as $matchedTag) {
					$replacedTag = preg_replace(
						[
							'/'.$pattern.'/'
							// '/\{/', 
							// '/\}/', 
							// '/\)/', 
							// '/endif/i', 
							// '/else \?\>$/i',
							// '/endfor(\s*)\?\>/i',
							// '/endforeach(\s*)\?\>/i',
							// '/endeach(\s*)\?\>/i',
							// '/\<\?php(\s*)(\$(.*?))(\s*)\?\>/i',
							// '/\s+/'
						],
						[
							$tag
						], $matchedTag);
					
					$this->data  = preg_replace('/' . preg_quote($matchedTag) . '/', $replacedTag, $this->data);
				}
			}
		}

		file_put_contents(__DIR__ . '/test.php', $this->data);
		
		foreach($attr as $var => $value){
			$$var = $value;
		}

		include 'test.php';

	}
	
	protected function renderIf()
	{

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
		static::existsDir($path, 'storage');
		
		static::$storage = $path;
	}
	
	/**
     * Set raw data path
     *
     * @param $path string
     * @return void
     */
	public function view($path)
	{
		static::existsDir($path, 'view');
		
		static::$view = $path;
	}
	
	protected function existsDir($path, $dirName = false)
	{
		if(! file_exists($path)) {
			if($dirName){
				throw new Exception($dirName . ' not exists');
			}
		}
	}

}
