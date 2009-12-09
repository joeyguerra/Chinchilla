<h1>
	Origin of Chinchilla, the RESTful framework
</h1>

<p>
	The Chinchilla project began over 5 years ago out of a desire to learn and implement software design patterns and object oriented principles in PHP. It has morphed into a RESTful implementation with a Front Controller that maps HTTP requests to resource object methods.
</p>

<h2>
	The Basic Idea
</h2>

<p>
	The Front Controller processes HTTP requests except for javascript and stylesheet resources. It instantiates an object based on the URL. For instance, an HTTP GET request to /index/ results in an instance of the IndexResource class being instantiated and the method, get_index, called on it. The output of that method is the response to the HTTP GET request. An HTTP POST request results in the method, post_index, getting called on an instance of the IndexResource class. As with the DELETE HTTP method, delete_index. However, the DELETE method is not handled consistently across browsers so I added logic and data to get the framework to call delete methods. 
</p>

<h2>
	Handling the DELETE HTTP Method
</h2>
<p>
	I added logic in the Front Controller to look for a key called _method in the PHP's $_POST parameters and determine the method to call based on that first. If it doesn't exist, then follow the normal design.
</p>

<h2>
	What Next?
</h2>
<p>
	I just added this code in <a href="http://github.com/ijoey/Chinchilla">(Chinchilla on) GitHub</a> so go get it and take a look. I'm actively developing it and would love ya'll to use it and give me your feedback, suggestions, comments, critics, complaints, etc. Please contact me via <a href="http://github.com/">GitHub</a>.
</p>
