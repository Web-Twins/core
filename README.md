The core source code of Web-Twins
=============================
 

Travis CI status: [![Unit testing](https://travis-ci.org/Web-Twins/core.png?branch=master)](https://travis-ci.org/Web-Twins/core) [![Coverage Status](https://coveralls.io/repos/Web-Twins/core/badge.png?branch=master)](https://coveralls.io/r/Web-Twins/core?branch=master)

<img src="https://camo.githubusercontent.com/fe2d9e9063dabaf5951ef8f3835bbbc16cec52e3/68747470733a2f2f706f7365722e707567782e6f72672f7a6f72646975732f6c696768746e63616e64792f6c6963656e73652e737667" alt="license">

<img src="https://raw.githubusercontent.com/puritys/MyProgram/master/images/twins_architecture.png">

Purpose
-------

This framework want to keep the development more simple.

All Frontend engineer should do is to modify the templates,css,javascript and commit, then everything will be finished. Backend engineer do not need to spend any time integrating templates. This will be a perfect way for developing a website.


Infrastructure Design of web-twins
==================================

The framework will give what to you
-----------------------------------

* MVC: A MVC platform  for front-end and back-end engineers.
* Modularization: Modularize the website pages. 
* Support Nodejs and PHP. You can use Nodejs or PHP to build a MVC website, also you can use both of them if you want.
* Shorten the time of program integration.
* To easily simulate any situation which end-user will meet and quickly to find a root cause.
* Support Desktop and Mobile web.
* Switch between template mode and website mode.


Directory Structure
-------------------
<pre>
| - core
| -- nodejs
| ---- layoutParser.js
| ---- module.js
| ---- server.js
| ---- util.js
| -- php
| - tests
</pre>

layoutParser.js: <br/>
This object support to parse the XML config of page then render the result of HTML.

The following are the features of layouerParser:<br />
* Parse the XML config. We have two kind of XML config, one is the definition of pages, another is the definietion of whole website.
* Render CSS on the head and footer. Also directly compile the less file.
* Render JavaScript on the head and footer.
* Render HTML body.
* Redner Module HTML by calling module object render.
* Automatically Combine the CSS and JavaScript file list.

module.js: <br />
This object support to parse the tag module then render the module's HTML. A module is a little like the MVC structure. But it includes the view and model without controller. We could define multi models for template mode development. The format of model is a JSON or YAML. The development of view is a handlebar template, The module.js can compile the templates and parse the models and  combine this two kind of data to become a HTML. In the future, We will support more kind of templates.


CSS/JavaScript 
--------------

* Combination
* 
Web-Twins support the combo of CSS/JS to improve the frontend performace.  

Attention! 
Take care of the relative image path in css, if you use the combination of CSS,  be sure the combo url path has the same level with separated CSS file path.


* Less and Sass/Scaa
* 
Web-Twins support less and sass/scss.

Attention for development!
--------------------------

* You should set head tag in page config, or the setting of head in site config will not be rendered.
