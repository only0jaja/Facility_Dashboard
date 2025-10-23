// Filter and Search functionality
function initializeFilterAndSearch() {
    const searchInput = document.getElementById('searchInput');
    const filterButtons = document.querySelectorAll('.filter');
    const roomGrid = document.getElementById('roomGrid');
    
    // Store original room cards for filtering
    let originalRoomCards = Array.from(roomGrid.querySelectorAll('.room-card'));

    function applyFilters() {
        const activeFilter = document.querySelector('.filter.active').dataset.filter;
        const searchTerm = searchInput.value.toLowerCase().trim();

        // Clear the grid
        roomGrid.innerHTML = '';

        let hasVisibleRooms = false;

        // Filter rooms
        originalRoomCards.forEach(roomCard => {
            const roomCode = roomCard.dataset.roomCode.toLowerCase();
            const roomType = roomCard.dataset.roomType.toLowerCase();
            const status = roomCard.dataset.status;

            // Apply status filter
            let statusMatch = true;
            if (activeFilter !== 'all') {
                statusMatch = status === activeFilter;
            }

            // Apply search filter
            let searchMatch = true;
            if (searchTerm) {
                searchMatch = roomCode.includes(searchTerm) || roomType.includes(searchTerm);
            }

            // Show room if it matches both filters
            if (statusMatch && searchMatch) {
                roomGrid.appendChild(roomCard.cloneNode(true));
                hasVisibleRooms = true;
            }
        });

        // Show message if no rooms match
        if (!hasVisibleRooms) {
            const noRoomsMessage = document.createElement('div');
            noRoomsMessage.className = 'no-rooms-message';
            noRoomsMessage.textContent = 'No rooms match your search criteria.';
            roomGrid.appendChild(noRoomsMessage);
        }
    }

    // Event listeners for filter buttons
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active state
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Apply filters
            applyFilters();
        });
    });

    // Event listener for search input
    searchInput.addEventListener('input', applyFilters);

    // Store original room cards for later use
    window.originalRoomCards = originalRoomCards;
}

// Modified loadRooms function to work with filters
function loadRooms() {
    fetch('ajax/fetch_room.php')
        .then(response => response.text())
        .then(data => {
            const roomGrid = document.querySelector('.room-grid');
            
            // Get current filter and search state
            const activeFilter = document.querySelector('.filter.active').dataset.filter;
            const searchTerm = document.getElementById('searchInput').value;
            
            // Create a temporary div to parse the HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = data;
            
            // Extract room cards and add data attributes
            const roomCards = tempDiv.querySelectorAll('.room-card');
            roomCards.forEach(card => {
                const roomCode = card.querySelector('h4').textContent;
                const statusElement = card.querySelector('.status');
                const status = statusElement.textContent.toLowerCase();
                const statusClass = status === 'occupied' ? 'occupied' : 'available';
                const roomType = card.querySelector('p:nth-child(3)').textContent.replace('🏫 Type: ', '');
                
                // Add data attributes
                card.setAttribute('data-room-code', roomCode);
                card.setAttribute('data-room-type', roomType);
                card.setAttribute('data-status', statusClass);
            });
            
            // Update the roomGrid with new data
            roomGrid.innerHTML = tempDiv.innerHTML;
            
            // Reinitialize filter and search functionality
            initializeFilterAndSearch();
            
            // Reapply current filters
            if (activeFilter !== 'all' || searchTerm) {
                setTimeout(() => {
                    document.querySelector(`.filter[data-filter="${activeFilter}"]`).click();
                    document.getElementById('searchInput').value = searchTerm;
                    document.getElementById('searchInput').dispatchEvent(new Event('input'));
                }, 100);
            }
            
            // Add fade effect
            roomGrid.classList.add('fade');
            setTimeout(() => roomGrid.classList.remove('fade'), 400);
        })
        .catch(err => console.error('Error loading rooms:', err));
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeFilterAndSearch();
    
    // Load rooms initially
    loadRooms();
});