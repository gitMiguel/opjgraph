## OPJGraph - Easy charts for openHAB and MySQL

### Requirements (*Tested with*)

- Web server with PHP (Apache 2 + PHP 7)
- JpGraph library (4.2.2)
- OH MySQL Persistece


### Usage

- View charts as images directly in your browser. 
- Use graph.php to create images for current day and display them in your sitemap.


## Configuration

See configuration file for config descriptions


### Security

As opjgraph.ini file contains your database username and password remember to set up your web server now to allow direct view of .ini files.

For example here's apaches configuration section to hide all .ini and .inc files:

<Files \*.in\*>
    Order deny,allow
    Deny from all
<\/Files>
