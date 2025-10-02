document.addEventListener("DOMContentLoaded", () => {
    const programSelect = document.getElementById("program");
    const yearSelect = document.getElementById("year");
    const sectionSelect = document.getElementById("section");
    const scheduleContainer = document.getElementById("schedule-container");
    const scheduleContent = document.getElementById("schedule-content");
    const loading = document.getElementById("loading");

    // Load schedules
    function loadSchedule() {
        const program = programSelect.value;
        const year = yearSelect.value;
        const section = sectionSelect.value;

        scheduleContent.innerHTML = "";
        loading.style.display = "block";
        scheduleContainer.style.display = "block";

        fetch("api.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=get_schedule&program=${program}&year=${year}&section=${section}`
        })
            .then(response => response.json())
            .then(data => {
                loading.style.display = "none";
                if (data.success && data.data.length > 0) {
                    displaySchedule(data.data);
                } else {
                    scheduleContent.innerHTML =
                        "<p class='no-schedule'>No schedules found.</p>";
                }
            })
            .catch(err => {
                loading.style.display = "none";
                scheduleContent.innerHTML =
                    "<p class='no-schedule'>Error loading schedule.</p>";
                console.error(err);
            });
    }

    // Display grouped schedules
    function displaySchedule(schedules) {
        const grouped = {};
        schedules.forEach(sch => {
            if (!grouped[sch.SectionName]) grouped[sch.SectionName] = [];
            grouped[sch.SectionName].push(sch);
        });

        scheduleContent.innerHTML = "";

        for (const [section, items] of Object.entries(grouped)) {
            const table = buildScheduleTable(section, items);
            scheduleContent.innerHTML += table;
        }
    }

    // Load sections dynamically
    function loadSections() {
        const program = programSelect.value;
        const year = yearSelect.value;

        if (!program || !year) {
            sectionSelect.innerHTML = `<option value="">All Sections</option>`;
            return;
        }

        fetch("api.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=get_sections&program=${program}&year=${year}`
        })
            .then(response => response.json())
            .then(data => {
                sectionSelect.innerHTML = `<option value="">All Sections</option>`;
                if (data.length > 0) {
                    data.forEach(sec => {
                        const opt = document.createElement("option");
                        opt.value = sec.name;
                        opt.textContent = sec.name;
                        sectionSelect.appendChild(opt);
                    });
                }
            })
            .catch(err => {
                sectionSelect.innerHTML =
                    `<option value="">Error loading sections</option>`;
                console.error(err);
            });
    }

    // Build schedule table
    function buildScheduleTable(sectionName, items) {
        const days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        const slots = [
            "7:00 AM - 9:00 AM", "9:00 AM - 11:00 AM", "11:00 AM - 1:00 PM",
            "1:00 PM - 3:00 PM", "3:00 PM - 5:00 PM", "5:00 PM - 7:00 PM", "7:00 PM - 9:00 PM"
        ];

        // ✅ Remove duplicates
        const uniqueItems = [];
        const seen = new Set();
        items.forEach(s => {
            const key = `${s.SubjectCode}|${s.DayOfWeek}|${s.StartTime}|${s.EndTime}|${s.RoomName}|${s.Type}`;
            if (!seen.has(key)) {
                seen.add(key);
                uniqueItems.push(s);
            }
        });

        let html = `
            <div class="section-calendar-block">
                <div class="section-calendar-header">${sectionName}</div>
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            ${days.map(day => `<th>${day}</th>`).join("")}
                        </tr>
                    </thead>
                    <tbody>
        `;

        slots.forEach(slot => {
            html += `<tr><td>${slot}</td>`;

            days.forEach(day => {
                const matches = uniqueItems.filter(s =>
                    s.DayOfWeek === day &&
                    `${s.StartTime} - ${s.EndTime}` === slot
                );

                if (matches.length > 0) {
                    html += `<td class="slot-cell">`;
                    matches.forEach(match => {
                        const isVirtual = match.RoomName && match.RoomName.toLowerCase().includes('virtual');
                        const colorClass = isVirtual ? "online-block" : "f2f-block";

                        // ✅ dynamic height based on duration
                        let heightPercent = "100%";
                        if (parseFloat(match.Duration) <= 1.4) {
                            heightPercent = "67%";
                        }

                        html += `
                            <div class="schedule-block ${colorClass}" style="height:${heightPercent}">
                                <div class="subject-name">${match.SubjectCode}</div>
                                <div class="subject-details">
                                    ${match.SubjectName}<br>
                                    ${match.RoomName}<br>
                                    ${match.Type}<br>
                                    ${match.Duration} hrs
                                </div>
                            </div>
                        `;
                    });
                    html += `</td>`;
                } else {
                    html += `<td class="empty-block"></td>`;
                }
            });

            html += `</tr>`;
        });

        html += `</tbody></table></div>`;
        return html;
    }

    // Event listeners
    programSelect.addEventListener("change", () => {
        loadSections();
        loadSchedule();
    });
    yearSelect.addEventListener("change", () => {
        loadSections();
        loadSchedule();
    });
    sectionSelect.addEventListener("change", () => {
        loadSchedule();
    });
});
