
## OPJGraph - Easy charts for openHAB and MySQL ***[WIP]***

### Requirements (*Tested with*)

- Web server with PHP and GD support (Apache 2 + PHP 7)
- JpGraph library (4.2.2)
- OH MySQL Persistece


### Features

- View charts as images directly in your browser or in your sitemap. 
- Use "linegraph.php" or "bargraph.php" to draw images for current day and display them in your sitemap.


#### Line graph

- Time frame is 24 hours.
- Items with number values are drawn as line.
- State (*On/Off*) items are drawn as a bar at the bottom of the graph.
- Multiple graphs can be drawn under each other as a single image by specifying multiple *[chart\*]* sections in configuration.
-  *linegraph.php* accepts GET parameter *?period=[today,last24h,yyyy-mm-dd]*:
    * *today* - Today's data from 00:00:00 to 23:59:59.
    * *last24h* - Latest data to next full hour and 24 hours back.
    * *yyyy-mm-dd -  Historical data of specified date e.g. 2018-08-25. 


#### Bar graph

- Time frame is one week and is drawn as a grouped bar plot. One bar is an items average value per day.
- Only number items are supported.


#### Web page - index.php + opjgraph.js

- Simple web page to show configured graphs and access historical data.
- Selection elements for picking a date.
- Selections are automatically generated from dates in your database. *See Configuration - database.ini*


## Configuration


### Configuration file descriptions


#### Database - database.ini

| Setting           | Example value             | Description                                       |
| ----------------- |:------------------------- |:------------------------------------------------- |
|host               | your.database.com:3306    | Database host                                     |
|name               | mydatabase                | Database name                                     |
|uname              | user1                     | Database username                                 |
|pw                 | verysecretpassword        | Database password                                 |
|timetable          | outdoortemp_001           | Table name where to get all dates for saved data. |

- *timetable* - This is only used for *dates.php* to get distinct dates from your database.


#### Line graph - line.ini

| Setting           | Example value             | Description                                    |
| ----------------- |:------------------------- |:---------------------------------------------- |
|title              | My chart title            | Title of your graph.                           |
|sizev              | 1000                      | Vertical size of image in pixels               |
|sizeh              | 600                       | Horizonal size of image in pixels              |
|showlegend         | true                      | Wether to show legend box under chart          |
|legendcols         | 4                         | How many columns to have in legend box         |
|legendunit         | "&deg;C"                  | Text/unit to display after value in legend box |
|yaxistitle         | "Celsius"                 | Title for Y-axis                               |
|drawtofile         | false                     | Draw to a file. *false*/*"graph.png"*          |
|                   |                           |                                                |
|items[*yourlineitem*]  | "line:blue:Outdoor temperature:&deg;C" | Line item with *blue* color and legend title *Outdoor temperature*. |
|items[*yourstateitem*] | "state:green:Heat pump" | State item with *green* color and legen title *Heat pump*. |

- items[*yourlineitem*] - Item name in the square brackets *[ ]* as it is in your database. 

### Bar graph - bar.ini

| Setting           | Example value             | Description                                   |
| ----------------- |:------------------------- |:--------------------------------------------- |
|title              | My chart title            | Title of your graph.                          |
|sizev              | 1000                      | Vertical size of image                        |
|sizeh              | 600                       | Horizonal size of image                       |
|showlegend         | true                      | Wether to show legend box under chart         |
|legendcols         | 4                         | How many columns to have in legend box        |
|yaxistitle         | "Celsius"                 | Title for Y-axis                              |
|drawtofile         | false                     | Draw to a file. *false*/*"graph.png"*         |
|                   |                           |                                               |
|items[*yourbaritem*] | "lightblue:Indoor temperature" | Bar with *lightblue* color and legend title *Indoor temperature* |
|items[*yourbaritem*] | "lightgreen:Outdoor temperature" | Bar with *lightgreen* color and legend title *Outdoor temperature* |

- items[*yourbaritem*] - Item name in the square brackets *[ ]* as it is in your database.


### Security

As opjgraph.ini file contains your database username and password remember to set up your web server not to allow direct view of .ini files.

For example here's Apache's configuration section to hide all .ini and .inc files:

```
<Files \*.in\*>
    Order deny,allow
    Deny from all
<\/Files>
```

### License

Licensed under The MIT License (MIT). See [LICENSE.txt](../blob/master/LICENSE.txt)

### Changelog

Ver. 0.1 - 5.9.2018 
- First beta release

