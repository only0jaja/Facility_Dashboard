// Enhanced delete modal function with faculty restriction
function openDeleteModal(userId, firstName, lastName, role, status) {
    // Check if it's a faculty member with active status
    if (role === 'Faculty' && status === 'Active') {
        alert('Cannot delete faculty member with Active status. Please set status to Inactive first.');
        return;
    }
    
    // If not faculty or faculty is inactive, proceed with deletion modal
    document.getElementById('delete_user_id').value = userId;
    document.getElementById('delete_user_name').textContent = firstName + ' ' + lastName;
    document.getElementById('delete_user_role').textContent = role;
    document.getElementById('delete_user_status').textContent = status;
    
    // Show appropriate warnings
    const facultyWarning = document.getElementById('facultyWarning');
    const foreignKeyWarning = document.getElementById('foreignKeyWarning');
    
    if (role === 'Faculty') {
        facultyWarning.style.display = 'block';
        foreignKeyWarning.style.display = 'block';
    } else {
        facultyWarning.style.display = 'none';
        foreignKeyWarning.style.display = 'block';
    }
    
    document.getElementById('deleteUserModal').style.display = 'block';
}

// Open Edit Modal for full user editing
function openEditModal(userId, firstName, lastName, rfidTag, role, status, courseSectionId) {
    console.log("Opening edit modal for user:", userId, firstName, lastName, rfidTag, role, status, courseSectionId);
    
    // Set form values
    document.getElementById('edit_user_id').value = userId;
    document.getElementById('edit_rfid_tag').value = rfidTag;
    document.getElementById('edit_f_name').value = firstName;
    document.getElementById('edit_l_name').value = lastName;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_status').value = status;
    
    // Set course section if it exists
    if (courseSectionId && courseSectionId !== '') {
        document.getElementById('edit_courseSection_id').value = courseSectionId;
    } else {
        document.getElementById('edit_courseSection_id').value = '';
    }
    
    // Toggle course section field based on role
    toggleCourseSectionEdit();
    
    // Show modal
    document.getElementById('editUserModal').style.display = 'block';
}

// Toggle course section field in edit modal based on role
function toggleCourseSectionEdit() {
    const role = document.getElementById('edit_role').value;
    const courseSectionGroup = document.getElementById('edit_courseSectionGroup');
    const courseRequired = document.getElementById('edit_courseRequired');
    
    console.log("Toggle course section for role:", role);
    
    if (role === 'Student') {
        if (courseSectionGroup) courseSectionGroup.style.display = 'block';
        if (courseRequired) courseRequired.style.display = 'inline';
    } else {
        if (courseSectionGroup) courseSectionGroup.style.display = 'none';
        if (courseRequired) courseRequired.style.display = 'none';
    }
}

// Open Add User Modal with pre-selected role
function openAddUserModal(role) {
    console.log("Opening modal for role:", role);
    // Reset form first so any previous values are cleared, then set the role
    const form = document.querySelector('#addUserModal form');
    if (form) form.reset();
    document.getElementById('selected_role').value = role;
    document.getElementById('modalTitle').textContent = 'Add New ' + role;

    // Show/hide course section based on role
    const courseSectionGroup = document.getElementById('courseSectionGroup');
    const courseRequired = document.getElementById('courseRequired');
    if (role === 'Student') {
        if (courseSectionGroup) courseSectionGroup.style.display = 'block';
        if (courseRequired) courseRequired.style.display = 'inline';
    } else {
        if (courseSectionGroup) courseSectionGroup.style.display = 'none';
        if (courseRequired) courseRequired.style.display = 'none';
    }

    // Show modal
    document.getElementById('addUserModal').style.display = 'block';
}

// Enhanced form validation
function validateUserForm() {
    console.log("Form validation started");
    return true;
}

// Modal functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM loaded - initializing user management");
    
    // Get modals
    const addUserModal = document.getElementById('addUserModal');
    const editUserModal = document.getElementById('editUserModal');
    const deleteUserModal = document.getElementById('deleteUserModal');
    
    // Get close buttons
    const closeButtons = document.querySelectorAll('.close');
    
    // Close modals when clicking cancel buttons
    const cancelBtn = document.getElementById('cancelBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            addUserModal.style.display = 'none';
        });
    }
    
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function() {
            editUserModal.style.display = 'none';
        });
    }
    
    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function() {
            deleteUserModal.style.display = 'none';
        });
    }
    
    // Close modals when clicking X
    closeButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            addUserModal.style.display = 'none';
            editUserModal.style.display = 'none';
            deleteUserModal.style.display = 'none';
        });
    });
    
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === addUserModal) {
            addUserModal.style.display = 'none';
        }
        if (event.target === editUserModal) {
            editUserModal.style.display = 'none';
        }
        if (event.target === deleteUserModal) {
            deleteUserModal.style.display = 'none';
        }
    });
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterUsers();
        });
    }
    
    // Filter functionality
    const statusFilter = document.getElementById('statusFilter');
    const courseFilter = document.getElementById('courseFilter');
    const clearFilters = document.getElementById('clearFilters');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterUsers);
    }
    if (courseFilter) {
        courseFilter.addEventListener('change', filterUsers);
    }
    if (clearFilters) {
        clearFilters.addEventListener('click', function() {
            if (statusFilter) statusFilter.value = '';
            if (courseFilter) courseFilter.value = '';
            if (searchInput) searchInput.value = '';
            filterUsers();
        });
    }
    
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            window.location.href = `users.php?tab=${tabName}`;
        });
    });
    
    // Form submission debugging
    const addUserForm = document.querySelector('#addUserModal form');
    if (addUserForm) {
        addUserForm.addEventListener('submit', function(e) {
            console.log("=== ADD FORM SUBMISSION ===");
            const formData = new FormData(this);
            for (let [key, value] of formData.entries()) {
                console.log(key + ": " + value);
            }
            
            // Get the role from hidden field
            const role = document.getElementById('selected_role').value;
            
            // Validate required fields
            const rfid = document.getElementById('rfid_tag').value;
            const firstName = document.getElementById('f_name').value;
            const lastName = document.getElementById('l_name').value;
            const status = document.getElementById('status').value;
            
            if (!rfid || !firstName || !lastName || !status) {
                alert('Please fill in all required fields.');
                e.preventDefault();
                return false;
            }
            
            // For students only, validate course section
            if (role === 'Student') {
                const courseSection = document.getElementById('courseSection_id').value;
                if (!courseSection) {
                    alert('Course Section is required for Students.');
                    e.preventDefault();
                    return false;
                }
            }
            
            return true;
        });
    }
    
    // Edit form submission validation
    const editUserForm = document.querySelector('#editUserModal form');
    if (editUserForm) {
        editUserForm.addEventListener('submit', function(e) {
            console.log("=== EDIT FORM SUBMISSION ===");
            const formData = new FormData(this);
            for (let [key, value] of formData.entries()) {
                console.log(key + ": " + value);
            }
            
            // Validate required fields
            const rfid = document.getElementById('edit_rfid_tag').value;
            const firstName = document.getElementById('edit_f_name').value;
            const lastName = document.getElementById('edit_l_name').value;
            const role = document.getElementById('edit_role').value;
            const status = document.getElementById('edit_status').value;
            
            if (!rfid || !firstName || !lastName || !role || !status) {
                alert('Please fill in all required fields.');
                e.preventDefault();
                return false;
            }
            
            // For students only, validate course section
            if (role === 'Student') {
                const courseSection = document.getElementById('edit_courseSection_id').value;
                if (!courseSection) {
                    alert('Course Section is required for Students.');
                    e.preventDefault();
                    return false;
                }
            }
            
            return true;
        });
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 5000);
    });

    // Initial filter on page load
    filterUsers();
    
    console.log("User management initialized successfully");
});

// Filter users based on search and filters
function filterUsers() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const statusValue = document.getElementById('statusFilter').value;
    const courseValue = document.getElementById('courseFilter') ? document.getElementById('courseFilter').value : '';
    
    // Get current active tab
    const activeTab = document.querySelector('.tab-content.active');
    if (!activeTab) return;
    
    const rows = activeTab.querySelectorAll('tbody tr');
    let visibleRows = 0;
    
    // Remove existing no results message
    const existingNoResults = activeTab.querySelector('.no-results-message');
    if (existingNoResults) {
        existingNoResults.remove();
    }
    
    rows.forEach(function(row) {
        // Skip the no-results row if it exists
        if (row.classList.contains('no-results') || row.classList.contains('no-results-message')) {
            return;
        }
        
        const cells = row.cells;
        let display = true;
        
        // Check search filter
        if (searchValue) {
            let rowText = '';
            for (let i = 0; i < cells.length; i++) {
                rowText += cells[i].textContent.toLowerCase() + ' ';
            }
            if (!rowText.includes(searchValue)) {
                display = false;
            }
        }
        
        // Check status filter
        if (statusValue && display) {
            let statusText = '';
            
            // Try to find status badge first
            const statusBadge = row.querySelector('.status-active, .status-inactive');
            if (statusBadge) {
                statusText = statusBadge.textContent.trim();
            } else {
                // If no badge found, get status from the correct column index
                const statusIndex = getStatusColumnIndex(activeTab.id);
                if (statusIndex !== -1 && cells[statusIndex]) {
                    statusText = cells[statusIndex].textContent.trim();
                }
            }
            
            // Compare status text with filter value
            if (statusText !== statusValue) {
                display = false;
            }
        }
        
        // Check course filter (only for students and all tabs)
        if (courseValue && display) {
            // Find course section cell
            const courseIndex = getCourseColumnIndex(activeTab.id);
            if (courseIndex !== -1 && cells[courseIndex]) {
                const courseText = cells[courseIndex].textContent.toLowerCase();
                if (!courseText.includes(courseValue.toLowerCase())) {
                    display = false;
                }
            }
        }
        
        row.style.display = display ? '' : 'none';
        if (display) visibleRows++;
    });
    
    // Show no results message if no rows are visible and filters are applied
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const courseFilter = document.getElementById('courseFilter');
    
    const hasFilters = (searchInput && searchInput.value) || 
                      (statusFilter && statusFilter.value) || 
                      (courseFilter && courseFilter.value);
    
    if (visibleRows === 0 && hasFilters) {
        showNoResultsMessage(activeTab);
    }
}

// Helper function to get status column index based on tab
function getStatusColumnIndex(tabId) {
    switch(tabId) {
        case 'students-tab':
            return 6; // Status is 7th column (index 6) in students table
        case 'faculty-tab':
            return 5; // Status is 6th column (index 5) in faculty table
        case 'all-tab':
            return 6; // Status is 7th column (index 6) in all users table
        default:
            return -1;
    }
}

// Helper function to get course column index based on tab
function getCourseColumnIndex(tabId) {
    switch(tabId) {
        case 'students-tab':
            return 4; // Course is 5th column (index 4) in students table
        case 'all-tab':
            return 4; // Course is 5th column (index 4) in all users table
        default:
            return -1; // No course column in faculty table
    }
}

// Show no results message
function showNoResultsMessage(activeTab) {
    const tableBody = activeTab.querySelector('tbody');
    
    // Get number of columns based on the table
    const firstRow = tableBody.querySelector('tr:not(.no-results):not(.no-results-message)');
    const colCount = firstRow ? firstRow.cells.length : 
                    (activeTab.id === 'students-tab' ? 8 : 
                     activeTab.id === 'faculty-tab' ? 7 : 8);
    
    const noResultsMsg = document.createElement('tr');
    noResultsMsg.className = 'no-results-message';
    noResultsMsg.innerHTML = `<td colspan="${colCount}" class="no-results-filtered">
        <div class="no-results-content">
            <i class="fas fa-search"></i>
            <h3>No users found</h3>
            <p>No users match your current search criteria.</p>
            <button class="btn-clear-all" onclick="clearAllFilters()">Clear all filters</button>
        </div>
    </td>`;
    tableBody.appendChild(noResultsMsg);
}

// Clear all filters
function clearAllFilters() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const courseFilter = document.getElementById('courseFilter');
    
    if (searchInput) searchInput.value = '';
    if (statusFilter) statusFilter.value = '';
    if (courseFilter) courseFilter.value = '';
    
    filterUsers();
}

// Export functions for global access
window.filterUsers = filterUsers;
window.openEditModal = openEditModal;
window.openDeleteModal = openDeleteModal;
window.openAddUserModal = openAddUserModal;
window.validateUserForm = validateUserForm;
window.clearAllFilters = clearAllFilters;
window.toggleCourseSectionEdit = toggleCourseSectionEdit;

// Handle page refresh for browser back/forward
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(event) {
    // Ctrl + N to add new user
    if (event.ctrlKey && event.key === 'n') {
        event.preventDefault();
        // Find and click the first available add button based on current tab
        const addButton = document.querySelector('.add-user-btn');
        if (addButton) addButton.click();
    }
    
    // Escape key to close modals
    if (event.key === 'Escape') {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.style.display = 'none';
        });
    }
});