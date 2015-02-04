<?php

namespace Layout;

use Layout\Filesystem;

class Engine
{
	protected static $filesystem;

	protected static $data;

	protected static $tags = [
		'if' => ['\{(\s?)(if)(\s?)\((.*?)\)(\s?)\}']
	];
	
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
}