// Modal functionality
document.addEventListener('DOMContentLoaded', function() {
    // Get modals
    const addUserModal = document.getElementById('addUserModal');
    const editStatusModal = document.getElementById('editStatusModal');
    const deleteUserModal = document.getElementById('deleteUserModal');
    
    // Get buttons that open modals
    const addUserBtn = document.getElementById('addUserBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    
    // Get close buttons
    const closeButtons = document.querySelectorAll('.close');
    
    // Open Add User Modal
    if (addUserBtn) {
        addUserBtn.addEventListener('click', function() {
            addUserModal.style.display = 'block';
        });
    }
    
    // Close modals when clicking cancel buttons
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            addUserModal.style.display = 'none';
        });
    }
    
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function() {
            editStatusModal.style.display = 'none';
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
            editStatusModal.style.display = 'none';
            deleteUserModal.style.display = 'none';
        });
    });
    
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === addUserModal) {
            addUserModal.style.display = 'none';
        }
        if (event.target === editStatusModal) {
            editStatusModal.style.display = 'none';
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
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const courseFilter = document.getElementById('courseFilter');
    const clearFilters = document.getElementById('clearFilters');
    
    if (roleFilter) {
        roleFilter.addEventListener('change', filterUsers);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterUsers);
    }
    if (courseFilter) {
        courseFilter.addEventListener('change', filterUsers);
    }
    if (clearFilters) {
        clearFilters.addEventListener('click', function() {
            if (roleFilter) roleFilter.value = '';
            if (statusFilter) statusFilter.value = '';
            if (courseFilter) courseFilter.value = '';
            if (searchInput) searchInput.value = '';
            filterUsers();
        });
    }
});

// Filter users based on search and filters
function filterUsers() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const roleValue = document.getElementById('roleFilter').value;
    const statusValue = document.getElementById('statusFilter').value;
    const courseValue = document.getElementById('courseFilter').value;
    
    const rows = document.querySelectorAll('#usersTableBody tr');
    
    rows.forEach(function(row) {
        const userId = row.cells[0].textContent.toLowerCase();
        const rfidTag = row.cells[1].textContent.toLowerCase();
        const firstName = row.cells[2].textContent.toLowerCase();
        const lastName = row.cells[3].textContent.toLowerCase();
        const courseSection = row.cells[4].textContent.toLowerCase();
        const role = row.cells[5].querySelector('span').textContent;
        const status = row.cells[6].querySelector('span').textContent;
        
        const matchesSearch = !searchValue || 
            userId.includes(searchValue) ||
            rfidTag.includes(searchValue) ||
            firstName.includes(searchValue) ||
            lastName.includes(searchValue) ||
            courseSection.includes(searchValue);
            
        const matchesRole = !roleValue || role === roleValue;
        const matchesStatus = !statusValue || status === statusValue;
        const matchesCourse = !courseValue || courseSection.includes(courseValue.toLowerCase());
        
        if (matchesSearch && matchesRole && matchesStatus && matchesCourse) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Open Edit Modal
function openEditModal(userId, firstName, lastName, currentStatus) {
    document.getElementById('edit_user_id').value = userId;
    document.getElementById('edit_user_name').textContent = firstName + ' ' + lastName;
    document.getElementById('edit_current_status').textContent = currentStatus;
    document.getElementById('edit_status').value = currentStatus;
    document.getElementById('editStatusModal').style.display = 'block';
}

// Open Delete Modal with faculty restriction
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
    
    // Show warning for faculty members
    const facultyWarning = document.getElementById('facultyWarning');
    if (role === 'Faculty') {
        facultyWarning.style.display = 'block';
    } else {
        facultyWarning.style.display = 'none';
    }
    
    document.getElementById('deleteUserModal').style.display = 'block';
}

// Toggle course section based on role selection
function toggleCourseSection() {
    const role = document.getElementById('role').value;
    const courseSectionGroup = document.getElementById('courseSectionGroup');
    
    if (role === 'Student') {
        courseSectionGroup.style.display = 'block';
    } else {
        courseSectionGroup.style.display = 'none';
    }
}

// Export functions for global access
window.filterUsers = filterUsers;
window.openEditModal = openEditModal;
window.openDeleteModal = openDeleteModal;
window.toggleCourseSection = toggleCourseSection;