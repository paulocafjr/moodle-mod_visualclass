// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Sets finaliza.html page information from server
 *
 * @param {String} script
 * @param {String} data
 */
function send_data(script, data) {
    // Solving url
    var max_split = 30;
    var host = window.location.hostname;
    var path = window.location.pathname.split("/", max_split);
    var mod_home = "/mod/visualclass/";

    path.pop();
    path.pop();
    path.pop();
    path.pop();

    var url = "http://" + host + path.join("/") + mod_home + script;

    var ajax;
    if (window.XMLHttpRequest) {
        ajax = new XMLHttpRequest();
    } else {
        ajax = new ActiveXObject("Microsoft.XMLHTTP");
    }

    ajax.open("POST", url, true);
    ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajax.send(data);

    // Fetching Response
    ajax.onreadystatechange = function () {
        if (ajax.readyState == 4 && ajax.status == 200) { // Everything went ok
            // Retrieving information
            var values = JSON.parse(ajax.responseText);

            document.getElementById("urlredirect").value = values.urlredirect;

            document.getElementById("button").value = values.buttontext;
            document.getElementById("button").style.visibility = "visible";

            document.getElementById("labelcorrect").innerHTML = values.labelcorrect;
            document.getElementById("labelwrong").innerHTML = values.labelwrong;
            document.getElementById("labelscore").innerHTML = values.labelscore;
            document.getElementById("valuecorrect").style.visibility = "visible";
            document.getElementById("valuewrong").style.visibility = "visible";
            document.getElementById("valuescore").style.visibility = "visible";

            document.getElementById("status").src = "status_ok.png";
            document.getElementById("status").style.visibility = "visible";

            document.getElementById("text").innerHTML = values.message;
        } else if (ajax.readyState == 4 && ajax.status != 200) { // Something went wrong
            document.getElementById("button").style.visibility = "visible";
            document.getElementById("button").value = "!";

            document.getElementById("labelcorrect").style.visibility = "hidden";
            document.getElementById("labelwrong").style.visibility = "hidden";
            document.getElementById("labelscore").style.visibility = "hidden";
            document.getElementById("valuecorrect").style.visibility = "hidden";
            document.getElementById("valuewrong").style.visibility = "hidden";
            document.getElementById("valuescore").style.visibility = "hidden";

            document.getElementById("status").src = "status_error.png";
            document.getElementById("status").style.visibility = "visible";

            document.getElementById("text").innerHTML = "Session Error";
        }
    }
}
