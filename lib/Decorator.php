<?php
class Decorator{
	public static function decorate($dest, $src){
		$reflector = new ReflectionObject($src);
		$name = null;
		foreach($reflector->getMethods() as $method){
			if($method->isPublic() && stripos($method->getName(), 'get') === 0){
				$name = str_replace('get', '', String::toLower($method->getName()));
				$dest->{$name} = $method->invoke();
			}
		}
		
		return $dest;
	}
}

