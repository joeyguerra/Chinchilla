<h1>This is an example on how to use Chinchilla, a RESTful framework in PHP</h1>
<ol>
	<li>
		<p>Create a resource by creating a file in the resources folder. You can copy either ExampleResource.php or IndexResource.php.</p>
	</li>
	<li>
		<p>Create a method in your new resource named the same as the HTTP method you want it to handle. For example, if you look at the ExampleResource.php file, you'll see a method called "get". This method responds to an HTTP GET request to a relative url "/example/". If you want to handle the HTTP POST method, then create a method called "post".</p>
	</li>
	<li>
		<p>Your methods will almost always look like this:</p>
		<pre>
			<code>
public function get(){
	/* logic here */
	$this->title = "An example using Chinchilla, A RESTful framework in PHP";
	$this->output = $this->render('example/index', null);
	return $this->render_layout('default', null);
}
			</code>
		</pre>
	</li>
	<li>
		<p>The renderView method will look in "views/example/" for a file called "index_html.php". The framework determines the file type (eg. html, xml, json, rss, atom, etc.) based on the url. If the url is "/example.html", then the framework will set the resource's fileType property to "html" and renderView will render the views/example/index_html.php file to the resource's "output" property.</p>
	</li>
	<li>
		<p>The return call in the function above render's the output property in the default layout which resides in "views/layouts/default_html.php", where the "_html" in the file name indicates it's representation.</p>
	</li>
	<li>
		<p>You'll also notice a themes folder in the file hierarchy. This folder contains themes that you might want to use for your site. There's a "default" theme in the code base now which can be configured by returning the name of the theme folder in the AppConfiguration::getTheme method. If the "views" folder hierarchy is defined in a theme folder within the "themes" folder, the framework will look there first to find any views it's looking for. The "views" functionality is how the framework implements the "Representation" part of REST. You can represent the data in any way by creating a view for it, according to the file type you specify. For instance, if I want to represent ".doc" files, I would create view files appended with "_doc.php". Check out the example resource and views to see what I mean.</p>
	</li>
</ol>