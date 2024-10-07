<!DOCTYPE html>
<html>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>

<body>
    <h1 style="text-align: center; background-color:aqua; ">Email Dashboard</h1>
    <canvas id="myChart" style="width:100%;max-width:600px"></canvas>
    <input type="hidden" id="myInput" value="{$NO_OF_CAMPAIGN}">
    <input type="hidden" id="access" value="{$ACCESS}">
    <input type="hidden" id="click" value="{$CLICK}">

    <script>
    var sent = document.getElementById("myInput").value;
    var access = document.getElementById("access").value;
    var click = document.getElementById("click").value;
    
        const xValues = ["Recipients", "Opened", "Unopened", "Clicked", "Unsubscribed"];
        var yValues = [sent, access, 0, click, 0, 0];
        const barColors = ["red", "green", "blue", "orange", "brown"];

        new Chart("myChart", {
            type: "bar",
            data: {
                labels: xValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: yValues
                }]
            },
            options: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: "Email tracking garph"
                }
            }
        });
    </script>
    </body>
</html>
