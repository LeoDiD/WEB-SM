document.addEventListener('DOMContentLoaded', function() {
    const userContainer = document.getElementById('userContainer');
    const dropdown = document.getElementById('userDropdown');
    const dropdownIcon = document.getElementById('dropdownIcon');

    // Toggle dropdown when clicking the user container
    userContainer.addEventListener('click', function(e) {
        // Prevent this from closing immediately when clicking inside
        e.stopPropagation();
        
        // Toggle dropdown visibility
        dropdown.classList.toggle('show');
        
        // Rotate the chevron icon
        dropdownIcon.classList.toggle('fa-chevron-up');
        dropdownIcon.classList.toggle('fa-chevron-down');
    });

    // Close dropdown when clicking anywhere else
    document.addEventListener('click', function(e) {
        if (!userContainer.contains(e.target)) {
            dropdown.classList.remove('show');
            dropdownIcon.classList.remove('fa-chevron-up');
            dropdownIcon.classList.add('fa-chevron-down');
        }
    });

    // Prevent dropdown from closing when clicking inside it
    dropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});