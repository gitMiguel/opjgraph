
var selectedYear = new Date().getFullYear();
var selectedMonth = new Date().getMonth() + 1;
var selectedDay = new Date().getDate();


function loadPicture(time) {
    var xmlhttp = new XMLHttpRequest();
    var url = "scripts/linegraph.php?period=" + time;

    xmlhttp.open("GET",url,true);
    xmlhttp.responseType = 'arraybuffer';
    xmlhttp.onload = function() {
        var arrayBufferView = new Uint8Array( this.response );
        var blob = new Blob( [ arrayBufferView ], { type: "image/jpeg" } );
        var urlCreator = window.URL || window.webkitURL;
        var imageUrl = urlCreator.createObjectURL( blob );
        var img = document.querySelector( "#graph" );
        img.src = imageUrl;
    };
    xmlhttp.send();

    populateSelections();
}

function loadSelection() {
    var year = document.getElementById("year");
    selectedYear = year.options[year.selectedIndex].value;

    var month = document.getElementById("month");
    selectedMonth = month.options[month.selectedIndex].value;

    var day = document.getElementById("day");
    selectedDay = day.options[day.selectedIndex].value;

    var date = selectedYear + "-" + selectedMonth + "-" + selectedDay

    loadPicture(date);
}

function populateSelections() {
    populateSelection("year", calendar, selectedYear);
    populateSelection("month", calendar[document.getElementById('year').value], selectedMonth);
    populateSelection("day", calendar[document.getElementById('year').value][document.getElementById('month').value], selectedDay);
}

function populateSelection(selection, options, selectedValue) {
    var option = document.getElementById(selection);
    option.innerHTML = "";
    for (x in options) {
        var element = document.createElement("option");
        element.textContent = x;
        element.value = x;
        option.add(element);
    }
    document.getElementById(selection).value = selectedValue;
}
