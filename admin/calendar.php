<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

require "../includes/header.php";
require "../includes/navbar.php";
?>

<!-- STOP TOOLTIP FROM BLOCKING CLICKS -->
<style>
.tippy-box { pointer-events: none !important; }
</style>

<!-- BOOTBOX -->
<script src="https://cdn.jsdelivr.net/npm/bootbox@5/bootbox.min.js"></script>

<!-- Tippy Tooltip -->
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light.css" />
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>

<div class="container mt-4">
    <h2>📅 Room Availability Calendar</h2>
    <p class="text-muted">Hover or click an event to see full details.</p>
    <div id="calendar"></div>
</div>

<?php require "../includes/footer.php"; ?>

<!-- FULLCALENDAR -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.5/index.global.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    var calendar = new FullCalendar.Calendar(document.getElementById("calendar"), {
        initialView: "dayGridMonth",
        height: 650,

        eventSources: [{
            url: "calendar_data.php",
            color: "#007bff",
            textColor: "white"
        }],

        /* Tooltip */
        eventDidMount: function(info) {
            tippy(info.el, {
                content: `
                    <b>Room:</b> ${info.event.extendedProps.room}<br>
                    <b>User:</b> ${info.event.extendedProps.user_name}<br>
                    <b>Date:</b> ${info.event.start.toISOString().split("T")[0]}<br>
                    <b>Time:</b> ${info.event.extendedProps.start_time} - ${info.event.extendedProps.end_time}
                `,
                allowHTML: true,
                theme: "light",
                placement: "top"
            });
        },

        /* Click popup */
        eventClick: function(info) {

            const p = info.event.extendedProps;
            const date = info.event.start.toISOString().split("T")[0];

            bootbox.dialog({
                title: "📌 Booking Details",
                message: `
                    <p><b>Room:</b> ${p.room}</p>
                    <p><b>User:</b> ${p.user_name}</p>
                    <p><b>Date:</b> ${date}</p>
                    <p><b>Time:</b> ${p.start_time} - ${p.end_time}</p>

                    <button class="btn btn-danger mt-3" id="cancelBook" data-id="${p.booking_id}">
                        Cancel Booking
                    </button>
                `,
                size: "small"
            });

            setTimeout(() => {
                document.getElementById("cancelBook").onclick = function () {

                    const bookingID = this.getAttribute("data-id");

                    bootbox.confirm({
                        message: "Are you sure you want to cancel this booking?",
                        buttons: {
                            confirm: { label: "Yes", className: "btn-danger" },
                            cancel:  { label: "No", className: "btn-secondary" }
                        },
                        callback: function(result) {
                            if (result) {
                                fetch("cancel_booking_admin.php?id=" + bookingID)
                                    .then(r => r.json())
                                    .then(res => {
                                        if (res.status === "success") {
                                            bootbox.alert("Booking cancelled!", function () {
                                                info.event.remove();
                                            });
                                        } else {
                                            bootbox.alert("Error: " + res.message);
                                        }
                                    });
                            }
                        }
                    });

                };
            }, 200);
        }
    });

    calendar.render();
});
</script>







