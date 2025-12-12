/**
 * ============================================
 * CALENDAR VIEW LOGIC - FIXED
 * ============================================
 */

let currentCalendarDate = new Date();
let currentView = 'list'; // 'list' or 'calendar'

// Initialize Calendar
function initCalendar() {
    renderCalendar();

    // Add event listeners for navigation
    document.getElementById('prevMonth').addEventListener('click', () => changeMonth(-1));
    document.getElementById('nextMonth').addEventListener('click', () => changeMonth(1));
}

// Switch View Function
function switchView(view) {
    currentView = view;
    const listView = document.getElementById('tournoiList');
    const calendarView = document.getElementById('calendarView');
    const btnList = document.getElementById('btnListView');
    const btnCalendar = document.getElementById('btnCalendarView');

    if (view === 'list') {
        listView.style.display = 'flex'; // Bootstrap row is flex
        calendarView.style.display = 'none';
        btnList.classList.add('active');
        btnCalendar.classList.remove('active');
        // Re-apply filters to list
        applyFilters();
    } else {
        listView.style.display = 'none';
        calendarView.style.display = 'block';
        btnList.classList.remove('active');
        btnCalendar.classList.add('active');
        renderCalendar();
    }
}

// Change Month
function changeMonth(delta) {
    currentCalendarDate.setMonth(currentCalendarDate.getMonth() + delta);
    renderCalendar();
}

// Render Calendar Grid
function renderCalendar() {
    const gridContainer = document.querySelector('.calendar-grid');
    const monthTitle = document.getElementById('calendarTitle');

    if (!gridContainer || !monthTitle) return;

    // Find the actual grid element (after headers)
    let grid = document.getElementById('calendarGrid');

    // If grid doesn't exist, we need to restructure
    if (!grid) {
        // Clear everything except headers
        const headers = Array.from(gridContainer.querySelectorAll('.calendar-day-header'));
        gridContainer.innerHTML = '';

        // Re-add headers
        headers.forEach(header => gridContainer.appendChild(header));

        // Create grid container for days
        grid = document.createElement('div');
        grid.id = 'calendarGrid';
        grid.style.display = 'contents'; // This makes children part of parent grid
        gridContainer.appendChild(grid);
    }

    grid.innerHTML = ''; // Clear existing days

    const year = currentCalendarDate.getFullYear();
    const month = currentCalendarDate.getMonth();

    // Update Title
    const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];
    monthTitle.textContent = `${monthNames[month]} ${year}`;

    // Calculate days
    const firstDay = new Date(year, month, 1).getDay(); // 0 = Sunday
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    // Render empty slots for previous month
    for (let i = 0; i < firstDay; i++) {
        const dayEl = document.createElement('div');
        dayEl.className = 'calendar-day other-month';
        grid.appendChild(dayEl);
    }

    // Render days
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        const dayEl = document.createElement('div');
        dayEl.className = 'calendar-day';

        // Check if today
        const checkDate = new Date(date);
        checkDate.setHours(0, 0, 0, 0);

        if (checkDate.getTime() === today.getTime()) {
            dayEl.classList.add('today');
        }

        // Create day structure
        const dayNumber = document.createElement('span');
        dayNumber.className = 'day-number';
        dayNumber.textContent = day;
        dayEl.appendChild(dayNumber);

        const contentContainer = document.createElement('div');
        contentContainer.className = 'calendar-day-content';
        dayEl.appendChild(contentContainer);

        // Find events for this day
        const dayEvents = getEventsForDate(date);

        dayEvents.forEach(tournament => {
            const eventEl = document.createElement('div');

            // Determine class based on level
            let levelClass = 'event-beginner';
            if (tournament.niveau === 'Intermédiaire') levelClass = 'event-intermediate';
            if (tournament.niveau === 'Expert') levelClass = 'event-expert';

            eventEl.className = `calendar-event ${levelClass}`;
            eventEl.textContent = tournament.nom;
            eventEl.title = `${tournament.nom} (${tournament.niveau})`;

            // Click to open modal
            eventEl.onclick = (e) => {
                e.stopPropagation();
                openTournamentModal(tournament.id);
            };

            contentContainer.appendChild(eventEl);
        });

        grid.appendChild(dayEl);
    }

    // Fill remaining cells to complete the grid (optional, for visual consistency)
    const totalCells = firstDay + daysInMonth;
    const remainingCells = totalCells % 7;
    if (remainingCells > 0) {
        for (let i = remainingCells; i < 7; i++) {
            const dayEl = document.createElement('div');
            dayEl.className = 'calendar-day other-month';
            grid.appendChild(dayEl);
        }
    }
}

// Helper: Get events that are active on a specific date
function getEventsForDate(date) {
    // We use the global 'allTournaments' variable from tour.html
    if (typeof allTournaments === 'undefined') return [];

    return allTournaments.filter(t => {
        // 1. Check Date Range
        const start = new Date(t.date_debut);
        const end = new Date(t.date_fin);

        // Normalize times to compare dates only
        start.setHours(0, 0, 0, 0);
        end.setHours(0, 0, 0, 0);
        const checkDate = new Date(date);
        checkDate.setHours(0, 0, 0, 0);

        const isDateMatch = checkDate >= start && checkDate <= end;
        if (!isDateMatch) return false;

        // 2. Check Active Filters (Global variables from tour.html)
        // currentStatusFilter, currentLevelFilter, currentSearchQuery

        // Status Filter
        const status = getStatus(t.date_debut, t.date_fin); // Helper from tour.html
        if (currentStatusFilter !== 'all' && status !== currentStatusFilter) return false;

        // Level Filter
        if (currentLevelFilter !== 'all' && t.niveau !== currentLevelFilter) return false;

        // Search Filter
        if (currentSearchQuery) {
            const search = currentSearchQuery.toLowerCase();
            const name = t.nom.toLowerCase();
            const theme = t.theme.toLowerCase();
            if (!name.includes(search) && !theme.includes(search)) return false;
        }

        return true;
    });
}

console.log('📅 Calendar Module Loaded');