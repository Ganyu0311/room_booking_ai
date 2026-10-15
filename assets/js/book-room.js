// Booking page: time slot filtering + room recommendations
// Generate 30-minute intervals (08:00 – 20:00)
function generateTimeSlots() {
    const slots = [];
    for (let h = 8; h <= 19; h++) {
        for (let m of [0, 30]) {
            slots.push(`${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:00`);
        }
    }
    slots.push("20:00:00");
    return slots;
}

function formatTime(t) {
    let [h, m] = t.split(":");
    h = parseInt(h);
    const suffix = h >= 12 ? "PM" : "AM";
    h = (h % 12) || 12;
    return `${h}:${m} ${suffix}`;
}

function makeDateTime(date, time) {
    return new Date(`${date}T${time}`);
}

function updateStartTimes() {
    const startSelect = document.getElementById("start_time");
    const endSelect = document.getElementById("end_time");
    const date = document.getElementById("date").value;

    startSelect.innerHTML = "";
    endSelect.innerHTML = "";

    if (!date) return;

    const now = new Date();
    const today = new Date().toISOString().split("T")[0];
    const slots = generateTimeSlots();

    slots.forEach(t => {
        const slotTime = makeDateTime(date, t);
        if (date === today && slotTime < now) return;
        startSelect.add(new Option(formatTime(t), t));
    });

    updateEndTimes();
    checkAvailability();
}

function updateEndTimes() {
    const date = document.getElementById("date").value;
    const startVal = document.getElementById("start_time").value;
    const endSelect = document.getElementById("end_time");

    endSelect.innerHTML = "";
    if (!date || !startVal) return;

    generateTimeSlots().forEach(t => {
        if (t > startVal) {
            endSelect.add(new Option(formatTime(t), t));
        }
    });

    checkAvailability();
}

// LIVE ROOM AVAILABILITY
function checkAvailability() {
    const date = document.getElementById("date").value;
    const start = document.getElementById("start_time").value;
    const end = document.getElementById("end_time").value;

    if (!date || !start || !end) return;

    fetch("../ai/get_available_rooms.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: `date=${encodeURIComponent(date)}&start_time=${encodeURIComponent(start)}&end_time=${encodeURIComponent(end)}`
    })
    .then(res => res.json())
    .then(data => {
        const box = document.getElementById("liveAvailability");
        box.style.display = "block";

        if (data.status !== "success") {
            box.innerHTML = "<p class='text-danger'>Error loading room availability.</p>";
            return;
        }

        let html = "<h5>Available Rooms</h5>";

        if (data.available.length === 0) {
            html += "<p class='text-danger'>No rooms available.</p>";
        } else {
            html += "<ul>";
            data.available.forEach(r => {
                html += `<li><b>${r.room_name}</b> (Capacity: ${r.capacity}, Equipment: ${r.equipment})</li>`;
            });
            html += "</ul>";
        }

        html += "<hr><h5>Unavailable Rooms</h5>";

        if (data.unavailable.length === 0) {
            html += "<p class='text-success'>All rooms are free.</p>";
        } else {
            html += "<ul>";
            data.unavailable.forEach(r => {
                html += `<li><b>${r.room_name}</b> (Booked)</li>`;
            });
            html += "</ul>";
        }

        box.innerHTML = html;
    });
}

document.getElementById("date").addEventListener("change", updateStartTimes);
document.getElementById("start_time").addEventListener("change", updateEndTimes);
document.getElementById("end_time").addEventListener("change", checkAvailability);

window.addEventListener("load", () => {
    const dateInput = document.getElementById("date");
    if (!dateInput.value) {
        dateInput.value = new Date().toISOString().split("T")[0];
    }
    updateStartTimes();
});

function getSelectedEquipmentCSV() {
    const checked = Array.from(document.querySelectorAll(".equipChk:checked")).map(x => x.value.trim());
    return checked.join(",");
}

document.getElementById("aiSuggest").addEventListener("click", function () {
    const date = document.getElementById("date").value;
    const start = document.getElementById("start_time").value;
    const end = document.getElementById("end_time").value;

    const capacity = document.getElementById("capacity").value || "";
    const equipmentCSV = getSelectedEquipmentCSV();

    if (!date || !start || !end) {
        alert("Please select date and time first");
        return;
    }

    fetch("../ai/recommend_room.php", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: `date=${encodeURIComponent(date)}&start_time=${encodeURIComponent(start)}&end_time=${encodeURIComponent(end)}&capacity=${encodeURIComponent(capacity)}&equipment=${encodeURIComponent(equipmentCSV)}`
    })
    .then(r => r.json())
    .then(data => {
        const box = document.getElementById("aiResult");
        box.style.display = "block";

        if (data.status === "success") {
            if (data.recommendations && data.recommendations.length > 0) {
                let html = `<h5>AI Recommended Rooms</h5><ol>`;
                data.recommendations.forEach(r => {
                    html += `<li>
                        <b>${r.room_name}</b> (Cap: ${r.capacity}, Equip: ${r.equipment})
                        <br><span class="text-muted">${r.explanation || ""}</span>
                        <br><button type="button" class="btn btn-sm btn-primary mt-2" onclick="selectRoom(${r.room_id})">Use this room</button>
                        <br><br>
                    </li>`;
                });
                html += `</ol>`;
                box.innerHTML = html;
            } else {
                box.innerHTML = `<p class="text-warning">No recommended room found.</p>`;
            }
        } else {
            box.innerHTML = `<p class="text-danger">${data.message || "AI error"}</p>`;
        }
    });
});

function selectRoom(roomId) {
    document.getElementById("room_id").value = roomId;
}
