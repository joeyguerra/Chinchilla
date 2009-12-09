<?php 
	class String{		
	    static $plural = array(
	        '/(quiz)$/i'               => "$1zes",
	        '/^(ox)$/i'                => "$1en",
	        '/([m|l])ouse$/i'          => "$1ice",
	        '/(matr|vert|ind)ix|ex$/i' => "$1ices",
	        '/(x|ch|ss|sh)$/i'         => "$1es",
	        '/([^aeiouy]|qu)y$/i'      => "$1ies",
	        '/(hive)$/i'               => "$1s",
	        '/(?:([^f])fe|([lr])f)$/i' => "$1$2ves",
	        '/(shea|lea|loa|thie)f$/i' => "$1ves",
	        '/sis$/i'                  => "ses",
	        '/([ti])um$/i'             => "$1a",
	        '/(tomat|potat|ech|her|vet)o$/i'=> "$1oes",
	        '/(bu)s$/i'                => "$1ses",
	        '/(alias)$/i'              => "$1es",
	        '/(octop)us$/i'            => "$1i",
	        '/(ax|test)is$/i'          => "$1es",
	        '/(us)$/i'                 => "$1es",
	        '/s$/i'                    => "s",
	        '/$/'                      => "s"
	    );

	    static $singular = array(
	        '/(quiz)zes$/i'             => "$1",
	        '/(matr)ices$/i'            => "$1ix",
	        '/(vert|ind)ices$/i'        => "$1ex",
	        '/^(ox)en$/i'               => "$1",
	        '/(alias)es$/i'             => "$1",
	        '/(octop|vir)i$/i'          => "$1us",
	        '/(cris|ax|test)es$/i'      => "$1is",
	        '/(shoe)s$/i'               => "$1",
	        '/(o)es$/i'                 => "$1",
	        '/(bus)es$/i'               => "$1",
	        '/([m|l])ice$/i'            => "$1ouse",
	        '/(x|ch|ss|sh)es$/i'        => "$1",
	        '/(m)ovies$/i'              => "$1ovie",
	        '/(s)eries$/i'              => "$1eries",
	        '/([^aeiouy]|qu)ies$/i'     => "$1y",
	        '/([lr])ves$/i'             => "$1f",
	        '/(tive)s$/i'               => "$1",
	        '/(hive)s$/i'               => "$1",
	        '/(li|wi|kni)ves$/i'        => "$1fe",
	        '/(shea|loa|lea|thie)ves$/i'=> "$1f",
	        '/(^analy)ses$/i'           => "$1sis",
	        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i'  => "$1$2sis",
	        '/([ti])a$/i'               => "$1um",
	        '/(n)ews$/i'                => "$1ews",
	        '/(h|bl)ouses$/i'           => "$1ouse",
	        '/(corpse)s$/i'             => "$1",
	        '/(us)es$/i'                => "$1",
	        '/s$/i'                     => ""
	    );

	    static $irregular = array(
	        'move'   => 'moves',
	        'foot'   => 'feet',
	        'goose'  => 'geese',
	        'sex'    => 'sexes',
	        'child'  => 'children',
	        'man'    => 'men',
	        'tooth'  => 'teeth',
	        'person' => 'people'
	    );

	    static $uncountable = array(
	        'sheep',
	        'fish',
	        'deer',
	        'series',
	        'species',
	        'money',
	        'rice',
	        'information',
	        'equipment'
	    );
	    public static function pluralize( $string )
	    {
	        // save some time in the case that singular and plural are the same
	        if ( in_array( strtolower( $string ), self::$uncountable ) )
	            return $string;

	        // check for irregular singular forms
	        foreach ( self::$irregular as $pattern => $result )
	        {
	            $pattern = '/' . $pattern . '$/i';

	            if ( preg_match( $pattern, $string ) )
	                return self::replace( $pattern, $result, $string);
	        }

	        // check for matches using regular expressions
	        foreach ( self::$plural as $pattern => $result )
	        {
	            if ( preg_match( $pattern, $string ) )
	                return self::replace( $pattern, $result, $string );
	        }

	        return $string;
	    }

	    public static function singularize( $string )
	    {
	        // save some time in the case that singular and plural are the same
	        if ( in_array( strtolower( $string ), self::$uncountable ) )
	            return $string;

	        // check for irregular plural forms
	        foreach ( self::$irregular as $result => $pattern )
	        {
	            $pattern = '/' . $pattern . '$/i';

	            if ( preg_match( $pattern, $string ) )
	                return self::replace( $pattern, $result, $string);
	        }

	        // check for matches using regular expressions
	        foreach ( self::$singular as $pattern => $result )
	        {
	            if ( preg_match( $pattern, $string ) )
	                return self::replace( $pattern, $result, $string );
	        }

	        return $string;
	    }

	    public static function pluralize_if($count, $string)
	    {
	        if ($count == 1)
	            return "1 $string";
	        else
	            return $count . " " . self::pluralize($string);
	    }
		
		public static function explodeAndTrim($csvString){
			$list = explode(',', $csvString);
			foreach($list as $key=>$value){
				$list[$key] = trim($value);
			}
			return $list;
		}
		public static function decamelize($string){
			if(strlen(trim($string)) > 0){
				return strtolower(ltrim(preg_replace('/([A-Z])+/', '_$1', $string), '_'));
			}else{
				return $string;
			}
		}
		public static function camelize($string){
	        return str_replace(' ','',ucwords(self::replace('/[^A-Z^a-z^0-9]+/',' ',$string)));
	    }
		public static function replace($pattern, $with, $string){
			return preg_replace($pattern, $with, $string);
		}
		public static function stripCarriageReturnsAndTabs($string){
			$string = preg_replace('/[\\r?|\\n?]+/', '', $string);
			$string = preg_replace('/[\\t?]+/', '', $string);
			return $string;
		}
		public static function stringForUrl($string){
			$string = strtolower($string);
			$string = preg_replace("`\[.*\]`U","",$string);
			$string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i','-',$string);
			$string = htmlentities($string, ENT_COMPAT, 'utf-8');
			$string = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i","\\1", $string );
			$string = preg_replace( array("`[^a-z0-9]`i","`[-]+`") , "-", $string);
			return strtolower(trim($string, '-'));
		}
		public static function encrypt($value){
			return sha1($value);
		}
		public static function stripHtmlTags($html){
			return strip_tags($html);
		}
		
	}
?>