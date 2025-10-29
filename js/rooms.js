// Simple Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded - initializing modal');
    
    // Get elements
    const addRoomBtn = document.getElementById('addRoomBtn');
    const addRoomModal = document.getElementById('addRoomModal');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.querySelector('.cancel-btn');
    const addRoomForm = document.getElementById('addRoomForm');
    
    console.log('Elements found:', {
        addRoomBtn: !!addRoomBtn,
        addRoomModal: !!addRoomModal,
        closeModalBtn: !!closeModalBtn,
        cancelBtn: !!cancelBtn,
        addRoomForm: !!addRoomForm
    });
    
    // OPEN MODAL - Simple and direct
    if (addRoomBtn) {
        addRoomBtn.addEventListener('click', function() {
            console.log('Add Room button clicked!');
            if (addRoomModal) {
                console.log('Opening modal...');
                addRoomModal.style.display = 'flex';
                addRoomModal.classList.add('active');
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            }
        });
    } else {
        console.error('Add Room button not found!');
    }
    
    // CLOSE MODAL function
    function closeModal() {
        console.log('Closing modal');
        if (addRoomModal) {
            addRoomModal.style.display = 'none';
            addRoomModal.classList.remove('active');
            document.body.style.overflow = ''; // Restore scrolling
            
            // Reset form
            if (addRoomForm) {
                addRoomForm.reset();
            }
        }
    }
    
    // Close modal events
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }
    
    // Close modal when clicking outside
    if (addRoomModal) {
        addRoomModal.addEventListener('click', function(e) {
            if (e.target === addRoomModal) {
                closeModal();
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && addRoomModal && addRoomModal.classList.contains('active')) {
            closeModal();
        }
    });
    
    // Form submission
    if (addRoomForm) {
        addRoomForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');
            
            // Simple validation
            const roomCode = document.getElementById('roomCode').value;
            const capacity = document.getElementById('capacity').value;
            const roomType = document.getElementById('roomType').value;
            
            if (!roomCode || !capacity || !roomType) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Show success message
            alert('Room added successfully!');
            
            // Close modal
            closeModal();
            
            // In real implementation, you would submit via AJAX here
            // submitRoomForm({ roomCode, capacity, roomType });
        });
    }
    
    // Initialize filter and search functionality
    initializeFilterAndSearch();
});

// Filter and Search functionality
function initializeFilterAndSearch() {
    const searchInput = document.getElementById('searchInput');
    const filterButtons = document.querySelectorAll('.filter');
    const roomGrid = document.getElementById('roomGrid');
    
    // Check if elements exist
    if (!searchInput || !roomGrid || filterButtons.length === 0) {
        console.log('Filter/search elements not found, skipping initialization');
        return;
    }
    
    console.log('Initializing filter and search');
    
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

// AJAX function for form submission (for future use)
function submitRoomForm(roomData) {
    // Create FormData for AJAX submission
    const formData = new FormData();
    formData.append('roomCode', roomData.roomCode);
    formData.append('capacity', roomData.capacity);
    formData.append('roomType', roomData.roomType);
    formData.append('status', roomData.status);
    formData.append('action', 'add_room');

    // Make AJAX request
    fetch('ajax/add_room.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Room added successfully!');
            // Reload the page to show the new room
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}