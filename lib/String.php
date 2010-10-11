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
	            if ( preg_match( $pattern, $string ) ){
					return self::replace( $pattern, $result, $string );
				}
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
		public static function getConjunctions(){
			return array('an', 'and', 'but', 'or', 'nor', 'so', 'yet', 'when', 'for', 'after', 'although', 'as', 'because', 'before', 'how', 'if', 'once', 'since', 'than', 'though', 'till', 'until', 'where', 'whether', 'while', 'either', 'not', 'only', 'also', 'the', 'thats', 'that', "that's", 'that is', 'that was'); 
		}
		public static function getPrepositions(){
			return array('about', 'above', 'across', 'after', 'against', 'along', 'among', 'around', 'at', 'before', 'behind', 'below', 'beneath', 'beside', 'between', 'beyond', 'but', 'by', 'despite', 'down', 'during', 'except', 'for', 'from', 'in', 'inside', 'into', 'like', 'near', 'of', 'off', 'on', 'onto', 'out', 'outside', 'over', 'past', 'since', 'through', 'throughout', 'till', 'to', 'toward', 'under', 'underneath', 'until', 'up', 'upon', 'with', 'within','without', 'was', 'a', 'to');
		}
		public static function getPronouns(){
			return array('him', 'he', 'his', 'it', 'her', 'she', 'hers', 'we', 'our', 'ours', 'theirs', 'their', 'us');
		}
		public static function getAdjectives(){
			return array('tough');
		}
		public static function getAdverbs(){
			return array('how', 'when', 'where', 'how much');
		}
		public static function getVerbs(){
			return array('are', 'am', 'is', 'was', 'using', 'use', 'uses', 'want');
		}
		public static function getNouns(){
			return array('key');
		}
		public static function getKeyWordsFromContent($content){
			$pattern = implode(' | ', self::getConjunctions());
			$pattern .= implode(' | ', self::getPrepositions());
			$pattern .= implode(' | ', self::getPronouns());
			$pattern .= implode(' | ', self::getAdjectives());
			$pattern .= implode(' | ', self::getAdverbs());
			$pattern .= implode(' | ', self::getVerbs());
			$pattern .= implode(' | ', self::getNouns());
			$content = self::replace('/'. $pattern . '/i', '', $content);			
			$keywords = self::getImportantWordsFrom($content);
			$popular_words = array();
			$current_word = null;
			$ubounds = count($keywords);
			$list = implode(' ', $keywords);
			$matches = array();
			foreach($keywords as $current_word){
				if(preg_match_all('/' . $current_word . '/i', $list, $matches) > 5 && array_search($current_word, $popular_words) === false){
					$popular_words[] = $current_word;
				}
			}
			return $popular_words;
		}
		public static function getImportantWordsFrom($content){
			$words = explode(' ', $content);
			$keywords = array();
			$index = 0;
			$ubounds = count($words);
			for($index = 0; $index < $ubounds; $index++){
				$words[$index] = self::replace('/[^A-Z^a-z^0-9]+/', '', $words[$index]);
				if(strlen(trim($words[$index])) > 0){
					$keywords[] = $words[$index];
				}
			}
			$keywords = array_diff($keywords, self::getConjunctions(), self::getPrepositions(), self::getAdverbs(), self::getVerbs(), self::getPronouns(), self::getNouns(), self::getAdjectives());
			return $keywords;
		}

		public static function explodeAndTrim($csvString){
			$list = explode(',', $csvString);
			foreach($list as $key=>$value){
				$list[$key] = trim($value);
			}
			return $list;
		}
		public static function toArray($csvString){
			$list = self::explodeAndTrim($csvString);
			$new_list = array();
			foreach($list as $value){
				list($key, $val) = explode('=', $value);
				$new_list[$key] = $val;
			}
			return $new_list;
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
		public static function find($pattern, $value){
			$matches = array();
			$did_match = preg_match($pattern, $value, $matches);
			return $matches;
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
		public static function toLower($value){
			return strtolower($value);
		}
		public static function encrypt($value){
			return sha1($value);
		}
		public static function stripHtmlTags($html, $allowed_tags = null){
			return strip_tags($html, $allowed_tags);
		}
		public static function truncate($text, $length, $suffix = '...'){
			$string = $text;
			if(strlen($string) > $length){
				$string = substr($string, 0, $length - 1) . $suffix;
			}
			return $string;
		}
		public static function toString($val){
			if(is_array($val)){
				return implode(',', $val);
			}
			return $val;
		}
		public static function sanitize($val){
			return filter_var($val, FILTER_SANITIZE_STRING);
		}
		public static function isNullOrEmpty($val){
			if($val === null){
				return true;
			}else if(strlen($val) === 0){
				return true;
			}else{
				return false;
			}
		}
	}
?>