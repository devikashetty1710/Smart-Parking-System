// Initialize user management
document.addEventListener('DOMContentLoaded', () => {
    initializeUserManagement();
});

// Initialize user management system
function initializeUserManagement() {
    // Check if user is admin
    const currentUser = JSON.parse(localStorage.getItem('currentUser'));
    if (!currentUser || currentUser.role !== 'admin') {
        window.location.href = '../index.html';
        return;
    }

    // Update admin name
    document.getElementById('adminName').textContent = currentUser.name;

    // Load users
    loadUsers();

    // Initialize search
    const searchInput = document.getElementById('searchUser');
    searchInput.addEventListener('input', handleSearch);
}

// Load users from localStorage
function loadUsers() {
    const users = JSON.parse(localStorage.getItem('users')) || [];
    const usersList = document.getElementById('usersList');
    usersList.innerHTML = '';

    if (users.length === 0) {
        usersList.innerHTML = '<p class="no-data">No users found</p>';
        return;
    }

    users.forEach(user => {
        const userCard = document.createElement('div');
        userCard.className = 'user-card';
        userCard.innerHTML = `
            <div class="user-info">
                <h3>${user.name}</h3>
                <p><i class="fas fa-envelope"></i> ${user.email}</p>
                <p><i class="fas fa-phone"></i> ${user.phone}</p>
                <p><i class="fas fa-user-tag"></i> ${user.role}</p>
            </div>
            <div class="user-actions">
                <button onclick="editUser('${user.email}')" class="action-btn edit">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button onclick="deleteUser('${user.email}')" class="action-btn delete">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        `;
        usersList.appendChild(userCard);
    });
}

// Handle adding new user
function handleAddUser(event) {
    event.preventDefault();

    const name = document.getElementById('userName').value;
    const email = document.getElementById('userEmail').value;
    const phone = document.getElementById('userPhone').value;
    const role = document.getElementById('userRole').value;
    const password = document.getElementById('userPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // Validate passwords match
    if (password !== confirmPassword) {
        showError('Passwords do not match');
        return;
    }

    // Check if user already exists
    const users = JSON.parse(localStorage.getItem('users')) || [];
    if (users.some(user => user.email === email)) {
        showError('User with this email already exists');
        return;
    }

    // Create new user
    const newUser = {
        name,
        email,
        phone,
        role,
        password // In a real app, this should be hashed
    };

    users.push(newUser);
    localStorage.setItem('users', JSON.stringify(users));

    // Clear form
    event.target.reset();
    showSuccess('User added successfully');
    loadUsers();
}

// Edit user
function editUser(email) {
    const users = JSON.parse(localStorage.getItem('users')) || [];
    const user = users.find(u => u.email === email);
    
    if (!user) {
        showError('User not found');
        return;
    }

    // Pre-fill form
    document.getElementById('userName').value = user.name;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userPhone').value = user.phone;
    document.getElementById('userRole').value = user.role;
    
    // Change form to edit mode
    const form = document.getElementById('addUserForm');
    form.onsubmit = (event) => {
        event.preventDefault();
        updateUser(email);
    };
    form.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-save"></i> Update User';
}

// Update user
function updateUser(oldEmail) {
    const name = document.getElementById('userName').value;
    const email = document.getElementById('userEmail').value;
    const phone = document.getElementById('userPhone').value;
    const role = document.getElementById('userRole').value;
    const password = document.getElementById('userPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // Validate passwords match if they're being changed
    if (password && password !== confirmPassword) {
        showError('Passwords do not match');
        return;
    }

    const users = JSON.parse(localStorage.getItem('users')) || [];
    const userIndex = users.findIndex(u => u.email === oldEmail);

    if (userIndex === -1) {
        showError('User not found');
        return;
    }

    // Update user
    users[userIndex] = {
        ...users[userIndex],
        name,
        email,
        phone,
        role,
        password: password || users[userIndex].password
    };

    localStorage.setItem('users', JSON.stringify(users));

    // Reset form
    const form = document.getElementById('addUserForm');
    form.reset();
    form.onsubmit = (event) => {
        event.preventDefault();
        handleAddUser(event);
    };
    form.querySelector('button[type="submit"]').innerHTML = '<i class="fas fa-user-plus"></i> Add User';

    showSuccess('User updated successfully');
    loadUsers();
}

// Delete user
function deleteUser(email) {
    if (!confirm('Are you sure you want to delete this user?')) {
        return;
    }

    const users = JSON.parse(localStorage.getItem('users')) || [];
    const updatedUsers = users.filter(user => user.email !== email);

    if (users.length === updatedUsers.length) {
        showError('User not found');
        return;
    }

    localStorage.setItem('users', JSON.stringify(updatedUsers));
    showSuccess('User deleted successfully');
    loadUsers();
}

// Handle search
function handleSearch(event) {
    const searchTerm = event.target.value.toLowerCase();
    const users = JSON.parse(localStorage.getItem('users')) || [];
    const filteredUsers = users.filter(user => 
        user.name.toLowerCase().includes(searchTerm) ||
        user.email.toLowerCase().includes(searchTerm) ||
        user.phone.includes(searchTerm) ||
        user.role.toLowerCase().includes(searchTerm)
    );

    const usersList = document.getElementById('usersList');
    usersList.innerHTML = '';

    if (filteredUsers.length === 0) {
        usersList.innerHTML = '<p class="no-data">No users found</p>';
        return;
    }

    filteredUsers.forEach(user => {
        const userCard = document.createElement('div');
        userCard.className = 'user-card';
        userCard.innerHTML = `
            <div class="user-info">
                <h3>${user.name}</h3>
                <p><i class="fas fa-envelope"></i> ${user.email}</p>
                <p><i class="fas fa-phone"></i> ${user.phone}</p>
                <p><i class="fas fa-user-tag"></i> ${user.role}</p>
            </div>
            <div class="user-actions">
                <button onclick="editUser('${user.email}')" class="action-btn edit">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button onclick="deleteUser('${user.email}')" class="action-btn delete">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        `;
        usersList.appendChild(userCard);
    });
}

// Show error message
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'message error';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(errorDiv);
    setTimeout(() => errorDiv.remove(), 3000);
}

// Show success message
function showSuccess(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'message success';
    successDiv.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(successDiv);
    setTimeout(() => successDiv.remove(), 3000);
}

// Add some styles
const style = document.createElement('style');
style.textContent = `
    .user-management-content {
        padding: 20px;
    }

    .management-section {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
    }

    .form-group {
        flex: 1;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #666;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .action-btn {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .action-btn.edit {
        background: #4CAF50;
        color: white;
    }

    .action-btn.delete {
        background: #f44336;
        color: white;
    }

    .user-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    .user-info h3 {
        margin: 0 0 10px 0;
        color: #333;
    }

    .user-info p {
        margin: 5px 0;
        color: #666;
    }

    .user-actions {
        display: flex;
        gap: 10px;
    }

    .no-data {
        text-align: center;
        color: #666;
        padding: 20px;
    }

    .message {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease-out;
    }

    .message.error {
        background: #ffebee;
        color: #c62828;
        border: 1px solid #ef9a9a;
    }

    .message.success {
        background: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #a5d6a7;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style); 