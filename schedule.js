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

    function downloadScheduleCSV() {
    // Get current filter values
    const program = document.getElementById('program').value;
    const year = document.getElementById('year').value;
    const section = document.getElementById('section').value;
    
    // Get schedule data from the current display
    const scheduleData = extractScheduleData();
    
    if (scheduleData.length === 0) {
        alert('No schedule data to download. Please load schedules first.');
        return;
    }
    
    const csvContent = convertToCSV(scheduleData);
    const filename = generateFilename(program, year, section);
    
    downloadCSV(csvContent, filename);
}

function extractScheduleData() {
    const scheduleContent = document.getElementById('schedule-content');
    const sectionBlocks = scheduleContent.querySelectorAll('.section-calendar-block');
    const data = [];
    
    sectionBlocks.forEach((block, blockIndex) => {
        // Get section name from the header
        const sectionHeader = block.querySelector('.section-calendar-header');
        const sectionName = sectionHeader ? sectionHeader.textContent.trim() : 'Unknown Section';
        
        // Add section header row
        data.push([sectionName]);
        data.push([]); // Empty row for spacing
        
        // Get the table within this section block
        const table = block.querySelector('table');
        if (!table) return;
        
        const rows = table.querySelectorAll('tr');
        
        rows.forEach((row, rowIndex) => {
            const cells = row.querySelectorAll('td, th');
            const rowData = [];
            
            cells.forEach((cell, cellIndex) => {
                if (rowIndex === 0) {
                    // Header row - just get text content
                    rowData.push(cell.textContent.trim());
                } else if (cellIndex === 0) {
                    // Time column - just get the text
                    rowData.push(cell.textContent.trim());
                } else {
                    // Day columns - extract and format course information
                    const scheduleBlocks = cell.querySelectorAll('.schedule-block');
                    
                    if (scheduleBlocks.length > 0) {
                        let cellContent = [];
                        
                        scheduleBlocks.forEach(block => {
                            let courseInfo = '';
                            
                            // Get course code
                            const courseCodeElement = block.querySelector('.subject-name');
                            if (courseCodeElement) {
                                courseInfo = courseCodeElement.textContent.trim();
                            }
                            
                            // Get course details and clean up formatting
                            const detailsElement = block.querySelector('.subject-details');
                            if (detailsElement) {
                                const fullDetails = detailsElement.textContent
                                    .trim()
                                    .replace(/\s+/g, ' ')
                                    .replace(/\s*\n\s*/g, '\n');
                                
                                if (fullDetails) {
                                    courseInfo += '\n' + fullDetails;
                                }
                            }
                            
                            if (courseInfo) {
                                cellContent.push(courseInfo);
                            }
                        });
                        
                        // Join multiple courses in the same cell
                        rowData.push(cellContent.join('\n\n'));
                    } else {
                        // Empty cell
                        rowData.push('');
                    }
                }
            });
            
            if (rowData.length > 0) {
                data.push(rowData);
            }
        });
        
        // Add empty row between different sections
        if (blockIndex !== sectionBlocks.length - 1) {
            data.push([]);
        }
    });
    
    return data;
}

function getSectionName(table) {
    // Method 1: Look for section name in previous sibling elements
    let currentElement = table.previousElementSibling;
    while (currentElement) {
        // Check for section headers (h1-h6, divs with section info, etc.)
        if (currentElement.tagName && currentElement.tagName.match(/^H[1-6]$/)) {
            return currentElement.textContent.trim();
        }
        
        // Check for divs or spans that might contain section info
        if (currentElement.classList && 
            (currentElement.classList.contains('section-title') || 
             currentElement.classList.contains('section-header') ||
             currentElement.classList.contains('schedule-section'))) {
            return currentElement.textContent.trim();
        }
        
        // Look for text content that might indicate section
        const text = currentElement.textContent?.trim();
        if (text && (text.includes('Section') || text.includes('Year') || text.includes('Program'))) {
            return text;
        }
        
        currentElement = currentElement.previousElementSibling;
    }
    
    // Method 2: Look for section info in table caption or first row
    const caption = table.querySelector('caption');
    if (caption) {
        return caption.textContent.trim();
    }
    
    // Method 3: Look for section info in table attributes
    if (table.dataset.section) {
        return table.dataset.section;
    }
    
    // Method 4: Extract from table ID or class
    if (table.id) {
        const match = table.id.match(/section[_-]?(\w+)/i);
        if (match) return `Section ${match[1]}`;
    }
    
    // Method 5: Look in parent container for section info
    let parentElement = table.parentElement;
    while (parentElement && parentElement !== document.body) {
        const sectionInfo = parentElement.querySelector('.section-name, .section-title, [data-section]');
        if (sectionInfo) {
            return sectionInfo.textContent.trim() || sectionInfo.dataset.section;
        }
        parentElement = parentElement.parentElement;
    }
    
    return null; // No section name found
}

function convertToCSV(data) {
    const csvRows = [];
    
    data.forEach(row => {
        const csvRow = row.map(cell => {
            // Escape quotes and wrap in quotes if contains comma or quote
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
        // For IE
        navigator.msSaveBlob(blob, filename);
    } else {
        // For other browsers
        const url = URL.createObjectURL(blob);
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }
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
    document.getElementById('download-csv').addEventListener('click', function() {
        downloadScheduleCSV();
    });
});
