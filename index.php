<!DOCTYPE html>
<style>
.custom-button {
    padding-top: 1px;
    padding-bottom: 1px;
    text-align: center;
    font-family: Arial;
    font-size: 16px;
    cursor: pointer;
}
</style>
<head>
    <?php include("scripts/dates.php"); ?>
    <script type="text/javascript"> var calendar = <?php echo json_encode($calendar); ?>; </script>
    <script type="text/javascript" src="scripts/opjgraph.js"> </script>
</head>

<body onload="loadPicture('today')">
    
    <table align=center width=1000>
        <tr>
            <td align=center valign=middle colspan=2 bgcolor=lightgrey>
                <h2>OPJGraph</h2></div>
            </td>
        </tr>
        <tr>
            <td bgcolor=white>
        </tr>
        <tr bgcolor=lightgrey height=50>
            <td align=center bgcolor=lightgrey width=50%>
                <button class="custom-button" type="button" onclick="loadPicture('today')">Today</button>
                <button class="custom-button" type="button" onclick="loadPicture('last24h')">Last 24h</button>
            </td>
            <td align=center>
                <select id="year" onchange="populateSelection('month', calendar[document.getElementById('year').value], selectedMonth);">
                    <option>Year</option>
                </select>
                <select id="month" onchange="populateSelection('day', calendar[document.getElementById('year').value][document.getElementById('month').value], selectedDay);">
                    <option>Month</option>
                </select>
                <select id="day" onchange="loadSelection()">
                    <option>Day</option>
                </select>
            </td>
        </tr>
    </table>
    <table border=0 align=center valign=middle>
        <td align=center valign=top>
            <img id="graph"/>
        </td>
    </table>
</body>
</html>