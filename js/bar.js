var de = document.documentElement,
    g = document.getElementsByTagName('body')[0],
    xWidth = window.innerWidth || de.clientWidth || g.clientWidth,
    yWidth = window.innerHeight|| de.clientHeight|| g.clientHeight;

// Set the dimensions of the canvas / graph
var margin = {top: 30, right: 20, bottom: 70, left: 30},
    width = xWidth - margin.left - margin.right,
    height = (yWidth - margin.top - margin.bottom) / 4;

// Parse the date / time
var parseDate = d3.timeParse("%Y-%m-%d %H:%M:%S");

// Set the ranges
var xScale = d3.scaleBand().rangeRound([0, width]).padding(0.1);
var yScale = d3.scaleLinear().rangeRound([height, 0]);

// Adds the svg canvas
var svg2 = d3.select("body")
    .append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .attr("class", "graph-background")
    .append("g")
        .attr("transform", 
              "translate(" + margin.left + "," + margin.top + ")");


// Get the data
d3.json("./core/yearaverages.php").then(function(data) {
    data.forEach(function(d) {
        d.time = parseDate(d.time);
        d.value = +d.value;
    });

    // Scale the range of the data
    xScale.domain(data.map(function (d) { return d.time; }));
    yScale.domain([-20, 25]);

    // Add the X Axis
    svg2.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(d3.axisBottom(xScale).tickFormat(d3.timeFormat("%Y-%m")))
        .selectAll("text") 
        .style("text-anchor", "end")
        .attr("dx", "-.8em")
        .attr("dy", ".15em")
        .attr("transform", "rotate(-45)");

    // Add the Y Axis
    svg2.append("g")
        .attr("class", "y axis")
        .call(d3.axisLeft(yScale));

    svg2.selectAll(".bar")
        .data(data)
        .enter().append("rect")
        .attr("class", "bar")
        .attr("x", function (d) {
            return xScale(d.time);
        })
        .attr("y", function (d) {
            return yScale(Number(d.value));
        })
        .attr("width", xScale.bandwidth())
        .attr("height", function (d) {
            return height - yScale(Number(d.value));
    });
});