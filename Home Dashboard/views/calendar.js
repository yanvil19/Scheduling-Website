// calendar.js
document.addEventListener('DOMContentLoaded', function() {
    generateCurrentMonthCalendar();
});

function generateCurrentMonthCalendar() {
    const templateElement = document.querySelector('.template');
    
    if (!templateElement) {
        console.error('Template element not found');
        return;
    }

    // Get current date
    const today = new Date();
    const currentYear = today.getFullYear(); // 2025
    const currentMonth = today.getMonth(); // 8 (September is month 8, 0-indexed)
    const currentDay = today.getDate(); // 10
    
    // Update month name
    const monthNameElement = document.querySelector('.month-name');
    if (monthNameElement) {
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                           'July', 'August', 'September', 'October', 'November', 'December'];
        monthNameElement.textContent = `${monthNames[currentMonth]} ${currentYear}`;
    }

    // Clear existing content
    templateElement.innerHTML = '';

    // Create header section
    const headerSection = document.createElement('div');
    headerSection.className = 'header-change-bg-color';

    // Create week days header
    const weekDays = document.createElement('div');
    weekDays.className = 'week-days';
    
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    dayNames.forEach(dayName => {
        const weekDay = document.createElement('div');
        weekDay.className = 'week-day';
        const daySpan = document.createElement('div');
        daySpan.className = 'day2';
        daySpan.textContent = dayName;
        weekDay.appendChild(daySpan);
        weekDays.appendChild(weekDay);
    });

    const horizontalDivider = document.createElement('div');
    horizontalDivider.className = 'horizontal-divider';

    headerSection.appendChild(weekDays);
    headerSection.appendChild(horizontalDivider);

    // Create calendar body
    const calendar = document.createElement('div');
    calendar.className = 'calendar';

    // Generate calendar for current month
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    const daysInPrevMonth = new Date(currentYear, currentMonth, 0).getDate();
    
    // Create weeks array
    const weeks = [];
    let currentWeek = [];
    
    // Add previous month's days
    for (let i = firstDay - 1; i >= 0; i--) {
        currentWeek.push({ 
            day: daysInPrevMonth - i, 
            isCurrentMonth: false,
            isPrevMonth: true 
        });
    }
    
    // Add current month's days
    for (let day = 1; day <= daysInMonth; day++) {
        currentWeek.push({ 
            day: day, 
            isCurrentMonth: true,
            isToday: day === currentDay
        });
        
        if (currentWeek.length === 7) {
            weeks.push(currentWeek);
            currentWeek = [];
        }
    }
    
    // Add next month's days to complete the last week
    let nextMonthDay = 1;
    while (currentWeek.length < 7) {
        currentWeek.push({ 
            day: nextMonthDay, 
            isCurrentMonth: false,
            isNextMonth: true 
        });
        nextMonthDay++;
    }
    if (currentWeek.length > 0) {
        weeks.push(currentWeek);
    }

    // Generate HTML for weeks
    weeks.forEach((week, weekIndex) => {
        const weekRow = document.createElement('div');
        weekRow.className = 'week-row';
        
        const days = document.createElement('div');
        days.className = 'days';

        week.forEach((dayObj, dayIndex) => {
            const dayElement = document.createElement('div');
            const dayNumber = document.createElement('div');
            dayNumber.className = 'div';
            dayNumber.textContent = dayObj.day.toString().padStart(2, '0');

            // Add positioning class
            dayElement.classList.add(`day-col-${dayIndex}`);

            // Determine day styling
            if (!dayObj.isCurrentMonth) {
                // Previous/next month days - lighter color
                dayElement.className += ' day4'; // light blue background
                dayNumber.style.opacity = '0.5'; // Make text lighter
            } else if (dayObj.isToday) {
                // Today - highlight in yellow
                dayElement.className += ' day8';
            } else if (dayIndex === 0 || dayIndex === 6) {
                // Weekend days of current month
                dayElement.className += ' day10';
            } else {
                // Weekdays of current month
                dayElement.className += ' day3';
            }

            dayElement.appendChild(dayNumber);
            days.appendChild(dayElement);

            // Add vertical dividers between days (except last day)
            if (dayIndex < 6) {
                const divider = document.createElement('div');
                divider.className = `rectangle-${6 - dayIndex}`;
                days.appendChild(divider);
            }
        });

        weekRow.appendChild(days);

        // Add horizontal divider after each week (except last)
        if (weekIndex < weeks.length - 1) {
            const horizontalDivider2 = document.createElement('div');
            horizontalDivider2.className = 'horizontal-divider2';
            weekRow.appendChild(horizontalDivider2);
        }

        calendar.appendChild(weekRow);
    });

    // Append everything to template
    templateElement.appendChild(headerSection);
    templateElement.appendChild(calendar);
}

