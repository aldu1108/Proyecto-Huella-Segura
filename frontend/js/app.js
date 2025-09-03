// Mini scripts: calendar + placeholder interactions
document.addEventListener('DOMContentLoaded', () => {
    // Render mini month (current month) - simple
    const cal = document.getElementById('miniCalendar');
    if (cal) {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth();
        const first = new Date(year, month, 1);
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const startDay = first.getDay(); // 0 sun .. 6 sat

        // header: short month
        const header = document.createElement('div');
        header.style.marginBottom = '6px';
        header.style.fontSize = '13px';
        header.style.textAlign = 'center';
        header.textContent = now.toLocaleString('es-ES', { month: 'long', year: 'numeric' });
        cal.appendChild(header);

        // grid container
        const grid = document.createElement('div');
        grid.style.display = 'grid';
        grid.style.gridTemplateColumns = 'repeat(7, 1fr)';
        grid.style.gap = '4px';
        cal.appendChild(grid);

        // filler days
        for (let i = 0; i < startDay; i++) {
            const empty = document.createElement('div');
            empty.className = 'day';
            grid.appendChild(empty);
        }
        for (let d = 1; d <= daysInMonth; d++) {
            const day = document.createElement('div');
            day.className = 'day';
            day.textContent = d;
            const today = new Date();
            if (d === today.getDate() && month === today.getMonth()) {
                day.classList.add('today');
            }
            grid.appendChild(day);
        }
    }

    // bottom nav clicks (placeholder)
    document.querySelectorAll('.bn-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.bn-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });
});
