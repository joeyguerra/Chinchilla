<h1>This is an example on how to use Chinchilla, a RESTful framework in PHP</h1>
<ol>
	<li>Create a resource by creating a file in the resources folder. You can copy either ExampleResource.php or IndexResource.php</li>
	<li>Create a method in your new resource prefixed with the HTTP method you want it to handle. For example, if you look at the ExampleResource.php file, you'll see a method called "get_example". This method responds to an HTTP GET request to a relative url "/example/". If you want to handle the HTTP POST method, then create a method called "post_example".</li>
	<li>Your methods will almost always look like this:<br />
		<code>
			function get_example(){
				/* logic here */
				$this->title = "An example using Chinchilla, A RESTful framework in PHP";
				$this->output = $this->renderView('example/index', null);
				return $this->renderView('layouts/default', null);
			}
		</code>
		<br />
	</li>
	<li>The renderView method will look in "views/example/" for a file called "index_html.php". The framework determines the file type (eg. html, xml, json, rss, atom, etc.) based on the url. If the url is "/example.html", then the framework will set the resource's fileType property to "html" and renderView will render the views/example/index_html.php file to the resource's "output" property.</li>
	<li>
		The return call in the function above render's the output property in the default layout which resides in "views/layouts/default_html.php", where the "_html" in the file name indicates it's representation.
	</li>
</ol>