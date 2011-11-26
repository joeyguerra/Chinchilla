<?php
class google_analytics{
	function after_rendering_layout($publisher, $info){
		$tag = <<<eos
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-8615536-12']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>

eos;
		$info = str_replace("</body>", "
<!--ga: " .  date("c") ."--><!--here-->
</body>", $info);
		if(resource::domain() === null) return $info;
		$info = str_replace("<!--here-->", $tag, $info);
		return $info;
	}
}
filter_center::subscribe("after_rendering_layout", null, new google_analytics());
