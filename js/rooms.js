// Wait for the page to load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded - initializing scripts');
    
    // Auto-remove messages after 3 seconds
    autoRemoveMessages();
    
    // Modal functionality
    const addRoomBtn = document.getElementById('addRoomBtn');
    const addRoomModal = document.getElementById('addRoomModal');
    const deleteRoomModal = document.getElementById('deleteRoomModal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const cancelBtns = document.querySelectorAll('.cancel-btn');
    const addRoomForm = document.getElementById('addRoomForm');
    const deleteRoomForm = document.getElementById('deleteRoomForm');
    
    // Open add room modal
    if (addRoomBtn) {
        addRoomBtn.addEventListener('click', function() {
            console.log('Add Room button clicked');
            addRoomModal.style.display = 'flex';
        });
    }
    
    // Close modal functions
    function closeAllModals() {
        addRoomModal.style.display = 'none';
        deleteRoomModal.style.display = 'none';
    }
    
    // Close modal events
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });
    
    cancelBtns.forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });
    
    // Close modal when clicking outside
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeAllModals();
            }
        });
    });
    
    // Form validation
    if (addRoomForm) {
        addRoomForm.addEventListener('submit', function(event) {
            const roomCode = document.getElementById('roomCode').value;
            const capacity = document.getElementById('capacity').value;
            const roomType = document.getElementById('roomType').value;
            
            if (!roomCode || !capacity || !roomType) {
                alert('Please fill in all required fields');
                event.preventDefault();
                return;
            }
            
            console.log('Form submitted with values:', {
                roomCode: roomCode,
                capacity: capacity,
                roomType: roomType,
                status: document.getElementById('status').value
            });
        });
    }

    // Initialize filter functionality
    initializeFilters();
});

// Auto-remove messages after 3 seconds
function autoRemoveMessages() {
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');
    
    if (successMessage) {
        setTimeout(() => {
            successMessage.classList.add('alert-fade-out');
            setTimeout(() => {
                successMessage.remove();
            }, 500);
        }, 3000);
    }
    
    if (errorMessage) {
        setTimeout(() => {
            errorMessage.classList.add('alert-fade-out');
            setTimeout(() => {
                errorMessage.remove();
            }, 500);
        }, 3000);
    }
}

// Open delete confirmation modal
function openDeleteModal(roomId, roomCode) {
    document.getElementById('delete_room_id').value = roomId;
    document.getElementById('delete_room_name').textContent = roomCode;
    document.getElementById('deleteRoomModal').style.display = 'flex';
}

// Filter functionality
function initializeFilters() {
    const searchInput = document.getElementById('searchInput');
    const filterButtons = document.querySelectorAll('.filter');
    const roomGrid = document.getElementById('roomGrid');
    
    if (!searchInput || !roomGrid) return;
    
    const originalRoomCards = Array.from(roomGrid.querySelectorAll('.room-card'));
    
    function filterRooms() {
        const activeFilter = document.querySelector('.filter.active').getAttribute('data-filter');
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        roomGrid.innerHTML = '';
        
        let hasVisibleRooms = false;
        
        originalRoomCards.forEach(card => {
            const roomCode = card.getAttribute('data-room-code').toLowerCase();
            const roomType = card.getAttribute('data-room-type').toLowerCase();
            const status = card.getAttribute('data-status');
            
            let matchesFilter = true;
            if (activeFilter !== 'all') {
                matchesFilter = status === activeFilter;
            }
            
            const matchesSearch = !searchTerm || 
                                 roomCode.includes(searchTerm) || 
                                 roomType.includes(searchTerm);
            
            if (matchesFilter && matchesSearch) {
                roomGrid.appendChild(card.cloneNode(true));
                hasVisibleRooms = true;
            }
        });
        
        if (!hasVisibleRooms) {
            const message = document.createElement('div');
            message.className = 'no-rooms-message';
            message.textContent = 'No rooms match your search criteria.';
            roomGrid.appendChild(message);
        }
    }
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            filterRooms();
        });
    });
    
    searchInput.addEventListener('input', filterRooms);
}

// Page refresh for back/forward cache
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});