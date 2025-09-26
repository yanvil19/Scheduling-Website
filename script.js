function filterSections() {
    let program = document.getElementById("program").value;
    let year = document.getElementById("year").value;
    let sectionSelect = document.getElementById("section");

    // Save previously selected value
    let prevValue = sectionSelect.value;

    sectionSelect.innerHTML = '<option value="">-- All Sections --</option>';

    sectionsData.forEach(sec => {
        if ((program === "" || sec.ProgramID == program) &&
            (year === "" || sec.YearLevelID == year)) {
            let opt = document.createElement("option");
            opt.value = sec.SectionID;
            opt.textContent = sec.SectionName;
            sectionSelect.appendChild(opt);
        }
    });

    // Restore previous value if still valid
    if (prevValue) {
        let found = Array.from(sectionSelect.options).some(opt => opt.value == prevValue);
        if (found) {
            sectionSelect.value = prevValue;
        }
    }
}


function autoSubmit() {
    filterSections();

    let formData = new FormData(document.getElementById("scheduleForm"));
    fetch("schedule.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
    console.log("Schedule Data:", data); // ✅ Debug
    renderSchedule(data);
})

}

function renderSchedule(data) {
    let container = document.getElementById("scheduleTable");

    if (data.length === 0) {
        container.innerHTML = "<p><strong>No schedule found.</strong></p>";
        return;
    }

    let html = `
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Program</th>
                <th>Year</th>
                <th>Section</th>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Type</th>
                <th>Room</th>
                <th>Time Slot</th>
                <th>Day</th>
                <th>Duration (hrs)</th>
            </tr>
    `;

    data.forEach(row => {
        html += `
            <tr>
                <td>${row.ProgramCode}</td>
                <td>${row.YearLevel}</td>
                <td>${row.SectionName}</td>
                <td>${row.SubjectCode}</td>
                <td>${row.SubjectName}</td>
                <td>${row.Type}</td>
                <td>${row.RoomName}</td>
                <td>${row.TimeSlot}</td>
                <td>${row.day}</td>
                <td>${row.duration}</td>
            </tr>
        `;
    });

    html += "</table>";
    container.innerHTML = html;
}

// Load all schedule on page load
window.onload = () => {
    filterSections();
    autoSubmit();
};
