The core source code of Web-Twins
=============================
 

Travis CI status: [![Unit testing](https://travis-ci.org/Web-Twins/core.png?branch=master)](https://travis-ci.org/Web-Twins/core) [![Coverage Status](https://coveralls.io/repos/Web-Twins/core/badge.png?branch=master)](https://coveralls.io/r/Web-Twins/core?branch=master)

Development Structure Figure

<img src="https://camo.githubusercontent.com/fe2d9e9063dabaf5951ef8f3835bbbc16cec52e3/68747470733a2f2f706f7365722e707567782e6f72672f7a6f72646975732f6c696768746e63616e64792f6c6963656e73652e737667" alt="license">

<img src="https://raw.githubusercontent.com/puritys/MyProgram/master/images/twins_architecture.png">

Purpose
-------

This framework want to give engineers a simplest framework for their development. We will remove  the technical gap between the backend engineers and the frontend engineers.

Within Web-Twins, all Frontend engineer should only modify the Templates,CSS,JavaScript and commit these file, then everything will be finished. Backend engineers don't need to spend any time integrating these templates. This will be a perfect way for developing a website.

What is Web-Twins meanning?

Twins mean that it will give two websites, one is the Template Mode and another is the Real Mode. These two websites can switch to each other at any time. After you modify the source code in Template Mode, the Real mode will look like the same with Template Mode immedidately.

We suggest frontend engineer to use Node.js to develop the websites for Template mode. Use Nodejs and NPM to install this framework and double click the file server.js. It will quickly create a web server. You don't need to worry about the installation of web service, just focus on tempalte development.

Key-Wins
-------

* Connection: Web-Twins has two data format JSON and YAML. Web-Twins use the MVC design pattern to connect the backend and frontend program.
* Independent: Backend engineers can focus on programming without worrying about frontend HTML code or what CSS/JS should be loaded. Frontend enginners can also focus on template development.
* Switching: We can switch Template Mode and Real Mode to compare the difference, also backend engineer can switch t he web page to template mode seeing what is the original UI design of Frontend engineer. This will clarify the Frontend requirements for the programming integrator.   
* Debugging: Using JSON or YAML data to decide the responsibility of bug is belong to Backend or Frontend.
* Bulit-in Frontend Skill: CSS/JS combination, Image Sprite.


Infrastructure Design of web-twins
==================================

The framework will give what to you
-----------------------------------

* MVC: A MVC framework  for front-end and back-end engineers.
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

Web-Twins support the combo of CSS/JS to improve the frontend performace.  

Attention! <br />
Take care of the relative image path in css, if you use the combination of CSS,  be sure the combo url path has the same level with separated CSS file path.


* Less and Sass/Scaa
 
Web-Twins support less and sass/scss.

Attention for development!
--------------------------

* You should set head tag in page config, or the setting of head in site config will not be rendered.
