document.addEventListener("DOMContentLoaded", () => {
    const programSelect = document.getElementById("program");
    const yearSelect = document.getElementById("year");
    const sectionSelect = document.getElementById("section");
    const scheduleContainer = document.getElementById("schedule-container");
    const scheduleContent = document.getElementById("schedule-content");
    const loading = document.getElementById("loading");

    const roomUsageBtn = document.getElementById("room-usage");
    const roomUsageModal = document.getElementById("room-usage-modal");
    const closeModal = document.querySelector(".close-modal");
    const roomSelect = document.getElementById("room-select");
    const roomUsageContent = document.getElementById("room-usage-content");

    // Store schedule data globally when loaded
    let currentScheduleData = [];

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
                    currentScheduleData = data.data; // Store the schedule data
                    displaySchedule(data.data);
                } else {
                    currentScheduleData = []; // Clear stored data
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

        // Remove duplicates
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

                        // dynamic height based on duration
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
                                    ${match.ActualStartTime}-${match.ActualEndTime}
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

    function downloadScheduleCSV() {
        // Get current filter values
        const program = document.getElementById('program').value;
        const year = document.getElementById('year').value;
        const section = document.getElementById('section').value;

        // Fetch CSV-specific data from API
        fetch("api.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=get_schedule_csv&program=${program}&year=${year}&section=${section}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    const csvContent = convertToCSV(data.data);
                    const filename = generateFilename(program, year, section);
                    downloadCSV(csvContent, filename);
                } else {
                    alert('No schedule data to download. Please load schedules first.');
                }
            })
            .catch(err => {
                alert('Error downloading CSV.');
                console.error(err);
            });
    }

    function convertToCSV(scheduleData) {
        const data = [];

        // CSV header row
        data.push(['Course Code', 'Course Title', 'Program', 'Section', 'Day', 'Start', 'End', 'Type', 'Room']);

        // Helper to format time to HH:MM:SS (24-hour format)
        function formatTime(timeStr) {
            if (!timeStr) return '';
            // Try parsing common formats like "7:00 AM" or "07:00"
            const date = new Date(`1970-01-01T${timeStr.replace(/ /g, '')}`);
            if (!isNaN(date.getTime())) {
                return date.toTimeString().split(' ')[0]; // "HH:MM:SS"
            }

            // If AM/PM format
            const match = timeStr.match(/(\d{1,2}):(\d{2})\s*(AM|PM)?/i);
            if (match) {
                let [_, h, m, ap] = match;
                h = parseInt(h, 10);
                m = parseInt(m, 10);
                if (ap) {
                    if (ap.toUpperCase() === 'PM' && h < 12) h += 12;
                    if (ap.toUpperCase() === 'AM' && h === 12) h = 0;
                }
                return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:00`;
            }

            return timeStr; // fallback
        }

        // Add each row
        scheduleData.forEach(schedule => {
            data.push([
                schedule.SubjectCode || '',
                schedule.SubjectName || '',
                schedule.ProgramName || '',
                schedule.SectionName || '',
                schedule.DayOfWeek || '',
                formatTime(schedule.ActualStartTime || schedule.StartTime || ''),
                formatTime(schedule.ActualEndTime || schedule.EndTime || ''),
                schedule.Type || '',
                schedule.RoomName || ''
            ]);
        });

        // Convert to CSV text
        const csvRows = [];
        data.forEach(row => {
            const csvRow = row.map(cell => {
                const escaped = String(cell).replace(/"/g, '""');
                return escaped.includes(',') || escaped.includes('"') || escaped.includes('\n')
                    ? `"${escaped}"`
                    : escaped;
            });
            csvRows.push(csvRow.join(','));
        });

        return csvRows.join('\n');
    }


    function generateFilename(program, year, section) {
        let filename = 'SigmaScheds_Schedule';

        if (program) filename += `_${program}`;
        if (year) filename += `_Year${year}`;
        if (section) filename += `_Section${section}`;

        const date = new Date();
        const dateStr = date.toISOString().split('T')[0];
        filename += `_${dateStr}.csv`;

        return filename;
    }

    function downloadCSV(csvContent, filename) {
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');

        if (navigator.msSaveBlob) {
            navigator.msSaveBlob(blob, filename);
        } else {
            const url = URL.createObjectURL(blob);
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }
    }

    function loadRooms() {
        fetch("api.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "action=get_rooms"
        })
            .then(response => response.json())
            .then(data => {
                roomSelect.innerHTML = '<option value="">Choose a room</option>';
                if (data.success && data.data.length > 0) {
                    data.data.forEach(room => {
                        const option = document.createElement("option");
                        option.value = room;
                        option.textContent = room;
                        roomSelect.appendChild(option);
                    });
                }
            })
            .catch(err => {
                console.error("Error loading rooms:", err);
                roomSelect.innerHTML = '<option value="">Error loading rooms</option>';
            });
    }

    function loadRoomUsage(roomName) {
        if (!roomName) {
            roomUsageContent.innerHTML = "";
            return;
        }

        roomUsageContent.innerHTML = '<div class="loading">Loading room usage...</div>';

        fetch("api.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=get_room_usage&room=${encodeURIComponent(roomName)}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayRoomUsage(data.data, data.room);
                } else {
                    roomUsageContent.innerHTML = '<p class="error">Error loading room usage data.</p>';
                }
            })
            .catch(err => {
                roomUsageContent.innerHTML = '<p class="error">Error loading room usage data.</p>';
                console.error("Error:", err);
            });
    }

    function displayRoomUsage(usageData, roomName) {
        if (usageData.length === 0) {
            roomUsageContent.innerHTML = `<p>No schedules found for room <strong>${roomName}</strong>.</p>`;
            return;
        }

        // Define the time slots (same as your main schedule)
        const timeSlots = [
            "7:00 AM - 9:00 AM",
            "9:00 AM - 11:00 AM",
            "11:00 AM - 1:00 PM",
            "1:00 PM - 3:00 PM",
            "3:00 PM - 5:00 PM",
            "5:00 PM - 7:00 PM",
            "7:00 PM - 9:00 PM"
        ];

        const days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

        // Group data by day and time
        const scheduleGrid = {};

        // Initialize empty grid
        timeSlots.forEach(slot => {
            scheduleGrid[slot] = {};
            days.forEach(day => {
                scheduleGrid[slot][day] = [];
            });
        });

        // Populate the grid with schedule data
        usageData.forEach(item => {
            const actualTime = (item.ActualStartTime && item.ActualEndTime)
                ? `${item.ActualStartTime} - ${item.ActualEndTime}`
                : `${item.StartTime} - ${item.EndTime}`;

            // Find matching time slot
            let matchingSlot = null;
            for (let slot of timeSlots) {
                if (actualTime === slot || `${item.StartTime} - ${item.EndTime}` === slot) {
                    matchingSlot = slot;
                    break;
                }
            }

            // If no exact match, try to find the slot that contains this time
            if (!matchingSlot) {
                const startTime = item.ActualStartTime || item.StartTime;
                const endTime = item.ActualEndTime || item.EndTime;

                for (let slot of timeSlots) {
                    const [slotStart, slotEnd] = slot.split(' - ');
                    if (startTime.includes(slotStart.split(':')[0]) ||
                        endTime.includes(slotEnd.split(':')[0])) {
                        matchingSlot = slot;
                        break;
                    }
                }
            }

            if (matchingSlot && scheduleGrid[matchingSlot] && scheduleGrid[matchingSlot][item.DayOfWeek]) {
                scheduleGrid[matchingSlot][item.DayOfWeek].push({
                    ...item,
                    displayTime: actualTime
                });
            }
        });

        // Build the HTML table
        let html = `
            <div class="room-usage-table-container">
                <h4>${roomName}</h4>
                <table class="room-usage-table">
                    <thead>
                        <tr>
                            <th class="time-header">Time</th>
                            ${days.map(day => `<th class="day-header">${day}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>
        `;

        // Build table rows
        timeSlots.forEach(timeSlot => {
            html += `<tr><td class="time-slot">${timeSlot}</td>`;

            days.forEach(day => {
                const schedules = scheduleGrid[timeSlot][day];

                if (schedules.length > 0) {
                    html += `<td class="schedule-cell">`;

                    schedules.forEach((schedule, index) => {
                        const isVirtual = schedule.RoomName && schedule.RoomName.toLowerCase().includes('virtual');
                        const colorClass = isVirtual ? "online-schedule" : "f2f-schedule";

                        html += `
                            <div class="schedule-item ${colorClass}">
                                <div class="subject-code">${schedule.SubjectCode}</div>
                                <div class="subject-details">
                                    <div class="subject-name">${schedule.SubjectName}</div>
                                    <div class="section-info">${schedule.SectionName}</div>
                                    <div class="program-info">${schedule.ProgramName}</div>
                                    <div class="type-info">${schedule.Type}</div>
                                    <div class="time-info">${schedule.displayTime}</div>
                                </div>
                            </div>
                        `;

                        if (index < schedules.length - 1) {
                            html += `<hr class="schedule-divider">`;
                        }
                    });

                    html += `</td>`;
                } else {
                    html += `<td class="empty-cell"></td>`;
                }
            });

            html += `</tr>`;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        roomUsageContent.innerHTML = html;
    }

    // Modal Event Listeners
    roomUsageBtn.addEventListener("click", (e) => {
        e.preventDefault();
        roomUsageModal.style.display = "block";
        loadRooms();
    });

    closeModal.addEventListener("click", () => {
        roomUsageModal.style.display = "none";
        roomUsageContent.innerHTML = "";
        roomSelect.value = "";
    });

    // Close modal when clicking outside of it
    window.addEventListener("click", (e) => {
        if (e.target === roomUsageModal) {
            roomUsageModal.style.display = "none";
            roomUsageContent.innerHTML = "";
            roomSelect.value = "";
        }
    });

    roomSelect.addEventListener("change", (e) => {
        loadRoomUsage(e.target.value);
    });

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
    document.getElementById('download-csv').addEventListener('click', function () {
        downloadScheduleCSV();
    });
});