// Extracted from admin/dashboard.php
// Fetch dashboard data
fetch("dashboard_data.php")
    .then(res => res.json())
    .then(data => {

        // ----- STAT CARDS -----
        document.getElementById("statCards").innerHTML = `
            <div class="col-md-3">
                <div class="card shadow p-3">
                    <h5>Total Users</h5>
                    <h3>${data.total_users}</h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow p-3">
                    <h5>Total Rooms</h5>
                    <h3>${data.total_rooms}</h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow p-3">
                    <h5>Total Bookings</h5>
                    <h3>${data.total_bookings}</h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow p-3">
                    <h5>Total Check-ins</h5>
                    <h3>${data.total_checkins}</h3>
                </div>
            </div>

            <div class="col-md-12 mt-4">
                <div class="card shadow p-3">
                    <h5>Today’s Check-ins</h5>
                    <h3>${data.today_checkins}</h3>
                </div>
            </div>
        `;


        // ----- ROOM CHART -----
        new Chart(document.getElementById("roomChart"), {
            type: "bar",
            data: {
                labels: data.room_usage.map(x => x.room_name),
                datasets: [{
                    label: "Bookings",
                    data: data.room_usage.map(x => x.total),
                    backgroundColor: "rgba(54,162,235,0.6)"
                }]
            }
        });

        // ----- DAILY BOOKING CHART -----
        new Chart(document.getElementById("dailyChart"), {
            type: "line",
            data: {
                labels: data.daily_bookings.map(x => x.date),
                datasets: [{
                    label: "Bookings",
                    data: data.daily_bookings.map(x => x.total),
                    borderColor: "rgba(255,99,132,1)",
                    fill: false
                }]
            }
        });

    });
