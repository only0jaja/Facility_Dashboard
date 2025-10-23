    // Modal functions
        function openModal() {
            document.getElementById('addScheduleModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addScheduleModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addScheduleModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        
        function clearFilters() {
            window.location.href = 'schedule.php';
        }
        // Add this script if you keep separate forms
    document.getElementById('searchInput').addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        const allTables = document.querySelectorAll('.schedule table');

        allTables.forEach(table => {
            const rows = table.querySelectorAll('tbody tr');
            let hasVisibleRow = false;

            // Check each row for match
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let matchFound = false;

                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(searchValue)) {
                        matchFound = true;
                    }
                });

                row.style.display = matchFound || searchValue === '' ? '' : 'none';
                if (matchFound) hasVisibleRow = true;
            });

            // Handle the title and table visibility
            const title = table.previousElementSibling;
            if (hasVisibleRow || searchValue === '') {
                table.style.display = '';
                if (title && title.tagName.toLowerCase() === 'h2') {
                    title.style.display = '';
                }
            } else {
                table.style.display = 'none';
                if (title && title.tagName.toLowerCase() === 'h2') {
                    title.style.display = 'none';
                }
            }
        });

        // Optionally show a message if *no tables* have visible rows
        const visibleTables = Array.from(allTables).some(table => table.style.display !== 'none');
        let noResultsMsg = document.querySelector('.no-results-search');

        if (!visibleTables && searchValue !== '') {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.className = 'no-results-search';
                noResultsMsg.textContent = 'No schedule found matching your search.';
                document.querySelector('.schedule').appendChild(noResultsMsg);
            }
        } else if (noResultsMsg) {
            noResultsMsg.remove();
        }
    });
