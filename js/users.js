 // Modal functionality
    const addUserModal = document.getElementById('addUserModal');
    const editStatusModal = document.getElementById('editStatusModal');
    const deleteUserModal = document.getElementById('deleteUserModal');
    const addUserBtn = document.getElementById('addUserBtn');
    const closeBtns = document.querySelectorAll('.close');
    const cancelBtn = document.getElementById('cancelBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

    // Open add user modal
    addUserBtn.addEventListener('click', function() {
        addUserModal.style.display = 'block';
    });

    // Open edit status modal
    function openEditModal(userId, firstName, lastName, currentStatus) {
        document.getElementById('edit_user_id').value = userId;
        document.getElementById('edit_user_name').textContent = firstName + ' ' + lastName;
        document.getElementById('edit_current_status').textContent = currentStatus;
        document.getElementById('edit_status').value = currentStatus;
        editStatusModal.style.display = 'block';
    }

    // Open delete user modal
    function openDeleteModal(userId, firstName, lastName) {
        document.getElementById('delete_user_id').value = userId;
        document.getElementById('delete_user_name').textContent = firstName + ' ' + lastName;
        deleteUserModal.style.display = 'block';
    }

    // Close modals
    function closeAllModals() {
        addUserModal.style.display = 'none';
        editStatusModal.style.display = 'none';
        deleteUserModal.style.display = 'none';
    }

    closeBtns.forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });

    cancelBtn.addEventListener('click', closeAllModals);
    cancelEditBtn.addEventListener('click', closeAllModals);
    cancelDeleteBtn.addEventListener('click', closeAllModals);

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === addUserModal || event.target === editStatusModal || event.target === deleteUserModal) {
            closeAllModals();
        }
    });

    // Toggle course section based on role selection
    function toggleCourseSection() {
        const role = document.getElementById('role').value;
        const courseSectionGroup = document.getElementById('courseSectionGroup');
        
        if (role === 'Student') {
            courseSectionGroup.style.display = 'block';
        } else {
            courseSectionGroup.style.display = 'none';
            document.getElementById('courseSection_id').value = '';
        }
    }

    // Initialize course section visibility
    document.addEventListener('DOMContentLoaded', function() {
        toggleCourseSection();
    });

    // Function to filter table rows based on search and filter criteria
    function filterUsers() {
        const searchValue = document.getElementById('searchInput').value.toLowerCase();
        const roleFilter = document.getElementById('roleFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;
        const courseFilter = document.getElementById('courseFilter').value;
        
        const tableBody = document.getElementById('usersTableBody');
        const rows = tableBody.getElementsByTagName('tr');
        
        let visibleRows = 0;
        
        for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let showRow = true;
        
        // Skip the "no users" row if it exists
        if (cells.length === 1 && cells[0].className === 'no-results') {
            rows[i].style.display = 'none';
            continue;
        }
        
        // Search filter - check all cells for matching text
        if (searchValue) {
            let rowContainsText = false;
            for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toLowerCase().includes(searchValue)) {
                rowContainsText = true;
                break;
            }
            }
            if (!rowContainsText) {
            showRow = false;
            }
        }
        
        // Role filter
        if (roleFilter && showRow) {
            const roleCell = cells[5]; // Role is in the 6th column (index 5)
            if (roleCell && roleCell.textContent.trim() !== roleFilter) {
            showRow = false;
            }
        }
        
        // Status filter
        if (statusFilter && showRow) {
            const statusCell = cells[6]; // Status is in the 7th column (index 6)
            if (statusCell && statusCell.textContent.trim() !== statusFilter) {
            showRow = false;
            }
        }
        
        // Course filter
        if (courseFilter && showRow) {
            const courseCell = cells[4]; // Course is in the 5th column (index 4)
            if (courseCell && courseCell.textContent.trim() !== courseFilter) {
            showRow = false;
            }
        }
        
        // Show or hide the row
        rows[i].style.display = showRow ? '' : 'none';
        if (showRow) visibleRows++;
        }
        
        // Show "no results" message if no rows are visible
        const existingNoResults = tableBody.querySelector('.no-results');
        if (existingNoResults) {
        existingNoResults.parentElement.remove();
        }
        
        if (visibleRows === 0) {
        const noResultsRow = document.createElement('tr');
        noResultsRow.innerHTML = '<td colspan="8" class="no-results">No users match your search criteria</td>';
        tableBody.appendChild(noResultsRow);
        }
    }

    // Event listeners for filtering
    document.getElementById('searchInput').addEventListener('input', filterUsers);
    document.getElementById('roleFilter').addEventListener('change', filterUsers);
    document.getElementById('statusFilter').addEventListener('change', filterUsers);
    document.getElementById('courseFilter').addEventListener('change', filterUsers);

    // Clear filters button
    document.getElementById('clearFilters').addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        document.getElementById('roleFilter').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('courseFilter').value = '';
        filterUsers();
    });