// ============================================
// USER MANAGEMENT SYSTEM - TAB FIXED VERSION
// ============================================

// --------------------
// MODAL MANAGER
// --------------------
const ModalManager = {
    activeModal: null,
    
    open(modalId) {
        if (this.activeModal) {
            this.close(this.activeModal);
        }
        this.activeModal = document.getElementById(modalId);
        if (this.activeModal) {
            this.activeModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    },
    
    close(modalElement) {
        if (modalElement) {
            modalElement.style.display = 'none';
        }
        if (modalElement === this.activeModal) {
            this.activeModal = null;
        }
        document.body.style.overflow = '';
    },
    
    closeAll() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.style.display = 'none';
        });
        this.activeModal = null;
        document.body.style.overflow = '';
    }
};

// --------------------
// TAB MANAGEMENT
// --------------------
function initializeTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const tabName = this.getAttribute('data-tab');
            
            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
            
            // Switch tabs visually
            switchTab(tabName);
        });
    });
    
    // Handle browser back/forward navigation
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tabName = urlParams.get('tab') || 'students';
        switchTab(tabName);
    });
}

function switchTab(tabName) {
    // Remove active class from all tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Add active class to selected tab
    const activeTabBtn = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
    const activeTabContent = document.getElementById(`${tabName}-tab`);
    
    if (activeTabBtn) {
        activeTabBtn.classList.add('active');
    }
    
    if (activeTabContent) {
        activeTabContent.classList.add('active');
    }
    
    // Update course filter visibility based on tab
    updateFilterVisibility(tabName);
    
    // Refresh filters for the new tab
    filterUsers();
}

function updateFilterVisibility(tabName) {
    const courseFilter = document.getElementById('courseFilter');
    if (courseFilter) {
        if (tabName === 'students' || tabName === 'all') {
            courseFilter.style.display = 'block';
        } else {
            courseFilter.style.display = 'none';
            courseFilter.value = '';
        }
    }
}

// --------------------
// RFID POLLING SYSTEM
// --------------------
let lastUID = '';
let rfidPollingInterval;

function startRFIDPolling() {
    clearInterval(rfidPollingInterval);
    
    rfidPollingInterval = setInterval(async () => {
        try {
            const response = await fetch('http://localhost:5000/api/latest-rfid');
            
            if (response.ok) {
                const data = await response.json();
                
                if (data.uid && data.uid !== lastUID) {
                    const rfidInput = document.getElementById('rfid_tag');
                    const editRfidInput = document.getElementById('edit_rfid_tag');
                    
                    // Only auto-fill if modal is open and input is not disabled
                    if (rfidInput && document.getElementById('addUserModal').style.display === 'block') {
                        rfidInput.value = data.uid;
                    }
                    if (editRfidInput && document.getElementById('editUserModal').style.display === 'block') {
                        editRfidInput.value = data.uid;
                    }
                    
                    lastUID = data.uid;
                }
            }
        } catch (error) {
            console.warn('RFID service temporarily unavailable');
        }
    }, 1000);
}

// --------------------
// MODAL FUNCTIONS
// --------------------
function openDeleteModal(userId, firstName, lastName, role, status) {
    // Check if it's a faculty member with active status
    if (role === 'Faculty' && status === 'Active') {
        const userFullName = `${firstName} ${lastName}`;
        const shouldDeactivate = confirm(
            `Cannot delete faculty member with Active status.\n\n` +
            `User: ${userFullName}\n` +
            `Do you want to set this user to Inactive instead?`
        );
        
        if (shouldDeactivate) {
            // Find and click the edit button for this user
            const editButton = document.querySelector(`button[onclick*="openEditModal(${userId}"]`);
            if (editButton) {
                editButton.click();
            }
            return;
        }
        return;
    }
    
    // Set up delete modal
    document.getElementById('delete_user_id').value = userId;
    document.getElementById('delete_user_name').textContent = `${firstName} ${lastName}`;
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
    
    ModalManager.open('deleteUserModal');
}

function openEditModal(userId, firstName, lastName, rfidTag, role, status, courseSectionId) {
    console.log("Opening edit modal for user:", { userId, firstName, lastName, rfidTag, role, status, courseSectionId });
    
    // Set form values
    document.getElementById('edit_user_id').value = userId;
    document.getElementById('edit_rfid_tag').value = rfidTag || '';
    document.getElementById('edit_f_name').value = firstName;
    document.getElementById('edit_l_name').value = lastName;
    document.getElementById('edit_role').value = role;
    document.getElementById('edit_status').value = status;
    
    // Set course section if it exists and user is a student
    const courseSectionField = document.getElementById('edit_courseSection_id');
    if (role === 'Student' && courseSectionId && courseSectionId !== '' && courseSectionId !== 'null' && courseSectionId !== null) {
        courseSectionField.value = courseSectionId;
    } else {
        courseSectionField.value = '';
    }
    
    // Toggle course section field based on role
    toggleCourseSectionEdit();
    
    ModalManager.open('editUserModal');
}

function openAddUserModal(role = 'Student') {
    console.log("Opening modal for role:", role);
    
    // Reset form
    const form = document.querySelector('#addUserModal form');
    if (form) {
        form.reset();
    }
    
    // Set role - PHP expects this in a hidden field AND the visible dropdown
    document.getElementById('selected_role').value = role; // Hidden field
    document.getElementById('role').value = role; // Visible dropdown
    
    // Update modal title
    const modalTitle = document.getElementById('modalTitle');
    if (modalTitle) {
        modalTitle.textContent = `Add New ${role}`;
    }
    
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
    
    ModalManager.open('addUserModal');
}

// --------------------
// FORM HANDLING
// --------------------
function toggleCourseSection() {
    const role = document.getElementById('role')?.value;
    const courseSectionGroup = document.getElementById('courseSectionGroup');
    
    if (role === 'Student') {
        courseSectionGroup.style.display = 'block';
    } else {
        courseSectionGroup.style.display = 'none';
        document.getElementById('courseSection_id').value = '';
    }
}

function toggleCourseSectionEdit() {
    const role = document.getElementById('edit_role').value;
    const courseSectionGroup = document.getElementById('edit_courseSectionGroup');
    
    if (role === 'Student') {
        courseSectionGroup.style.display = 'block';
    } else {
        courseSectionGroup.style.display = 'none';
        document.getElementById('edit_courseSection_id').value = '';
    }
}

function validateForm(formElement) {
    let isValid = true;
    const requiredFields = formElement.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = '#dc3545';
        } else {
            field.style.borderColor = '';
        }
    });
    
    // Special validation for students
    if (formElement.id === 'addUserForm' || formElement.id === 'editUserForm') {
        const role = formElement.querySelector('[name="role"]')?.value;
        if (role === 'Student') {
            const courseSection = formElement.querySelector('[name="courseSection_id"]');
            if (courseSection && !courseSection.value.trim()) {
                courseSection.style.borderColor = '#dc3545';
                isValid = false;
            } else if (courseSection) {
                courseSection.style.borderColor = '';
            }
        }
    }
    
    return isValid;
}

// --------------------
// SEARCH & FILTER SYSTEM
// --------------------
let filterTimeout;
const filterDelay = 300;

function filterUsers() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(performFilter, filterDelay);
}

function performFilter() {
    const searchValue = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const statusValue = document.getElementById('statusFilter')?.value || '';
    const courseValue = document.getElementById('courseFilter')?.value || '';
    
    // Get current active tab
    const activeTab = document.querySelector('.tab-content.active');
    if (!activeTab) return;
    
    const rows = activeTab.querySelectorAll('tbody tr');
    let visibleRows = 0;
    
    // Remove existing no results message
    const existingNoResults = activeTab.querySelector('.no-results-message');
    if (existingNoResults) existingNoResults.remove();
    
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
            
            if (statusText !== statusValue) {
                display = false;
            }
        }
        
        // Check course filter (only for students and all tabs)
        if (courseValue && display) {
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
    
    // Show no results message if needed
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const courseFilter = document.getElementById('courseFilter');
    
    const hasFilters = (searchInput && searchInput.value) || 
                      (statusFilter && statusFilter.value) || 
                      (courseFilter && courseFilter && courseFilter.value);
    
    if (visibleRows === 0 && hasFilters) {
        showNoResultsMessage(activeTab);
    }
}

function getStatusColumnIndex(tabId) {
    switch(tabId) {
        case 'students-tab': return 5; // Status is 6th column (0-indexed 5)
        case 'faculty-tab': return 4;  // Status is 5th column
        case 'all-tab': return 6;      // Status is 7th column
        default: return -1;
    }
}

function getCourseColumnIndex(tabId) {
    switch(tabId) {
        case 'students-tab': return 4; // Course is 5th column
        case 'all-tab': return 4;      // Course is 5th column
        default: return -1;
    }
}

function showNoResultsMessage(activeTab) {
    const tableBody = activeTab.querySelector('tbody');
    if (!tableBody) return;
    
    const firstRow = tableBody.querySelector('tr:not(.no-results):not(.no-results-message)');
    const colCount = firstRow ? firstRow.cells.length : 
                    (activeTab.id === 'students-tab' ? 7 : 
                     activeTab.id === 'faculty-tab' ? 6 : 8);
    
    const noResultsMsg = document.createElement('tr');
    noResultsMsg.className = 'no-results-message';
    noResultsMsg.innerHTML = `
        <td colspan="${colCount}" class="no-results-filtered">
            <div class="no-results-content">
                <i class="fas fa-search"></i>
                <h3>No users found</h3>
                <p>No users match your current search criteria.</p>
                <button class="btn-clear-all" onclick="clearAllFilters()">Clear all filters</button>
            </div>
        </td>
    `;
    tableBody.appendChild(noResultsMsg);
}

function clearAllFilters() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const courseFilter = document.getElementById('courseFilter');
    
    if (searchInput) searchInput.value = '';
    if (statusFilter) statusFilter.value = '';
    if (courseFilter) courseFilter.value = '';
    
    filterUsers();
}

// --------------------
// EVENT LISTENERS SETUP
// --------------------
document.addEventListener('DOMContentLoaded', function() {
    console.log("User Management System Initializing...");
    
    // Initialize tabs
    initializeTabs();
    
    // Start RFID polling
    startRFIDPolling();
    
    // Setup modal close handlers
    document.addEventListener('click', function(event) {
        // Close modal when clicking X
        if (event.target.classList.contains('close')) {
            ModalManager.closeAll();
            return;
        }
        
        // Close modal when clicking cancel button
        if (event.target.classList.contains('btn-secondary') || 
            event.target.id === 'cancelBtn' || 
            event.target.id === 'cancelEditBtn' || 
            event.target.id === 'cancelDeleteBtn') {
            ModalManager.closeAll();
            return;
        }
        
        // Close modal when clicking outside
        if (event.target.classList.contains('modal')) {
            ModalManager.close(event.target);
            return;
        }
    });
    
    // Search and filter event listeners
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const courseFilter = document.getElementById('courseFilter');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterUsers);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterUsers);
    }
    if (courseFilter) {
        courseFilter.addEventListener('change', filterUsers);
    }
    
    // Clear filters button
    const clearFilters = document.getElementById('clearFilters');
    if (clearFilters) {
        clearFilters.addEventListener('click', clearAllFilters);
    }
    
    // Add User Button
    const addUserBtn = document.getElementById('addUserBtn');
    if (addUserBtn) {
        addUserBtn.addEventListener('click', function() {
            // Get current tab to determine default role
            const currentTab = document.querySelector('.tab-btn.active')?.getAttribute('data-tab');
            let defaultRole = 'Student';
            if (currentTab === 'faculty') {
                defaultRole = 'Faculty';
            }
            openAddUserModal(defaultRole);
        });
    }
    
    // Add User Form handling
    const addUserForm = document.getElementById('addUserForm');
    if (addUserForm) {
        // Add hidden field for role if it doesn't exist
        if (!document.getElementById('selected_role')) {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.id = 'selected_role';
            hiddenInput.name = 'selected_role';
            hiddenInput.value = 'Student';
            addUserForm.appendChild(hiddenInput);
        }
        
        addUserForm.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            
            // Update hidden role field with selected role
            const roleSelect = document.getElementById('role');
            const hiddenRole = document.getElementById('selected_role');
            if (roleSelect && hiddenRole) {
                hiddenRole.value = roleSelect.value;
            }
            
            return true; // Allow form submission to PHP
        });
    }
    
    // Edit User Form handling
    const editUserForm = document.getElementById('editUserForm');
    if (editUserForm) {
        editUserForm.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
            return true; // Allow form submission to PHP
        });
    }
    
    // Role change listeners for course section toggling
    const roleSelect = document.getElementById('role');
    if (roleSelect) {
        roleSelect.addEventListener('change', toggleCourseSection);
        toggleCourseSection(); // Initial call
    }
    
    const editRoleSelect = document.getElementById('edit_role');
    if (editRoleSelect) {
        editRoleSelect.addEventListener('change', toggleCourseSectionEdit);
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.style.opacity = '0';
                setTimeout(() => {
                    if (alert.parentNode) alert.remove();
                }, 300);
            }
        }, 5000);
    });
    
    // Initial filter on page load
    filterUsers();
    
    console.log("User Management System Initialized Successfully");
});

// --------------------
// KEYBOARD SHORTCUTS
// --------------------
document.addEventListener('keydown', function(event) {
    // Ctrl+Shift+N to add new user
    if (event.ctrlKey && event.shiftKey && event.key === 'N') {
        event.preventDefault();
        document.getElementById('addUserBtn')?.click();
    }
    
    // Escape key to close modals
    if (event.key === 'Escape') {
        ModalManager.closeAll();
    }
});

// --------------------
// GLOBAL EXPORTS
// --------------------
window.filterUsers = filterUsers;
window.openEditModal = openEditModal;
window.openDeleteModal = openDeleteModal;
window.openAddUserModal = openAddUserModal;
window.clearAllFilters = clearAllFilters;
window.toggleCourseSection = toggleCourseSection;
window.toggleCourseSectionEdit = toggleCourseSectionEdit;