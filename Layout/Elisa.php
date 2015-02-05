<?php

namespace Layout;

use Layout\Filesystem;

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
	protected static $filesystem;
		
	/**
     * Raw data variable
     * 
     * @var string
     */
	protected static $data;
	
	/**
     * Converted data storage path
     * 
     * @var string
     */
	protected static $storage;
	
	/**
     * Raw data path
     * 
     * @var string
     */
	protected static $view;
	
	/**
   #FFFFFF  * Elisa Regex patterns
     * 
     * @var string
     */
	protected static $tags = [
		'if' => ['\{(\s?)(if)(\s?)\((.*?)\)(\s?)\}']
	];
	
	 /**
     * Raw data render
     *
     * @param $tpl string
     * @param $attr array
     * @return string
     */
	public static function render($tpl, array $attr)
	{
		static::$data = $tpl;

		//Filesystem::put();
		foreach(static::$tags as $type => $tag) {

			preg_match_all('/' . $tag[0] . '/',$tpl, $matches);
			
			$filtredMatches = static::filterMutliArray($matches);
			
			foreach($matches[4] as $multiMatch){
				$replacedTpl = preg_replace('/\{(\s?)(if)(\s?)\(' . preg_quote($multiMatch) . '\)(\s?)\}/', '<?php if(' . $multiMatch . '): ?>', static::$data);	
				$replacedTpl = preg_replace('/\{(\s?)endif(\s?)\}/', '<?php endif; ?>', $replacedTpl);
				$replacedTpl = preg_replace('/\{(\s?)else(\s?)\}/', '<?php else: ?>', $replacedTpl);
				
				static::$data = $replacedTpl;

				$replacedTpl = '';
			}

			file_put_contents(__DIR__ . '/test.php', static::$data);
			
			foreach($attr as $var => $value){
				$$var = $value;
			}

			include 'test.php';
		}
	}
	
	/**
     * Filter multi array
     *
     * @param $matches array
     * @return array
     */
	protected static function filterMutliArray(array $matches)
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
	public static function storage($path)
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
	public static function view($path)
	{
		static::existsDir($path, 'view');
		
		static::$view = $path;
	}
	
	protected static function existsDir($path, $dirName = false)
	{
		if(! file_exists($path)) {
			if($dirName){
				throw new Exception($dirName . ' not exists');
			}
		}
	}

}
